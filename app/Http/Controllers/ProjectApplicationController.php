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
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;
    
class ProjectApplicationController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        $request->validate([
            'position' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);
        
        ProjectUser::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'status' => 'applied',
            'position' => $request->position,
        ]);
        
        // FIX: Use ActivityLogger service
        ActivityLogger::log('applied', $project->id, 'applied for the position of ' . $request->position);
        
        return redirect()->route('projects.show', $project)
            ->with('success', 'Your application has been submitted successfully!');
    }

    public function updateStatus(Request $request, Project $project, User $user)
    {
        if ($project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate(['status' => 'required|in:accepted,rejected']);
        
        $projectUser = ProjectUser::where('project_id', $project->id)
            ->where('user_id', $user->id)->first();
        
        if (!$projectUser) {
            return redirect()->route('projects.team', $project)->with('error', 'Application not found.');
        }
        
        $projectUser->status = $request->status;
        $projectUser->save();
        
        $action = $request->status == 'accepted' ? 'accepted_application' : 'rejected_application';
        $description = $request->status == 'accepted' 
            ? 'accepted ' . $user->name . '\'s application as ' . $projectUser->position
            : 'rejected ' . $user->name . '\'s application';

        // FIX: Use ActivityLogger service
        ActivityLogger::log($action, $project->id, $description);
        
        $message = $request->status == 'accepted' 
            ? $user->name . ' has been added to the team.'
            : $user->name . '\'s application has been rejected.';
            
        return redirect()->route('projects.team', $project)->with('success', $message);
    }

    public function removeMember(Project $project, User $user)
    {
        if ($project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        ProjectUser::where('project_id', $project->id)->where('user_id', $user->id)->delete();
        
        // FIX: Use ActivityLogger service
        ActivityLogger::log('removed_member', $project->id, 'removed ' . $user->name . ' from the project');
        
        return redirect()->route('projects.team', $project)
            ->with('success', $user->name . ' has been removed from the team.');
    }

    public function inviteWorker(Request $request, Project $project)
    {
        if ($project->owner_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), ['email' => 'required|email|max:255']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $email = $request->input('email');
        $invitedUser = User::where('email', $email)->first();

        if (!$invitedUser) { return response()->json(['success' => false, 'message' => 'User with this email not found.'], 404); }
        if ($invitedUser->id === $project->owner_id) { return response()->json(['success' => false, 'message' => 'You cannot invite the project owner.'], 422); }

        $existingPivot = ProjectUser::where('project_id', $project->id)->where('user_id', $invitedUser->id)->first();
        if ($existingPivot) {
            return response()->json(['success' => false, 'message' => $invitedUser->name . ' is already involved with this project.'], 422);
        }

        $projectUser = ProjectUser::create(['project_id' => $project->id, 'user_id' => $invitedUser->id, 'status' => 'invited', 'position' => 'Invited Worker']);
        
        // FIX: Use ActivityLogger service
        ActivityLogger::log('invited_member', $project->id, 'invited ' . $invitedUser->name . ' to the project.');
        
        $invitedUser->notify(new UserInvitedToProjectNotification($project, Auth::user()));

        return response()->json(['success' => true, 'message' => $invitedUser->name . ' has been successfully invited.']);
    }

    public function updateInvitationStatus(Request $request, Project $project, User $user)
    {
        $currentUser = Auth::user();
        $action = $request->input('action'); // 'accept' or 'decline' or 'cancel_pm'

        // Pastikan projectUser ada dan berstatus 'invited'
        $projectUser = ProjectUser::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('status', 'invited')
            ->first();

        if (!$projectUser) {
            // Jika request adalah API, kembalikan JSON error
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Invitation not found or has already been responded to.'], 404);
            }
            return redirect()->route('dashboard')->with('error', 'Undangan tidak valid atau sudah direspons.');
        }

        // --- Hapus notifikasi terkait ---
        // (Logika notifikasi tetap sama)
        $notification = $currentUser->notifications()
            ->where('data->project_id', $project->id)
            ->where('type', 'App\Notifications\UserInvitedToProjectNotification')
            ->latest()
            ->first();
        if ($notification) {
            $notification->markAsRead();
        }

        $message = '';
        $success = false;

        // Aksi oleh PEKERJA yang diundang
        if (($action === 'accept' || $action === 'decline') && $currentUser->id === $user->id) {
            if ($action === 'accept') {
                $projectUser->status = 'accepted'; $projectUser->save();
                // FIX: Use ActivityLogger service
                ActivityLogger::log('joined_project', $project->id, $user->name . ' has joined the project.');
                $message = 'You have successfully joined the project.';
                $success = true;
            } else { // decline
                $projectUser->delete();
                // FIX: Use ActivityLogger service
                ActivityLogger::log('declined_invitation', $project->id, $user->name . ' declined the project invitation.');
                $message = 'You have declined the project invitation.';
                $success = true;
            }
        }
        // Aksi oleh PROJECT MANAGER untuk membatalkan
        elseif ($action === 'cancel_pm' && $currentUser->id === $project->owner_id) {
            $projectUser->delete();
            // FIX: Use ActivityLogger service
            ActivityLogger::log('cancelled_invitation', $project->id, 'cancelled the invitation for ' . $user->name);
            $message = 'Invitation for ' . $user->name . ' has been cancelled.';
            $success = true;
        } else {
            // Jika tidak ada kondisi yang cocok, berarti tidak terotorisasi
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Aksi tidak diizinkan.');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }

        // Redirect untuk web
        if ($success && $action === 'accept') {
            return redirect()->route('projects.dashboard', $project)->with('success', $message);
        }
        return redirect()->route('dashboard')->with($success ? 'success' : 'error', $message);
    }
}