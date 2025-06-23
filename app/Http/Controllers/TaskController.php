<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;
use App\Models\TaskComment;      // Add this
use App\Models\TaskAttachment;  // Add this
use App\Models\ActivityLog;     // Add this
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Storage; // Add this
use Illuminate\Support\Facades\Auth;    // Add this
use App\Services\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Notification;
// Impor semua kelas notifikasi yang baru dibuat
use App\Notifications\NewTaskAssignedNotification;
use App\Notifications\TaskUpdatedNotification;
use App\Notifications\NewCommentOnTaskNotification;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Database\Eloquent\Builder;

class TaskController extends Controller
{
    // Kanban View (Load necessary data)
    // Update this method in TaskController.php
public function kanban(Project $project)
{
    $this->authorize('viewKanban', $project);
    // Eager load relationships needed for cards and modals + counts
    $tasks = $project->tasks()
                    ->with([
                        'difficultyLevel:id,name,value,color', // Include color
                        'priorityLevel:id,name,value,color',   // Include color
                        'assignedUser:id,name',
                    ])
                    ->withCount('attachments')
                    ->orderBy('order')
                    ->get();

    // Get users for this project
    $users = $this->getProjectUsers($project);

    // Get levels specific to this project with ordering by display_order (not value)
    $difficultyLevels = $project->difficultyLevels()
                                ->orderBy('display_order')
                                ->get(['id', 'name', 'value', 'color', 'display_order']);
                                
    $priorityLevels = $project->priorityLevels()
                              ->orderBy('display_order')
                              ->get(['id', 'name', 'value', 'color', 'display_order']);

    return view('kanban.index', compact('project', 'tasks', 'users', 'difficultyLevels', 'priorityLevels'));
}

    // NEW: Get Full Task Details for Modal
    public function show(Task $task)
    {
         // Authorization: Check if user can view this task (e.g., part of project)
         $this->authorize('view', $task); // Example policy check

         $task->load([
             'comments.user:id,name', // Load comments and the user who made them
             'attachments.user:id,name', // Load attachments and the user who uploaded
             'activityLogs.user:id,name', // Load history and the user involved
             'difficultyLevel:id,name,value',
             'priorityLevel:id,name,value',
             'assignedUser:id,name',
             'project:id' // Load project ID if needed later
         ]);

         return response()->json([
             'success' => true,
             'task' => $task,
             // Separate data for easier consumption in JS (optional)
             'comments' => $task->comments,
             'attachments' => $task->attachments,
             'history' => $task->activityLogs, // Use the loaded relationship
         ]);
    }


