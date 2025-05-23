<?php

namespace App\Livewire\Project;

use Livewire\Component;
use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;

class Dashboard extends Component
{
    public Project $project;
    
    // Tambahkan property ini untuk menyimpan ID komponen
    public $componentId;
    
    public string $activeTab = 'tasks';

    protected $layout = 'layouts.app';

    public function mount(Project $project)
    {
        $this->project = $project;
        // Inisialisasi ID komponen unik saat mount
        $this->componentId = uniqid('dashboard-');
    }

    public function switchTab($tabName)
    {
        $this->activeTab = $tabName;
    }

    public function render()
    {
        // --- Logika Pengambilan Data (dipindahkan dari ProjectController@projectDashboard) ---

        // 1. Data Aktivitas
        $recentActivities = ActivityLog::where('project_id', $this->project->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 2. Data Tugas (Statistik & Grafik)
        $tasks = $this->project->tasks()->with([
                'assignedUser:id,name',
                'difficultyLevel:id,name,value',
                'priorityLevel:id,name,value'
                ])->get();

        $taskStats = [
            'total'       => $tasks->count(),
            'todo'        => $tasks->where('status', 'To Do')->count(),
            'in_progress' => $tasks->where('status', 'In Progress')->count(),
            'review'      => $tasks->where('status', 'Review')->count(),
            'done'        => $tasks->where('status', 'Done')->count(),
        ];
        $inProgressTasks = $tasks->where('status', 'In Progress')->take(5);

        // Data untuk Grafik Tugas per Anggota (Stacked Bar)
        $tasksByAssigneeGrouped = $tasks->groupBy('assigned_to');
        $assigneeIds = $tasksByAssigneeGrouped->keys()->filter(fn($id) => !is_null($id) && $id > 0)->values()->all();
        $assignees = User::whereIn('id', $assigneeIds)->pluck('name', 'id');
        $statusOrder = ['To Do', 'In Progress', 'Review', 'Done'];
        $statusColors = ['#E5E7EB','#FCD34D','#93C5FD','#6EE7B7'];
        $statusBorderColors = ['#9CA3AF','#F59E0B','#3B82F6','#10B981'];
        $assigneeLabels = collect($assigneeIds)->map(fn($id) => $assignees->get($id, "User ID: {$id}"))->toArray();
        $unassignedTasks = $tasksByAssigneeGrouped->get(null, collect())->merge($tasksByAssigneeGrouped->get(0, collect()));
        $hasUnassigned = $unassignedTasks->isNotEmpty();
        if ($hasUnassigned) $assigneeLabels[] = 'Unassigned';
        $datasets = [];
        foreach ($statusOrder as $index => $status) {
            $dataCounts = [];
            foreach ($assigneeIds as $id) $dataCounts[] = optional($tasksByAssigneeGrouped->get($id))->where('status', $status)->count() ?? 0;
            if ($hasUnassigned) $dataCounts[] = $unassignedTasks->where('status', $status)->count();
            $datasets[] = [
                'label' => $status,
                'data' => $dataCounts,
                'backgroundColor' => $statusColors[$index] ?? '#cccccc',
                'borderColor' => $statusBorderColors[$index] ?? '#999999',
                'borderWidth' => 1
            ];
        }
        $tasksByAssigneeStatusChartData = [ 'labels' => $assigneeLabels, 'datasets' => $datasets ];

        // 3. Data Tim
        $acceptedWorkers = $this->project->workers()->wherePivot('status', 'accepted')->select('users.id', 'users.name', 'project_users.position')->take(5)->get();
        $acceptedWorkersCount = $this->project->workers()->wherePivot('status', 'accepted')->count();

        // 4. Data Finansial
        $budget = $this->project->budget ?? 0;
        $allDoneTasks = Task::where('project_id', $this->project->id)->where('status', 'Done')
                            ->with(['projectUserMembership.wageStandard:id,task_price', 'difficultyLevel:id,value', 'priorityLevel:id,value', 'project:id,difficulty_weight,priority_weight'])
                            ->get();
        $totalTaskHakGaji = $allDoneTasks->sum('calculated_value');
        $totalOtherFullHakGaji = Payment::where('project_id', $this->project->id)->whereIn('payment_type', ['other', 'full'])->sum('amount');
        $totalPaidTaskTermin = Payment::where('project_id', $this->project->id)->whereIn('payment_type', ['task', 'termin'])->where('status', Payment::STATUS_APPROVED)->sum('amount');
        $totalPaidOtherFull = Payment::where('project_id', $this->project->id)->whereIn('payment_type', ['other', 'full'])->where('status', Payment::STATUS_APPROVED)->sum('amount');
        $totalHakGaji = $totalTaskHakGaji + $totalOtherFullHakGaji;
        $totalPaid = $totalPaidTaskTermin + $totalPaidOtherFull;
        $remainingUnpaid = max(0, $totalHakGaji - $totalPaid);
        $budgetDifference = $budget - $totalHakGaji;
        $financialStats = [
            'budget' => $budget, 'totalTaskHakGaji' => $totalTaskHakGaji, 'totalOtherFullHakGaji' => $totalOtherFullHakGaji, 'totalHakGaji' => $totalHakGaji, 'totalPaidTaskTermin' => $totalPaidTaskTermin, 'totalPaidOtherFull' => $totalPaidOtherFull, 'totalPaid' => $totalPaid, 'remainingUnpaid' => $remainingUnpaid, 'budgetDifference' => $budgetDifference,
            'overviewChartData' => [ 'paidTaskTermin' => $totalPaidTaskTermin, 'paidOtherFull' => $totalPaidOtherFull, 'remainingUnpaid' => $remainingUnpaid, ],
            'spendingVsBudgetChartData' => [ 'budget' => $budget, 'hakGaji' => $totalHakGaji, 'paid' => $totalPaid, ]
        ];

        return view('livewire.project.dashboard', [
            'taskStats' => $taskStats,
            'tasksByAssigneeStatusChartData' => $tasksByAssigneeStatusChartData,
            'inProgressTasks' => $inProgressTasks,
            'acceptedWorkers' => $acceptedWorkers,
            'acceptedWorkersCount' => $acceptedWorkersCount,
            'recentActivities' => $recentActivities,
            'financialStats' => $financialStats,
        ]);
    }
}