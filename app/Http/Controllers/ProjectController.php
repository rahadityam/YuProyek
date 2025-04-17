<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Category;
use App\Models\User;
use App\Models\WageStandard;
use App\Models\ActivityLog;
use App\Models\Task; // Tambahkan ini
use App\Models\Payment; // Tambahkan ini
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 
use Illuminate\Support\Collection;

class ProjectController extends Controller
{
    public function dashboard()
    {
        // Get all projects for the global view
        $projects = Project::orderBy('created_at', 'desc')->get();

        $user = Auth::user();
        $userProjects = [];

        // Get user's specific projects based on role
        if ($user->role === 'project_owner') {
            // If user is a project owner, get projects they own
            $userProjects = Project::where('owner_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // If user is a worker, get projects they follow/participate in
            $userProjects = $user->projects()
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('dashboard', compact('projects', 'userProjects'));
    }

    // Menampilkan detail proyek
    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    // Menampilkan daftar proyek
    public function index(Request $request)
{
    // Start with a base query
    $query = Project::query();

    // Search functionality
    if ($request->has('search')) {
        $searchTerm = $request->input('search');
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhereHas('owner', function($subQuery) use ($searchTerm) {
                  $subQuery->where('name', 'like', "%{$searchTerm}%");
              });
        });
    }

    // Filter by status
    if ($request->has('status') && $request->input('status') !== 'all') {
        $query->where('status', $request->input('status'));
    }

    // Filter by category
    if ($request->has('category') && $request->input('category') !== 'all') {
        $query->whereHas('categories', function($q) use ($request) {
            $q->where('categories.id', $request->input('category'));
        });
    }

    // Filter by budget range
    if ($request->has('budget_min')) {
        $query->where('budget', '>=', $request->input('budget_min'));
    }
    if ($request->has('budget_max')) {
        $query->where('budget', '<=', $request->input('budget_max'));
    }

    // Sorting
    $sortField = $request->input('sort', 'created_at');
    $sortDirection = $request->input('direction', 'desc');
    
    // Validate sort field to prevent SQL injection
    $allowedSortFields = ['name', 'budget', 'start_date', 'end_date', 'created_at'];
    $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'created_at';
    $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

    $query->orderBy($sortField, $sortDirection);

    // Paginate results
    $projects = $query->paginate(9)->withQueryString();

    // Get categories for filter dropdown
    $categories = Category::all();

    return view('projects.index', compact('projects', 'categories'));
}

    // Menampilkan form untuk membuat proyek baru
    public function create()
    {
        $categories = Category::all();
        return view('projects.create', compact('categories'));
    }

    // Menyimpan proyek baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric',
            'status' => 'required|string|in:open,in_progress,completed,cancelled',
            'owner_id' => 'required|exists:users,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        // Create the project
        $project = Project::create($request->except('categories'));