    // Store New Task
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'difficulty_level_id' => 'nullable|exists:difficulty_levels,id',
            'priority_level_id' => 'nullable|exists:priority_levels,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'progress_percentage' => 'sometimes|integer|min:0|max:100|nullable',
            'achievement_percentage' => 'sometimes|integer|min:0|max:100|nullable',
        ]);
        

         // Authorization: Check if user can create tasks in this project
         $project = Project::find($validated['project_id']);
         // $this->authorize('createTask', $project); // Example policy check
        $this->authorize('create', [Task::class, $project]);

        if ($validated['status'] === 'In Progress' && $project->wip_limits > 0) {
            $inProgressCount = Task::where('project_id', $project->id)
                                   ->where('status', 'In Progress')
                                   ->count();

            if ($inProgressCount >= $project->wip_limits) {
                return response()->json([
                    'success' => false,
                    'message' => 'WIP Limit tercapai! Tidak dapat menambahkan tugas baru ke kolom "In Progress".'
                ], 422); // 422 Unprocessable Entity adalah status yang cocok
            }
        }

         // Additional validation for levels belonging to project (optional but recommended)
         if ($request->filled('difficulty_level_id') && !DifficultyLevel::where('id', $request->difficulty_level_id)->where('project_id', $project->id)->exists()) {
             return response()->json(['success' => false, 'message' => 'Invalid Difficulty Level selected for this project.'], 422);
         }
         if ($request->filled('priority_level_id') && !PriorityLevel::where('id', $request->priority_level_id)->where('project_id', $project->id)->exists()) {
             return response()->json(['success' => false, 'message' => 'Invalid Priority Level selected for this project.'], 422);
         }

         // Set default order (e.g., put at the end of the list for its status)
         $maxOrder = Task::where('project_id', $validated['project_id'])
                         ->where('status', $validated['status'])
                         ->max('order');
         $validated['order'] = $maxOrder + 1;
        $validated['progress_percentage'] = $validated['progress_percentage'] ?? 0;
         $validated['achievement_percentage'] = $validated['achievement_percentage'] ?? 0;

        $task = Task::create($validated);

        if ($task->assignedUser && $task->assigned_to !== Auth::id()) {
            $task->assignedUser->notify(new NewTaskAssignedNotification($task, Auth::user()));
        }

        // Log Activity
        ActivityLogger::log('created', $task, $project->id, 'created task "' . $task->title . '"');

        // Reload relations for the card view
        $task->load(['assignedUser', 'difficultyLevel', 'priorityLevel'])->loadCount('attachments');

        // Render the card HTML to send back
        $taskHtml = view('tasks.partials.task_card', [
            'task' => $task,
            'color' => $this->getColorForStatus($task->status)
        ])->render();

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task, // Send back the full task object
            'taskHtml' => $taskHtml
        ]);
    }

    // Update Existing Task
    public function update(Request $request, Task $task)
    {
        // Otorisasi: Izinkan jika user adalah PM atau assignee yang sah
        $this->authorize('update', $task);
        
        $originalAssignedTo = $task->assigned_to;
        $currentUser = Auth::user();
        $isOwner = $currentUser->isProjectOwner($task->project);

        // Tentukan field mana yang boleh diubah berdasarkan peran
        $ownerUpdatableFields = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:To Do,In Progress,Review,Done',
            'assigned_to' => 'required|exists:users,id',
            'difficulty_level_id' => 'nullable|exists:difficulty_levels,id,project_id,' . $task->project_id,
            'priority_level_id' => 'nullable|exists:priority_levels,id,project_id,' . $task->project_id,
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'achievement_percentage' => 'sometimes|integer|min:0|max:100|nullable',
        ];

        // PW hanya boleh mengubah status dan deskripsi/catatan
        // (progress_percentage di-handle oleh endpoint terpisah)
        $workerUpdatableFields = [
            'status' => 'required|string|in:To Do,In Progress,Review,Done',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ];

        $rules = $isOwner ? $ownerUpdatableFields : $workerUpdatableFields;
        $validated = $request->validate($rules);
        
        $wipLimit = $task->project->wip_limits;
        $newStatus = $validated['status'] ?? $task->status; // Ambil status baru atau yang lama
        $assigneeId = $validated['assigned_to'] ?? $task->assigned_to; // Ambil assignee baru atau yang lama

        // Cek hanya jika status berubah MENJADI 'In Progress' dan ada limit
        if ($wipLimit > 0 && $newStatus === 'In Progress' && $task->status !== 'In Progress') {
            if ($assigneeId) {
                $inProgressCountForUser = Task::where('project_id', $task->project_id)
                                            ->where('status', 'In Progress')
                                            ->where('assigned_to', $assigneeId)
                                            ->where('id', '!=', $task->id)
                                            ->count();

                if ($inProgressCountForUser >= $wipLimit) {
                    $assigneeName = User::find($assigneeId)->name ?? "User ID: {$assigneeId}";
                    return response()->json([
                        'success' => false,
                        'message' => "WIP Limit per orang ({$wipLimit}) untuk '{$assigneeName}' telah tercapai.",
                        'error_type' => 'WIP_LIMIT_EXCEEDED',
                        'errors' => [ // Kirim format error yang konsisten
                            'status' => ["WIP Limit untuk {$assigneeName} tercapai."]
                        ]
                    ], 422);
                }
            }
        }

        $originalData = $task->getOriginal();
        $task->update($validated);

        // --- Logika Notifikasi ---
        // (Pastikan logika ini tetap ada dan benar)
        $newAssignedTo = $task->assigned_to;
        if ($isOwner && $newAssignedTo != $originalAssignedTo) {
            if ($originalAssignedTo) {
                User::find($originalAssignedTo)->notify(new TaskUpdatedNotification($task, $currentUser));
            }
            if ($newAssignedTo && $newAssignedTo != $currentUser->id) {
                $task->assignedUser->notify(new NewTaskAssignedNotification($task, $currentUser));
            }
        }

        $changes = $task->getChanges();
        if (!empty($changes)) {
            unset($changes['updated_at']);
            if (!empty($changes)) {
                ActivityLogger::log('updated', $task, $task->project_id, 'updated task "' . $task->title . '"', ['changed' => $changes, 'original' => array_intersect_key($originalData, $changes)]);
            }
        }

        $task->load(['assignedUser', 'difficultyLevel', 'priorityLevel'])->loadCount('attachments');
        $taskHtml = view('tasks.partials.task_card', ['task' => $task, 'color' => $this->getColorForStatus($task->status)])->render();

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => $task,
            'taskHtml' => $taskHtml
        ]);
    }

    // Delete Task
    public function destroy(Task $task, Request $request)
    {
        // Authorization check
        $this->authorize('delete', $task);

        DB::beginTransaction();
        try {
             $projectId = $task->project_id;
             $taskTitle = $task->title;

             // Delete attachments from storage first
             foreach ($task->attachments as $attachment) {
                 if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                     Storage::disk('public')->delete($attachment->file_path);
                 }
                 // No need to delete attachment record separately if cascade on delete is set,
                 // but explicit deletion is safer if constraint is missing.
                 // $attachment->delete();
             }

             // Delete comments (optional, depends on cascade constraint)
             // $task->comments()->delete();

             // Delete Activity Logs related to this task
             ActivityLog::where('loggable_type', Task::class)->where('loggable_id', $task->id)->delete();


             $task->delete(); // This should trigger cascade deletes for comments/attachments if foreign keys are set up correctly

             DB::commit();

             // Log Deletion (Log before deleting the model itself if needed)
             // ActivityLogger::log('deleted', null, $projectId, 'deleted task "' . $taskTitle . '"');

             if ($request->ajax() || $request->wantsJson()) {
                 return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
             }
             return redirect()->route('projects.kanban', $projectId)->with('success', 'Task deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
             Log::error("Error deleting task {$task->id}: " . $e->getMessage()); // Log the error
             if ($request->ajax() || $request->wantsJson()) {
                 return response()->json(['success' => false, 'message' => 'Failed to delete task. ' . $e->getMessage()], 500);
             }
             return back()->with('error', 'Failed to delete task. ' . $e->getMessage());
        }
    }

    // NEW: Store Comment
    public function storeComment(Request $request, Task $task)
    {
        // Authorization
        // $this->authorize('comment', $task);
        $this->authorize('createComment', $task);

        $validated = $request->validate(['comment' => 'required|string|max:2000']);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        // --- KIRIM NOTIFIKASI KOMENTAR BARU ---
        $commenter = Auth::user();
        $recipients = collect();

        // Tambahkan PM jika bukan dia yang komentar
        if ($task->project->owner_id !== $commenter->id) {
            $recipients->push($task->project->owner);
        }

        // Tambahkan assignee tugas jika bukan dia yang komentar
        if ($task->assigned_to && $task->assigned_to !== $commenter->id) {
            // Pastikan tidak duplikat dengan PM
            if (!$recipients->contains('id', $task->assigned_to)) {
                $recipients->push($task->assignedUser);
            }
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewCommentOnTaskNotification($task, $commenter));
        }

         // Log Activity
         ActivityLogger::log('commented', $task, $task->project_id, 'commented on task "' . $task->title . '"');


        $comment->load('user:id,name'); // Load user for the response

        return response()->json(['success' => true, 'comment' => $comment]);
    }

    // NEW: Store Attachment(s)
    public function storeAttachment(Request $request, Task $task)
    {
         // Authorization
         // $this->authorize('attach', $task);
        $this->authorize('manageAttachments', $task);

        $validated = $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'required|file|max:10240', // Max 10MB per file, adjust as needed
        ]);

        $uploadedAttachments = [];
        DB::beginTransaction();
        try {
             foreach ($request->file('attachments') as $file) {
                // Store file in 'task_attachments/{project_id}/{task_id}' directory
                $filePath = $file->store("task_attachments/{$task->project_id}/{$task->id}", 'public');
                if(!$filePath) {
                     throw new \Exception("Failed to store file: " . $file->getClientOriginalName());
                 }

                $attachment = $task->attachments()->create([
                    'user_id' => Auth::id(),
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
                $attachment->load('user:id,name'); // Load user for response
                $uploadedAttachments[] = $attachment;

                 // Log Activity
                 ActivityLogger::log('attached', $task, $task->project_id, 'uploaded attachment "' . $file->getClientOriginalName() . '" to task "' . $task->title . '"');

             }
             DB::commit();
             return response()->json(['success' => true, 'attachments' => $uploadedAttachments]);
        } catch (\Exception $e) {
            DB::rollBack();
             Log::error("Attachment upload failed for task {$task->id}: " . $e->getMessage());
            // Delete already stored files if transaction fails
            foreach ($uploadedAttachments as $att) {
                if (Storage::disk('public')->exists($att->file_path)) {
                     Storage::disk('public')->delete($att->file_path);
                }
            }
            return response()->json(['success' => false, 'message' => 'Attachment upload failed: ' . $e->getMessage()], 500);
        }
    }

    // NEW: Delete Attachment
    public function destroyAttachment(Task $task, TaskAttachment $attachment)
    {
         // Authorization: Check if user can delete this attachment (e.g., uploader or project owner)
         if (Auth::id() !== $attachment->user_id && Auth::id() !== $task->project->owner_id) {
             abort(403, 'You cannot delete this attachment.');
         }
        // $this->authorize('delete', $attachment); // Example policy
        $this->authorize('manageAttachments', $task);

        DB::beginTransaction();
        try {
             $filePath = $attachment->file_path;
             $fileName = $attachment->file_name;
             $projectId = $task->project_id;
             $taskTitle = $task->title;

             $attachment->delete(); // Delete DB record

             // Delete file from storage
             if ($filePath && Storage::disk('public')->exists($filePath)) {
                 Storage::disk('public')->delete($filePath);
             }

             DB::commit();

              // Log Activity
             ActivityLogger::log('detached', $task, $projectId, 'deleted attachment "' . $fileName . '" from task "' . $taskTitle . '"');


             return response()->json(['success' => true, 'message' => 'Attachment deleted.']);

        } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Attachment delete failed for task {$task->id}, attachment {$attachment->id}: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Failed to delete attachment: ' . $e->getMessage()], 500);
         }
    }


    // NEW: Get Task History (Activity Log) - Used if loading history on demand
    public function getHistory(Task $task)
    {
         // Authorization
         // $this->authorize('view', $task);

        $history = $task->activityLogs() // Use the relationship defined in Task model
                       ->with('user:id,name')
                       ->get();

        return response()->json(['success' => true, 'history' => $history]);
    }

    // Helper to get project users (Existing code from prompt)
    private function getProjectUsers(Project $project)
    {
         $workerIds = $project->workers()->wherePivot('status', 'accepted')->pluck('users.id')->toArray();
         $userIds = array_unique(array_merge([$project->owner_id], $workerIds));
         return User::whereIn('id', $userIds)->orderBy('name')->get(['id', 'name']);
    }

    // Helper to get color (Existing code from prompt)
    private function getColorForStatus(string $status): string
    {
         $colors = [ 'To Do' => '#ef4444', 'In Progress' => '#fbbf24', 'Review' => '#60a5fa', 'Done' => '#34d399'];
         return $colors[$status] ?? '#6b7280';
    }

    /**
     * Batch update tasks status and order
     */
    public function batchUpdate(Request $request)
{
    $orderData = $request->input('data');
    $projectId = $request->input('project_id');

    if (!$projectId || !is_numeric($projectId)) {
        return response()->json(['success' => false, 'message' => 'Project ID is required.'], 400);
    }
    if (!$orderData || !is_array($orderData)) {
        return response()->json(['success' => false, 'message' => 'Task data is malformed.'], 400);
    }

    $project = Project::find($projectId);
    if (!$project) {
        return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
    }

    $currentUser = Auth::user();
    $wipLimit = $project->wip_limits;
    $inProgressStatusName = 'In Progress';

    DB::beginTransaction();
    try {
        $draggedTaskId = $request->input('dragged_task_id', null);

        foreach ($orderData as $newStatus => $tasksInStatus) {
            if (!is_array($tasksInStatus)) continue;

            foreach ($tasksInStatus as $taskData) {
                if (!isset($taskData['id']) || !isset($taskData['order'])) continue;

                $task = Task::find($taskData['id']);
                if (!$task) {
                    Log::warning("TaskController@batchUpdate: Task ID {$taskData['id']} not found.");
                    continue;
                }
                
                // Pastikan tugas milik proyek yang benar
                if ($task->project_id != $projectId) {
                    throw new \Exception("Task ID {$task->id} does not belong to project ID {$projectId}.");
                }

                $oldStatus = $task->status;
                $statusChanged = ($oldStatus !== $newStatus);
                
                // ===== PERUBAHAN LOGIKA VALIDASI WIP LIMIT =====
                if ($statusChanged && $newStatus === $inProgressStatusName && $wipLimit > 0) {
                    
                    // Cek jika tugas ini punya assignee
                    $assigneeId = $task->assigned_to;
                    if ($assigneeId) {
                        // Hitung jumlah tugas 'In Progress' yang SUDAH ADA untuk assignee ini
                        // (tidak termasuk tugas yang sedang dipindahkan ini)
                        $inProgressCountForUser = Task::where('project_id', $projectId)
                                                      ->where('status', $inProgressStatusName)
                                                      ->where('assigned_to', $assigneeId)
                                                      ->where('id', '!=', $task->id) // Abaikan tugas ini sendiri
                                                      ->count();
                        
                        // Jika jumlah tugas yang ada + tugas baru ini akan melebihi limit
                        if ($inProgressCountForUser + 1 > $wipLimit) {
                            $assigneeName = $task->assignedUser->name ?? "User ID: {$assigneeId}";
                            // Lempar exception dengan pesan yang spesifik
                            throw new \Exception("WIP Limit per orang ({$wipLimit}) untuk '{$assigneeName}' telah tercapai.");
                        }
                    }
                }
                
                $isDraggedTask = ($draggedTaskId && $task->id == $draggedTaskId);

                    // OTORISASI:
                    // Hanya lakukan otorisasi ketat 'updateStatus' jika:
                    // 1. Status task ini berubah, ATAU
                    // 2. Ini adalah task yang secara eksplisit di-drag oleh pengguna.
                    // Untuk task lain yang hanya berubah urutannya karena ada task lain yang disisipkan/dihapus,
                    // dan task tersebut bukan milik worker, kita skip otorisasi 'updateStatus' yang ketat.
                    // Project Owner akan selalu lolos karena TaskPolicy@before.
                    if ($statusChanged || $isDraggedTask) {
                        $this->authorize('updateStatus', $task);
                    } else {
                        // Jika ini worker, dan task bukan miliknya, dan statusnya tidak berubah,
                        // kita mungkin tetap ingin mencegah perubahan order task orang lain.
                        // Ini tergantung seberapa ketat Anda ingin.
                        // Pilihan 1: Biarkan order berubah (lebih sederhana)
                        // Pilihan 2: Tambah policy baru 'updateOrder' yang dicek di sini
                        //            jika !currentUser->isProjectOwner($project) && $task->assigned_to !== $currentUser->id
                        //            maka $this->authorize('updateOrder', $task) -> ini akan gagal jika tidak ada permission.
                        // Untuk sekarang, kita biarkan worker bisa mengubah order task lain
                        // HANYA JIKA task yang di-drag adalah miliknya dan status task lain tidak berubah.
                        if (!$currentUser->isProjectOwner($project) && $task->assigned_to !== $currentUser->id) {
                            // Jika worker mencoba mengubah order task orang lain (yang statusnya tidak berubah)
                            // ini adalah efek samping dari memindahkan task miliknya.
                            // Jika ini tidak diinginkan, Anda perlu logic yang lebih kompleks.
                            // Untuk sekarang, kita log saja.
                            Log::info("TaskController@batchUpdate: User {$currentUser->id} (worker) indirectly changed order of task {$task->id} (assigned to {$task->assigned_to}) which they do not own, because status did not change.");
                        }
                    }


                    $task->status = $newStatus;
                    $task->order = $taskData['order'];
                    $task->save();

                    if ($statusChanged) {
                        ActivityLogger::log(
                            'status_changed',
                            $task,
                            $projectId,
                            $currentUser->name . ' updated task "' . $task->title . '" to status "' . $newStatus . '"',
                            [
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'updated_by_id' => $currentUser->id,
                                'updated_by_name' => $currentUser->name
                            ]
                        );

                        // --- KIRIM NOTIFIKASI PERUBAHAN STATUS ---
                        $assignee = $task->assignedUser;
                        $projectOwner = $task->project->owner;
                        
                        // Jika worker mengubah status, notifikasi PM
                        if ($currentUser->id === $assignee?->id && $projectOwner->id !== $currentUser->id) {
                            $projectOwner->notify(new TaskStatusChangedNotification($task, $oldStatus, $newStatus, $currentUser));
                        } 
                        // Jika PM mengubah status, notifikasi worker (jika ada dan bukan PM itu sendiri)
                        elseif ($currentUser->id === $projectOwner->id && $assignee && $assignee->id !== $currentUser->id) {
                            $assignee->notify(new TaskStatusChangedNotification($task, $oldStatus, $newStatus, $currentUser));
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task order and statuses updated successfully.']);

        } catch (AuthorizationException $e) {
        DB::rollBack();
        Log::warning("Auth Exception in batchUpdate: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'You are not authorized to update one or more tasks.'], 403);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::warning("Exception in batchUpdate: " . $e->getMessage());
        // Kirim pesan error yang spesifik ke frontend
        return response()->json([
            'success' => false, 
            'message' => $e->getMessage(),
            'error_type' => 'WIP_LIMIT_EXCEEDED' // Tipe error untuk di-handle frontend
        ], 422); // Gunakan 422 Unprocessable Entity
    }
}

    // Add this method to your TaskController to handle AJAX search and filter requests
    public function search(Request $request)
    {
        $query = Task::query()->where('project_id', $request->project_id);

        // Apply filters if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('assigned_to', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->difficulty);
        }

        if ($request->filled('priority')) {
            $query->where('priority_level', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get filtered tasks
        $tasks = $query->orderBy('order')->get();
        
        // Group tasks by status
        $tasksByStatus = $tasks->groupBy('status');
        
        // Prepare HTML for each task
        $html = [];
        foreach ($tasksByStatus as $status => $statusTasks) {
            $html[$status] = '';
            foreach ($statusTasks as $task) {
                $task->load('assignedUser');
                $color = $this->getColorForStatus($status);
                $html[$status] .= view('tasks.partials.task_card', ['task' => $task, 'color' => $color])->render();
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $html
        ]);
    }

    public function recap(Project $project, Request $request)
    {
        $this->authorize('viewKanban', $project);

        $query = Task::query()
            ->where('tasks.project_id', $project->id)
            ->select(
                'tasks.*',
                'users.name as assigned_user_name',
                'd_levels.value as difficulty_value',
                'p_levels.value as priority_value'
            )
            ->join('users', 'tasks.assigned_to', '=', 'users.id')
            ->leftJoin('difficulty_levels as d_levels', 'tasks.difficulty_level_id', '=', 'd_levels.id')
            ->leftJoin('priority_levels as p_levels', 'tasks.priority_level_id', '=', 'p_levels.id')
            ->with(['assignedUser', 'difficultyLevel', 'priorityLevel']);

        // --- Filtering Logic ---
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('tasks.title', 'like', "%{$searchTerm}%")
                  ->orWhere('tasks.description', 'like', "%{$searchTerm}%")
                  ->orWhere('users.name', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('tasks.status', $request->status);
        }
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('tasks.assigned_to', $request->user_id);
        }

        if ($request->filled('date_from')) {
            // Filter berdasarkan tanggal mulai tugas
            $query->whereDate('tasks.start_time', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            // Filter berdasarkan tanggal akhir tugas
            $query->whereDate('tasks.end_time', '<=', $request->date_to);
        }
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = [
            'title' => 'tasks.title',
            'status' => 'tasks.status',
            'start_time' => 'tasks.start_time',
            'end_time' => 'tasks.end_time',
            'achievement_percentage' => 'tasks.achievement_percentage',
            'created_at' => 'tasks.created_at',
            'assigned_user_name' => 'users.name',
            'difficulty_value' => 'd_levels.value',
            'priority_value' => 'p_levels.value',
        ];

        if (array_key_exists($sortField, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sortField], $sortDirection);
            if ($sortField !== 'created_at') {
                $query->orderBy('tasks.created_at', 'desc');
            }
        } else {
            $query->orderBy('tasks.created_at', 'desc');
        }

        $perPageOptions = [10, 15, 25, 50, 100];
        $perPage = $request->input('per_page', 15);
        if (!in_array($perPage, $perPageOptions)) $perPage = 15;
        
        $tasks = $query->paginate($perPage)->withQueryString();
        
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('tasks.partials._recap_table_content', compact('tasks', 'project', 'request'))->render(),
                'pagination_html' => $tasks->links('vendor.pagination.tailwind')->toHtml(),
            ]);
        }

        $projectUsers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();
        $projectUsers->prepend($project->owner);

        return view('tasks.recap', [
            'project' => $project,
            'tasks' => $tasks,
            'projectUsers' => $projectUsers,
            'perPageOptions' => $perPageOptions,
            'request' => $request,
        ]);
    }

    public function updateProgress(Request $request, Task $task)
    {
        // Otorisasi baru: Hanya user yang di-assign ke task ini yang boleh update progres
        $this->authorize('updateProgress', $task); // Kita perlu buat policy ini

        $validated = $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);

        $task->update($validated);
        
        // Log aktivitas
        ActivityLogger::log(
            'progress_updated',
            $task,
            $task->project_id,
            'updated progress for task "' . $task->title . '" to ' . $validated['progress_percentage'] . '%'
        );
        
        // Kita tidak perlu mengembalikan task HTML, cukup pesan sukses
        return response()->json(['success' => true, 'message' => 'Progress updated!']);
    }
}