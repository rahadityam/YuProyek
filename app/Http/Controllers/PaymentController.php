<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log; // Import Log facade

class PaymentController extends Controller
{

    /**
     * Display the Payment Upload Form and Payment History List page.
     */
    public function payment(Project $project, Request $request)
    {
        // --- Get Workers & Payment History ---
        $workers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();
        $query = Payment::where('project_id', $project->id)->with('user', 'tasks');

        // Sorting Logic
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['created_at', 'amount', 'payment_name', 'status', 'user_name', 'payment_type'];
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'user_name') {
                $query->select('payments.*')
                      ->join('users', 'payments.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        $payments = $query->paginate(10, ['*'], 'history_page')->withQueryString();


        // --- Get Unpaid Tasks with Calculated Values ---
         $unpaidTasks = Task::where('project_id', $project->id)
             ->where('status', 'Done')
             ->whereNull('payment_id')
             ->select('id', 'title', 'assigned_to', 'achievement_percentage', 'difficulty_level_id', 'priority_level_id', 'project_id')
             ->with([
                 'assignedUser:id,name',
                 'difficultyLevel:id,value',
                 'priorityLevel:id,value',
                 'projectUserMembership.wageStandard' // For base_value accessor
             ])
             ->get()
             ->map(function ($task) use ($project) {
                 // Ensure project relation is loaded for accessors if needed
                 if (!$task->relationLoaded('project')) {
                    $task->setRelation('project', $project);
                 }
                 // Explicitly call accessors to add values to the object
                 $task->wsm_score = $task->wsm_score;
                 $task->calculated_value = $task->calculated_value;
                 return $task;
             });

        $unpaidTasksGrouped = $unpaidTasks->groupBy('assigned_to');

        return view('payments.index', compact(
            'project',
            'workers',
            'payments',
            'unpaidTasksGrouped',
            'sortField',
            'sortDirection',
            'request'
        ));
    }

    /**
     * Store a new payment record (from upload form).
     */
    public function storePayment(Request $request, Project $project)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type' => ['required', Rule::in(['task', 'other'])],
            'payment_name' => 'required|string|max:255',
            'amount' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === 'other'),
                'nullable', 'numeric', 'min:0'
            ],
            'proof_image' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'notes' => 'nullable|string',
            'task_ids' => [
                Rule::requiredIf(fn () => $request->input('payment_type') === 'task'),
                'nullable', 'array'
            ],
            'task_ids.*' => ['exists:tasks,id'],
        ]);

        $worker = User::find($validated['user_id']);
        if (!$worker) {
             return back()->withErrors(['user_id' => 'Pekerja tidak ditemukan.'])->withInput();
        }
        $bankAccount = $worker->bank_account ?? 'N/A';

        DB::beginTransaction();
        try {
            $path = null;
            if ($request->hasFile('proof_image')) {
                $path = $request->file('proof_image')->store('payment_proofs', 'public');
            }

            $paymentAmount = 0;
            $validTasks = collect();

            if ($validated['payment_type'] === 'task') {
                 $tasksFromRequest = Task::with(['difficultyLevel', 'priorityLevel', 'project', 'projectUserMembership.wageStandard']) // Load relations for calc
                                         ->findMany($validated['task_ids']);

                 $validTasks = $tasksFromRequest->filter(function ($task) use ($validated, $project) {
                     if (!$task) return false;
                     return $task->project_id == $project->id &&
                            $task->assigned_to == $validated['user_id'] &&
                            $task->status == 'Done' &&
                            is_null($task->payment_id);
                 });

                 if ($validTasks->count() !== count($validated['task_ids'])) {
                     $requestedIds = $validated['task_ids'];
                     $validIds = $validTasks->pluck('id')->toArray();
                     $invalidIds = array_diff($requestedIds, $validIds);
                     $invalidIdString = implode(', ', $invalidIds);
                     $errorMessage = "Task dengan ID ({$invalidIdString}) tidak valid (sudah dibayar/status salah/bukan milik pekerja). Silakan refresh.";
                     throw new \Exception($errorMessage);
                 }

                 // Recalculate amount based on VALID tasks in backend
                 $paymentAmount = $validTasks->sum(function($task) {
                     return $task->calculated_value; // Call accessor
                 });

            } else { // Tipe 'other'
                $paymentAmount = $validated['amount'];
            }

            $payment = Payment::create([
                'project_id' => $project->id,
                'user_id' => $validated['user_id'],
                'payment_type' => $validated['payment_type'],
                'payment_name' => $validated['payment_name'],
                'bank_account' => $bankAccount,
                'amount' => $paymentAmount, // Use the determined/recalculated amount
                'proof_image' => $path,
                'notes' => $validated['notes'],
                'status' => 'completed', // Default to completed
            ]);

            // Link Tasks ONLY if type is 'task' and tasks are valid
            if ($validated['payment_type'] === 'task' && $validTasks->isNotEmpty()) {
                Task::whereIn('id', $validTasks->pluck('id'))->update(['payment_id' => $payment->id]);
            }

            DB::commit();

             $message = $validated['payment_type'] === 'task'
                ? 'Bukti pembayaran berhasil diunggah dan task terkait.'
                : 'Pembayaran (' . e($validated['payment_name']) . ') berhasil diunggah.';
            return redirect()->route('projects.pembayaran', $project)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path) && $path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            Log::error('Payment Store Failed for Project ' . $project->id . ': ' . $e->getMessage());
            return back()->withErrors(['general' => 'Gagal menyimpan pembayaran: ' . $e->getMessage()])->withInput();
        }
    }

     /**
     * Display the details of a specific payment, including linked tasks.
     */
    public function showPaymentDetail(Project $project, Payment $payment)
    {
         if ($payment->project_id !== $project->id) { abort(404); }
        $payment->load(['user', 'tasks.difficultyLevel', 'tasks.priorityLevel', 'tasks.assignedUser']);
        return view('payments.show_detail', compact('project', 'payment'));
    }


    /**
     * Update the status of a payment.
     */
    public function updateStatus(Request $request, Project $project, Payment $payment)
    {
         if ($payment->project_id !== $project->id) { abort(404); }
        $validated = $request->validate(['status' => 'required|in:pending,completed,rejected']);
        $originalStatus = $payment->status;
        $newStatus = $validated['status'];
        DB::beginTransaction();
        try {
            $payment->update(['status' => $newStatus]);
            // Unlink tasks if payment type is 'task' and status changes to 'rejected'
            if ($payment->payment_type === 'task' && $newStatus === 'rejected' && ($originalStatus === 'completed' || $originalStatus === 'pending')) {
                $payment->tasks()->update(['payment_id' => null]);
            }
            // Optional: Handle changing FROM rejected TO completed (maybe re-link?) - complex, usually handled by re-upload/edit
            DB::commit();
            return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Payment Status Update Failed for Payment ' . $payment->id . ': ' . $e->getMessage());
             return back()->withErrors(['general' => 'Gagal memperbarui status pembayaran: ' . $e->getMessage()]);
         }
    }

    /**
     * Delete a payment and unlink associated tasks.
     */
    public function destroy(Project $project, Payment $payment)
    {
         if ($payment->project_id !== $project->id) { abort(404); }
        DB::beginTransaction();
        try {
             // Unlink tasks ONLY if payment type is 'task'
             if ($payment->payment_type === 'task') {
                 $payment->tasks()->update(['payment_id' => null]);
             }
             // Delete proof image
             if ($payment->proof_image && Storage::disk('public')->exists($payment->proof_image)) {
                 Storage::disk('public')->delete($payment->proof_image);
             }
             // Delete payment record
             $payment->delete();
             DB::commit();
              $message = $payment->payment_type === 'task'
                ? 'Pembayaran berhasil dihapus dan task terkait dikembalikan ke status belum dibayar.'
                : 'Pembayaran (' . e($payment->payment_name) . ') berhasil dihapus.';
             return redirect()->route('projects.pembayaran', $project)->with('success', $message);
        } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Payment Delete Failed for Payment '. $payment->id .': ' . $e->getMessage());
             return back()->withErrors(['general' => 'Gagal menghapus pembayaran: ' . $e->getMessage()]);
         }
    }

    // =====================================================================
    // PAYROLL CALCULATION PAGE
    // =====================================================================

    /**
     * Display the Payroll Calculation page or update its content via AJAX.
     */
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
            ->leftJoin('priority_levels as p_levels', 'tasks.priority_level_id', '=', 'p_levels.id');

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
            // Cannot sort by accessors (wsm_score, base_value, calculated_value) in DB query
        ];

        if (array_key_exists($sortField, $allowedSorts)) {
            $taskQuery->orderBy($allowedSorts[$sortField], $sortDirection);
            // Add secondary sort for consistency if primary sort is not unique enough
            if ($sortField !== 'updated_at') {
                $taskQuery->orderBy('tasks.updated_at', 'desc');
            }
        } else {
            $taskQuery->orderBy('tasks.updated_at', 'desc'); // Default sort
        }

        // --- Calculate Filtered Task Payroll ---
        $totalFilteredTaskPayroll = 0;
        try {
            // Get IDs of filtered tasks
            $filteredTaskIds = (clone $taskQuery)->pluck('tasks.id');
            // Fetch full models only for filtered tasks + relations needed for accessors
            if ($filteredTaskIds->isNotEmpty()) {
                $allFilteredTasks = Task::with([
                                        'difficultyLevel', // Needed for WSM
                                        'priorityLevel', // Needed for WSM
                                        'projectUserMembership.wageStandard', // Needed for base_value
                                        'project' // Needed for weights in WSM
                                    ])
                                    ->findMany($filteredTaskIds);
                // Sum the calculated_value accessor
                $totalFilteredTaskPayroll = $allFilteredTasks->sum('calculated_value');
            }
        } catch (\Exception $e) {
            Log::error("Error calculating totalFilteredTaskPayroll: " . $e->getMessage());
            // Optionally add query details: ['query' => $taskQuery->toSql(), 'bindings' => $taskQuery->getBindings()]
            $totalFilteredTaskPayroll = 0; // Fallback
        }

        // --- Paginate Tasks AFTER calculating filtered sum ---
        $perPageOptions = [10, 15, 25, 50, 100];
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, $perPageOptions)) $perPage = 10;
        $tasks = $taskQuery->paginate($perPage)->withQueryString(); // PAGINATED TASK RESULTS

        // --- Calculate Filtered Other Payments ---
        $otherPaymentQuery = Payment::query()->from('payments as p') // Use alias
                                    ->where('p.project_id', $project->id)
                                    ->where('p.payment_type', 'other');
        if ($selectedWorkerId && $selectedWorkerId !== 'all') {
            $otherPaymentQuery->where('p.user_id', $selectedWorkerId);
        }
        if ($searchTerm) {
            // Join users only if searching
            $otherPaymentQuery->join('users as u', 'p.user_id', '=', 'u.id')
                              ->where(function (Builder $q) use ($searchTerm) {
                                  $q->where('p.payment_name', 'like', "%{$searchTerm}%")
                                    ->orWhere('p.notes', 'like', "%{$searchTerm}%")
                                    ->orWhere('u.name', 'like', "%{$searchTerm}%"); // Search user name
                              })
                              ->select('p.*'); // Select only payment columns after join
        }
        $totalFilteredOtherPayments = $otherPaymentQuery->sum('p.amount'); // Sum amount

        // --- Calculate Filtered PAID Amounts (status=completed) ---
        // 1. Filtered Paid Task Amount
        $filteredPaidTaskQuery = Payment::query()->from('payments as p')
                                        ->where('p.project_id', $project->id)
                                        ->where('p.payment_type', 'task')
                                        ->where('p.status', 'completed');
        if ($selectedWorkerId && $selectedWorkerId !== 'all') { $filteredPaidTaskQuery->where('p.user_id', $selectedWorkerId); }
        if ($searchTerm) {
             $filteredPaidTaskQuery->join('users as u', 'p.user_id', '=', 'u.id')
                                 ->where(function (Builder $q) use ($searchTerm) {
                                      $q->where('p.payment_name', 'like', "%{$searchTerm}%")
                                        ->orWhere('p.notes', 'like', "%{$searchTerm}%")
                                        ->orWhere('u.name', 'like', "%{$searchTerm}%");
                                  })
                                 ->select('p.*');
        }
        $totalFilteredPaidTaskAmount = $filteredPaidTaskQuery->sum('p.amount');

        // 2. Filtered Paid Other Amount
        $filteredPaidOtherQuery = Payment::query()->from('payments as p')
                                        ->where('p.project_id', $project->id)
                                        ->where('p.payment_type', 'other')
                                        ->where('p.status', 'completed');
        if ($selectedWorkerId && $selectedWorkerId !== 'all') { $filteredPaidOtherQuery->where('p.user_id', $selectedWorkerId); }
        if ($searchTerm) {
             $filteredPaidOtherQuery->join('users as u', 'p.user_id', '=', 'u.id')
                                  ->where(function (Builder $q) use ($searchTerm) {
                                      $q->where('p.payment_name', 'like', "%{$searchTerm}%")
                                        ->orWhere('p.notes', 'like', "%{$searchTerm}%")
                                        ->orWhere('u.name', 'like', "%{$searchTerm}%");
                                  })
                                 ->select('p.*');
        }
        $totalFilteredPaidOtherAmount = $filteredPaidOtherQuery->sum('p.amount');

        // --- Calculate Overall Totals (No Filters applied except project_id/status) ---
        // Overall Hak Gaji Task
        $totalOverallTaskPayroll = 0;
        try {
             $allDoneTasks = Task::where('project_id', $project->id)
                              ->where('status', 'Done')
                              ->with(['difficultyLevel', 'priorityLevel', 'projectUserMembership.wageStandard', 'project'])
                              ->get();
             $totalOverallTaskPayroll = $allDoneTasks->sum('calculated_value');
         } catch (\Exception $e) {
             Log::error("Error calculating totalOverallTaskPayroll: " . $e->getMessage());
         }
        // Overall Hak Gaji Other
        $totalOverallOtherPayments = Payment::where('project_id', $project->id)
                                            ->where('payment_type', 'other')
                                            ->sum('amount');
        // Overall Paid Task
        $totalOverallPaidTaskAmount = Payment::where('project_id', $project->id)
                                             ->where('payment_type', 'task')
                                             ->where('status', 'completed')
                                             ->sum('amount');
        // Overall Paid Other
        $totalOverallPaidOtherAmount = Payment::where('project_id', $project->id)
                                              ->where('payment_type', 'other')
                                              ->where('status', 'completed')
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
            // Render only the table+pagination part
            $tableHtml = view('penggajian._payroll_table_content', compact(
                'project',
                'tasks', // PAGINATED tasks
                'request'
            ))->render();

            // Return JSON with filtered totals
            return response()->json([
                'html' => $tableHtml,
                'totalFilteredTaskPayroll' => $totalFilteredTaskPayroll,
                'totalFilteredOtherPayments' => $totalFilteredOtherPayments,
                'totalPaidTaskAmount' => $totalFilteredPaidTaskAmount, // Filtered paid task
                'totalPaidOtherAmount' => $totalFilteredPaidOtherAmount, // Filtered paid other
                // Overall totals are usually static and not needed in AJAX response
            ]);
        }

        // Return full view with ALL totals for initial load
        return view('penggajian.calculate', compact(
            'project',
            'tasks', // PAGINATED tasks
            'workers',
            'totalFilteredTaskPayroll',
            'totalFilteredOtherPayments',
            'totalFilteredPaidTaskAmount',   // Filtered Paid Task
            'totalFilteredPaidOtherAmount',  // Filtered Paid Other
            'totalOverallTaskPayroll',
            'totalOverallOtherPayments',
            'totalOverallPayroll',
            'totalOverallPaidTaskAmount',    // Overall Paid Task
            'totalOverallPaidOtherAmount',   // Overall Paid Other
            'budgetDifference',
            'perPageOptions',
            'request'
        ));
    }
}