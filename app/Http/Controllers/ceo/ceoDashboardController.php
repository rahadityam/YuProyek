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
    // Ganti seluruh method index() di app/Http/Controllers/ceo/ceoDashboardController.php

    public function index()
    {
        // --- Data yang tidak berubah ---
        $userStatus = [
            'active'  => User::where('status', 'active')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
        ];

        // === LOGIKA BARU: Definisikan status aktif sekali untuk digunakan berulang kali ===
        $activeProjectStatuses = ['open', 'in_progress', 'completed', 'active'];

        // === LOGIKA BARU: Ringkasan Proyek ===
        $totalProjects = Project::count();
        $totalActiveProjects = Project::whereIn('status', $activeProjectStatuses)->count();
        $totalInactiveProjects = Project::whereNotIn('status', $activeProjectStatuses)->count();

        // === LOGIKA BARU: Ringkasan User & Worker ===
        $totalUsers = User::count();
        $workerQuery = User::where('role', '!=', 'ceo');
        $activeWorkersCount = (clone $workerQuery)->whereHas('projects', function ($q) use ($activeProjectStatuses) {
            $q->whereIn('projects.status', $activeProjectStatuses);
        })->count();
        $inactiveWorkersCount = (clone $workerQuery)->whereDoesntHave('projects', function ($q) use ($activeProjectStatuses) {
            $q->whereIn('projects.status', $activeProjectStatuses);
        })->count();

        // === PERBAIKAN DI SINI: Gunakan definisi proyek aktif yang baru ===
        // Ringkasan progress per proyek yang sedang berjalan (atau aktif)
        $projectsProgress = Project::whereIn('status', $activeProjectStatuses) // Diubah dari where('status', 'in_progress')
            ->with('tasks')
            ->latest() // Tampilkan yang terbaru dulu
            ->take(6) // Batasi maksimal 6 proyek untuk menjaga layout
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

        // --- Data Chart (tidak berubah) ---
        // Weekly
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weeklyProjects = Project::whereBetween('start_date', [$startOfWeek, $endOfWeek])->selectRaw('DAYOFWEEK(start_date) as day, COUNT(*) as total')->groupBy('day')->pluck('total', 'day')->toArray();
        $weekDays = [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];
        $projectLabels['daily'] = array_values($weekDays);
        $projectData['daily'] = array_map(fn($d) => $weeklyProjects[$d] ?? 0, array_keys($weekDays));

        // Monthly
        $monthlyProjects = Project::whereYear('start_date', now()->year)->selectRaw('MONTH(start_date) as month, COUNT(*) as total')->groupBy('month')->pluck('total', 'month')->toArray();
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $projectLabels['monthly'] = $months;
        $projectData['monthly'] = array_map(fn($i) => $monthlyProjects[$i] ?? 0, range(1, 12));

        // Yearly
        $currentYear = now()->year;
        $years = range($currentYear - 4, $currentYear);
        $yearlyProjects = Project::whereIn(DB::raw('YEAR(start_date)'), $years)->selectRaw('YEAR(start_date) as year, COUNT(*) as total')->groupBy('year')->pluck('total', 'year')->toArray();
        $projectLabels['yearly'] = array_map('strval', $years);
        $projectData['yearly'] = array_map(fn($y) => $yearlyProjects[$y] ?? 0, $years);

        // --- Kirim ke view ---
        return view('ceo.dashboard', [
            'totalUsers' => $totalUsers,
            'userStatus' => $userStatus,
            'totalProjects' => $totalProjects,
            'totalActiveProjects' => $totalActiveProjects,
            'totalInactiveProjects' => $totalInactiveProjects,
            'projectLabels' => $projectLabels,
            'projectData' => $projectData,
            'activeWorkersCount' => $activeWorkersCount,
            'inactiveWorkersCount' => $inactiveWorkersCount,
            'projectsProgress' => $projectsProgress
        ]);
    }
}
