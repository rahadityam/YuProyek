<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentTerm;
use App\Models\Project;
// use App\Models\ProjectUser; // Tidak digunakan secara langsung di method ini
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
use Illuminate\Support\Facades\Validator; // Tambahkan ini
use App\Notifications\PayslipApprovedNotification;
use Illuminate\Support\Facades\Notification;

class PaymentController extends Controller
{
    public function createAndDraftPayslip(Project $project, Request $request)
    {
        return redirect()->route('projects.payroll.calculate', $project);
    }

    public function storePayslip(Request $request, Project $project)
    {
        $this->authorize('create', [Payment::class, $project]);

        Log::info('storePayslip - Request Data:', $request->all());

        // Tentukan aturan validasi dasar
        $baseRules = [
            'user_id' => 'required|exists:users,id',
            'payment_type' => ['required', Rule::in(['task', 'termin', 'full', 'other'])],
            'payment_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ];

        // Tentukan aturan validasi spesifik berdasarkan tipe pembayaran
        $specificRules = [];
        $paymentTypeInput = $request->input('payment_type');

        if ($paymentTypeInput === 'task') {
            $specificRules = [
                'task_ids' => ['present', 'array'],
                'task_ids.*' => ['integer', 'exists:tasks,id'],
            ];
            // Jika task_ids WAJIB diisi untuk tipe 'task'
            // $baseRules['task_ids'] = ['required', 'array', 'min:1'];
        } elseif ($paymentTypeInput === 'termin') {
            $specificRules = [
                'payment_term_id' => [
                     'required',
                     'integer',
                     Rule::exists(
                         'payment_terms',
                         'id'
                    )->where(
                        'project_id',
                        $project->id
                    )],
                'task_ids' => ['present', 'array'],
                'task_ids.*' => ['integer', 'exists:tasks,id'],
            ];
            // Jika task_ids WAJIB diisi untuk tipe 'termin'
            // $baseRules['task_ids'] = ['required', 'array', 'min:1'];
        } elseif ($paymentTypeInput === 'full' || $paymentTypeInput === 'other') {
            $specificRules = [
                'amount' => ['required', 'numeric', 'min:0'],
            ];
        } else {
            // Jika request AJAX (dari modal)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tipe pembayaran tidak valid.', 'errors' => ['payment_type' => ['Tipe pembayaran tidak valid.']]], 422);
            }
            return back()->withErrors(['payment_type' => 'Tipe pembayaran tidak valid.'])->withInput();
        }

        // Gabungkan aturan dan lakukan validasi
        $validator = Validator::make($request->all(), array_merge($baseRules, $specificRules));

        if ($validator->fails()) {
            Log::warning('storePayslip - Validation Failed:', $validator->errors()->toArray());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data yang diberikan tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated(); // Ambil data yang sudah tervalidasi

        // Jika tipe 'task' atau 'termin', dan task_ids kosong padahal seharusnya ada (min:1), tambahkan validasi manual
        if (($paymentTypeInput === 'task' || $paymentTypeInput === 'termin') && empty($validated['task_ids'])) {
            $errorMessage = 'Minimal satu task harus dipilih untuk tipe pembayaran ' . $paymentTypeInput . '.';
            Log::warning('storePayslip - Validation Failed: ' . $errorMessage);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage, 'errors' => ['task_ids' => [$errorMessage]]], 422);
            }
            return back()->withErrors(['task_ids' => $errorMessage])->withInput();
        }


        $worker = User::find($validated['user_id']);
        if (!$worker) {
            Log::error('storePayslip - Worker not found for ID: ' . $validated['user_id']);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pekerja tidak ditemukan.', 'errors' => ['user_id' => ['Pekerja tidak ditemukan.']]], 422);
            }
            return back()->withErrors(['user_id' => 'Pekerja tidak ditemukan.'])->withInput();
        }
        $bankAccount = $worker->bank_account ?? 'N/A';

        DB::beginTransaction();
        try {
            $paymentAmount = 0;
            $validTasksCollection = collect(); // Ganti nama agar lebih jelas
            $paymentTermId = $validated['payment_term_id'] ?? null; // Ambil dari validated jika ada

            if ($paymentTypeInput === 'task' || $paymentTypeInput === 'termin') {
                $startDate = null;
                $endDate = null;

                if ($paymentTypeInput === 'termin') {
                    // payment_term_id sudah divalidasi ada di $validated
                    $paymentTerm = PaymentTerm::find($paymentTermId);
                    if (!$paymentTerm) { // Double check, seharusnya tidak terjadi
                        throw new \Exception("Termin pembayaran tidak valid (ID: {$paymentTermId}).");
                    }
                    $startDate = Carbon::parse($paymentTerm->start_date)->startOfDay();
                    $endDate = Carbon::parse($paymentTerm->end_date)->endOfDay();
                }

                // Ambil task berdasarkan ID yang divalidasi
                $taskIdsFromRequest = $validated['task_ids'] ?? []; // Pastikan array
                if (empty($taskIdsFromRequest)) { // Seharusnya sudah ditangani validasi min:1
                    throw new \Exception("Tidak ada task yang dipilih untuk tipe pembayaran ini.");
                }

                $tasksFromRequest = Task::with(['difficultyLevel', 'priorityLevel', 'project', 'projectUserMembership.wageStandard'])
                    ->whereIn('id', $taskIdsFromRequest) // Gunakan whereIn untuk efisiensi
                    ->get();

                Log::info('storePayslip - Tasks found from request IDs:', $tasksFromRequest->pluck('id')->all());

                $validTasksCollection = $tasksFromRequest->filter(function ($task) use ($validated, $project, $paymentTypeInput, $startDate, $endDate) {
                    // $task sudah pasti ada karena whereIn
                    $isBasicValid = $task->project_id == $project->id &&
                        $task->assigned_to == $validated['user_id'] &&
                        $task->status == 'Done' &&
                        is_null($task->payment_id);

                    if (!$isBasicValid) {
                        Log::warning("storePayslip - Task {$task->id} basic validation failed: ", [
                            'task_project_id' => $task->project_id,
                            'expected_project_id' => $project->id,
                            'task_assigned_to' => $task->assigned_to,
                            'expected_user_id' => $validated['user_id'],
                            'task_status' => $task->status,
                            'task_payment_id' => $task->payment_id
                        ]);
                        return false;
                    }

                    if ($paymentTypeInput === 'termin') {
                        // startDate dan endDate sudah pasti ada jika tipe 'termin'
                        $taskFinishedDate = Carbon::parse($task->updated_at); // Asumsi updated_at adalah tanggal selesai dan tidak null
                        if (!$taskFinishedDate->betweenIncluded($startDate, $endDate)) {
                            Log::warning("storePayslip - Task {$task->id} (selesai: {$taskFinishedDate->toDateString()}) tidak masuk range termin {$startDate->toDateString()} - {$endDate->toDateString()}");
                            return false;
                        }
                    }
                    return true;
                });

                Log::info('storePayslip - Valid tasks after filtering:', $validTasksCollection->pluck('id')->all());

                // Periksa apakah semua task yang dikirim dari frontend valid setelah filter backend
                if ($validTasksCollection->count() !== count($taskIdsFromRequest)) {
                    $submittedIds = $taskIdsFromRequest;
                    $backendValidIds = $validTasksCollection->pluck('id')->toArray();
                    $failedValidationIds = array_diff($submittedIds, $backendValidIds);
                    $invalidIdString = implode(', ', $failedValidationIds);

                    $errorMessage = $paymentTypeInput === 'termin'
                        ? "Task dengan ID ({$invalidIdString}) tidak valid (sudah dibayar/status salah/bukan milik pekerja/di luar periode termin). Silakan cek kembali pilihan termin dan task."
                        : "Task dengan ID ({$invalidIdString}) tidak valid (sudah dibayar/status salah/bukan milik pekerja). Silakan refresh.";
                    Log::error("storePayslip - Task validation mismatch: " . $errorMessage . " Submitted: " . json_encode($submittedIds) . " Backend Valid: " . json_encode($backendValidIds));
                    throw new \Exception($errorMessage);
                }

                $paymentAmount = $validTasksCollection->sum(fn($task) => $task->calculated_value);
                Log::info('storePayslip - Calculated paymentAmount for task/termin: ' . $paymentAmount);

            } else { // Tipe 'full' atau 'other'
                $paymentAmount = $validated['amount'];
                Log::info('storePayslip - paymentAmount for full/other: ' . $paymentAmount);
            }

            $payslip = Payment::create([
                'project_id' => $project->id,
                'user_id' => $validated['user_id'],
                'payment_type' => $validated['payment_type'],
                'payment_term_id' => $paymentTermId, // Ini sudah di-set di atas atau null
                'payment_name' => $validated['payment_name'],
                'bank_account' => $bankAccount,
                'amount' => $paymentAmount,
                'proof_image' => null,
                'notes' => $validated['notes'] ?? null,
                'status' => Payment::STATUS_DRAFT,
                'signature_type' => null,
                'signature_path' => null,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            Log::info('storePayslip - Payslip created with ID: ' . $payslip->id);

            if (($paymentTypeInput === 'task' || $paymentTypeInput === 'termin') && $validTasksCollection->isNotEmpty()) {
                Task::whereIn('id', $validTasksCollection->pluck('id'))->update(['payment_id' => $payslip->id]);
                Log::info('storePayslip - Tasks updated with payment_id: ', $validTasksCollection->pluck('id')->all());
            }

            DB::commit();

            $message = 'Draft slip gaji (' . e($validated['payment_name']) . ') berhasil dibuat.';
            Log::info('storePayslip - Success: ' . $message);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'payslip' => $payslip->load('user'),
                    'redirect_url' => route('projects.payslips.history', $project)
                ]);
            }
            return redirect()->route('projects.payslips.history', $project)->with('success', $message);

        }
        // Jangan tangkap ValidationException di sini karena sudah ditangani Validator di atas
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('storePayslip - Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString() // Tambahkan trace untuk debugging lebih detail
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan slip gaji: ' . $e->getMessage(), 'errors' => ['general' => [$e->getMessage()]]], 500);
            }
            return back()->withErrors(['general' => 'Gagal menyimpan slip gaji: ' . $e->getMessage()])->withInput();
        }
    }

    public function payslipList(Project $project, Request $request)
    {
        $this->authorize('viewAny', [Payment::class, $project]);

        $currentUser = Auth::user();
        $isProjectOwner = $currentUser->isProjectOwner($project);

        // UBAH: Mulai query dengan alias dan select eksplisit untuk menghindari ambiguitas
        $query = Payment::query()->from('payments as p')
            ->select('p.*') // Pastikan kita hanya menyeleksi kolom dari tabel payments
            ->join('users as worker', 'p.user_id', '=', 'worker.id'); // Selalu join dengan user

        $query->where('p.project_id', $project->id)
            ->whereIn('p.status', [Payment::STATUS_DRAFT, Payment::STATUS_APPROVED]);

        if (!$isProjectOwner) {
            $query->where('p.user_id', $currentUser->id)->where('p.status', Payment::STATUS_APPROVED);
        }

        // --- FILTERING (DISEMPURNAKAN) ---
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            // Sekarang kita bisa menggunakan 'worker.name' secara langsung
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('p.payment_name', 'like', "%{$searchTerm}%")
                    ->orWhere('p.notes', 'like', "%{$searchTerm}%")
                    ->orWhere('worker.name', 'like', "%{$searchTerm}%"); // Lebih efisien daripada orWhereHas
            });
        }

        // Filter lain tetap sama
        if ($isProjectOwner && $request->filled('user_id')) {
            $query->where('p.user_id', $request->user_id);
        }
        if ($request->filled('payment_type')) {
            $query->where('p.payment_type', $request->payment_type);
        }
        if ($isProjectOwner && $request->filled('status')) {
            $query->where('p.status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('p.created_at', '>=', $request->date_from)
                    ->orWhereDate('p.approved_at', '>=', $request->date_from);
            });
        }
        if ($request->filled('date_to')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('p.created_at', '<=', $request->date_to)
                    ->orWhereDate('p.approved_at', '<=', $request->date_to);
            });
        }


        // --- SORTING (DISEMPURNAKAN) ---
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = [
            'updated_at' => 'p.updated_at',
            'created_at' => 'p.created_at',
            'approved_at' => 'p.approved_at',
            'amount' => 'p.amount',
            'payment_name' => 'p.payment_name',
            'payment_type' => 'p.payment_type',
            'status' => 'p.status',
            'user_name' => 'worker.name',
            'approver_name' => 'approver.name'
        ];

        if (array_key_exists($sortField, $allowedSorts)) {
            if ($sortField === 'approver_name') {
                // Left Join untuk approver karena bisa jadi NULL
                $query->leftJoin('users as approver', 'p.approved_by', '=', 'approver.id')
                    ->orderBy($allowedSorts[$sortField], $sortDirection);
            } else {
                // Untuk semua kolom lain (termasuk user_name), orderBy biasa sudah cukup karena sudah di-join
                $query->orderBy($allowedSorts[$sortField], $sortDirection);
            }
        } else {
            $query->orderBy('p.updated_at', 'desc'); // Default sort
        }

        $payslips = $query->paginate(15)->withQueryString();

        // Dapatkan hasil paginasi
        $payslips = $query->paginate(15)->withQueryString();

        // Jika request dari API, kembalikan data JSON
        if ($request->wantsJson()) {
            return response()->json($payslips);
        }

        // --- AJAX RESPONSE (TETAP SAMA) ---
        if ($request->ajax()) {
            // Kita pass 'request' ke view partial agar sort indicator bisa berfungsi
            return response()->json([
                'table_html' => view('payslips.partials._list_table_content', compact('payslips', 'project', 'isProjectOwner', 'request'))->render(),
                'pagination_html' => $payslips->links('vendor.pagination.tailwind')->toHtml(),
            ]);
        }

        // --- INITIAL PAGE LOAD RESPONSE (TETAP SAMA) ---
        $workersForFilter = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();
        $paymentTypes = Payment::where('project_id', $project->id)->distinct()->pluck('payment_type');
        $statusesForFilter = $isProjectOwner ? [Payment::STATUS_DRAFT => 'Draft', Payment::STATUS_APPROVED => 'Approved'] : [];

        return view('payslips.list', compact(
            'project',
            'payslips',
            'workersForFilter',
            'paymentTypes',
            'statusesForFilter',
            'isProjectOwner',
            'request'
        ));
    }

    public function showPayslipDetail(Request $request, Project $project, Payment $payslip)
    {
        $this->authorize('view', $payslip);
        if ($payslip->project_id !== $project->id) {
            abort(404);
        }

        $payslip->load([
            'user',
            'approver',
            'paymentTerm',
            'tasks' => fn($query) => $query->with(['difficultyLevel', 'priorityLevel'])
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslip' => $payslip
            ]);
        }

        $projectName = $project->name;
        $ownerName = $project->owner->name;

        return view('payslips.show_detail', compact('project', 'payslip', 'projectName', 'ownerName'));
    }

    public function approvePayslip(Request $request, Project $project, Payment $payslip)
    {
        $this->authorize('approve', $payslip);
        if ($payslip->project_id !== $project->id || $payslip->status !== Payment::STATUS_DRAFT) {
            abort(400, 'Slip Gaji tidak valid atau sudah disetujui.');
        }

        $validated = $request->validate([
            'signature_type' => ['required', Rule::in([Payment::SIGNATURE_DIGITAL, Payment::SIGNATURE_SCANNED])],
            'signature_file' => [
                'required',
                'file',
                Rule::when($request->input('signature_type') == Payment::SIGNATURE_DIGITAL, ['mimes:png,jpg,jpeg', 'max:1024']),
                Rule::when($request->input('signature_type') == Payment::SIGNATURE_SCANNED, ['mimes:pdf,png,jpg,jpeg', 'max:2048']),
            ],
        ]);

        DB::beginTransaction();
        try {
            $path = null;
            if ($request->hasFile('signature_file')) {
                $fileName = 'signature_' . $payslip->id . '_' . time() . '.' . $request->file('signature_file')->getClientOriginalExtension();
                $path = $request->file('signature_file')->storeAs("payslip_signatures/{$project->id}", $fileName, 'public');
                if ($payslip->signature_path && Storage::disk('public')->exists($payslip->signature_path)) {
                    Storage::disk('public')->delete($payslip->signature_path);
                }
            } else {
                throw new \Exception('File tanda tangan tidak ditemukan.');
            }

            $payslip->update([
                'status' => Payment::STATUS_APPROVED,
                'signature_type' => $validated['signature_type'],
                'signature_path' => $path,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            // --- KIRIM NOTIFIKASI SLIP GAJI DISETUJUI ---
            $worker = $payslip->user;
            $approver = Auth::user();
            if ($worker) {
                $worker->notify(new PayslipApprovedNotification($payslip, $approver));
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Slip gaji berhasil disetujui.',
                    'redirect_url' => route('projects.payslips.show', [$project, $payslip])
                ]);
            }
            return redirect()->route('projects.payslips.show', [$project, $payslip])->with('success', 'Slip gaji berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path) && $path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            Log::error('Payslip Approval Failed for Payment ' . $payslip->id . ': ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyetujui slip gaji: ' . $e->getMessage(), 'errors' => ['general' => [$e->getMessage()]]], 500);
            }
            return back()->withErrors(['general' => 'Gagal menyetujui slip gaji: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Project $project, Payment $payslip)
    {
        $this->authorize('delete', $payslip);
        if ($payslip->project_id !== $project->id) {
            abort(404);
        }
        if ($payslip->status !== Payment::STATUS_DRAFT) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Hanya slip gaji draft yang dapat dihapus.'], 400);
            }
            return back()->withErrors(['general' => 'Hanya slip gaji draft yang dapat dihapus.']);
        }

        DB::beginTransaction();
        try {
            if (in_array($payslip->payment_type, ['task', 'termin'])) {
                $payslip->tasks()->update(['payment_id' => null]);
            }
            if ($payslip->signature_path && Storage::disk('public')->exists($payslip->signature_path)) {
                Storage::disk('public')->delete($payslip->signature_path);
            }
            $payslip->delete();
            DB::commit();

            $message = 'Draft slip gaji (' . e($payslip->payment_name) . ') berhasil dihapus.';
            if (in_array($payslip->payment_type, ['task', 'termin'])) {
                $message .= ' Task terkait dikembalikan ke status belum dibayar.';
            }
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->route('projects.payslips.history', $project)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payslip Delete Failed for Payment ' . $payslip->id . ': ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus slip gaji: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['general' => 'Gagal menghapus slip gaji: ' . $e->getMessage()]);
        }
    }

    public function showPayrollCalculation(Request $request, Project $project)
    {
        $currentUser = Auth::user();
        $isProjectOwner = $currentUser->isProjectOwner($project);

        $taskQuery = Task::query()
            ->where('tasks.project_id', $project->id)
            ->where('tasks.status', 'Done')
            ->select(
                'tasks.*',
                'users.name as assigned_user_name',
                'd_levels.value as difficulty_value',
                'p_levels.value as priority_value'
            )
            ->join('users', 'tasks.assigned_to', '=', 'users.id')
            ->leftJoin('difficulty_levels as d_levels', 'tasks.difficulty_level_id', '=', 'd_levels.id')
            ->leftJoin('priority_levels as p_levels', 'tasks.priority_level_id', '=', 'p_levels.id')
            ->with(['payment']);

        if (!$isProjectOwner) {
            if ($currentUser->isProjectMember($project)) {
                $taskQuery->where('tasks.assigned_to', $currentUser->id);
            } else {
                $taskQuery->whereRaw('1 = 0');
            }
        }

        $selectedWorkerIdInput = $request->input('worker_id');
        $selectedWorkerId = $isProjectOwner ? ($selectedWorkerIdInput === 'all' ? null : $selectedWorkerIdInput) : ($currentUser->isProjectMember($project) ? $currentUser->id : null);

        if ($selectedWorkerId) {
            $taskQuery->where('tasks.assigned_to', $selectedWorkerId);
        }

        if ($request->input('payment_status')) {
            if ($request->input('payment_status') === 'paid')
                $taskQuery->whereNotNull('tasks.payment_id');
            elseif ($request->input('payment_status') === 'unpaid')
                $taskQuery->whereNull('tasks.payment_id');
        }

        // Add date filters
        if ($request->filled('start_date')) {
            $taskQuery->whereDate('tasks.updated_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $taskQuery->whereDate('tasks.updated_at', '<=', $request->input('end_date'));
        }

        // Restrict search to task title only
        if ($request->input('search')) {
            $searchTerm = $request->input('search');
            $taskQuery->where('tasks.title', 'like', "%{$searchTerm}%");
        }

        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = [
            'title' => 'tasks.title',
            'assigned_user_name' => 'users.name',
            'difficulty_value' => 'd_levels.value',
            'priority_value' => 'p_levels.value',
            'achievement_percentage' => 'tasks.achievement_percentage',
            'payment_status' => DB::raw('CASE WHEN tasks.payment_id IS NULL THEN 0 ELSE 1 END'),
            'updated_at' => 'tasks.updated_at',
        ];

        if (array_key_exists($sortField, $allowedSorts)) {
            $taskQuery->orderBy($allowedSorts[$sortField], $sortDirection);
            if ($sortField !== 'updated_at') {
                $taskQuery->orderBy('tasks.updated_at', 'desc');
            }
        } else {
            $taskQuery->orderBy('tasks.updated_at', 'desc');
        }

        $totalFilteredTaskPayroll = 0;
        $tasksForSumCalculationQuery = clone $taskQuery;
        $filteredTaskIds = $tasksForSumCalculationQuery->pluck('tasks.id');
        if ($filteredTaskIds->isNotEmpty()) {
            $allFilteredTasks = Task::with([
                'difficultyLevel',
                'priorityLevel',
                'projectUserMembership.wageStandard',
                'project'
            ])->findMany($filteredTaskIds);
            $totalFilteredTaskPayroll = $allFilteredTasks->sum('calculated_value');
        }

        $perPageOptions = [10, 15, 25, 50, 100];
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, $perPageOptions))
            $perPage = 10;
        $tasks = $taskQuery->paginate($perPage)->withQueryString();

        // Other payments query (remove search since it shouldn't apply to payments)
        $otherPaymentQuery = Payment::query()->from('payments as p')
            ->where('p.project_id', $project->id)
            ->whereIn('p.payment_type', ['other', 'full']);
        if (!$isProjectOwner && $currentUser->isProjectMember($project)) {
            $otherPaymentQuery->where('p.user_id', $currentUser->id);
        } elseif ($isProjectOwner && $selectedWorkerId) {
            $otherPaymentQuery->where('p.user_id', $selectedWorkerId);
        } elseif (!$isProjectOwner && !$currentUser->isProjectMember($project)) {
            $otherPaymentQuery->whereRaw('1 = 0');
        }
        $totalFilteredOtherPayments = $otherPaymentQuery->sum('p.amount');

        // Filtered paid task query (remove search since it shouldn't apply)
        $filteredPaidTaskQuery = Payment::query()->from('payments as p')
            ->where('p.project_id', $project->id)
            ->whereIn('p.payment_type', ['task', 'termin'])
            ->where('p.status', Payment::STATUS_APPROVED);
        if (!$isProjectOwner && $currentUser->isProjectMember($project)) {
            $filteredPaidTaskQuery->where('p.user_id', $currentUser->id);
        } elseif ($isProjectOwner && $selectedWorkerId) {
            $filteredPaidTaskQuery->where('p.user_id', $selectedWorkerId);
        } elseif (!$isProjectOwner && !$currentUser->isProjectMember($project)) {
            $filteredPaidTaskQuery->whereRaw('1 = 0');
        }
        $totalFilteredPaidTaskAmount = $filteredPaidTaskQuery->sum('p.amount');

        // Filtered paid other query (remove search since it shouldn't apply)
        $filteredPaidOtherQuery = Payment::query()->from('payments as p')
            ->where('p.project_id', $project->id)
            ->whereIn('p.payment_type', ['other', 'full'])
            ->where('p.status', Payment::STATUS_APPROVED);
        if (!$isProjectOwner && $currentUser->isProjectMember($project)) {
            $filteredPaidOtherQuery->where('p.user_id', $currentUser->id);
        } elseif ($isProjectOwner && $selectedWorkerId) {
            $filteredPaidOtherQuery->where('p.user_id', $selectedWorkerId);
        } elseif (!$isProjectOwner && !$currentUser->isProjectMember($project)) {
            $filteredPaidOtherQuery->whereRaw('1 = 0');
        }
        $totalFilteredPaidOtherAmount = $filteredPaidOtherQuery->sum('p.amount');

        $totalOverallTaskPayroll = 0;
        $totalOverallOtherPayments = 0;
        $totalOverallPaidTaskAmount = 0;
        $totalOverallPaidOtherAmount = 0;
        $totalOverallPayroll = 0;
        $budgetDifference = 0;
        if ($isProjectOwner) {
            $allDoneTasks = Task::where('project_id', $project->id)
                ->where('status', 'Done')
                ->when($request->filled('start_date'), fn($q) => $q->whereDate('updated_at', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn($q) => $q->whereDate('updated_at', '<=', $request->input('end_date')))
                ->with(['difficultyLevel', 'priorityLevel', 'projectUserMembership.wageStandard', 'project'])
                ->get();
            $totalOverallTaskPayroll = $allDoneTasks->sum('calculated_value');
            $totalOverallOtherPayments = Payment::where('project_id', $project->id)
                ->whereIn('payment_type', ['other', 'full'])
                ->sum('amount');
            $totalOverallPaidTaskAmount = Payment::where('project_id', $project->id)
                ->whereIn('payment_type', ['task', 'termin'])
                ->where('status', Payment::STATUS_APPROVED)
                ->sum('amount');
            $totalOverallPaidOtherAmount = Payment::where('project_id', $project->id)
                ->whereIn('payment_type', ['other', 'full'])
                ->where('status', Payment::STATUS_APPROVED)
                ->sum('amount');
            $totalOverallPayroll = $totalOverallTaskPayroll + $totalOverallOtherPayments;
            $budget = $project->budget ?? 0;
            $budgetDifference = $budget - $totalOverallPayroll;
        } else {
            $totalOverallTaskPayroll = $totalFilteredTaskPayroll;
            $totalOverallOtherPayments = $totalFilteredOtherPayments;
            $totalOverallPaidTaskAmount = $totalFilteredPaidTaskAmount;
            $totalOverallPaidOtherAmount = $totalFilteredPaidOtherAmount;
            $totalOverallPayroll = $totalOverallTaskPayroll + $totalOverallOtherPayments;
        }

        $workersForFilter = collect();
        if ($isProjectOwner) {
            $workersForFilter = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();
        } else {
            if ($currentUser->isProjectMember($project)) {
                $workersForFilter = collect([$currentUser]);
            }
        }

        $modalWorkers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get() ?? collect();
        $modalPaymentCalculationType = $project->payment_calculation_type ?? 'task';
        $modalPaymentTerms = collect();
        if ($modalPaymentCalculationType === 'termin') {
            $modalPaymentTerms = $project->paymentTerms()
                ->select('id', 'name', 'start_date', 'end_date')
                ->orderBy('start_date')
                ->get()
                ->map(fn($term) => [
                    'id' => $term->id,
                    'name' => $term->name,
                    'start_date_formatted' => $term->start_date ? $term->start_date->toDateString() : null,
                    'end_date_formatted' => $term->end_date ? $term->end_date->toDateString() : null,
                ]) ?? collect();
        }
        $modalUnpaidTasksQuery = Task::where('project_id', $project->id)
            ->where('status', 'Done')
            ->whereNull('payment_id')
            ->whereNotNull('updated_at')
            ->select('id', 'title', 'assigned_to', 'achievement_percentage', 'difficulty_level_id', 'priority_level_id', 'project_id', 'updated_at')
            ->with(['assignedUser:id,name', 'difficultyLevel:id,value', 'priorityLevel:id,value', 'projectUserMembership.wageStandard:id,task_price']);

        $modalUnpaidTasks = $modalUnpaidTasksQuery->get()
            ->map(function ($task) use ($project) {
                if (!$task->relationLoaded('project'))
                    $task->setRelation('project', $project);
                $task->finished_date = $task->updated_at ? Carbon::parse($task->updated_at)->toDateString() : null;
                return $task;
            }) ?? collect();
        $modalUnpaidTasksGrouped = $modalUnpaidTasks->groupBy('assigned_to') ?? collect();

        $nextTerminNumber = 1;
        $modalDefaultTerminName = '';
        if ($modalPaymentCalculationType === 'termin') {
            $lastTermin = Payment::where('project_id', $project->id)
                ->where('payment_type', 'termin')
                ->where('payment_name', 'like', 'Termin %')
                ->orderByRaw('CAST(SUBSTRING_INDEX(payment_name, " ", -1) AS UNSIGNED) DESC, created_at DESC')
                ->first();
            if ($lastTermin && preg_match('/Termin (\d+)/', $lastTermin->payment_name, $matches)) {
                $nextTerminNumber = intval($matches[1]) + 1;
            }
            $modalDefaultTerminName = "Termin " . $nextTerminNumber;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'project' => $project->only('id', 'name', 'budget', 'payment_calculation_type'),
                    'is_project_owner' => $isProjectOwner,
                    'tasks' => $tasks, // Data tasks yang sudah dipaginasi
                    'summary' => [
                        'filtered' => [
                            'total_task_payroll' => $totalFilteredTaskPayroll,
                            'total_other_payments' => $totalFilteredOtherPayments,
                            'total_paid_task_amount' => $totalFilteredPaidTaskAmount,
                            'total_paid_other_amount' => $totalFilteredPaidOtherAmount,
                        ],
                        'overall' => $isProjectOwner ? [
                            'total_task_payroll' => $totalOverallTaskPayroll,
                            'total_other_payments' => $totalOverallOtherPayments,
                            'total_payroll' => $totalOverallPayroll,
                            'total_paid_task_amount' => $totalOverallPaidTaskAmount,
                            'total_paid_other_amount' => $totalOverallPaidOtherAmount,
                            'budget_difference' => $budgetDifference,
                        ] : null,
                    ],
                    'modal_data' => [ // Data untuk modal pembuatan slip gaji
                        'workers' => $modalWorkers,
                        'payment_terms' => $modalPaymentTerms,
                        'unpaid_tasks_grouped' => $modalUnpaidTasksGrouped,
                        'default_termin_name' => $modalDefaultTerminName
                    ],
                ]
            ]);
        }

        if ($request->ajax()) {
            $tableHtml = view('penggajian._payroll_table_content', compact('project', 'tasks', 'request'))->render();
            return response()->json([
                'html' => $tableHtml,
                'totalFilteredTaskPayroll' => $totalFilteredTaskPayroll,
                'totalFilteredOtherPayments' => $totalFilteredOtherPayments,
                'totalPaidTaskAmount' => $totalFilteredPaidTaskAmount,
                'totalPaidOtherAmount' => $totalFilteredPaidOtherAmount,
                'totalOverallTaskPayroll' => $totalOverallTaskPayroll,
                'totalOverallOtherPayments' => $totalOverallOtherPayments,
                // 'totalOverallPaidTaskAmount' => $totalOverallPaidTaskAmount,
                // 'totalOverallPaidOtherAmount' => $totalOverallPaidOtherAmount,
            ]);
        }

        return view('penggajian.calculate', compact(
            'project',
            'tasks',
            'workersForFilter',
            'isProjectOwner',
            'totalFilteredTaskPayroll',
            'totalFilteredOtherPayments',
            'totalFilteredPaidTaskAmount',
            'totalFilteredPaidOtherAmount',
            'totalOverallTaskPayroll',
            'totalOverallOtherPayments',
            'totalOverallPayroll',
            'totalOverallPaidTaskAmount',
            'totalOverallPaidOtherAmount',
            'budgetDifference',
            'perPageOptions',
            'request',
            'modalWorkers',
            'modalPaymentCalculationType',
            'modalPaymentTerms',
            'modalUnpaidTasksGrouped',
            'modalDefaultTerminName'
        ));
    }

    // ... di dalam kelas PaymentController ...

    public function downloadPayslip(Request $request, Project $project, Payment $payslip)
    {
        $this->authorize('view', $payslip);

        // Logika untuk generate PDF akan ada di sini.
        // Untuk API test, kita hanya perlu mengembalikan respons sukses.

        return response()->json([
            'success' => true,
            'message' => 'Download endpoint hit successfully. PDF generation logic goes here.',
            'payslip_id' => $payslip->id,
        ]);
    }
}