        // Attach categories if any are selected
        if ($request->has('categories')) {
            $project->categories()->attach($request->categories);
        }

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dibuat!');
    }

    // Gunakan metode yang sama untuk edit dan update
    public function edit(Project $project)
    {
        $categories = Category::all();
        $selectedCategories = $project->categories->pluck('id')->toArray();
        return view('projects.edit', compact('project', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $project->update($request->except('categories'));

        // Sync categories
        if ($request->has('categories')) {
            $project->categories()->sync($request->categories);
        } else {
            $project->categories()->detach();
        }

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil diperbarui!');
    }

    // Menghapus proyek
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus!');
    }

    // Menampilkan proyek yang diikuti oleh user yang login
    public function myProjects(Request $request)
{
    $user = Auth::user();
    $query = null;
    $isOwner = false;

    // Check if user is a project owner
    if ($user->role === 'project_owner') {
        $query = Project::where('owner_id', $user->id);
        $isOwner = true;
    } else {
        // If user is a worker, get projects they follow/participate in
        $query = $user->projects();
    }

    // Search functionality
    if ($request->has('search')) {
        $searchTerm = $request->input('search');
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    // Filter by status
    if ($request->has('status') && $request->input('status') !== 'all') {
        $query->where('status', $request->input('status'));
    }

    // Sorting
    $sortField = $request->input('sort', 'created_at');
    $sortDirection = $request->input('direction', 'desc');
    
    // Validate sort field to prevent SQL injection
    $allowedSortFields = ['name', 'budget', 'start_date', 'end_date', 'created_at'];
    $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'created_at';
    $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

    $query->orderBy($sortField, $sortDirection);

    // Paginate results
    $projects = $query->paginate(9)->withQueryString();

    return view('projects.my-projects', compact('projects', 'isOwner'));
}

/**
     * Menampilkan dashboard internal spesifik untuk sebuah proyek.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function projectDashboard(Project $project)
    {
        // --- 1. Data Aktivitas ---
        $recentActivities = ActivityLog::where('project_id', $project->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // --- 2. Data Tugas (Statistik & Grafik) ---
        $tasks = $project->tasks()
                      ->with(['assignedUser', 'difficultyLevel', 'priorityLevel']) // Tetap eager load jika perlu di tempat lain
                      ->get();

        // Statistik Tugas Dasar (Tetap Sama)
        $taskStats = [
            'total'       => $tasks->count(),
            'todo'        => $tasks->where('status', 'To Do')->count(),
            'in_progress' => $tasks->where('status', 'In Progress')->count(),
            'review'      => $tasks->where('status', 'Review')->count(),
            'done'        => $tasks->where('status', 'Done')->count(),
        ];

        // Tugas yang sedang dikerjakan (Tetap Sama)
        $inProgressTasks = $tasks->where('status', 'In Progress')->take(5);

        // --- BARU: Data untuk Grafik Tugas per Anggota (Stacked Bar) ---
        $tasksByAssigneeGrouped = $tasks->groupBy('assigned_to');
        $assigneeIds = $tasksByAssigneeGrouped->keys()->filter(fn($id) => !is_null($id) && $id > 0)->values()->all();
        $assignees = User::whereIn('id', $assigneeIds)->pluck('name', 'id');

        $statusOrder = ['To Do', 'In Progress', 'Review', 'Done']; // Urutan status untuk stack
        $statusColors = [ // Warna untuk setiap status
            'To Do'       => '#E5E7EB', // Gray 200
            'In Progress' => '#FCD34D', // Amber 300
            'Review'      => '#93C5FD', // Blue 300
            'Done'        => '#6EE7B7', // Emerald 300
        ];
         $statusBorderColors = [ // Warna border (lebih gelap)
            'To Do'       => '#9CA3AF', // Gray 400
            'In Progress' => '#F59E0B', // Amber 500
            'Review'      => '#3B82F6', // Blue 500
            'Done'        => '#10B981', // Emerald 500
        ];


        $assigneeLabels = collect($assigneeIds)->map(fn($id) => $assignees->get($id, "User ID: {$id}"))->toArray();
        $unassignedTasks = $tasksByAssigneeGrouped->get(null, collect())->merge($tasksByAssigneeGrouped->get(0, collect()));
        $hasUnassigned = $unassignedTasks->isNotEmpty();

        if ($hasUnassigned) {
            $assigneeLabels[] = 'Unassigned'; // Tambah label unassigned jika ada
        }

        $datasets = [];
        foreach ($statusOrder as $status) {
            $dataCounts = [];
            // Hitung untuk setiap assignee yang terdaftar
            foreach ($assigneeIds as $id) {
                $dataCounts[] = optional($tasksByAssigneeGrouped->get($id))->where('status', $status)->count() ?? 0;
            }
            // Hitung untuk unassigned jika ada
            if ($hasUnassigned) {
                $dataCounts[] = $unassignedTasks->where('status', $status)->count();
            }

            $datasets[] = [
                'label' => $status,
                'data' => $dataCounts,
                'backgroundColor' => $statusColors[$status],
                 'borderColor' => $statusBorderColors[$status], // Tambahkan border color
                 'borderWidth' => 1 // Tambahkan border width
            ];
        }

        $tasksByAssigneeStatusChartData = [
            'labels' => $assigneeLabels,
            'datasets' => $datasets,
        ];
        // --- Akhir Data Grafik Baru ---


        // --- 3. Data Tim --- (Tetap Sama)
        $acceptedWorkers = $project->workers()
            ->wherePivot('status', 'accepted')
            ->get();

        // --- 4. Data Finansial (Statistik & Grafik) --- (Tetap Sama)
        $budget = $project->budget ?? 0;
        $allDoneTasks = Task::where('project_id', $project->id)
                         ->where('status', 'Done')
                         ->with(['difficultyLevel', 'priorityLevel', 'projectUserMembership.wageStandard', 'project'])
                         ->get();
        $totalTaskHakGaji = $allDoneTasks->sum('calculated_value');
        $totalOtherHakGaji = Payment::where('project_id', $project->id)
                                 ->where('payment_type', 'other')->sum('amount');
        $totalPaidTask = Payment::where('project_id', $project->id)
                                ->where('payment_type', 'task')->where('status', 'completed')->sum('amount');
        $totalPaidOther = Payment::where('project_id', $project->id)
                                 ->where('payment_type', 'other')->where('status', 'completed')->sum('amount');
        $totalHakGaji = $totalTaskHakGaji + $totalOtherHakGaji;
        $totalPaid = $totalPaidTask + $totalPaidOther;
        $remainingUnpaid = max(0, $totalHakGaji - $totalPaid);
        $budgetDifference = $budget - $totalHakGaji;
        $financialStats = [
            'budget' => $budget,
            'totalTaskHakGaji' => $totalTaskHakGaji,
            'totalOtherHakGaji' => $totalOtherHakGaji,
            'totalHakGaji' => $totalHakGaji,
            'totalPaidTask' => $totalPaidTask,
            'totalPaidOther' => $totalPaidOther,
            'totalPaid' => $totalPaid,
            'remainingUnpaid' => $remainingUnpaid,
            'budgetDifference' => $budgetDifference,
            'overviewChartData' => [
                'paidTask' => $totalPaidTask, 'paidOther' => $totalPaidOther, 'remainingUnpaid' => $remainingUnpaid,
            ],
            'spendingVsBudgetChartData' => [
                'budget' => $budget, 'hakGaji' => $totalHakGaji, 'paid' => $totalPaid,
            ]
        ];

        // --- 5. Kirim Data ke View ---
        return view('projects.dashboard', compact(
            'project',
            'taskStats',
            'tasksByAssigneeStatusChartData', // Ganti dengan data chart baru
            'inProgressTasks',
            'acceptedWorkers',
            'recentActivities',
            'financialStats'
        ));
    }

    /**
     * Display team members and applicants for a project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function teamMembers(Project $project)
{
    // Check if user is authorized to view team members
    // $this->authorize('view', $project);

    // Get the project owner
    $owner = $project->owner;

    // Get active project members (accepted status)
    $members = $project->workers()
        ->wherePivot('status', 'accepted')
        ->withPivot('position', 'wage_standard_id')
        ->get();

    // Get applicants (applied status)
    $applicants = $project->workers()
        ->wherePivot('status', 'applied')
        ->withPivot('position')
        ->get();
        
    // Get wage standards for this project
    $wageStandards = $project->wageStandards()->orderBy('job_category')->get();

    return view('projects.team', compact('project', 'owner', 'members', 'applicants', 'wageStandards'));
}

/**
 * Update team member's wage standard
 */
/**
 * Update team member's wage standard
 */
public function updateMemberWage(Request $request, Project $project, User $user)
{
    $request->validate([
        'wage_standard_id' => 'required|exists:wage_standards,id',
    ]);
    
    // Check if the wage standard belongs to this project
    $wageStandard = WageStandard::findOrFail($request->wage_standard_id);
    if ($wageStandard->project_id !== $project->id) {
        return response()->json(['error' => 'The selected wage standard does not belong to this project.'], 400);
    }
    
    // Update the wage standard for this team member
    $project->workers()->updateExistingPivot($user->id, [
        'wage_standard_id' => $request->wage_standard_id,
    ]);
    
    // If this is an AJAX request, return JSON response
    if ($request->ajax()) {
        return response()->json(['success' => true, 'message' => 'Wage standard updated successfully.']);
    }
    
    // For non-AJAX requests, redirect back with success message
    return back()->with('success', 'Wage standard updated successfully.');
}
}