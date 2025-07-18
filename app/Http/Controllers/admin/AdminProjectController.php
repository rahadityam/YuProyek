<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminProjectController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $projects = Project::with('owner:id,name')->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json($projects);
        }

        return view('admin.users.projectadmin', compact('projects'));
    }

    public function toggleStatus($id)
    {
        $project = Project::findOrFail($id);

        // Logika toggle yang lebih baik:
        // Jika statusnya BUKAN 'blocked', maka blokir.
        // Jika statusnya ADALAH 'blocked', maka kembalikan ke 'active'.
        // Ini menangani kasus di mana status awalnya adalah 'open', 'in_progress', dll.
        if ($project->status !== 'blocked') {
            $project->status = 'blocked';
            $message = 'Project has been blocked successfully.';
        } else {
            $project->status = 'active'; // Selalu kembalikan ke 'active', bukan status sebelumnya.
            $message = 'Project has been unblocked successfully.';
        }
        
        $project->save();

        return redirect()->route('admin.projects.index')->with('success', $message);
    }
}