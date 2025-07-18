<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;
use App\Models\Category;
use App\Models\WageStandard;
use App\Models\Payment;
use App\Models\PaymentTerm;
use App\Models\Task;
use App\Models\User;
use App\Models\ProjectUser;
use App\Models\ProjectPosition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('viewSettings', $project);

        $wageStandards = $project->wageStandards()->orderBy('job_category')->get();
        $members = $project->workers()->wherePivot('status', 'accepted')->withPivot('wage_standard_id')->orderBy('name')->get();
        $paymentTerms = $project->paymentTerms()->orderBy('start_date')->get();
        $difficultyLevels = $project->difficultyLevels()->orderBy('display_order', 'asc')->get();
        $priorityLevels = $project->priorityLevels()->orderBy('display_order', 'asc')->get();
        $projectFiles = $project->files()->paginate(10);
        $isProjectLocked = Payment::where('project_id', $project->id)
                                ->where('status', Payment::STATUS_APPROVED)
                                ->exists();

        return view('projects.pengaturan', compact(
            'project',
            'wageStandards',
            'members',
            'paymentTerms',
            'difficultyLevels',
            'priorityLevels',
            'projectFiles',
            'isProjectLocked'
        ));
    }

    public function updateProjectInfo(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $isLocked = Payment::where('project_id', $project->id)
                           ->where('status', Payment::STATUS_APPROVED)
                           ->exists();

        $baseRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:open,in_progress,completed,cancelled',
            'wip_limits' => 'nullable|integer|min:1',
            'positions' => 'nullable|array',
            'positions.*.id' => 'nullable|integer|exists:project_positions,id,project_id,' . $project->id,
            'positions.*.delete' => 'nullable|boolean',
        ];

        $lockableRules = [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
        ];
        
        $rules = $baseRules;
        if (!$isLocked) {
            $rules = array_merge($baseRules, $lockableRules);
        }

        foreach ($request->input('positions', []) as $key => $position) {
            if (!isset($position['delete']) || !$position['delete']) {
                $rules["positions.{$key}.name"] = 'required|string|max:255';
                $rules["positions.{$key}.count"] = 'required|integer|min:1';
            }
        }

        $validated = $request->validate($rules, [
            'positions.*.name.required' => 'Nama posisi wajib diisi.',
            'positions.*.count.required' => 'Jumlah untuk posisi wajib diisi.',
            'positions.*.count.min' => 'Jumlah untuk posisi minimal 1.',
        ]);

        DB::beginTransaction();
        try {
            // FIX: Safely assign wip_limits
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'wip_limits' => $validated['wip_limits'] ?? $project->wip_limits, // Use old value if not present
            ];

            if (!$isLocked) {
                $updateData['start_date'] = $validated['start_date'];
                $updateData['end_date'] = $validated['end_date'];
                $updateData['budget'] = $validated['budget'];
            }
            
            $project->update($updateData);

            if (isset($validated['positions'])) {
                foreach ($validated['positions'] as $positionData) {
                    if (isset($positionData['id']) && $positionData['id']) {
                        $position = ProjectPosition::find($positionData['id']);
                        if ($position && $position->project_id === $project->id) {
                            if (isset($positionData['delete']) && $positionData['delete']) {
                                if (!$isLocked) $position->delete();
                            } else {
                                if (isset($positionData['name']) && isset($positionData['count'])) {
                                     if (!$isLocked) $position->update([
                                        'name' => $positionData['name'],
                                        'count' => $positionData['count'],
                                    ]);
                                }
                            }
                        }
                    } elseif (!isset($positionData['delete']) || !$positionData['delete']) {
                        if (isset($positionData['name']) && isset($positionData['count'])) {
                             if (!$isLocked) $project->projectPositions()->create([
                                'name' => $positionData['name'],
                                'count' => $positionData['count'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('projects.pengaturan', $project)
                             ->with('success_info', 'Informasi proyek berhasil diperbarui!')
                             ->with('active_tab', 'project');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating project info for project {$project->id}: " . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['general' => 'Gagal memperbarui informasi proyek: ' . $e->getMessage()])
                         ->withInput()
                         ->with('active_tab', 'project');
        }
    }

    public function updatePaymentCalculationType(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        // FIX: Tambahkan pengecekan kunci proyek
        $isLocked = Payment::where('project_id', $project->id)
                           ->where('status', Payment::STATUS_APPROVED)
                           ->exists();

        if ($isLocked) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metode kalkulasi pembayaran tidak dapat diubah karena proyek sudah terkunci.',
                ], 403);
            }
            return redirect()->route('projects.pengaturan', $project)
                             ->withErrors(['general' => 'Metode kalkulasi pembayaran tidak dapat diubah karena sudah ada pembayaran yang disetujui.'])
                             ->with('active_tab', 'financial');
        }
        // End of FIX

        $validated = $request->validate([
            'payment_calculation_type' => ['required', Rule::in(['termin', 'task', 'full'])],
        ]);

        $project->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Metode kalkulasi pembayaran berhasil diperbarui!',
                'project' => $project->fresh()->only('id', 'payment_calculation_type')
            ]);
        }

        return redirect()->route('projects.pengaturan', $project)
                         ->with('success_financial', 'Metode kalkulasi pembayaran berhasil diperbarui!')
                         ->with('active_tab', 'financial');
    }

    public function updatePaymentTerms(Request $request, Project $project)
    {
        // Authorization check
        if (Auth::id() !== $project->owner_id) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        $terms = $input['terms'] ?? [];
        $errors = [];

        // Ensure project has valid start and end dates
        if (!$project->start_date || !$project->end_date) {
            return back()->withErrors(['general' => 'Proyek harus memiliki tanggal mulai dan selesai yang valid.'])
                ->withInput()
                ->with('active_tab', 'financial');
        }

        // Collect term names for uniqueness check
        $termNames = collect($terms)->pluck('name')->map('strtolower');
        $existingNames = PaymentTerm::where('project_id', $project->id)
            ->whereNotIn('id', collect($terms)->pluck('id')->filter())
            ->pluck('name')->map('strtolower');

        // Sort terms by start_date for overlap and gap checks
        $sortedTerms = collect($terms)->sortBy('start_date')->values()->all();

        // Validate each term
        foreach ($terms as $index => $termData) {
            $validator = Validator::make($termData, [
                'name' => 'required|string|max:255',
                'start_date' => [
                    'required',
                    'date',
                    'after_or_equal:' . $project->start_date->toDateString(),
                    'before_or_equal:' . $project->end_date->toDateString(),
                ],
                'end_date' => [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                    'before_or_equal:' . $project->end_date->toDateString(),
                ],
                'id' => 'nullable|integer|exists:payment_terms,id,project_id,' . $project->id,
                'delete' => 'nullable|boolean',
            ], [
                'start_date.after_or_equal' => 'Tanggal mulai termin harus berada di atau setelah tanggal mulai proyek.',
                'start_date.before_or_equal' => 'Tanggal mulai termin tidak boleh melebihi tanggal selesai proyek.',
                'end_date.after_or_equal' => 'Tanggal akhir termin harus berada di atau setelah tanggal mulai termin.',
                'end_date.before_or_equal' => 'Tanggal akhir termin tidak boleh melebihi tanggal selesai proyek.',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errors["terms.$index.$field"] = $messages;
                }
            }

            // Check for unique term names within request
            if (!isset($termData['delete']) || !$termData['delete']) {
                $currentNameLower = strtolower($termData['name']);
                if ($termNames->filter(fn($name) => $name === $currentNameLower)->count() > 1) {
                    $errors["terms.$index.name"][] = 'Nama termin tidak boleh duplikat dalam satu proyek.';
                }
                if ($existingNames->contains($currentNameLower)) {
                    $errors["terms.$index.name"][] = 'Nama termin sudah ada dalam proyek ini.';
                }
            }
        }

        // Validate no overlaps and ensure dates are sequential
        for ($i = 0; $i < count($sortedTerms) - 1; $i++) {
            if (isset($sortedTerms[$i]['delete']) && $sortedTerms[$i]['delete']) {
                continue; // Skip terms marked for deletion
            }

            $currentTerm = $sortedTerms[$i];
            $nextTerm = $sortedTerms[$i + 1];

            if (isset($nextTerm['delete']) && $nextTerm['delete']) {
                continue; // Skip if next term is marked for deletion
            }

            $currentEndDate = new \DateTime($currentTerm['end_date']);
            $nextStartDate = new \DateTime($nextTerm['start_date']);

            // Check for overlap (next term starts before current term ends)
            if ($nextStartDate <= $currentEndDate) {
                $errors["terms." . array_search($nextTerm, $terms) . ".start_date"][] = 
                    "Tanggal mulai termin '{$nextTerm['name']}' tidak boleh sebelum atau sama dengan tanggal akhir termin sebelumnya ('{$currentTerm['name']}').";
            }

            // Optional: Check for gaps between terms (uncomment if gaps are not allowed)
            /*
            $nextDayAfterCurrent = (clone $currentEndDate)->modify('+1 day');
            if ($nextStartDate > $nextDayAfterCurrent) {
                $errors["terms." . array_search($nextTerm, $terms) . ".start_date"][] = 
                    "Tanggal mulai termin '{$nextTerm['name']}' harus tepat sehari setelah tanggal akhir termin sebelumnya ('{$currentTerm['name']}').";
            }
            */
        }

        // Return validation errors if any
        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput()->with('active_tab', 'financial');
        }

        Log::info("Memulai updatePaymentTerms untuk Proyek ID: {$project->id}");
    Log::info("Data termin yang diterima dari request:", $request->input('terms', []));

    DB::beginTransaction();
    try {
        $termsData = $request->input('terms', []);
        $idsFromRequest = []; // Array untuk menampung ID dari termin yang di-request

        foreach ($termsData as $index => $termData) {
            
            // Logika untuk UPDATE atau CREATE
            if (!empty($termData['id'])) {
                // UPDATE: term yang sudah ada
                $term = PaymentTerm::find($termData['id']);
                if ($term) {
                    $idsFromRequest[] = $term->id; // Tambahkan ID ke daftar yang valid

                    if (isset($termData['delete']) && $termData['delete'] == '1') {
                        Log::info("Menandai untuk menghapus termin ID: {$term->id}");
                        $term->delete();
                    } else {
                        Log::info("Memperbarui termin ID: {$term->id}");
                        $term->update([
                            'name' => $termData['name'],
                            'start_date' => $termData['start_date'],
                            'end_date' => $termData['end_date'],
                        ]);
                    }
                }
            } else {
                // CREATE: term baru
                if (!isset($termData['delete']) || !$termData['delete']) {
                    Log::info("Membuat termin baru. Data:", $termData);
                    $newTerm = PaymentTerm::create([
                        'project_id' => $project->id,
                        'name' => $termData['name'],
                        'start_date' => $termData['start_date'],
                        'end_date' => $termData['end_date'],
                    ]);
                    // ===== PERBAIKAN KUNCI: Tambahkan ID baru ke daftar =====
                    $idsFromRequest[] = $newTerm->id; 
                }
            }
        }

        // ===== PERBAIKAN LOGIKA PENGHAPUSAN =====
        // Hapus hanya termin yang ada di DB tapi tidak ada di daftar ID yang kita proses
        // (baik yang di-update maupun yang baru dibuat).
        Log::info("ID termin yang diproses (tidak akan dihapus):", $idsFromRequest);
        
        $termsToDelete = PaymentTerm::where('project_id', $project->id)->whereNotIn('id', $idsFromRequest)->get();
        if ($termsToDelete->isNotEmpty()) {
            foreach ($termsToDelete as $term) {
                Log::info("Menghapus termin usang (tidak ada di request & tidak baru dibuat) ID: {$term->id}, Nama: {$term->name}");
                $term->delete();
            }
        } else {
            Log::info("Tidak ada termin usang yang perlu dihapus.");
        }

        DB::commit();
        Log::info("Transaksi berhasil di-commit untuk Proyek ID: {$project->id}");

        return redirect()->route('projects.pengaturan', $project)
            ->with('success_financial', 'Data termin pembayaran berhasil diperbarui.')
            ->with('active_tab', 'financial');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Gagal memperbarui termin untuk Proyek ID {$project->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return back()->withErrors(['general' => 'Gagal memperbarui termin: ' . $e->getMessage()])->withInput()->with('active_tab', 'financial');
    }
}

    // HAPUS Method 'update' yang lama jika sudah tidak dipakai (jika semua update dipecah)
    // public function update(Request $request, Project $project) { ... }

    public function editWeights(Request $request, Project $project) // <-- Tambahkan Request $request
    {
        // Otorisasi sederhana, bisa disempurnakan dengan Policy
        if (Auth::id() !== $project->owner_id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }
            abort(403);
        }

        // ==========================================================
        // ===== BLOK PERBAIKAN UNTUK API DIMULAI DI SINI =====
        // ==========================================================
        
        // Jika request dari API, kembalikan data bobot dalam format JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'difficulty_weight' => $project->difficulty_weight,
                    'priority_weight' => $project->priority_weight,
                ]
            ]);
        }
        
        // ==========================================================
        // ===== AKHIR BLOK PERBAIKAN =====
        // ==========================================================

        // Jika request dari web, redirect ke halaman pengaturan utama
        // karena tidak ada view terpisah untuk ini.
        return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'criteria']);
    }

    public function updateWeights(Request $request, Project $project) // Tambahkan Request
    {
        // Otorisasi: Hanya owner yang bisa mengubah
        $this->authorize('update', $project);

        $validated = $request->validate([
            'difficulty_weight' => 'required|integer|min:0|max:100',
            'priority_weight' => 'required|integer|min:0|max:100',
        ]);

        if (($validated['difficulty_weight'] + $validated['priority_weight']) != 100) {
            // Jika validasi gagal, kembalikan error JSON untuk API
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'weights_total' => ['Total bobot Kesulitan dan Prioritas harus 100%.']
                    ]
                ], 422);
            }
            return back()->withErrors(['weights' => 'Total bobot Kesulitan dan Prioritas harus 100%.'])->withInput()->with('active_tab', 'criteria');
        }

        $project->update($validated);
        
        // ==========================================================
        // ===== BLOK PERBAIKAN UNTUK API DIMULAI DI SINI =====
        // ==========================================================
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bobot WSM berhasil diperbarui.',
                'data' => $project->fresh()->only('difficulty_weight', 'priority_weight')
            ]);
        }
        
        // ==========================================================
        // ===== AKHIR BLOK PERBAIKAN =====
        // ==========================================================

        return redirect()->route('projects.pengaturan', $project)
                ->with('success_criteria', 'Bobot WSM berhasil diperbarui.')
                ->with('active_tab', 'criteria');
    }

    public function manageLevels(Project $project)
    {
        if (Auth::id() !== $project->owner_id) { abort(403); }

        $difficultyLevels = $project->difficultyLevels()->orderBy('display_order', 'asc')->get();
        $priorityLevels = $project->priorityLevels()->orderBy('display_order', 'asc')->get();

        return view('projects.settings-levels', compact('project', 'difficultyLevels', 'priorityLevels')); // <-- Ganti nama view jika perlu
    }

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

    /**
     * Batch update wage standards for team members.
     */
    public function batchUpdateMemberWageStandards(Request $request, Project $project)
    {
        // Autorisasi: Hanya pemilik proyek
        if (Auth::id() !== $project->owner_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.user_id' => 'required|integer|exists:users,id',
            'assignments.*.wage_standard_id' => [
                'nullable',
                'integer',
                Rule::exists('wage_standards', 'id')->where(function ($query) use ($project) {
                    return $query->where('project_id', $project->id);
                }),
            ],
        ], [
            'assignments.*.wage_standard_id.exists' => 'Salah satu standar upah yang dipilih tidak valid untuk proyek ini.'
        ]);

        $updatedAssignmentsForFrontend = [];

        DB::beginTransaction();
        try {
            foreach ($validated['assignments'] as $assignment) {
                $user = User::find($assignment['user_id']);
                
                // Pastikan user adalah member aktif proyek ini
                $isMember = $project->workers()
                                    ->where('user_id', $user->id)
                                    ->wherePivot('status', 'accepted') // Hanya member yang accepted
                                    ->exists();

                if (!$isMember) {
                    Log::warning("Attempted to update wage standard for non-accepted member user {$user->id} in project {$project->id}. Skipping.");
                    continue; // Lewati jika bukan member aktif
                }

                $project->workers()->updateExistingPivot($user->id, [
                    'wage_standard_id' => $assignment['wage_standard_id'] // Ini bisa null
                ]);

                // Kumpulkan data yang akan dikirim balik ke frontend untuk update UI
                $updatedAssignmentsForFrontend[] = [
                    'user_id' => $assignment['user_id'],
                    'wage_standard_id' => $assignment['wage_standard_id'],
                ];
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Standar upah anggota tim berhasil diperbarui.',
                'updatedAssignments' => $updatedAssignmentsForFrontend // Kirim balik data yang diupdate
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error batch updating member wage standards for project {$project->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui standar upah anggota tim. Silakan coba lagi.'], 500);
        }
    }

    // public function updateMemberWageStandard(Request $request, Project $project, User $user)
    // {
    //     if (Auth::id() !== $project->owner_id) {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    //     }

    //     $validated = $request->validate([
    //         // Bisa null jika ingin menghapus assignment
    //         'wage_standard_id' => 'nullable|exists:wage_standards,id',
    //     ]);

    //     // Pastikan wage standard (jika dipilih) milik proyek ini
    //     if ($validated['wage_standard_id']) {
    //         $wageStandard = WageStandard::find($validated['wage_standard_id']);
    //         if (!$wageStandard || $wageStandard->project_id !== $project->id) {
    //             return response()->json(['success' => false, 'message' => 'Invalid Wage Standard selected.'], 422);
    //         }
    //     }

    //     // Pastikan user adalah member proyek
    //     $member = $project->workers()->where('user_id', $user->id)->wherePivot('status', 'accepted')->exists();
    //     if (!$member) {
    //          return response()->json(['success' => false, 'message' => 'User is not an active member of this project.'], 404);
    //     }

    //     try {
    //         // Update pivot table
    //         $project->workers()->updateExistingPivot($user->id, [
    //             'wage_standard_id' => $validated['wage_standard_id'] // Bisa null
    //         ]);

    //         return response()->json(['success' => true, 'message' => 'Wage standard for ' . $user->name . ' updated successfully.']);
    //     } catch (\Exception $e) {
    //         \Log::error("Error updating member wage standard: " . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Failed to update wage standard.'], 500);
    //     }
    // }

    public function storeProjectFile(Request $request, Project $project)
    {
        $this->authorize('update', $project); // Atau buat policy khusus 'manageFiles'

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB, sesuaikan
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            // Buat nama file yang unik untuk menghindari konflik
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
            // Simpan file di storage/app/public/project_files/{project_id}
            $filePath = $file->storeAs("project_files/{$project->id}", $fileName, 'public');

            ProjectFile::create([
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'file_name' => $originalName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'display_name' => $request->input('display_name', $originalName),
                'description' => $request->input('description'),
            ]);

            return back()->with('success_files', 'File berhasil diunggah.')
                         ->with('active_tab', 'files');
        }

        return back()->withErrors(['file' => 'Gagal mengunggah file. Pastikan file terlampir.'])
                     ->with('active_tab', 'files');
    }

    public function destroyProjectFile(Request $request, Project $project, ProjectFile $projectFile)
    {
        $this->authorize('update', $project); // Atau policy 'manageFiles'

        if ($projectFile->project_id !== $project->id) {
            abort(404);
        }

        // Hapus file dari storage
        if (Storage::disk('public')->exists($projectFile->file_path)) {
            Storage::disk('public')->delete($projectFile->file_path);
        }

        // Hapus record dari database
        $projectFile->delete();

        return back()->with('success_files', 'File berhasil dihapus.')
                     ->with('active_tab', 'files');
    }
}