<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentTerm; // <-- BARU: Import PaymentTerm
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * [REVISI] Menampilkan halaman Buat Slip Gaji & List Draft Slip Gaji.
     */
    public function createAndDraftPayslip(Project $project, Request $request)
    {
        if ($project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // --- Data untuk Form Pembuatan Slip Gaji ---
        $workers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();
        $paymentCalculationType = $project->payment_calculation_type ?? 'task';

        // --- Ambil data termin jika tipe 'termin' dan format tanggalnya ---
        $paymentTerms = collect();
        if ($paymentCalculationType === 'termin') {
            $paymentTerms = $project->paymentTerms()
                                     ->select('id', 'name', 'start_date', 'end_date') // Pilih kolom spesifik
                                     ->orderBy('start_date')
                                     ->get()
                                     ->map(function ($term) {
                                         // Pastikan format YYYY-MM-DD dan handle null
                                         // Kita akan kirim object Carbon ke view, formatting di view/JS
                                         $term->start_date_formatted = $term->start_date ? $term->start_date->toDateString() : null;
                                         $term->end_date_formatted = $term->end_date ? $term->end_date->toDateString() : null;
                                         // Tidak perlu unset, kirim saja format YYYY-MM-DD
                                         return $term;
                                     });
        }

        // --- Pengambilan Task ---
        $unpaidTasksQuery = Task::where('project_id', $project->id)
            ->where('status', 'Done')
            ->whereNull('payment_id')
            // Hanya ambil task yang updated_at nya tidak null (krusial untuk filter termin)
            ->whereNotNull('updated_at')
            ->select('id', 'title', 'assigned_to', 'achievement_percentage', 'difficulty_level_id', 'priority_level_id', 'project_id', 'updated_at')
            ->with([
                'assignedUser:id,name',
                'difficultyLevel:id,value', // Ambil value saja jika hanya itu yang dibutuhkan
                'priorityLevel:id,value',   // Ambil value saja
                'projectUserMembership.wageStandard:id,task_price' // Lebih spesifik kolom
            ]);

        $unpaidTasks = $unpaidTasksQuery->get()
            ->map(function ($task) use ($project) {
                if (!$task->relationLoaded('project')) {
                    // Set relasi project agar accessor bisa jalan
                    $task->setRelation('project', $project);
                }
                // Panggil accessor agar hasilnya ada di data JSON
                $task->wsm_score = $task->wsm_score;
                $task->calculated_value = $task->calculated_value;
                // Format tanggal selesai konsisten YYYY-MM-DD
                // Kita butuh updated_at sebagai Carbon object untuk betweenIncluded di store, jadi jangan unset dulu
                // Tapi tambahkan finished_date untuk JS
                $task->finished_date = $task->updated_at ? Carbon::parse($task->updated_at)->toDateString() : null;
                // Tidak perlu unset updated_at di sini
                // unset($task->created_at); // Hemat data
                return $task;
            });

        $unpaidTasksGrouped = $unpaidTasks->groupBy('assigned_to');

        // --- Data untuk List Draft Slip Gaji ---
        $draftPayslipsQuery = Payment::where('project_id', $project->id)
                                     ->where('status', Payment::STATUS_DRAFT)
                                     ->with('user')
                                     ->orderBy('created_at', 'desc');
        $sortFieldDraft = $request->input('sort_draft', 'created_at');
        $sortDirectionDraft = $request->input('direction_draft', 'desc');
        $allowedSortsDraft = ['created_at', 'payment_name', 'amount', 'user_name', 'payment_type'];
        if (in_array($sortFieldDraft, $allowedSortsDraft)) {
            if ($sortFieldDraft === 'user_name') {
                $draftPayslipsQuery->select('payments.*')
                      ->join('users', 'payments.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortDirectionDraft);
            } else {
                $draftPayslipsQuery->orderBy($sortFieldDraft, $sortDirectionDraft);
            }
        } else {
           $draftPayslipsQuery->orderBy('created_at', 'desc'); // Default sort
        }
        $draftPayslips = $draftPayslipsQuery->paginate(10, ['*'], 'draft_page')->withQueryString();

        // Hitung nama termin berikutnya
        $nextTerminNumber = 1;
        $defaultTerminName = '';
        if ($paymentCalculationType === 'termin') {
            $lastTermin = Payment::where('project_id', $project->id)
                ->where('payment_type', 'termin')
                ->where('payment_name', 'like', 'Termin %')
                ->orderByRaw('CAST(SUBSTRING_INDEX(payment_name, " ", -1) AS UNSIGNED) DESC, created_at DESC')
                ->first();
            if ($lastTermin && preg_match('/Termin (\d+)/', $lastTermin->payment_name, $matches)) {
                $nextTerminNumber = intval($matches[1]) + 1;
            }
            $defaultTerminName = "Termin " . $nextTerminNumber;
        }

        // Ambil old input untuk dikirim ke view agar state form bisa di-restore
        $oldInput = session()->getOldInput();

        return view('payslips.create_and_draft', compact(
            'project',
            'workers',
            'paymentCalculationType',
            'paymentTerms', // Kirim data termin yang sudah diformat
            'unpaidTasksGrouped', // Kirim semua task belum bayar per worker
            'draftPayslips',
            'sortFieldDraft',
            'sortDirectionDraft',
            'defaultTerminName',
            'request',
            'oldInput' // Kirim old input secara eksplisit
        ));
    }

    /**
     * [REVISI] Menyimpan data slip gaji baru (status draft).
     */
    public function storePayslip(Request $request, Project $project)
    {
        if ($project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $paymentCalculationType = $project->payment_calculation_type ?? 'task';

        // Validasi dasar
        $baseRules = [
            'user_id' => 'required|exists:users,id',
            'payment_type' => ['required', Rule::in(['task', 'termin', 'full', 'other'])],
            'payment_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ];

        // Aturan validasi spesifik berdasarkan tipe
        $specificRules = [];
        $paymentTypeInput = $request->input('payment_type'); // Ambil tipe dari input

        if ($paymentTypeInput === 'task') {
            $specificRules = [
                'task_ids' => ['required', 'array', 'min:1'],
                'task_ids.*' => ['exists:tasks,id'],
            ];
        } elseif ($paymentTypeInput === 'termin') {
            // --- REVISI: Validasi Termin ---
            $specificRules = [
                'payment_term_id' => ['required', 'integer', Rule::exists('payment_terms', 'id')->where('project_id', $project->id)], // Validasi term id
                'task_ids' => ['required', 'array', 'min:1'],
                'task_ids.*' => ['exists:tasks,id'],
                // Hapus validasi start_date & end_date dari request
            ];
            // --- END REVISI ---
        } elseif ($paymentTypeInput === 'full' || $paymentTypeInput === 'other') {
            $specificRules = [
                'amount' => ['required', 'numeric', 'min:0'],
            ];
        } else {
            return back()->withErrors(['payment_type' => 'Tipe pembayaran tidak valid.'])->withInput();
        }

        $validated = $request->validate(array_merge($baseRules, $specificRules));

        $worker = User::find($validated['user_id']);
        if (!$worker) {
            return back()->withErrors(['user_id' => 'Pekerja tidak ditemukan.'])->withInput();
        }
        $bankAccount = $worker->bank_account ?? 'N/A';

        DB::beginTransaction();
        try {
            $paymentAmount = 0;
            $validTasks = collect();
            $paymentTermId = null; // Untuk menyimpan ID termin

            // Kalkulasi amount dan validasi task untuk tipe 'task' dan 'termin'
            if ($paymentTypeInput === 'task' || $paymentTypeInput === 'termin') {
                // --- REVISI: Ambil tanggal dari termin jika tipe 'termin' ---
                $startDate = null;
                $endDate = null;
                if ($paymentTypeInput === 'termin') {
                    $paymentTerm = PaymentTerm::find($validated['payment_term_id']);
                    if (!$paymentTerm) {
                        // Seharusnya sudah divalidasi, tapi jaga-jaga
                        throw new \Exception("Termin pembayaran tidak valid.");
                    }
                    $startDate = Carbon::parse($paymentTerm->start_date)->startOfDay();
                    $endDate = Carbon::parse($paymentTerm->end_date)->endOfDay();
                    $paymentTermId = $paymentTerm->id; // Simpan ID termin
                }
                // --- END REVISI ---

                // Ambil task dari request
                $tasksFromRequest = Task::with(['difficultyLevel', 'priorityLevel', 'project', 'projectUserMembership.wageStandard'])
                                        ->findMany($validated['task_ids']);

                // Filter task yang valid
                $validTasks = $tasksFromRequest->filter(function ($task) use ($validated, $project, $paymentTypeInput, $startDate, $endDate) {
                    if (!$task) return false;
                    $isValid = $task->project_id == $project->id &&
                               $task->assigned_to == $validated['user_id'] &&
                               $task->status == 'Done' &&
                               is_null($task->payment_id);

                    // Validasi tambahan untuk 'termin': cek apakah task masuk range waktu termin
                    if ($isValid && $paymentTypeInput === 'termin') {
                        $taskFinishedDate = $task->updated_at; // Asumsi updated_at adalah tanggal selesai
                        // Pastikan tanggal selesai task ada dalam range termin
                        if (!$taskFinishedDate || !$startDate || !$endDate || !$taskFinishedDate->betweenIncluded($startDate, $endDate)) {
                            Log::warning("Task {$task->id} skipped for Termin ({$validated['payment_term_id']}): finished_at {$taskFinishedDate} not in range {$startDate}-{$endDate}");
                            return false;
                        }
                    }
                    return $isValid;
                });

                // Cek jika ada task tidak valid yang dikirim
                if ($validTasks->count() !== count($validated['task_ids'])) {
                    $requestedIds = $validated['task_ids'];
                    $validIds = $validTasks->pluck('id')->toArray();
                    $invalidIds = array_diff($requestedIds, $validIds);
                    $invalidIdString = implode(', ', $invalidIds);
                    $errorMessage = $paymentTypeInput === 'termin'
                        ? "Task dengan ID ({$invalidIdString}) tidak valid (sudah dibayar/status salah/bukan milik pekerja/di luar periode termin). Silakan cek kembali pilihan termin dan task."
                        : "Task dengan ID ({$invalidIdString}) tidak valid (sudah dibayar/status salah/bukan milik pekerja). Silakan refresh.";
                    throw new \Exception($errorMessage);
                }

                // Hitung ulang amount berdasarkan task yang valid
                $paymentAmount = $validTasks->sum(function($task) {
                    return $task->calculated_value; // Panggil accessor
                });

            } else { // Tipe 'full' atau 'other'
                $paymentAmount = $validated['amount'];
            }

            // Buat record payment (slip gaji draft)
            $payslip = Payment::create([
                'project_id' => $project->id,
                'user_id' => $validated['user_id'],
                'payment_type' => $validated['payment_type'],
                'payment_term_id' => $paymentTermId, // <-- BARU: Simpan ID termin jika ada
                'payment_name' => $validated['payment_name'],
                'bank_account' => $bankAccount,
                'amount' => $paymentAmount,
                'proof_image' => null,
                'notes' => $validated['notes'],
                'status' => Payment::STATUS_DRAFT,
                'signature_type' => null,
                'signature_path' => null,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            // Link task jika tipe 'task' atau 'termin'
            if (($paymentTypeInput === 'task' || $paymentTypeInput === 'termin') && $validTasks->isNotEmpty()) {
                Task::whereIn('id', $validTasks->pluck('id'))->update(['payment_id' => $payslip->id]);
            }

            DB::commit();

            $message = 'Draft slip gaji (' . e($validated['payment_name']) . ') berhasil dibuat.';
            return redirect()->route('projects.payslips.create', $project)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payslip Store Failed for Project ' . $project->id . ': ' . $e->getMessage());
            // Sertakan error validasi spesifik jika ada
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                 return back()->withErrors($e->validator)->withInput();
            }
            return back()->withErrors(['general' => 'Gagal menyimpan slip gaji: ' . $e->getMessage()])->withInput();
        }
    }

    // ... Method lainnya (payslipHistory, showPayslipDetail, approvePayslip, destroy, showPayrollCalculation) tidak perlu diubah terkait fungsionalitas termin ini ...
     /**
     * [BARU] Menampilkan halaman Riwayat Slip Gaji (yang sudah disetujui).
     */
    public function payslipHistory(Project $project, Request $request)
    {
         // Pastikan user adalah owner proyek
         if ($project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
         }

         $query = Payment::where('project_id', $project->id)
                         ->where('status', Payment::STATUS_APPROVED) // Hanya yang sudah disetujui
                         ->with(['user', 'approver', 'tasks', 'paymentTerm']); // <-- BARU: Eager load paymentTerm

         // --- Filter ---
         $filters = $request->only(['search', 'user_id', 'payment_type', 'date_from', 'date_to']);
         if (!empty($filters['search'])) {
             $searchTerm = $filters['search'];
             $query->where(function (Builder $q) use ($searchTerm) {
                 $q->where('payment_name', 'like', "%{$searchTerm}%")
                   ->orWhere('notes', 'like', "%{$searchTerm}%")
                   ->orWhereHas('user', function ($uq) use ($searchTerm) {
                       $uq->where('name', 'like', "%{$searchTerm}%");
                   }) // <-- BARU: Cari berdasarkan nama termin jika ada
                   ->orWhereHas('paymentTerm', function ($tq) use ($searchTerm) {
                       $tq->where('name', 'like', "%{$searchTerm}%");
                   });
             });
         }
         if (!empty($filters['user_id'])) {
             $query->where('user_id', $filters['user_id']);
         }
          if (!empty($filters['payment_type'])) {
             $query->where('payment_type', $filters['payment_type']);
         }
         if (!empty($filters['date_from'])) {
             $query->whereDate('approved_at', '>=', $filters['date_from']); // Filter by approval date
         }
         if (!empty($filters['date_to'])) {
             $query->whereDate('approved_at', '<=', $filters['date_to']);
         }


         // --- Sorting ---
         $sortField = $request->input('sort', 'approved_at'); // Default sort by approval date
         $sortDirection = $request->input('direction', 'desc');
         $allowedSorts = ['approved_at', 'created_at', 'amount', 'payment_name', 'user_name', 'payment_type', 'approver_name', 'term_name']; // <-- BARU: Tambah 'term_name'

         if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'user_name') {
                 $query->select('payments.*')
                       ->join('users as worker', 'payments.user_id', '=', 'worker.id')
                       ->orderBy('worker.name', $sortDirection);
            } elseif ($sortField === 'approver_name') {
                $query->select('payments.*')
                       ->leftJoin('users as approver', 'payments.approved_by', '=', 'approver.id')
                       ->orderBy('approver.name', $sortDirection);
            } elseif ($sortField === 'term_name') { // <-- BARU: Sorting by term name
                 $query->select('payments.*')
                       ->leftJoin('payment_terms as pt', 'payments.payment_term_id', '=', 'pt.id')
                       ->orderBy('pt.name', $sortDirection);
            }
            else {
                 $query->orderBy($sortField, $sortDirection);
            }
         } else {
             $query->orderBy('approved_at', 'desc'); // Default sort
         }

         $approvedPayslips = $query->paginate(15)->withQueryString(); // Pagination

         // Data for filters
         $workers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();
         $paymentTypes = Payment::where('project_id', $project->id)
                               ->where('status', Payment::STATUS_APPROVED)
                               ->distinct()
                               ->pluck('payment_type');

         return view('payslips.history', compact(
             'project',
             'approvedPayslips',
             'workers',
             'paymentTypes',
             'request' // Pass request for filter/sort persistence
         ));
    }

     /**
     * [BARU/REVISI] Menampilkan detail slip gaji (format slip, approve form jika draft).
     */
     public function showPayslipDetail(Project $project, Payment $payslip) // Ubah nama parameter
     {
         // Pastikan user adalah owner proyek ATAU penerima slip gaji
         if ($project->owner_id !== Auth::id() && $payslip->user_id !== Auth::id()) {
             abort(403, 'Unauthorized action.');
         }
         // Pastikan slip gaji milik proyek ini
         if ($payslip->project_id !== $project->id) { abort(404); }

         // Eager load relasi yang dibutuhkan
         $payslip->load([
             'user', // Penerima
             'approver', // Yang menyetujui (jika sudah)
             'paymentTerm', // <-- BARU: Load data termin terkait
             'tasks' => function ($query) { // Load tasks jika ada, beserta detailnya
                 $query->with(['difficultyLevel', 'priorityLevel']);
             }
         ]);

         // Data tambahan untuk view
         $projectName = $project->name;
         $ownerName = $project->owner->name; // Nama PM

         return view('payslips.show_detail', compact('project', 'payslip', 'projectName', 'ownerName'));
     }

    /**
     * [BARU] Proses persetujuan slip gaji (upload signature, update status).
     */
    public function approvePayslip(Request $request, Project $project, Payment $payslip)
    {
         // Pastikan user adalah owner proyek
         if ($project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
         }
         // Pastikan slip gaji milik proyek ini dan statusnya draft
         if ($payslip->project_id !== $project->id || $payslip->status !== Payment::STATUS_DRAFT) {
             abort(400, 'Slip Gaji tidak valid atau sudah disetujui.');
         }

         $validated = $request->validate([
             'signature_type' => ['required', Rule::in([Payment::SIGNATURE_DIGITAL, Payment::SIGNATURE_SCANNED])],
             'signature_file' => [
                 'required',
                 'file',
                 // Validasi MIME berdasarkan tipe
                 Rule::when($request->input('signature_type') == Payment::SIGNATURE_DIGITAL, ['mimes:png,jpg,jpeg', 'max:1024']), // Max 1MB for image
                 Rule::when($request->input('signature_type') == Payment::SIGNATURE_SCANNED, ['mimes:pdf,png,jpg,jpeg', 'max:2048']), // Max 2MB for scan
             ],
         ]);

         DB::beginTransaction();
         try {
             $path = null;
             if ($request->hasFile('signature_file')) {
                 // Simpan file signature
                 $signatureType = $validated['signature_type'];
                 $fileName = 'signature_' . $payslip->id . '_' . time() . '.' . $request->file('signature_file')->getClientOriginalExtension();
                 $path = $request->file('signature_file')->storeAs("payslip_signatures/{$project->id}", $fileName, 'public');

                 // Hapus signature lama jika ada (meskipun seharusnya tidak ada untuk draft)
                  if ($payslip->signature_path && Storage::disk('public')->exists($payslip->signature_path)) {
                      Storage::disk('public')->delete($payslip->signature_path);
                  }
             } else {
                 throw new \Exception('File tanda tangan tidak ditemukan.');
             }

             // Update data slip gaji
             $payslip->update([
                 'status' => Payment::STATUS_APPROVED,
                 'signature_type' => $validated['signature_type'],
                 'signature_path' => $path,
                 'approved_at' => now(),
                 'approved_by' => Auth::id(),
             ]);

             DB::commit();

             return redirect()->route('projects.payslips.show', [$project, $payslip])->with('success', 'Slip gaji berhasil disetujui.');

         } catch (\Exception $e) {
             DB::rollBack();
             // Hapus file yang mungkin sudah terupload jika terjadi error
             if (isset($path) && $path && Storage::disk('public')->exists($path)) {
                 Storage::disk('public')->delete($path);
             }
             Log::error('Payslip Approval Failed for Payment ' . $payslip->id . ': ' . $e->getMessage());
             return back()->withErrors(['general' => 'Gagal menyetujui slip gaji: ' . $e->getMessage()])->withInput();
         }
    }

    /**
     * [REVISI] Menghapus slip gaji (hanya draft).
     */
    public function destroy(Project $project, Payment $payslip) // Ganti nama parameter
    {
        // Pastikan user adalah owner proyek
        if ($project->owner_id !== Auth::id()) {
           abort(403, 'Unauthorized action.');
        }
        // Pastikan slip gaji milik proyek ini
        if ($payslip->project_id !== $project->id) { abort(404); }

        // Hanya boleh hapus draft
        if ($payslip->status !== Payment::STATUS_DRAFT) {
             return back()->withErrors(['general' => 'Hanya slip gaji draft yang dapat dihapus.']);
        }

        DB::beginTransaction();
        try {
             // Unlink tasks jika payment type adalah 'task' atau 'termin'
             if (in_array($payslip->payment_type, ['task', 'termin'])) {
                 $payslip->tasks()->update(['payment_id' => null]);
             }

             // Hapus file signature jika ada (seharusnya tidak ada untuk draft, tapi jaga-jaga)
             if ($payslip->signature_path && Storage::disk('public')->exists($payslip->signature_path)) {
                 Storage::disk('public')->delete($payslip->signature_path);
             }

             // Hapus record payment (slip gaji)
             $payslip->delete();
             DB::commit();

             $message = 'Draft slip gaji (' . e($payslip->payment_name) . ') berhasil dihapus.';
             if (in_array($payslip->payment_type, ['task', 'termin'])) {
                 $message .= ' Task terkait dikembalikan ke status belum dibayar.';
             }
             // Redirect ke halaman create/draft lagi
             return redirect()->route('projects.payslips.create', $project)->with('success', $message);

        } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Payslip Delete Failed for Payment '. $payslip->id .': ' . $e->getMessage());
             return back()->withErrors(['general' => 'Gagal menghapus slip gaji: ' . $e->getMessage()]);
         }
    }

    // =====================================================================
    // PAYROLL CALCULATION PAGE (TETAP SAMA)
    // =====================================================================
    public function showPayrollCalculation(Request $request, Project $project)
    {
        // --- Base Query Task ---
        $taskQuery = Task::query()
            ->where('tasks.project_id', $project->id)
            ->where('tasks.status', 'Done')
            ->select(
                'tasks.*',
                'users.name as assigned_user_name', // Alias for user name
                'd_levels.value as difficulty_value', // Alias for difficulty value
                'p_levels.value as priority_value' // Alias for priority value
            )
            ->join('users', 'tasks.assigned_to', '=', 'users.id') // Always join user
            ->leftJoin('difficulty_levels as d_levels', 'tasks.difficulty_level_id', '=', 'd_levels.id')
            ->leftJoin('priority_levels as p_levels', 'tasks.priority_level_id', '=', 'p_levels.id')
            ->with(['payment']); // Eager load payment relation for status check

        // --- Filters ---
        $selectedWorkerId = $request->input('worker_id');
        $paymentStatus = $request->input('payment_status');
        $searchTerm = $request->input('search');

        if ($selectedWorkerId && $selectedWorkerId !== 'all') {
            $taskQuery->where('tasks.assigned_to', $selectedWorkerId);
        }
        if ($paymentStatus) {
            if ($paymentStatus === 'paid') $taskQuery->whereNotNull('tasks.payment_id');
            elseif ($paymentStatus === 'unpaid') $taskQuery->whereNull('tasks.payment_id');
        }
        if ($searchTerm) {
            $taskQuery->where(function (Builder $q) use ($searchTerm) {
                $q->where('tasks.title', 'like', "%{$searchTerm}%")
                  ->orWhere('users.name', 'like', "%{$searchTerm}%"); // Search user name via join
            });
        }

        // --- Sorting ---
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        // Map frontend field names to DB columns/aliases/expressions
        $allowedSorts = [
            'title' => 'tasks.title',
            'assigned_user_name' => 'users.name', // Use joined user name
            'difficulty_value' => 'd_levels.value', // Use alias from join
            'priority_value' => 'p_levels.value', // Use alias from join
            'achievement_percentage' => 'tasks.achievement_percentage',
            'payment_status' => DB::raw('CASE WHEN tasks.payment_id IS NULL THEN 0 ELSE 1 END'), // Sort unpaid first on ASC
            'updated_at' => 'tasks.updated_at',
        ];

        if (array_key_exists($sortField, $allowedSorts)) {
            $taskQuery->orderBy($allowedSorts[$sortField], $sortDirection);
            // Tambahkan secondary sort by updated_at jika bukan primary sort
             if ($sortField !== 'updated_at') {
                $taskQuery->orderBy('tasks.updated_at', 'desc');
             }
        } else {
            $taskQuery->orderBy('tasks.updated_at', 'desc');
        }

        // --- Calculate Filtered Task Payroll ---
        $totalFilteredTaskPayroll = 0;
        try {
            $filteredTaskIds = (clone $taskQuery)->pluck('tasks.id');
            if ($filteredTaskIds->isNotEmpty()) {
                $allFilteredTasks = Task::with([
                                        'difficultyLevel', 'priorityLevel',
                                        'projectUserMembership.wageStandard', 'project'
                                    ])->findMany($filteredTaskIds);
                $totalFilteredTaskPayroll = $allFilteredTasks->sum('calculated_value');
            }
        } catch (\Exception $e) {
            Log::error("Error calculating totalFilteredTaskPayroll: " . $e->getMessage());
            $totalFilteredTaskPayroll = 0;
        }

        // --- Paginate Tasks AFTER calculating filtered sum ---
        $perPageOptions = [10, 15, 25, 50, 100];
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, $perPageOptions)) $perPage = 10;
        $tasks = $taskQuery->paginate($perPage)->withQueryString(); // PAGINATED TASK RESULTS

        // --- Calculate Filtered Other Payments (status doesn't matter here for 'Hak Gaji') ---
        // Meliputi 'other' dan 'full'
        $otherPaymentQuery = Payment::query()->from('payments as p')
                                    ->where('p.project_id', $project->id)
                                    ->whereIn('p.payment_type', ['other', 'full']); // Include 'full'
        if ($selectedWorkerId && $selectedWorkerId !== 'all') {
            $otherPaymentQuery->where('p.user_id', $selectedWorkerId);
        }
        if ($searchTerm) {
            $otherPaymentQuery->join('users as u', 'p.user_id', '=', 'u.id')
                              ->where(function (Builder $q) use ($searchTerm) {
                                  $q->where('p.payment_name', 'like', "%{$searchTerm}%")
                                    ->orWhere('p.notes', 'like', "%{$searchTerm}%")
                                    ->orWhere('u.name', 'like', "%{$searchTerm}%");
                              })->select('p.*');
        }
        $totalFilteredOtherPayments = $otherPaymentQuery->sum('p.amount');

        // --- Calculate Filtered PAID Amounts (status=approved) ---
        // 1. Filtered Paid Task Amount (termasuk termin)
        $filteredPaidTaskQuery = Payment::query()->from('payments as p')
                                        ->where('p.project_id', $project->id)
                                        ->whereIn('p.payment_type', ['task', 'termin']) // Include termin
                                        ->where('p.status', Payment::STATUS_APPROVED); // Status Approved
        if ($selectedWorkerId && $selectedWorkerId !== 'all') { $filteredPaidTaskQuery->where('p.user_id', $selectedWorkerId); }
        if ($searchTerm) {
             $filteredPaidTaskQuery->join('users as u', 'p.user_id', '=', 'u.id')
                                 ->where(function (Builder $q) use ($searchTerm) {
                                      $q->where('p.payment_name', 'like', "%{$searchTerm}%")
                                        ->orWhere('p.notes', 'like', "%{$searchTerm}%")
                                        ->orWhere('u.name', 'like', "%{$searchTerm}%");
                                  })->select('p.*');
        }
        $totalFilteredPaidTaskAmount = $filteredPaidTaskQuery->sum('p.amount');

        // 2. Filtered Paid Other/Full Amount
        $filteredPaidOtherQuery = Payment::query()->from('payments as p')
                                        ->where('p.project_id', $project->id)
                                        ->whereIn('p.payment_type', ['other', 'full']) // Include full
                                        ->where('p.status', Payment::STATUS_APPROVED); // Status Approved
        if ($selectedWorkerId && $selectedWorkerId !== 'all') { $filteredPaidOtherQuery->where('p.user_id', $selectedWorkerId); }
        if ($searchTerm) {
             $filteredPaidOtherQuery->join('users as u', 'p.user_id', '=', 'u.id')
                                  ->where(function (Builder $q) use ($searchTerm) {
                                      $q->where('p.payment_name', 'like', "%{$searchTerm}%")
                                        ->orWhere('p.notes', 'like', "%{$searchTerm}%")
                                        ->orWhere('u.name', 'like', "%{$searchTerm}%");
                                  })->select('p.*');
        }
        $totalFilteredPaidOtherAmount = $filteredPaidOtherQuery->sum('p.amount');


        // --- Calculate Overall Totals (No Filters applied except project_id/status) ---
        // Overall Hak Gaji Task (Nilai dari semua task 'Done')
        $totalOverallTaskPayroll = 0;
        try {
             $allDoneTasks = Task::where('project_id', $project->id)
                              ->where('status', 'Done')
                              ->with(['difficultyLevel', 'priorityLevel', 'projectUserMembership.wageStandard', 'project'])
                              ->get();
             $totalOverallTaskPayroll = $allDoneTasks->sum('calculated_value');
         } catch (\Exception $e) { Log::error("Error calculating totalOverallTaskPayroll: " . $e->getMessage()); }

        // Overall Hak Gaji Other (Termasuk Full)
        $totalOverallOtherPayments = Payment::where('project_id', $project->id)
                                            ->whereIn('payment_type', ['other', 'full'])
                                            ->sum('amount');
        // Overall Paid Task (Termasuk Termin)
        $totalOverallPaidTaskAmount = Payment::where('project_id', $project->id)
                                             ->whereIn('payment_type', ['task', 'termin'])
                                             ->where('status', Payment::STATUS_APPROVED)
                                             ->sum('amount');
        // Overall Paid Other (Termasuk Full)
        $totalOverallPaidOtherAmount = Payment::where('project_id', $project->id)
                                              ->whereIn('payment_type', ['other', 'full'])
                                              ->where('status', Payment::STATUS_APPROVED)
                                              ->sum('amount');
        // Overall Gabungan Hak Gaji
        $totalOverallPayroll = $totalOverallTaskPayroll + $totalOverallOtherPayments;

        // Budget Difference vs Overall Hak Gaji
        $budget = $project->budget ?? 0;
        $budgetDifference = $budget - $totalOverallPayroll;

        // --- Additional Data for View ---
        $workers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();

        // --- Response ---
        if ($request->ajax()) {
            $tableHtml = view('penggajian._payroll_table_content', compact('project','tasks', 'request'))->render();
            return response()->json([
                'html' => $tableHtml,
                'totalFilteredTaskPayroll' => $totalFilteredTaskPayroll,
                'totalFilteredOtherPayments' => $totalFilteredOtherPayments,
                'totalPaidTaskAmount' => $totalFilteredPaidTaskAmount,
                'totalPaidOtherAmount' => $totalFilteredPaidOtherAmount,
            ]);
        }

        // Return full view with ALL totals for initial load
        return view('penggajian.calculate', compact(
            'project', 'tasks', 'workers',
            'totalFilteredTaskPayroll', 'totalFilteredOtherPayments',
            'totalFilteredPaidTaskAmount', 'totalFilteredPaidOtherAmount',
            'totalOverallTaskPayroll', 'totalOverallOtherPayments', 'totalOverallPayroll',
            'totalOverallPaidTaskAmount', 'totalOverallPaidOtherAmount',
            'budgetDifference', 'perPageOptions', 'request'
        ));
    }
}