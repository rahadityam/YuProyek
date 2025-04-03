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
use Illuminate\Support\Facades\Storage; // Add this
use Illuminate\Support\Facades\Auth;    // Add this
use App\Services\ActivityLogger;
class TaskController extends Controller
{
    // Kanban View (Load necessary data)
    // Update this method in TaskController.php
public function kanban(Project $project)
{
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
         // $this->authorize('view', $task); // Example policy check

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
            'achievement_percentage' => 'sometimes|integer|min:0|max:100|nullable', // Updated
             // No need for order initially, handled by batch update
        ]);

         // Authorization: Check if user can create tasks in this project
         $project = Project::find($validated['project_id']);
         // $this->authorize('createTask', $project); // Example policy check

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
         $validated['achievement_percentage'] = $validated['achievement_percentage'] ?? 0; // Default to 0 if not provided

        $task = Task::create($validated);

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
         // Authorization check
         // $this->authorize('update', $task);

         $validated = $request->validate([
             'title' => 'required|string|max:255',
             'description' => 'nullable|string',
             // Status is usually updated via drag-and-drop/batch update
             // 'status' => 'sometimes|required|string',
             'assigned_to' => 'required|exists:users,id',
             'difficulty_level_id' => 'nullable|exists:difficulty_levels,id',
             'priority_level_id' => 'nullable|exists:priority_levels,id',
             'start_time' => 'nullable|date',
             'end_time' => 'nullable|date|after_or_equal:start_time',
             'achievement_percentage' => 'sometimes|integer|min:0|max:100|nullable',
         ]);

         // Project level validation (if needed, similar to store)
         $project = $task->project;
         if ($request->filled('difficulty_level_id') && !DifficultyLevel::where('id', $request->difficulty_level_id)->where('project_id', $project->id)->exists()) { /* Error */ }
         if ($request->filled('priority_level_id') && !PriorityLevel::where('id', $request->priority_level_id)->where('project_id', $project->id)->exists()) { /* Error */ }

         $validated['achievement_percentage'] = $validated['achievement_percentage'] ?? $task->achievement_percentage; // Keep old if null

         $originalData = $task->getOriginal(); // Get data before update for logging
         $task->update($validated);
         $changes = $task->getChanges(); // Get changed attributes

         // Log Activity for specific changes
         if (!empty($changes)) {
              // Don't log timestamp changes
              unset($changes['updated_at']);
              if (!empty($changes)) {
                   ActivityLogger::log(
                       'updated',
                       $task,
                       $task->project_id,
                       'updated task "' . $task->title . '"',
                       ['changed' => $changes, 'original' => array_intersect_key($originalData, $changes)]
                   );
              }
         }

         // Reload relations for the card view
         $task->load(['assignedUser', 'difficultyLevel', 'priorityLevel'])->loadCount('attachments');

         // Render the card HTML
         $taskHtml = view('tasks.partials.task_card', [
             'task' => $task,
             'color' => $this->getColorForStatus($task->status)
         ])->render();

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
        // $this->authorize('delete', $task);

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

        $validated = $request->validate(['comment' => 'required|string|max:2000']);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

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
        
        DB::beginTransaction();
        
        try {
            foreach ($orderData as $status => $tasks) {
                foreach ($tasks as $taskData) {
                    $task = Task::findOrFail($taskData['id']);
                    $oldStatus = $task->status;
                    
                    // Ensure the task belongs to the correct project
                    if ($task->project_id != $projectId) {
                        throw new \Exception("Task does not belong to this project");
                    }
                    
                    $task->update([
                        'status' => $status,
                        'order' => $taskData['order']
                    ]);
                    
                    // Log status change if status changed
                    if ($oldStatus !== $status) {
                        ActivityLogger::log(
                            'status_changed',
                            $task,
                            $projectId,
                            'has updated task "' . $task->title . '" to "' . $status . '"',
                            [
                                'old_status' => $oldStatus,
                                'new_status' => $status
                            ]
                        );
                    }
                }
            }
            
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
}