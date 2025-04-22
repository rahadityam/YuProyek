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
                      ->with(['assignedUser', 'difficultyLevel', 'priorityLevel'])
                      ->get();

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
        foreach ($statusOrder as $status) {
            $dataCounts = [];
            foreach ($assigneeIds as $id) $dataCounts[] = optional($tasksByAssigneeGrouped->get($id))->where('status', $status)->count() ?? 0;
            if ($hasUnassigned) $dataCounts[] = $unassignedTasks->where('status', $status)->count();
            $datasets[] = [ 'label' => $status, 'data' => $dataCounts, 'backgroundColor' => $statusColors[array_search($status, $statusOrder)], 'borderColor' => $statusBorderColors[array_search($status, $statusOrder)], 'borderWidth' => 1 ];
        }
        $tasksByAssigneeStatusChartData = [ 'labels' => $assigneeLabels, 'datasets' => $datasets ];

        // --- 3. Data Tim ---
        $acceptedWorkers = $project->workers()->wherePivot('status', 'accepted')->get();

        // --- 4. Data Finansial (Statistik & Grafik) --- (REVISI PERHITUNGAN PAID)
        $budget = $project->budget ?? 0;

        // Hak Gaji Task (Total nilai semua task 'Done', terlepas sudah dibayar/belum)
        $allDoneTasks = Task::where('project_id', $project->id)
                            ->where('status', 'Done')
                            ->with(['difficultyLevel', 'priorityLevel', 'projectUserMembership.wageStandard', 'project']) // Eager load needed for calculation
                            ->get();
        $totalTaskHakGaji = $allDoneTasks->sum('calculated_value'); // Use accessor

        // Hak Gaji Other/Full (Total amount semua payment tipe 'other'/'full', terlepas sudah dibayar/belum)
        // Ini dihitung dari tabel Payment, bukan Task
        $totalOtherFullHakGaji = Payment::where('project_id', $project->id)
                                 ->whereIn('payment_type', ['other', 'full']) // Include 'full'
                                 ->sum('amount');

        // Total Dibayar Task/Termin (Hanya yang statusnya 'approved')
        $totalPaidTaskTermin = Payment::where('project_id', $project->id)
                                ->whereIn('payment_type', ['task', 'termin']) // Include 'termin'
                                ->where('status', Payment::STATUS_APPROVED) // <-- FIX: Gunakan status 'approved'
                                ->sum('amount');

        // Total Dibayar Other/Full (Hanya yang statusnya 'approved')
        $totalPaidOtherFull = Payment::where('project_id', $project->id)
                                 ->whereIn('payment_type', ['other', 'full']) // Include 'full'
                                 ->where('status', Payment::STATUS_APPROVED) // <-- FIX: Gunakan status 'approved'
                                 ->sum('amount');

        // Kalkulasi Total Keseluruhan
        $totalHakGaji = $totalTaskHakGaji + $totalOtherFullHakGaji; // Total estimasi gaji
        $totalPaid = $totalPaidTaskTermin + $totalPaidOtherFull;     // Total yang *benar-benar* sudah dibayar (approved)
        $remainingUnpaid = max(0, $totalHakGaji - $totalPaid);      // Sisa yang belum dibayar (estimasi)
        $budgetDifference = $budget - $totalHakGaji;                // Selisih budget vs *estimasi* total gaji

        $financialStats = [
            'budget' => $budget,
            'totalTaskHakGaji' => $totalTaskHakGaji,
            'totalOtherFullHakGaji' => $totalOtherFullHakGaji, // Ganti nama variabel agar jelas
            'totalHakGaji' => $totalHakGaji,
            'totalPaidTaskTermin' => $totalPaidTaskTermin,     // Ganti nama variabel agar jelas
            'totalPaidOtherFull' => $totalPaidOtherFull,     // Ganti nama variabel agar jelas
            'totalPaid' => $totalPaid, // Ini total semua yang sudah approved
            'remainingUnpaid' => $remainingUnpaid,
            'budgetDifference' => $budgetDifference,
            // --- REVISI: Sesuaikan data untuk chart ---
            'overviewChartData' => [
                'paidTaskTermin' => $totalPaidTaskTermin,   // Data task/termin yg sudah dibayar
                'paidOtherFull' => $totalPaidOtherFull,    // Data other/full yg sudah dibayar
                'remainingUnpaid' => $remainingUnpaid,     // Sisa estimasi
            ],
            'spendingVsBudgetChartData' => [
                'budget' => $budget,
                'hakGaji' => $totalHakGaji, // Total estimasi gaji
                'paid' => $totalPaid,       // Total yang sudah dibayar (approved)
            ]
        ];
        // --- END REVISI PERHITUNGAN PAID ---


        // --- 5. Kirim Data ke View ---
        return view('projects.dashboard', compact(
            'project',
            'taskStats',
            'tasksByAssigneeStatusChartData',
            'inProgressTasks',
            'acceptedWorkers',
            'recentActivities',
            'financialStats' // Kirim data finansial yang sudah diperbaiki
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