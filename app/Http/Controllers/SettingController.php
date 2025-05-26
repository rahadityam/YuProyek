<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;
use App\Models\Category;
use App\Models\WageStandard;
use App\Models\PaymentTerm; // <-- BARU: Import PaymentTerm
use App\Models\Task;
use App\Models\User;
use App\Models\ProjectUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // <-- BARU: Import Validator

class SettingController extends Controller
{
    // --- Menampilkan Halaman Pengaturan Terpadu ---
    public function index(Project $project)
    {
        // Autorisasi: Hanya pemilik proyek yang bisa akses
        // if (Auth::id() !== $project->owner_id) {
        //     abort(403, 'Unauthorized action.');
        // }
        $this->authorize('viewSettings', $project);

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
        // --- BARU: Ambil Payment Terms ---
        $paymentTerms = $project->paymentTerms()->orderBy('start_date')->get();

        // Data untuk tab Kriteria
        $difficultyLevels = $project->difficultyLevels()->orderBy('display_order', 'asc')->get();
        $priorityLevels = $project->priorityLevels()->orderBy('display_order', 'asc')->get();

        return view('projects.pengaturan', compact(
            'project',
            'categories',
            'selectedCategories',
            'wageStandards',
            'members',
            'paymentTerms', // <-- BARU: Kirim paymentTerms ke view
            'difficultyLevels',
            'priorityLevels'
        ));
    }

