<?php

namespace App\Http\Controllers\ceo;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ceoDashboardController extends Controller
{
    public function index()
    {
        // Ringkasan user dan proyek
        $totalUsers = User::count();
        $totalProjects = Project::count();
        $totalProjectsStarted = Project::whereNotNull('start_date')->count();
        $totalProjectsEnded = Project::whereNotNull('end_date')->count();

        // Status user (active vs blocked)
        $userStatus = [
            'active'  => User::where('status', 'active')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
        ];

        // Jumlah pekerja aktif dan tidak aktif
        $activeWorkersCount = User::whereHas('projects', function ($q) {
            $q->where('projects.status', 'in_progress');
        })->count();

        $inactiveWorkersCount = User::whereDoesntHave('projects', function ($q) {
            $q->where('projects.status', 'in_progress');
        })->count();

        // Statistik tugas global
        $taskStats = [
            'total' => Task::count(),
            'done' => Task::where('status', 'done')->count()
        ];

        // Ringkasan status proyek
        $projectsStatus = [
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'done' => Project::where('status', 'done')->count(),
        ];

        // Ringkasan progress per proyek yang sedang berjalan
        $projectsProgress = Project::where('status', 'in_progress')
            ->with('tasks')
            ->get()
            ->map(function ($project) {
                $totalTasks = $project->tasks->count();
                $completedTasks = $project->tasks->where('status', 'done')->count();
                $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

                return [
                    'name' => $project->name,
                    'start_date' => $project->start_date?->toDateString(),
                    'end_date' => $project->end_date?->toDateString(),
                    'progress_percent' => $progress,
                    'completed_tasks' => $completedTasks,
                    'total_tasks' => $totalTasks
                ];
            });

        // Weekly (Daily Project)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weeklyProjects = Project::whereBetween('start_date', [$startOfWeek, $endOfWeek])
            ->selectRaw('DAYOFWEEK(start_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $weekDays = [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];
        $projectLabels['daily'] = array_values($weekDays);
        $projectData['daily'] = array_map(fn($d) => $weeklyProjects[$d] ?? 0, array_keys($weekDays));

        // Monthly
        $monthlyProjects = Project::whereYear('start_date', now()->year)
            ->selectRaw('MONTH(start_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $projectLabels['monthly'] = $months;
        $projectData['monthly'] = array_map(fn($i) => $monthlyProjects[$i] ?? 0, range(1, 12));

        // Yearly
        $currentYear = now()->year;
        $years = range($currentYear - 4, $currentYear);
        $yearlyProjects = Project::whereIn(DB::raw('YEAR(start_date)'), $years)
            ->selectRaw('YEAR(start_date) as year, COUNT(*) as total')
            ->groupBy('year')
            ->pluck('total', 'year')
            ->toArray();

        $projectLabels['yearly'] = array_map('strval', $years);
        $projectData['yearly'] = array_map(fn($y) => $yearlyProjects[$y] ?? 0, $years);

        // Kirim ke view
        return view('ceo.dashboard', compact(
            'totalUsers',
            'userStatus',
            'totalProjects',
            'totalProjectsStarted',
            'totalProjectsEnded',
            'projectLabels',
            'projectData',
            'activeWorkersCount',
            'inactiveWorkersCount',
            'taskStats',
            'projectsStatus',
            'projectsProgress'
        ));
    }
}
