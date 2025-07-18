<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
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

        // Cek driver database yang digunakan
        $dbDriver = DB::connection()->getDriverName();
        if ($dbDriver === 'sqlite') {
            // SQLite menggunakan strftime('%w', ...), dimana Minggu=0. Tambah 1 agar cocok dengan MySQL.
            $dayOfWeekExpression = "CAST(strftime('%w', start_date) AS INTEGER) + 1";
        } else {
            // MySQL menggunakan DAYOFWEEK(), dimana Minggu=1.
            $dayOfWeekExpression = "DAYOFWEEK(start_date)";
        }

        $weeklyProjects = Project::whereBetween('start_date', [$startOfWeek, $endOfWeek])
            ->selectRaw("{$dayOfWeekExpression} as day, COUNT(*) as total")
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $weekDays = [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];
        $projectLabels['daily'] = array_values($weekDays);
        $projectData['daily'] = array_map(fn($d) => $weeklyProjects[$d] ?? 0, array_keys($weekDays));

        // MONTHLY (Jan–Dec)
        $monthlyProjects = Project::whereYear('start_date', now()->year)
            // Gunakan strftime untuk kompatibilitas SQLite
            ->selectRaw("CAST(strftime('%m', start_date) AS INTEGER) as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $projectLabels['monthly'] = $months;
        $projectData['monthly'] = array_map(fn($i) => $monthlyProjects[$i] ?? 0, range(1, 12));

        // YEARLY (last 5 years)
        $currentYear = now()->year;
        $years = range($currentYear - 4, $currentYear);

        // Ganti YEAR() dengan strftime('%Y', ...) untuk kompatibilitas SQLite
        $yearlyProjects = Project::whereIn(DB::raw("strftime('%Y', start_date)"), array_map('strval', $years))
            ->selectRaw("strftime('%Y', start_date) as year, COUNT(*) as total")
            ->groupBy('year')
            ->pluck('total', 'year')
            ->toArray();

        $projectLabels['yearly'] = array_map('strval', $years);
        $projectData['yearly'] = array_map(fn($y) => $yearlyProjects[$y] ?? 0, $years);

        $dashboardData = [
            'total_users' => $totalUsers,
            'user_status' => $userStatus,
            'total_projects' => $totalProjects,
            'total_projects_started' => $totalProjectsStarted,
            'total_projects_ended' => $totalProjectsEnded,
            'charts' => [
                'projects' => [
                    'daily' => ['labels' => $projectLabels['daily'], 'data' => $projectData['daily']],
                    'monthly' => ['labels' => $projectLabels['monthly'], 'data' => $projectData['monthly']],
                    'yearly' => ['labels' => $projectLabels['yearly'], 'data' => $projectData['yearly']],
                ]
            ]
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);
        }

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
