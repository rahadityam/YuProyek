<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Category;

class ProjectController extends Controller
{
    public function dashboard()
    {
        // Get all projects for the global view
        $projects = Project::orderBy('created_at', 'desc')->get();
        
        $user = Auth::user();
        $userProjects = [];
        
        // Get user's specific projects based on role
        if ($user->role === 'project_owner') {
            // If user is a project owner, get projects they own
            $userProjects = Project::where('owner_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->get();
        } else {
            // If user is a worker, get projects they follow/participate in
            $userProjects = $user->projects()
                               ->orderBy('created_at', 'desc')
                               ->get();
        }
        
        return view('dashboard', compact('projects', 'userProjects'));
    }

    // Menampilkan daftar proyek
    public function index()
    {
        $projects = Project::all();
        return view('projects.index', compact('projects'));
    }

    // Menampilkan form untuk membuat proyek baru
    public function create()
    {
        $categories = Category::all();
        return view('projects.create', compact('categories'));
    }

    // Menyimpan proyek baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric',
            'status' => 'required|string|in:open,in_progress,completed,cancelled',
            'owner_id' => 'required|exists:users,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);
       
        // Create the project
        $project = Project::create($request->except('categories'));
       
        // Attach categories if any are selected
        if ($request->has('categories')) {
            $project->categories()->attach($request->categories);
        }
       
        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dibuat!');
    }
    
    // Gunakan metode yang sama untuk edit dan update
    public function edit(Project $project)
    {
        $categories = Category::all();
        $selectedCategories = $project->categories->pluck('id')->toArray();
        return view('projects.edit', compact('project', 'categories', 'selectedCategories'));
    }
    
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);
        
        $project->update($request->except('categories'));
        
        // Sync categories
        if ($request->has('categories')) {
            $project->categories()->sync($request->categories);
        } else {
            $project->categories()->detach();
        }
        
        return redirect()->route('projects.index')->with('success', 'Proyek berhasil diperbarui!');
    }

    // Menghapus proyek
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus!');
    }

    // Menampilkan proyek yang diikuti oleh user yang login
    public function myProjects()
{
    $user = Auth::user();
    $projects = [];
    $isOwner = false;

    // Cek apakah user memiliki role project_owner
    if ($user->role === 'project_owner') {
        // Jika user adalah project_owner, ambil proyek yang dia buat
        $projects = Project::where('owner_id', $user->id)->with('owner')->get();
        $isOwner = true;
    } else {
        // Jika user adalah worker, ambil proyek yang dia ikuti
        $projects = $user->projects()->with('owner')->get();
    }

    // Tampilkan view dengan data proyek dan flag isOwner
    return view('projects.my-projects', compact('projects', 'isOwner'));
}
}