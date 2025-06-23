<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();

        $userStatus = [
            'active'  => User::where('status', 'active')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
        ];

        $totalProjects = Project::count();
        $totalProjectsStarted = Project::whereNotNull('start_date')->count();
        $totalProjectsEnded = Project::whereNotNull('end_date')
            ->whereDate('end_date', '<=', Carbon::today())
            ->count();


        // WEEKLY (daily)
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek();     // Sunday
        $weeklyProjects = Project::whereBetween('start_date', [$startOfWeek, $endOfWeek])
            ->selectRaw('DAYOFWEEK(start_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $weekDays = [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];
        $projectLabels['daily'] = array_values($weekDays);
        $projectData['daily'] = array_map(fn($d) => $weeklyProjects[$d] ?? 0, array_keys($weekDays));

        // MONTHLY (Jan–Dec)
        $monthlyProjects = Project::whereYear('start_date', now()->year)
            ->selectRaw('MONTH(start_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $projectLabels['monthly'] = $months;
        $projectData['monthly'] = array_map(fn($i) => $monthlyProjects[$i] ?? 0, range(1, 12));

        // YEARLY (last 5 years)
        $currentYear = now()->year;
        $years = range($currentYear - 4, $currentYear);
        $yearlyProjects = Project::whereIn(DB::raw('YEAR(start_date)'), $years)
            ->selectRaw('YEAR(start_date) as year, COUNT(*) as total')
            ->groupBy('year')
            ->pluck('total', 'year')
            ->toArray();

        $projectLabels['yearly'] = array_map('strval', $years);
        $projectData['yearly'] = array_map(fn($y) => $yearlyProjects[$y] ?? 0, $years);

        return view('admin.users.dashboard', compact(
            'totalUsers',
            'userStatus',
            'totalProjects',
            'totalProjectsStarted',
            'totalProjectsEnded',
            'projectLabels',
            'projectData'
        ));
    }
}
