<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display the activity log for a project.
     */
    public function index(Project $project, Request $request)
    {
        // Get all activity logs for this project, paginated
        $logs = ActivityLog::where('project_id', $project->id)
                          ->with('user')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);
        
        return view('projects.activity', compact('project', 'logs'));
    }
    
    /**
     * Filter activity logs by user, action, or date range
     */
    public function filter(Project $project, Request $request)
    {
        $query = ActivityLog::where('project_id', $project->id);
        
        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by action if provided
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }
        
        // Filter by date range if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get the filtered logs
        $logs = $query->with('user')
                     ->orderBy('created_at', 'desc')
                     ->paginate(20);
        
        // Get users for the filter dropdown
        $users = $project->workers()->get();
        
        return view('projects.activity', compact('project', 'logs', 'users'));
    }
}