    // --- Method untuk Update Info Proyek SAJA ---
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
        ]);

        // Update project data
        $project->update($validated);

        // Sync categories
        if ($request->has('categories')) {
            $project->categories()->sync($request->categories);
        } else {
            $project->categories()->detach();
        }

        // Gunakan key flash message berbeda agar bisa menampilkan di tab yang benar
        return redirect()->route('projects.pengaturan', $project)
                         ->with('success_info', 'Informasi proyek berhasil diperbarui!')
                         ->with('active_tab', 'project'); // <-- Kembalikan ke tab project
    }

    // --- Method BARU untuk Update Tipe Kalkulasi Pembayaran ---
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
                         ->with('success_financial', 'Metode kalkulasi pembayaran berhasil diperbarui!')
                         ->with('active_tab', 'financial'); // <-- Kembalikan ke tab financial
    }

    // --- BARU: Method untuk Update Payment Terms ---
    public function updatePaymentTerms(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        $input = $request->all();
        $terms = $input['terms'] ?? [];
        $errors = [];

        // Validasi setiap termin
        foreach ($terms as $index => $termData) {
            $validator = Validator::make($termData, [
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'id' => 'nullable|integer|exists:payment_terms,id,project_id,' . $project->id, // Validasi ID jika ada
                'delete' => 'nullable|boolean', // Untuk flag hapus
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $message) {
                    $errors["terms.$index.$field"] = $message;
                }
            }
        }

        // Validasi nama termin unik dalam request (dan terhadap yang sudah ada di DB)
        $termNames = collect($terms)->pluck('name')->map('strtolower');
        $existingNames = PaymentTerm::where('project_id', $project->id)
            ->whereNotIn('id', collect($terms)->pluck('id')->filter()) // Exclude yang diedit
            ->pluck('name')->map('strtolower');

        foreach ($terms as $index => $termData) {
            if (!isset($termData['delete']) || !$termData['delete']) { // Hanya cek yang tidak dihapus
                $currentNameLower = strtolower($termData['name']);
                // Cek duplikat dalam request
                if ($termNames->filter(fn($name) => $name === $currentNameLower)->count() > 1) {
                    $errors["terms.$index.name"][] = 'Nama termin tidak boleh duplikat dalam satu proyek.';
                }
                // Cek duplikat dengan yang sudah ada di DB (kecuali dirinya sendiri jika edit)
                if ($existingNames->contains($currentNameLower)) {
                    $errors["terms.$index.name"][] = 'Nama termin sudah ada dalam proyek ini.';
                }
            }
        }


        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput()->with('active_tab', 'financial');
        }

        DB::beginTransaction();
        try {
            $existingTermIds = [];
            foreach ($terms as $termData) {
                $termData['project_id'] = $project->id;

                if (isset($termData['id']) && $termData['id']) {
                     $existingTermIds[] = $termData['id'];
                     $term = PaymentTerm::find($termData['id']);
                     if ($term) {
                          if (isset($termData['delete']) && $termData['delete']) {
                                // Cek jika ada payment terkait sebelum hapus? (Opsional, tergantung kebijakan)
                                // if ($term->payments()->exists()) {
                                //     throw new \Exception("Tidak dapat menghapus termin '{$term->name}' karena sudah ada pembayaran terkait.");
                                // }
                               $term->delete();
                          } else {
                               // Update data term yang ada
                               $term->update([
                                   'name' => $termData['name'],
                                   'start_date' => $termData['start_date'],
                                   'end_date' => $termData['end_date'],
                               ]);
                          }
                     }
                } elseif (!isset($termData['delete']) || !$termData['delete']) {
                    // Buat termin baru (hanya jika tidak ada flag delete)
                    PaymentTerm::create([
                        'project_id' => $project->id,
                        'name' => $termData['name'],
                        'start_date' => $termData['start_date'],
                        'end_date' => $termData['end_date'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('projects.pengaturan', $project)
                ->with('success_financial', 'Data termin pembayaran berhasil diperbarui.')
                ->with('active_tab', 'financial');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update payment terms for project {$project->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Gagal memperbarui termin: ' . $e->getMessage()])->withInput()->with('active_tab', 'financial');
        }
    }


    // HAPUS Method 'update' yang lama jika sudah tidak dipakai (jika semua update dipecah)
    // public function update(Request $request, Project $project) { ... }

    // --- Weight Management ---
    // ... (Tidak berubah) ...
    public function editWeights(Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }
        return view('projects.settings-weights', compact('project')); // <-- Ganti nama view jika perlu
    }

    public function updateWeights(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }
        $validated = $request->validate([
            'difficulty_weight' => 'required|integer|min:0|max:100',
            'priority_weight' => 'required|integer|min:0|max:100',
        ]);

        if (($validated['difficulty_weight'] + $validated['priority_weight']) != 100) {
            return back()->withErrors(['weights' => 'Total bobot Kesulitan dan Prioritas harus 100%.'])->withInput()->with('active_tab', 'criteria');
        }
        $project->update($validated);
        return redirect()->route('projects.pengaturan', $project)
                ->with('success_criteria', 'Bobot WSM berhasil diperbarui.')
                ->with('active_tab', 'criteria'); // Gunakan key berbeda agar bisa direct ke tab
    }

    // --- Level Management (Difficulty & Priority) ---
    // ... (Tidak berubah) ...
    public function manageLevels(Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        $difficultyLevels = $project->difficultyLevels()->orderBy('display_order', 'asc')->get();
        $priorityLevels = $project->priorityLevels()->orderBy('display_order', 'asc')->get();

        return view('projects.settings-levels', compact('project', 'difficultyLevels', 'priorityLevels')); // <-- Ganti nama view jika perlu
    }

    // --- Difficulty Level CRUD ---
    // ... (Tidak berubah) ...
    public function storeDifficultyLevel(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:difficulty_levels,name,NULL,id,project_id,'.$project->id, // Unique per project
            'value' => 'required|integer|min:1|unique:difficulty_levels,value,NULL,id,project_id,'.$project->id, // Unique per project
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'], // Validasi hex color
        ]);

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

        return back()->with('success_criteria', 'Level Kesulitan berhasil ditambahkan.')
                     ->with('active_tab', 'criteria');
    }

    public function updateDifficultyLevel(Request $request, Project $project, DifficultyLevel $difficultyLevel)
    {
        if (Auth::id() !== $project->owner_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        if ($difficultyLevel->project_id !== $project->id) {
             return response()->json(['success' => false, 'message' => 'Level not found in this project.'], 404);
         }

        $validated = $request->validate([
             'name' => ['required','string','max:255', Rule::unique('difficulty_levels', 'name')->ignore($difficultyLevel->id)->where('project_id', $project->id)],
             'value' => ['required','integer','min:1', Rule::unique('difficulty_levels', 'value')->ignore($difficultyLevel->id)->where('project_id', $project->id)],
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        try {
            $difficultyLevel->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Level Kesulitan berhasil diperbarui.',
                'level' => $difficultyLevel->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating difficulty level: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui level kesulitan.'], 500);
        }
    }

    public function destroyDifficultyLevel(Project $project, DifficultyLevel $difficultyLevel)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }
        if ($difficultyLevel->project_id !== $project->id) { abort(404); }

        // Cek relasi ke task (opsional, tapi direkomendasikan)
        if (Task::where('difficulty_level_id', $difficultyLevel->id)->exists()) {
             if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Level tidak dapat dihapus karena masih digunakan oleh task.'
                ], 400); // Bad Request
            }
             return back()->withErrors(['delete_level' => 'Level tidak dapat dihapus karena masih digunakan oleh task.'])
                          ->with('active_tab', 'criteria');
         }

        try {
            $difficultyLevel->delete();
             if (request()->ajax() || request()->wantsJson()) {
                 return response()->json(['success' => true, 'message' => 'Level Kesulitan berhasil dihapus.']);
             }
             return back()->with('success_criteria', 'Level Kesulitan berhasil dihapus.')
                          ->with('active_tab', 'criteria');
        } catch (\Exception $e) {
             Log::error('Error deleting difficulty level: ' . $e->getMessage());
             if (request()->ajax() || request()->wantsJson()) {
                 return response()->json(['success' => false, 'message' => 'Gagal menghapus level.'], 500);
             }
             return back()->withErrors(['delete_level' => 'Gagal menghapus level.'])
                          ->with('active_tab', 'criteria');
         }
    }

    // --- Priority Level CRUD (Mirip Difficulty) ---
    // ... (Tidak berubah) ...
     public function storePriorityLevel(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:priority_levels,name,NULL,id,project_id,'.$project->id,
            'value' => 'required|integer|min:1|unique:priority_levels,value,NULL,id,project_id,'.$project->id,
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $validated['project_id'] = $project->id;
        $maxOrder = PriorityLevel::where('project_id', $project->id)->max('display_order') ?? 0;
        $validated['display_order'] = $maxOrder + 1;

        $level = PriorityLevel::create($validated);

         if ($request->ajax() || $request->wantsJson()) {
             return response()->json([
                'success' => true,
                'message' => 'Level Prioritas berhasil ditambahkan.',
                'level' => $level
             ], 201);
         }

        return back()->with('success_criteria', 'Level Prioritas berhasil ditambahkan.')
                     ->with('active_tab', 'criteria');
    }

    public function updatePriorityLevel(Request $request, Project $project, PriorityLevel $priorityLevel)
    {
        if (Auth::id() !== $project->owner_id) {
             return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        if ($priorityLevel->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'Level not found in this project.'], 404);
        }

        $validated = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('priority_levels', 'name')->ignore($priorityLevel->id)->where('project_id', $project->id)],
            'value' => ['required','integer','min:1', Rule::unique('priority_levels', 'value')->ignore($priorityLevel->id)->where('project_id', $project->id)],
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        try {
            $priorityLevel->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Level Prioritas berhasil diperbarui.',
                'level' => $priorityLevel->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating priority level: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui level prioritas.'], 500);
        }
    }

    public function destroyPriorityLevel(Project $project, PriorityLevel $priorityLevel)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }
        if ($priorityLevel->project_id !== $project->id) { abort(404); }

         // Cek relasi ke task
         if (Task::where('priority_level_id', $priorityLevel->id)->exists()) {
             if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Level tidak dapat dihapus karena masih digunakan oleh task.'
                ], 400);
            }
              return back()->withErrors(['delete_level' => 'Level tidak dapat dihapus karena masih digunakan oleh task.'])
                           ->with('active_tab', 'criteria');
          }

        try {
            $priorityLevel->delete();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Level Prioritas berhasil dihapus.']);
            }
             return back()->with('success_criteria', 'Level Prioritas berhasil dihapus.')
                          ->with('active_tab', 'criteria');
        } catch (\Exception $e) {
            Log::error('Error deleting priority level: ' . $e->getMessage());
             if (request()->ajax() || request()->wantsJson()) {
                 return response()->json(['success' => false, 'message' => 'Gagal menghapus level.'], 500);
             }
             return back()->withErrors(['delete_level' => 'Gagal menghapus level.'])
                          ->with('active_tab', 'criteria');
        }
    }

    // --- **NEW: Update Level Order ---**
    // ... (Tidak berubah) ...
    public function updateOrder(Request $request, Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        $request->validate([
            'level_type' => 'required|in:difficulty,priority',
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:'.($request->level_type === 'difficulty' ? 'difficulty_levels' : 'priority_levels').',id,project_id,'.$project->id, // Pastikan ID milik project
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

    // --- ** Update Standar Gaji Anggota Tim via AJAX --- **
    // ... (Tidak berubah) ...
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