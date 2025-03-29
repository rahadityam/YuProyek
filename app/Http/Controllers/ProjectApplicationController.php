<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
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

    /**
 * Update application status (accept or reject).
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \App\Models\Project  $project
 * @param  \App\Models\User  $user
 * @return \Illuminate\Http\Response
 */
public function updateStatus(Request $request, Project $project, User $user)
{
    // Authorize that the current user is the project owner
    if ($project->owner_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }
    
    // Validate the request
    $request->validate([
        'status' => 'required|in:accepted,rejected',
    ]);
    
    // Update the project user status
    $projectUser = ProjectUser::where('project_id', $project->id)
        ->where('user_id', $user->id)
        ->first();
    
    if (!$projectUser) {
        return redirect()->route('projects.team', $project)
            ->with('error', 'Application not found.');
    }
    
    $projectUser->status = $request->status;
    $projectUser->save();
    
    // Log the activity
    ActivityLog::create([
        'project_id' => $project->id,
        'user_id' => Auth::id(),
        'action' => $request->status == 'accepted' ? 'accepted_application' : 'rejected_application',
        'description' => $request->status == 'accepted' 
            ? 'accepted ' . $user->name . '\'s application as ' . $projectUser->position
            : 'rejected ' . $user->name . '\'s application',
    ]);
    
    $message = $request->status == 'accepted' 
        ? $user->name . ' has been added to the team.'
        : $user->name . '\'s application has been rejected.';
        
    return redirect()->route('projects.team', $project)
        ->with('success', $message);
}

/**
 * Remove a member from the project.
 *
 * @param  \App\Models\Project  $project
 * @param  \App\Models\User  $user
 * @return \Illuminate\Http\Response
 */
public function removeMember(Project $project, User $user)
{
    // Authorize that the current user is the project owner
    if ($project->owner_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }
    
    // Delete the project user relationship
    ProjectUser::where('project_id', $project->id)
        ->where('user_id', $user->id)
        ->delete();
    
    // Log the activity
    ActivityLog::create([
        'project_id' => $project->id,
        'user_id' => Auth::id(),
        'action' => 'removed_member',
        'description' => 'removed ' . $user->name . ' from the project',
    ]);
    
    return redirect()->route('projects.team', $project)
        ->with('success', $user->name . ' has been removed from the team.');
}

/**
 * Show user profile in the project context.
 *
 * @param  \App\Models\Project  $project
 * @param  \App\Models\User  $user
 * @return \Illuminate\Http\Response
 */
public function viewProfile(Project $project, User $user)
{
    // Check if the user is part of the project
    $projectUser = ProjectUser::where('project_id', $project->id)
        ->where('user_id', $user->id)
        ->first();
    
    if (!$projectUser && $user->id !== $project->owner_id) {
        return redirect()->route('projects.team', $project)
            ->with('error', 'User not found in this project.');
    }
    
    // Get user's education data
    $educations = $user->educations ?? collect([]);
    
    // Get user's documents
    $cv = $user->documents()->where('type', 'cv')->first();
    $portfolio = $user->documents()->where('type', 'portfolio')->first();
    $certificates = $user->documents()->where('type', 'certificate')->get();
    
    return view('projects.user-profile', compact('project', 'user', 'projectUser', 'educations', 'cv', 'portfolio', 'certificates'));
}
}