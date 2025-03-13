<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    // Menampilkan halaman pengaturan proyek
    public function index(Project $project)
    {
        // Cek apakah pengguna berhak mengakses pengaturan proyek
        if (Auth::id() !== $project->owner_id) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'Anda tidak memiliki akses untuk mengubah pengaturan proyek ini.');
        }
        
        $categories = Category::all();
        $selectedCategories = $project->categories->pluck('id')->toArray();
        
        return view('projects.pengaturan', compact('project', 'categories', 'selectedCategories'));
    }
    
    // Memperbarui pengaturan proyek (Metode alternatif, jika tidak ingin menggunakan ProjectController::update)
    public function update(Request $request, Project $project)
    {
        // Cek apakah pengguna berhak mengakses pengaturan proyek
        if (Auth::id() !== $project->owner_id) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'Anda tidak memiliki akses untuk mengubah pengaturan proyek ini.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric',
            'status' => 'required|string|in:open,in_progress,completed,cancelled',
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
        
        return redirect()->route('projects.pengaturan', $project)
            ->with('success', 'Pengaturan proyek berhasil diperbarui!');
    }
}