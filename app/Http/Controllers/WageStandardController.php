<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WageStandard;
use Illuminate\Http\Request;

class WageStandardController extends Controller
{
    /**
     * Display a listing of the wage standards for a project.
     */
    public function index(Project $project)
    {
        // Get wage standards for the current project, paginated
        $wageStandards = WageStandard::where('project_id', $project->id)
                                    ->orderBy('job_category')
                                    ->paginate(10);
        
        return view('wage-standards.index', compact('project', 'wageStandards'));
    }

    /**
     * Show the form for creating a new wage standard.
     */
    public function create(Project $project)
    {
        return view('wage-standards.create', compact('project'));
    }

    /**
     * Store a newly created wage standard in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Validate input
        $validated = $request->validate([
            'job_category' => 'required|string|max:255',
            'task_price' => 'required|numeric|min:0',
        ]);
        
        // Add project_id to data
        $validated['project_id'] = $project->id;
        
        // Create new wage standard
        WageStandard::create($validated);
        
        return redirect()->route('projects.wage-standards.index', $project)
                        ->with('success', 'Standar upah berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified wage standard.
     */
    public function edit(Project $project, WageStandard $wageStandard)
    {
        return view('wage-standards.edit', compact('project', 'wageStandard'));
    }

    /**
     * Update the specified wage standard in storage.
     */
    public function update(Request $request, Project $project, WageStandard $wageStandard)
    {
        // Validate input
        $validated = $request->validate([
            'job_category' => 'required|string|max:255',
            'task_price' => 'required|numeric|min:0',
        ]);
        
        // Update wage standard
        $wageStandard->update($validated);
        
        return redirect()->route('projects.wage-standards.index', $project)
                        ->with('success', 'Standar upah berhasil diperbarui.');
    }

    /**
     * Remove the specified wage standard from storage.
     */
    public function destroy(Project $project, WageStandard $wageStandard)
    {
        $wageStandard->delete();
        
        return redirect()->route('projects.wage-standards.index', $project)
                        ->with('success', 'Standar upah berhasil dihapus.');
    }
}