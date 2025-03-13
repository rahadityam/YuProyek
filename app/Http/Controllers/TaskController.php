<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Show the kanban board for a specific project.
     */
    public function kanban(Project $project)
    {
        // Retrieve tasks for this project ordered by their position
        $tasks = $project->tasks()->orderBy('order')->get();
        
        // Return the kanban view with tasks and project data
        return view('kanban.index', compact('tasks', 'project'));
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
        
        // Get users for assignment dropdown
        $users = User::all();
        
        return view('tasks.create', compact('status', 'project_id', 'project', 'users'));
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

        // Redirect back to the project's kanban board
        return redirect()->route('projects.kanban', $request->project_id)
                         ->with('success', 'Task created successfully!');
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        // Get users for assignment dropdown
        $users = User::all();
        
        return view('tasks.edit', compact('task', 'users'));
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

        // Redirect back to the project's kanban board
        return redirect()->route('projects.kanban', $task->project_id)
                         ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        // Store project_id for redirection after deletion
        $projectId = $task->project_id;
        
        DB::transaction(function () use ($task) {
            // Get current task status, order, and project_id
            $status = $task->status;
            $order = $task->order;
            $projectId = $task->project_id;
            
            // Delete the task
            $task->delete();
            
            // Reorder tasks with higher order in the same status and project
            Task::where('status', $status)
                ->where('project_id', $projectId)
                ->where('order', '>', $order)
                ->update(['order' => DB::raw('`order` - 1')]);
        });

        // Redirect back to the project's kanban board
        return redirect()->route('projects.kanban', $projectId)
                         ->with('success', 'Task deleted successfully!');
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
                    
                    // Ensure the task belongs to the correct project
                    if ($task->project_id != $projectId) {
                        throw new \Exception("Task does not belong to this project");
                    }
                    
                    $task->update([
                        'status' => $status,
                        'order' => $taskData['order']
                    ]);
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