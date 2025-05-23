<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\Task;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
// use Illuminate\Http\Request; // Request tidak lagi dibutuhkan di sini

class PayrollCalculate extends Component
{
    use WithPagination;

    public Project $project;
    public $workers; // Untuk dropdown filter

    // Filter & Pagination State
    public $filterWorkerId = 'all';
    public $filterPaymentStatus = 'all';
    public $filterSearch = '';
    public $perPage = 10;
    public $sortField = 'updated_at';
    public $sortDirection = 'desc';

    public $perPageOptions = [10, 15, 25, 50, 100];

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'filterWorkerId' => ['except' => 'all', 'as' => 'worker'],
        'filterPaymentStatus' => ['except' => 'all', 'as' => 'status'],
        'filterSearch' => ['except' => '', 'as' => 'search'],
        'perPage' => ['except' => 10, 'as' => 'limit'],
        'sortField' => ['except' => 'updated_at', 'as' => 'sort'],
        'sortDirection' => ['except' => 'desc', 'as' => 'direction'],
        'page' => ['except' => 1]
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        // [✓] Perbaiki query ini agar tidak ambigu (spesifikasikan users.id dan users.name)
        $this->workers = $project->workers()
                          ->wherePivot('status', 'accepted')
                          ->orderBy('users.name') // Gunakan users.name
                          ->select('users.id', 'users.name') // Pilih kolom dari tabel users
                          ->get();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['filterWorkerId', 'filterPaymentStatus', 'filterSearch', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy($field)
    {
         $allowedSorts = ['title', 'assigned_user_name', 'difficulty_value', 'priority_value', 'achievement_percentage', 'payment_status', 'updated_at'];
         if (!in_array($field, $allowedSorts)) { return; }
         if ($this->sortField === $field) { $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'; }
         else { $this->sortDirection = 'asc'; $this->sortField = $field; }
         $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['filterWorkerId', 'filterPaymentStatus', 'filterSearch', 'perPage', 'sortField', 'sortDirection']);
        $this->resetPage();
    }

    public function render()
    {
        // --- Base Query Task ---
        $taskQuery = Task::query()
            ->where('tasks.project_id', $this->project->id)
            ->where('tasks.status', 'Done')
            // [✓] Spesifikasikan kolom dari tabel 'tasks' di SELECT utama
            ->select(
                'tasks.id', 'tasks.title', 'tasks.assigned_to', 'tasks.difficulty_level_id',
                'tasks.priority_level_id', 'tasks.achievement_percentage', 'tasks.updated_at',
                'tasks.payment_id', 'tasks.project_id', 'tasks.description', // Tambahkan kolom lain jika perlu
                'users.name as assigned_user_name', // Alias aman
                'd_levels.value as difficulty_value', // Alias aman
                'p_levels.value as priority_value' // Alias aman
            )
            ->join('users', 'tasks.assigned_to', '=', 'users.id')
            ->leftJoin('difficulty_levels as d_levels', 'tasks.difficulty_level_id', '=', 'd_levels.id')
            ->leftJoin('priority_levels as p_levels', 'tasks.priority_level_id', '=', 'p_levels.id')
            ->with([ // Eager loading tidak terpengaruh ambiguitas SELECT utama
                'assignedUser:id,name',
                'difficultyLevel:id,name,value',
                'priorityLevel:id,name,value',
                'payment:id,payment_name',
                'projectUserMembership.wageStandard:id,task_price',
                'project:id,difficulty_weight,priority_weight'
                ]);

        // --- Filters (Tetap sama) ---
        if ($this->filterWorkerId && $this->filterWorkerId !== 'all') { $taskQuery->where('tasks.assigned_to', $this->filterWorkerId); }
        if ($this->filterPaymentStatus && $this->filterPaymentStatus !== 'all') { if ($this->filterPaymentStatus === 'paid') $taskQuery->whereNotNull('tasks.payment_id'); elseif ($this->filterPaymentStatus === 'unpaid') $taskQuery->whereNull('tasks.payment_id'); }
        if (!empty($this->filterSearch)) { $searchTerm = $this->filterSearch; $taskQuery->where(function (Builder $q) use ($searchTerm) { $q->where('tasks.title', 'like', "%{$searchTerm}%") ->orWhere('users.name', 'like', "%{$searchTerm}%"); }); }

        // --- Sorting (Spesifikasikan nama tabel jika perlu) ---
        $allowedSortsMapping = [
            'title' => 'tasks.title', // [✓] Spesifikasikan tasks.title
            'assigned_user_name' => 'users.name', // Alias sudah OK
            'difficulty_value' => 'd_levels.value', // Alias sudah OK
            'priority_value' => 'p_levels.value', // Alias sudah OK
            'achievement_percentage' => 'tasks.achievement_percentage', // [✓] Spesifikasikan tasks...
            'payment_status' => DB::raw('CASE WHEN tasks.payment_id IS NULL THEN 0 ELSE 1 END'), // [✓] Spesifikasikan tasks.payment_id
            'updated_at' => 'tasks.updated_at', // [✓] Spesifikasikan tasks.updated_at
        ];

        if (array_key_exists($this->sortField, $allowedSortsMapping)) {
            $taskQuery->orderBy($allowedSortsMapping[$this->sortField], $this->sortDirection);
             if ($this->sortField !== 'updated_at') {
                $taskQuery->orderBy('tasks.updated_at', 'desc'); // [✓] Spesifikasikan tasks.updated_at
             }
        } else {
            $taskQuery->orderBy('tasks.updated_at', 'desc'); // [✓] Spesifikasikan tasks.updated_at
        }

        // --- Clone Query untuk Kalkulasi Total Filtered (Tetap sama) ---
        $filteredQueryForTotals = clone $taskQuery;

        // --- Calculate Filtered Task Payroll (Tetap sama) ---
        $totalFilteredTaskPayroll = 0;
        try {
             $allFilteredTasksForSum = $filteredQueryForTotals->get();
             $totalFilteredTaskPayroll = $allFilteredTasksForSum->sum('calculated_value');
        } catch (\Exception $e) { Log::error("LW Payroll Calc: Error calculating filtered task payroll: " . $e->getMessage()); session()->flash('calc_error', 'Gagal menghitung total nilai task terfilter.'); }

         // --- Paginate Tasks for Display ---
         $tasks = $taskQuery->paginate($this->perPage);

        // --- Calculate Filtered Other Payments (Bonus/Full) - Hak Gaji (Tetap sama) ---
        $otherPaymentQuery = Payment::query()->from('payments as p')->where('p.project_id', $this->project->id)->whereIn('p.payment_type', ['other', 'full']);
        if ($this->filterWorkerId && $this->filterWorkerId !== 'all') { $otherPaymentQuery->where('p.user_id', $this->filterWorkerId); }
        if (!empty($this->filterSearch)) { $searchTerm = $this->filterSearch; $otherPaymentQuery->join('users as u', 'p.user_id', '=', 'u.id')->where(function (Builder $q) use ($searchTerm) { $q->where('p.payment_name', 'like', "%{$searchTerm}%")->orWhere('p.notes', 'like', "%{$searchTerm}%")->orWhere('u.name', 'like', "%{$searchTerm}%"); })->select('p.*'); }
        $totalFilteredOtherPayments = $otherPaymentQuery->sum('p.amount');

        // --- Calculate Filtered PAID Amounts (status=approved) (Tetap sama) ---
        $filteredPaidTaskQuery = Payment::query()->from('payments as p')->where('p.project_id', $this->project->id)->whereIn('p.payment_type', ['task', 'termin'])->where('p.status', Payment::STATUS_APPROVED);
        if ($this->filterWorkerId && $this->filterWorkerId !== 'all') { $filteredPaidTaskQuery->where('p.user_id', $this->filterWorkerId); }
        if (!empty($this->filterSearch)) { /* Filter optional */ }
        $totalFilteredPaidTaskAmount = $filteredPaidTaskQuery->sum('p.amount');
        $filteredPaidOtherQuery = Payment::query()->from('payments as p')->where('p.project_id', $this->project->id)->whereIn('p.payment_type', ['other', 'full'])->where('p.status', Payment::STATUS_APPROVED);
        if ($this->filterWorkerId && $this->filterWorkerId !== 'all') { $filteredPaidOtherQuery->where('p.user_id', $this->filterWorkerId); }
        if (!empty($this->filterSearch)) { /* Filter optional */ }
        $totalFilteredPaidOtherAmount = $filteredPaidOtherQuery->sum('p.amount');

        // --- Calculate Overall Totals (Tetap sama) ---
        $totalOverallTaskPayroll = Task::where('project_id', $this->project->id)->where('status', 'Done')->get()->sum('calculated_value');
        $totalOverallOtherPayments = Payment::where('project_id', $this->project->id)->whereIn('payment_type', ['other', 'full'])->sum('amount');
        $totalOverallPaidTaskAmount = Payment::where('project_id', $this->project->id)->whereIn('payment_type', ['task', 'termin'])->where('status', Payment::STATUS_APPROVED)->sum('amount');
        $totalOverallPaidOtherAmount = Payment::where('project_id', $this->project->id)->whereIn('payment_type', ['other', 'full'])->where('status', Payment::STATUS_APPROVED)->sum('amount');
        $totalOverallPayroll = $totalOverallTaskPayroll + $totalOverallOtherPayments;
        $budget = $this->project->budget ?? 0;
        $budgetDifference = $budget - $totalOverallPayroll;

        // --- Kirim Data ke View ---
        return view('livewire.project.payroll-calculate', [
            'tasks' => $tasks,
            'totalFilteredTaskPayroll' => $totalFilteredTaskPayroll,
            'totalFilteredOtherPayments' => $totalFilteredOtherPayments,
            'totalFilteredPaidTaskAmount' => $totalFilteredPaidTaskAmount,
            'totalFilteredPaidOtherAmount' => $totalFilteredPaidOtherAmount,
            'totalOverallTaskPayroll' => $totalOverallTaskPayroll,
            'totalOverallOtherPayments' => $totalOverallOtherPayments,
            'totalOverallPayroll' => $totalOverallPayroll,
            'totalOverallPaidTaskAmount' => $totalOverallPaidTaskAmount,
            'totalOverallPaidOtherAmount' => $totalOverallPaidOtherAmount,
            'budgetDifference' => $budgetDifference,
        ]);
    }
}