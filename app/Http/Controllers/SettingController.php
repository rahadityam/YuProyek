<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;
use App\Models\Category;
use App\Models\WageStandard; // Tambahkan ini
use App\Models\User; // Tambahkan ini
use App\Models\ProjectUser; // Tambahkan ini
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    // --- Menampilkan Halaman Pengaturan Terpadu ---
    public function index(Project $project)
    {
        // Autorisasi: Hanya pemilik proyek yang bisa akses
        if (Auth::id() !== $project->owner_id) {
            abort(403, 'Unauthorized action.');
        }

        // Ambil semua data yang dibutuhkan untuk semua tab
        $categories = Category::orderBy('name')->get(); // Untuk tab Project Info
        $selectedCategories = $project->categories->pluck('id')->toArray(); // Untuk tab Project Info

        // Data untuk tab Finansial
        $wageStandards = $project->wageStandards()->orderBy('job_category')->get();
        $members = $project->workers()
                            ->wherePivot('status', 'accepted') // Hanya member yang diterima
                            ->withPivot('wage_standard_id') // Sertakan wage_standard_id dari pivot
                            ->orderBy('name')
                            ->get();

        // Data untuk tab Kriteria
        $difficultyLevels = $project->difficultyLevels()->orderBy('display_order', 'asc')->get();
        $priorityLevels = $project->priorityLevels()->orderBy('display_order', 'asc')->get();

        return view('projects.pengaturan', compact(
            'project',
            'categories',
            'selectedCategories',
            'wageStandards',
            'members',
            'difficultyLevels',
            'priorityLevels'
        ));
    }

    // --- PERBAIKAN: Method untuk Update Info Proyek SAJA ---
    public function updateProjectInfo(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        // Validasi HANYA untuk field info proyek
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:open,in_progress,completed,cancelled',
            'wip_limits' => 'nullable|integer|min:1',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            // 'payment_calculation_type' DIHAPUS dari sini
        ]);

        // Update project data (tanpa payment_calculation_type)
        $project->update($validated);

        // Sync categories
        if ($request->has('categories')) {
            $project->categories()->sync($request->categories);
        } else {
            $project->categories()->detach();
        }

        // Gunakan key flash message berbeda agar bisa menampilkan di tab yang benar
        return redirect()->route('projects.pengaturan', $project)
                         ->with('success_info', 'Informasi proyek berhasil diperbarui!');
    }

    // --- PERBAIKAN: Method BARU untuk Update Tipe Kalkulasi Pembayaran ---
    public function updatePaymentCalculationType(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        // Validasi HANYA untuk tipe pembayaran
        $validated = $request->validate([
            'payment_calculation_type' => ['required', Rule::in(['termin', 'task', 'full'])],
        ]);

        // Update hanya field payment_calculation_type
        $project->update($validated);

        // Gunakan key flash message berbeda
        return redirect()->route('projects.pengaturan', $project)
                         ->with('success_payment', 'Metode kalkulasi pembayaran berhasil diperbarui!');
    }

    // --- Memperbarui Pengaturan Proyek Utama & Tipe Pembayaran ---
    public function update(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        $validated = $request->validate([
            // Validasi dari form project info
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:open,in_progress,completed,cancelled',
            'wip_limits' => 'nullable|integer|min:1',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            // Validasi untuk tipe pembayaran baru
            'payment_calculation_type' => ['required', Rule::in(['termin', 'task', 'full'])],
        ]);

        // Update project data
        $project->update($validated);

        // Sync categories (jika ada)
        if ($request->has('categories')) {
            $project->categories()->sync($request->categories);
        } else {
            $project->categories()->detach(); // Hapus semua jika tidak ada yang dipilih
        }

        return redirect()->route('projects.pengaturan', $project)
                         ->with('success', 'Pengaturan proyek berhasil diperbarui!');
    }

    // --- Weight Management ---
    public function editWeights(Project $project)
    {
        // Authorization check (e.g., only project owner)
        // $this->authorize('update', $project);
        return view('settings.weights', compact('project'));
    }

    // --- Update Bobot WSM (Tetap sama) ---
    public function updateWeights(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }
        $validated = $request->validate([ /* ... */ ]);
        if (($validated['difficulty_weight'] + $validated['priority_weight']) != 100) { /* Error */ }
        $project->update($validated);
        return redirect()->route('projects.pengaturan', $project)->with('success_criteria', 'Bobot WSM berhasil diperbarui.'); // Gunakan key berbeda agar bisa direct ke tab
    }

    // --- Level Management (Difficulty & Priority) ---
    public function manageLevels(Project $project)
    {
        // $this->authorize('update', $project); // Uncomment jika ada policy

        // **ORDER BY display_order**
        $difficultyLevels = $project->difficultyLevels()->orderBy('display_order', 'asc')->get();
        $priorityLevels = $project->priorityLevels()->orderBy('display_order', 'asc')->get();

        return view('settings.levels', compact('project', 'difficultyLevels', 'priorityLevels'));
    }

    // --- Difficulty Level CRUD ---
    // In SettingController.php
    public function storeDifficultyLevel(Request $request, Project $project)
    {
        // Validate first to ensure all required fields are present
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|integer|min:1',
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'], // Validasi hex color
        ]);
        
        // Log the received data for debugging
        \Log::info('Received difficulty level data:', $validated);
        
        $validated['project_id'] = $project->id;
    
        $maxOrder = DifficultyLevel::where('project_id', $project->id)->max('display_order') ?? 0;
        $validated['display_order'] = $maxOrder + 1;
    
        $level = DifficultyLevel::create($validated);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Level Kesulitan berhasil ditambahkan.',
                'level' => $level
            ]);
        }
        
        return back()->with('success', 'Level Kesulitan berhasil ditambahkan.');
    }

    public function updateDifficultyLevel(Request $request, Project $project, DifficultyLevel $difficultyLevel)
{
    // Validate project ownership or access rights
    if (Auth::id() !== $project->owner_id) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk mengubah level kesulitan.'
        ], 403);
    }

    // Validate the difficulty level belongs to the project
    if ($difficultyLevel->project_id !== $project->id) {
        return response()->json([
            'success' => false,
            'message' => 'Level kesulitan tidak ditemukan dalam proyek ini.'
        ], 404);
    }

    // Validate incoming data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'value' => 'required|integer|min:1',
        'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'], 
    ]);

    try {
        // Update the difficulty level
        $difficultyLevel->update($validated);

        // Return success response with updated level
        return response()->json([
            'success' => true,
            'message' => 'Level Kesulitan berhasil diperbarui.',
            'level' => $difficultyLevel->fresh() // Refresh to get updated data
        ]);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error updating difficulty level: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui level kesulitan.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function destroyDifficultyLevel(Project $project, DifficultyLevel $difficultyLevel)
{
    // Log request information for debugging
    \Log::info('Delete difficulty level request:', [
        'project_id' => $project->id,
        'level_id' => $difficultyLevel->id ?? 'Not found'
    ]);
    
    if (!$difficultyLevel->exists) {
        return response()->json([
            'success' => false,
            'message' => 'Level tidak ditemukan.'
        ], 404);
    }
    
    if ($difficultyLevel->project_id !== $project->id) {
        return response()->json([
            'success' => false,
            'message' => 'Level tidak ditemukan dalam proyek ini.'
        ], 403);
    }

    try {
        $difficultyLevel->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Level Kesulitan berhasil dihapus.'
        ]);
    } catch (\Exception $e) {
        \Log::error('Error deleting difficulty level: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus level: ' . $e->getMessage()
        ], 500);
    }
}

    // --- Priority Level CRUD (Mirip Difficulty) ---
    public function storePriorityLevel(Request $request, Project $project)
{
    // Validate project ownership or access rights
    if (Auth::id() !== $project->owner_id) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk menambah level prioritas.'
        ], 403);
    }

    // Validate incoming data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'value' => 'required|integer|min:1',
        'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
    ]);

    try {
        // Set project ID and calculate display order
        $validated['project_id'] = $project->id;
        $maxOrder = PriorityLevel::where('project_id', $project->id)->max('display_order') ?? 0;
        $validated['display_order'] = $maxOrder + 1;

        // Create the priority level
        $level = PriorityLevel::create($validated);

        // Return success response with created level
        return response()->json([
            'success' => true,
            'message' => 'Level Prioritas berhasil ditambahkan.',
            'level' => $level
        ], 201); // 201 Created status
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error creating priority level: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'Gagal menambah level prioritas.',
            'error' => $e->getMessage()
        ], 500);
    }
}
    
