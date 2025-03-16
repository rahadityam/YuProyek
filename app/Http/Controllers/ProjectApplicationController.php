<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ActivityLog;

class ProjectApplicationController extends Controller
{
    /**
     * Show the project application form.
     */
    public function create(Project $project)
    {
        $user = Auth::user();
        
        // Check if the user has already applied
        $existingApplication = ProjectUser::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();
            
        if ($existingApplication) {
            return redirect()->route('projects.show', $project)
                ->with('info', 'You have already applied to this project.');
        }
        
        // Get user's education data
        $educations = $user->educations ?? collect([]);
        
        // Get user's documents
        $cv = $user->documents()->where('type', 'cv')->first();
        $portfolio = $user->documents()->where('type', 'portfolio')->first();
        $certificates = $user->documents()->where('type', 'certificate')->get();
        
        return view('projects.apply', compact('project', 'user', 'educations', 'cv', 'portfolio', 'certificates'));
    }
    
    /**
     * Store a project application.
     */
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Validate the request
        $request->validate([
            'position' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);
        
        // Create the project application
        ProjectUser::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'status' => 'applied',
            'position' => $request->position,
        ]);
        
        // Log the activity
        ActivityLog::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'action' => 'applied',
            'description' => ' applied for the position of ' . $request->position,
        ]);
        
        return redirect()->route('projects.show', $project)
            ->with('success', 'Your application has been submitted successfully!');
    }
}