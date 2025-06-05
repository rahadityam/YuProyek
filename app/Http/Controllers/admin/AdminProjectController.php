<?php
namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminProjectController extends Controller
{
  public function index(Request $request)
{
    $perPage = $request->get('perPage', 10); // default 10
    $projects = Project::paginate($perPage);

    return view('admin.users.projectadmin', compact('projects'));
}


 public function toggleStatus($id)
{
    $project = Project::findOrFail($id);
    $project->status = $project->status === 'blocked' ? 'active' : 'blocked';
    $project->save();

    $message = $project->status === 'blocked'
        ? 'Project has been blocked successfully.'
        : 'Project has been unblocked successfully.';

    return redirect()->route('admin.projects.index')->with('success', $message);
}
    
}