public function updatePriorityLevel(Request $request, Project $project, PriorityLevel $priorityLevel)
{
    // Validate project ownership or access rights
    if (Auth::id() !== $project->owner_id) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk mengubah level prioritas.'
        ], 403);
    }

    // Validate the priority level belongs to the project
    if ($priorityLevel->project_id !== $project->id) {
        return response()->json([
            'success' => false,
            'message' => 'Level prioritas tidak ditemukan dalam proyek ini.'
        ], 404);
    }

    // Validate incoming data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'value' => 'required|integer|min:1',
        'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'], 
    ]);

    try {
        // Update the priority level
        $priorityLevel->update($validated);

        // Return success response with updated level
        return response()->json([
            'success' => true,
            'message' => 'Level Prioritas berhasil diperbarui.',
            'level' => $priorityLevel->fresh() // Refresh to get updated data
        ]);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error updating priority level: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui level prioritas.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function destroyPriorityLevel(Project $project, PriorityLevel $priorityLevel)
{
    // Log request information for debugging
    \Log::info('Delete priority level request:', [
        'project_id' => $project->id,
        'level_id' => $priorityLevel->id ?? 'Not found'
    ]);
    
    // Validate project ownership or access rights
    if (Auth::id() !== $project->owner_id) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk menghapus level prioritas.'
        ], 403);
    }

    // Check if priority level exists
    if (!$priorityLevel->exists) {
        return response()->json([
            'success' => false,
            'message' => 'Level prioritas tidak ditemukan.'
        ], 404);
    }
    
    // Verify the priority level belongs to the project
    if ($priorityLevel->project_id !== $project->id) {
        return response()->json([
            'success' => false,
            'message' => 'Level prioritas tidak ditemukan dalam proyek ini.'
        ], 403);
    }

    try {
        // Delete the priority level
        $priorityLevel->delete();
        
        // Reorder remaining levels (optional)
        $this->reorderPriorityLevels($project);
        
        return response()->json([
            'success' => true,
            'message' => 'Level Prioritas berhasil dihapus.'
        ]);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error deleting priority level: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus level prioritas: ' . $e->getMessage()
        ], 500);
    }
}

