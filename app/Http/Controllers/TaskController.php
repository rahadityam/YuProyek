<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class TaskController extends Controller
{
    public function kanban(Project $project)
    {
        // Retrieve tasks for this project ordered by their position
        $tasks = $project->tasks()->orderBy('order')->get();
        
        // Get users for this project for the modal forms
        $users = $this->getProjectUsers($project);
        
        // Return the kanban view with tasks and project data
        return view('kanban.index', compact('tasks', 'project', 'users'));
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

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request)
    {
        $status = $request->status ?? 'To Do';
        $project_id = $request->project_id;
        
        // Validate project_id exists
        $project = Project::findOrFail($project_id);
        
        // Get only users who are part of this project (owner + workers)
        $users = $this->getProjectUsers($project);
        
        if($request->ajax()) {
            return view('tasks.partials.create_form', compact('status', 'project_id', 'project', 'users'));
        }
        
        return view('tasks.create', compact('status', 'project_id', 'project', 'users'));
    }

    public function edit(Task $task, Request $request)
    {
        // Get project for this task
        $project = $task->project;
        
        // Get only users who are part of this project (owner + workers)
        $users = $this->getProjectUsers($project);
        
        if($request->ajax()) {
            return view('tasks.partials.edit_modal', compact('task', 'users'));
        }
        
        return view('tasks.edit', compact('task', 'users'));
    }

    /**
     * Helper method to get users who are part of a project
     */
    private function getProjectUsers(Project $project)
    {
        // Get project workers with active status
        $workerIds = $project->workers()
            ->wherePivot('status', 'accepted')
            ->pluck('users.id')
            ->toArray();
        
        // Add project owner to the list
        $userIds = array_merge([$project->owner_id], $workerIds);
        
        // Get all these users
        return User::whereIn('id', $userIds)->get();
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:To Do,In Progress,Done',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'difficulty_level' => 'required|integer|min:1|max:5',
            'priority_level' => 'required|integer|min:1|max:5',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        // Get the highest order in this status for this project and add 1
        $maxOrder = Task::where('status', $request->status)
                        ->where('project_id', $request->project_id)
                        ->max('order') ?? -1;
        $newOrder = $maxOrder + 1;

        // Create the new task
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'project_id' => $request->project_id,
            'assigned_to' => $request->assigned_to,
            'difficulty_level' => $request->difficulty_level,
            'priority_level' => $request->priority_level,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'order' => $newOrder,
        ]);

        // Log the activity
        ActivityLogger::log(
            'created',
            $task,
            $request->project_id,
            'has created task "' . $task->title . '"'
        );

        if($request->ajax()) {
            // Load the user for the task
            $task->load('assignedUser');
            
            // Return a JSON response with the task data and HTML
            $taskHtml = view('tasks.partials.task_card', ['task' => $task, 'color' => $this->getColorForStatus($task->status)])->render();
            
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task' => $task,
                'taskHtml' => $taskHtml
            ]);
        }

        // Redirect back to the project's kanban board
        return redirect()->route('projects.kanban', $request->project_id)
                         ->with('success', 'Task created successfully!');
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'difficulty_level' => 'required|integer|min:1|max:5',
            'priority_level' => 'required|integer|min:1|max:5',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        $oldTask = $task->toArray();

        // Update the task
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'difficulty_level' => $request->difficulty_level,
            'priority_level' => $request->priority_level,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // Log the activity
        ActivityLogger::log(
            'updated',
            $task,
            $task->project_id,
            'has updated task "' . $task->title . '"',
            [
                'old' => $oldTask,
                'new' => $task->toArray()
            ]
        );

        $task = $task->fresh();

    if($request->ajax()) {
        $task->load('assignedUser');
        
        $taskHtml = view('tasks.partials.task_card', [
            'task' => $task, 
            'color' => $this->getColorForStatus($task->status)
        ])->render();
        
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully!',
            'task' => $task,
            'taskHtml' => $taskHtml
        ]);
    }

        // Redirect back to the project's kanban board
        return redirect()->route('projects.kanban', $task->project_id)
                         ->with('success', 'Task updated successfully!');
    }

    /**
     * Helper method to get color for status
     */
    private function getColorForStatus($status)
    {
        $colors = [
            'To Do' => '#ef4444',
            'In Progress' => '#ffd96b',
            'Done' => '#10b981'
        ];

        return $colors[$status] ?? '#6b7280';
    }

    /**
     * Remove the specified task from storage.
     */
    // In your TaskController
public function destroy(Task $task, Request $request)
{
    $projectId = $request->input('project_id');
    
    // Delete the task
    $task->delete();
    
    // Return JSON response for AJAX requests
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }
    
    // For non-AJAX requests, redirect back
    return redirect()->route('projects.kanban', $projectId)
        ->with('success', 'Task deleted successfully');
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
}