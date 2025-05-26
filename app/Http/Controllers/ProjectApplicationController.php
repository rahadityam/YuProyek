<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;
use App\Notifications\UserInvitedToProjectNotification;

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
    public function inviteWorker(Request $request, Project $project)
{
    // Authorize that the current user is the project owner
    if ($project->owner_id !== Auth::id()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
    }

    $validator = Validator::make($request->all(), [
        'email' => 'required|email|max:255',
        // 'position' => 'nullable|string|max:255', // Jika ingin menentukan posisi saat invite
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
    }

    $email = $request->input('email');
    // $position = $request->input('position', 'Invited Worker'); // Default posisi jika tidak diisi

    $invitedUser = User::where('email', $email)->first();

    if (!$invitedUser) {
        return response()->json(['success' => false, 'message' => 'User with this email not found.'], 404);
    }

    // Check if user is the project owner
    if ($invitedUser->id === $project->owner_id) {
        return response()->json(['success' => false, 'message' => 'You cannot invite the project owner.'], 422);
    }

    // Check if already a member or invited
    $existingPivot = ProjectUser::where('project_id', $project->id)
                                ->where('user_id', $invitedUser->id)
                                ->first();

    if ($existingPivot) {
        if ($existingPivot->status === 'accepted') {
            return response()->json(['success' => false, 'message' => $invitedUser->name . ' is already a member.'], 422);
        } elseif ($existingPivot->status === 'invited') {
            // Optionally, resend invitation or just inform
            return response()->json(['success' => false, 'message' => $invitedUser->name . ' has already been invited.'], 422);
        }
        // If 'rejected' or other status, you might want to allow re-inviting by updating or deleting old pivot.
        // For now, let's assume if any pivot exists, we don't re-invite with 'invited' status.
        // A more robust solution might be to update existing 'rejected' to 'invited'.
    }

    // Create the invitation
    $projectUser = ProjectUser::create([
        'project_id' => $project->id,
        'user_id' => $invitedUser->id,
        'status' => 'invited', // New status for invited users
        'position' => 'Invited Worker', // Atau ambil dari request jika ada inputnya
    ]);

    ActivityLog::create([
        'project_id' => $project->id,
        'user_id' => Auth::id(), // User yang mengundang (PM)
        'action' => 'invited_member',
        'description' => 'invited ' . $invitedUser->name . ' to the project.',
    ]);

    // (Opsional) Kirim notifikasi email ke $invitedUser
    // if (class_exists(UserInvitedToProjectNotification::class)) {
    //     $invitedUser->notify(new UserInvitedToProjectNotification($project, Auth::user()));
    // }

    return response()->json([
        'success' => true,
        'message' => $invitedUser->name . ' has been successfully invited to the project.',
        'invited_user' => [ // Kirim data user yang diundang untuk update UI jika perlu
            'id' => $invitedUser->id,
            'name' => $invitedUser->name,
            'email' => $invitedUser->email,
            'profile_photo_url' => $invitedUser->profile_photo_path ? Storage::url($invitedUser->profile_photo_path) : null,
            'position' => $projectUser->position, // posisi dari pivot
        ]
    ]);
}

/**
 * Project Manager actions on pending invitations (e.g., cancel)
 * Or Worker actions (accept/decline invitation - usually from a notification link)
 */
public function updateInvitationStatus(Request $request, Project $project, User $user)
{
    $currentUser = Auth::user();
    $action = $request->input('action'); // e.g., 'accept', 'decline', 'cancel_pm'

    $projectUser = ProjectUser::where('project_id', $project->id)
        ->where('user_id', $user->id)
        ->where('status', 'invited') // Hanya bisa bertindak pada status 'invited'
        ->first();

    if (!$projectUser) {
        return response()->json(['success' => false, 'message' => 'Invitation not found or already actioned.'], 404);
    }

    $message = '';

    if ($action === 'accept' && $currentUser->id === $user->id) { // Worker accepts
        $projectUser->status = 'accepted';
        $projectUser->save();
        $message = 'You have successfully joined the project.';
        ActivityLog::create([ /* ... */ 'description' => $user->name . ' accepted project invitation.']);
        // redirect ke dashboard proyek atau halaman tim
        return redirect()->route('projects.team', $project)->with('success', $message);
    } elseif ($action === 'decline' && $currentUser->id === $user->id) { // Worker declines
        $projectUser->delete(); // Atau set status 'declined_invitation'
        $message = 'You have declined the project invitation.';
        ActivityLog::create([ /* ... */ 'description' => $user->name . ' declined project invitation.']);
         // redirect ke dashboard umum atau halaman lain
        return redirect()->route('dashboard')->with('info', $message);
    } elseif ($action === 'cancel_pm' && $currentUser->id === $project->owner_id) { // PM cancels
        $projectUser->delete(); // Atau set status 'cancelled_invitation'
        $message = 'Invitation for ' . $user->name . ' has been cancelled.';
        ActivityLog::create([ /* ... */ 'description' => 'cancelled invitation for ' . $user->name . '.']);
        return back()->with('success', $message);
    } else {
        return response()->json(['success' => false, 'message' => 'Invalid action or unauthorized.'], 403);
    }
    // Untuk AJAX response dari modal jika perlu
    // return response()->json(['success' => true, 'message' => $message]);
}
}