private function reorderPriorityLevels(Project $project)
{
    $levels = PriorityLevel::where('project_id', $project->id)
        ->orderBy('display_order')
        ->get();

    foreach ($levels as $index => $level) {
        $level->update(['display_order' => $index + 1]);
    }
}
    // --- **NEW: Update Level Order ---**
    public function updateOrder(Request $request, Project $project)
    {
        // $this->authorize('update', $project);

        $request->validate([
            'level_type' => 'required|in:difficulty,priority',
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:'.($request->level_type === 'difficulty' ? 'difficulty_levels' : 'priority_levels').',id',
        ]);

        $levelType = $request->level_type;
        $orderedIds = $request->ordered_ids;
        $modelClass = ($levelType === 'difficulty') ? DifficultyLevel::class : PriorityLevel::class;

        DB::beginTransaction();
        try {
            foreach ($orderedIds as $index => $id) {
                $modelClass::where('id', $id)
                           ->where('project_id', $project->id) // Ensure level belongs to project
                           ->update(['display_order' => $index + 1]); // Update order starting from 1
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Urutan level berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating level order: " . $e->getMessage()); // Log error
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui urutan level.'], 500);
        }
    }

    // --- ** NEW: Update Standar Gaji Anggota Tim via AJAX --- **
    public function updateMemberWageStandard(Request $request, Project $project, User $user)
    {
        if (Auth::id() !== $project->owner_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            // Bisa null jika ingin menghapus assignment
            'wage_standard_id' => 'nullable|exists:wage_standards,id',
        ]);

        // Pastikan wage standard (jika dipilih) milik proyek ini
        if ($validated['wage_standard_id']) {
            $wageStandard = WageStandard::find($validated['wage_standard_id']);
            if (!$wageStandard || $wageStandard->project_id !== $project->id) {
                return response()->json(['success' => false, 'message' => 'Invalid Wage Standard selected.'], 422);
            }
        }

        // Pastikan user adalah member proyek
        $member = $project->workers()->where('user_id', $user->id)->wherePivot('status', 'accepted')->exists();
        if (!$member) {
             return response()->json(['success' => false, 'message' => 'User is not an active member of this project.'], 404);
        }

        try {
            // Update pivot table
            $project->workers()->updateExistingPivot($user->id, [
                'wage_standard_id' => $validated['wage_standard_id'] // Bisa null
            ]);

            return response()->json(['success' => true, 'message' => 'Wage standard for ' . $user->name . ' updated successfully.']);
        } catch (\Exception $e) {
            \Log::error("Error updating member wage standard: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update wage standard.'], 500);
        }
    }
}