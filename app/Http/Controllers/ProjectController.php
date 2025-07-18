<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Category;
use App\Models\User;
use App\Models\WageStandard;
use App\Models\PaymentTerm;
use App\Models\ProjectPosition;
use App\Models\ActivityLog;
use App\Models\Task; // Tambahkan ini
use App\Models\Payment; // Tambahkan ini
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; 
use Illuminate\Support\Collection;
use Carbon\Carbon;

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

    if ($request->wantsJson() || $request->ajax()) {
        // Untuk API, kita juga bisa menggunakan paginasi
        $projects = $query->paginate($request->input('per_page', 15));
        
        return response()->json($projects);
    }
    

    return view('projects.index', compact('projects', 'categories'));
}

    // Menampilkan form untuk membuat proyek baru
    public function create()
    {
        // $categories = Category::all(); // Tidak diperlukan lagi di sini
        // return view('projects.create', compact('categories'));
        // Jika halaman create.blade.php dihapus, redirect saja:
        return redirect()->route('projects.my-projects')->with('info', 'Gunakan tombol "Buat Proyek Baru" untuk membuat proyek.');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0',
            
            'wip_limits' => 'nullable|integer|min:1',
            'difficulty_weight' => 'nullable|integer|min:0|max:100',
            'priority_weight' => 'nullable|integer|min:0|max:100',
            
            // Ubah aturan validasi di sini
            'payment_calculation_type' => ['required', Rule::in(['termin', 'full'])], // Hanya 'termin' dan 'full'
            
            // Validasi payment_terms tetap sama, hanya relevan jika tipe adalah 'termin'
            'payment_terms' => Rule::requiredIf(fn () => $request->input('payment_calculation_type') === 'termin') . '|array',
            // Jika payment_calculation_type adalah 'termin' dan payment_terms kosong, validasi 'min:1' akan gagal.
            // Jadi, tambahkan min:1 hanya jika payment_calculation_type adalah 'termin'
            // Ini bisa dilakukan dengan callback validasi kustom atau membiarkan 'requiredIf' dan validasi array 'min' bekerja bersama.
            // Untuk menyederhanakan, kita pastikan 'payment_terms' hanya dikirim jika tipenya 'termin'.
            // Di frontend, kita sudah mengosongkan payment_terms jika tipe bukan termin.
            'payment_terms.*.name' => 'required_with:payment_terms|string|max:255',
            'payment_terms.*.start_date' => 'required_with:payment_terms|date',
            'payment_terms.*.end_date' => 'required_with:payment_terms|date|after_or_equal:payment_terms.*.start_date',
            
            'positions' => 'nullable|array',
            'positions.*.name' => 'required_with:positions|string|max:255',
            'positions.*.count' => 'required_with:positions|integer|min:1',
        ], [
            'payment_terms.required' => 'Jika metode pembayaran adalah Termin, minimal satu termin harus didefinisikan.',
            // 'payment_terms.min' => 'Jika metode pembayaran adalah Termin, minimal satu termin harus didefinisikan.', // Pesan ini mungkin tidak diperlukan lagi jika requiredIf sudah cukup
            'payment_terms.*.name.required_with' => 'Nama termin wajib diisi untuk setiap termin yang ditambahkan.',
            'payment_terms.*.start_date.required_with' => 'Tanggal mulai termin wajib diisi.',
            'payment_terms.*.end_date.required_with' => 'Tanggal akhir termin wajib diisi.',
            'payment_terms.*.end_date.after_or_equal' => 'Tanggal akhir termin harus setelah atau sama dengan tanggal mulai.',
            'positions.*.name.required_with' => 'Nama posisi wajib diisi jika menambahkan data posisi.',
            'positions.*.count.required_with' => 'Jumlah untuk posisi wajib diisi dan minimal 1.',
        ]);

        // Validasi total bobot WSM
        $difficultyWeight = $request->input('difficulty_weight', 65); // Default jika null
        $priorityWeight = $request->input('priority_weight', 35);   // Default jika null

        if (($difficultyWeight + $priorityWeight) > 100) {
             if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => ['weights_total' => ['Total bobot Kesulitan dan Prioritas tidak boleh lebih dari 100%.']]
                ], 422);
            }
            return back()->withErrors(['weights_total' => 'Total bobot Kesulitan dan Prioritas tidak boleh lebih dari 100%.'])->withInput();
        }
        if (($difficultyWeight + $priorityWeight) < 100 && ($difficultyWeight !=0 || $priorityWeight !=0) ) { // Hanya jika salah satu atau keduanya tidak 0
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => ['weights_total' => ['Total bobot Kesulitan dan Prioritas harus 100% jika salah satu atau keduanya diisi.']]
                ], 422);
            }
            return back()->withErrors(['weights_total' => 'Total bobot Kesulitan dan Prioritas harus 100% jika salah satu atau keduanya diisi.'])->withInput();
        }


        DB::beginTransaction();
        try {
            $projectData = [
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'budget' => $validatedData['budget'],
                'status' => 'open',
                'owner_id' => Auth::id(),
                'wip_limits' => $validatedData['wip_limits'],
                'difficulty_weight' => $request->input('difficulty_weight', 65),
                'priority_weight' => $request->input('priority_weight', 35),
                'payment_calculation_type' => $validatedData['payment_calculation_type'],
            ];

            $project = Project::create($projectData);

            // Simpan termin hanya jika tipe adalah 'termin' dan ada data termin
            if ($project->payment_calculation_type === 'termin' && !empty($validatedData['payment_terms'])) {
                foreach ($validatedData['payment_terms'] as $termData) {
                    $project->paymentTerms()->create($termData);
                }
            }

            // Simpan posisi pekerja jika ada (asumsi Anda punya model ProjectPosition)
            if (!empty($validatedData['positions'])) {
                foreach ($validatedData['positions'] as $positionData) {
                    // Pastikan model ProjectPosition ada dan relasinya sudah di-setup di Project.php
                    $project->projectPositions()->create($positionData);
                    // Contoh jika belum ada model ProjectPosition, bisa disimpan sebagai JSON di project (tidak ideal untuk query)
                    // $project->update(['job_positions_meta' => json_encode($validatedData['positions'])]); // Kurang disarankan
                    // Log::info('Data posisi untuk proyek baru:', $positionData); // Sementara log dulu
                }
            }
            // Kategori tidak di-handle di sini lagi

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Proyek berhasil dibuat!',
                    'project' => $project->load('owner')
                ], 201);
            }

            return redirect()->route('projects.my-projects')->with('success', 'Proyek berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating project: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan server saat membuat proyek. Silakan coba lagi.',
                    'errors' => ['general' => $e->getMessage()] // Kirim pesan error general
                ], 500);
            }
            return back()->with('error', 'Gagal membuat proyek: ' . $e->getMessage())->withInput();
        }
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

        if ($request->wantsJson()) { // <--- Header 'Accept: application/json' akan membuat ini TRUE
    return response()->json([
        'success' => true,
        'message' => 'Proyek berhasil diperbarui!',
        'project' => $project->fresh()
    ]);
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
            // ==========================================================
            // ===== MODIFIKASI DIMULAI DI SINI =====
            // ==========================================================
            // If user is a worker, get projects where their status is 'accepted'
            $query = $user->projects()->wherePivot('status', 'accepted');
            // ==========================================================
            // ===== AKHIR MODIFIKASI =====
            // ==========================================================
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
        
        $allowedSortFields = ['name', 'budget', 'start_date', 'end_date', 'created_at'];
        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'created_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        $query->orderBy($sortField, $sortDirection);

        // Jika request datang dari API, kembalikan JSON
        if ($request->wantsJson()) {
            $projects = $query->paginate($request->input('per_page', 15))->withQueryString();
            return response()->json($projects);
        }

        // Paginate results
        $projects = $query->paginate(9)->withQueryString();

        return view('projects.my-projects', compact('projects', 'isOwner'));
    }

    public function projectDashboard(Project $project, Request $request)
    {
        $currentUser = Auth::user();
        $isOwner = $currentUser->isProjectOwner($project);

        // --- 1. Data Aktivitas (Filter untuk PW) ---
        $activityQuery = ActivityLog::where('project_id', $project->id)->with('user');
        if (!$isOwner) {
            // PW hanya melihat aktivitas yang relevan dengannya
            $activityQuery->where(function($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id) // Aktivitas yang dia lakukan
                    ->orWhereHasMorph('loggable', [Task::class], function ($query) use ($currentUser) {
                        // Aktivitas pada task yang di-assign ke dia
                        $query->where('assigned_to', $currentUser->id);
                    });
            });
        }
        $recentActivities = $activityQuery->orderBy('created_at', 'desc')->take(5)->get();

        // --- 2. Data Tugas (Filter untuk PW) ---
        $taskQuery = $project->tasks()->with(['assignedUser', 'difficultyLevel', 'priorityLevel']);
        if (!$isOwner) {
            // PW hanya melihat tugas yang di-assign ke dia
            $taskQuery->where('assigned_to', $currentUser->id);
        }
        $allProjectTasks = $taskQuery->get();

        // Statistik Tugas akan secara otomatis terfilter berdasarkan $allProjectTasks
        $taskStats = [
            'total'       => $allProjectTasks->count(),
            'todo'        => $allProjectTasks->where('status', 'To Do')->count(),
            'in_progress' => $allProjectTasks->where('status', 'In Progress')->count(),
            'review'      => $allProjectTasks->where('status', 'Review')->count(),
            'done'        => $allProjectTasks->where('status', 'Done')->count(),
        ];

        // Kartu "Tugas Sedang Dikerjakan" & "Rekap Selesai" akan terfilter otomatis
        $inProgressTasksLimit = 4;
        $inProgressTasks = $allProjectTasks->where('status', 'In Progress')->sortByDesc('updated_at')->take($inProgressTasksLimit);
        $completedTasksForCalcLimit = 4;
        $completedTasksForCalc = $allProjectTasks->where('status', 'Done')->sortByDesc(fn ($task) => $task->end_time ?? $task->updated_at)->take($completedTasksForCalcLimit);

        // --- Grafik Status Tugas (Pie Chart) ---
        // Data untuk chart ini sudah terfilter karena berasal dari $taskStats
        $taskStatusChartData = [
            'labels' => ['Todo', 'In Progress', 'Review', 'Done'],
            'data' => [
                $taskStats['todo'],
                $taskStats['in_progress'],
                $taskStats['review'],
                $taskStats['done']
            ],
            'colors' => ['#E5E7EB', '#FCD34D', '#93C5FD', '#6EE7B7'],
            'borderColors' => ['#9CA3AF', '#F59E0B', '#3B82F6', '#10B981']
        ];

        // --- Grafik Progres Tugas Harian (Line Chart) ---
        $period = $request->input('progress_period', '7days');
        $startDate = match ($period) {
            '30days' => now()->subDays(29)->startOfDay(),
            'all' => $project->start_date?->startOfDay() ?? now()->subDays(6)->startOfDay(),
            default => now()->subDays(6)->startOfDay(),
        };
        $endDate = now()->endOfDay();

        $completedTasksQuery = Task::query()
            ->where('project_id', $project->id)
            ->where('status', 'Done')
            ->whereBetween('updated_at', [$startDate, $endDate]);

        if (!$isOwner) {
            $completedTasksQuery->where('assigned_to', $currentUser->id);
        }

        $completedTasksForChart = $completedTasksQuery
            ->select('assigned_to', DB::raw('DATE(updated_at) as completion_date'), DB::raw('count(*) as total'))
            ->groupBy('assigned_to', 'completion_date')
            ->orderBy('completion_date', 'asc')
            ->get();
        
        $workerIds = $isOwner ? $completedTasksForChart->pluck('assigned_to')->unique()->filter() : collect([$currentUser->id]);
        $workers = User::whereIn('id', $workerIds)->pluck('name', 'id');
        
        $progressChartData = ['labels' => [], 'datasets' => []];
        $dateRange = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->addDay());
        foreach ($dateRange as $date) {
            $progressChartData['labels'][] = $date->format('d M');
        }

        $workerColors = ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#6366F1', '#22C55E', '#FBBF24', '#F87171'];
        $colorIndex = 0;

        foreach ($workers as $workerId => $workerName) {
            $dataPoints = [];
            foreach ($dateRange as $date) {
                $formattedDate = $date->format('Y-m-d');
                $taskCount = $completedTasksForChart
                    ->where('assigned_to', $workerId)
                    ->where('completion_date', $formattedDate)
                    ->first()?->total ?? 0;
                $dataPoints[] = $taskCount;
            }

            $color = $workerColors[$colorIndex % count($workerColors)];
            $progressChartData['datasets'][] = [
                'label' => $workerName,
                'data' => $dataPoints,
                'borderColor' => $color,
                'backgroundColor' => $color . '33',
                'fill' => false,
                'tension' => 0.1
            ];
            $colorIndex++;
        }

        // --- 3. Data Tim ---
        $acceptedWorkers = $project->workers()->wherePivot('status', 'accepted')->get();

        // --- 4. Data Finansial (Filter untuk PW) ---
        $paymentsForStatsQuery = Payment::where('project_id', $project->id);
        if (!$isOwner) {
            $paymentsForStatsQuery->where('user_id', $currentUser->id);
        }
        $paymentsForStats = $paymentsForStatsQuery->get();

        $totalTaskHakGaji = $allProjectTasks->where('status', 'Done')->sum('calculated_value');
        $totalOtherFullHakGaji = $paymentsForStats->whereIn('payment_type', ['other', 'full'])->sum('amount');
        $totalPaidTaskTermin = $paymentsForStats->whereIn('payment_type', ['task', 'termin'])->where('status', Payment::STATUS_APPROVED)->sum('amount') ?? 0;
        $totalPaidOtherFull = $paymentsForStats->whereIn('payment_type', ['other', 'full'])->where('status', Payment::STATUS_APPROVED)->sum('amount') ?? 0;
        $totalPaid = $totalPaidTaskTermin + $totalPaidOtherFull;

        // Hitung data spesifik PM hanya jika dia adalah owner
        $budgetDifference = 0;
        if ($isOwner) {
            $totalOverallProjectEstimatedPayroll = Task::where('project_id', $project->id)->where('status', 'Done')->get()->sum('calculated_value') + Payment::where('project_id', $project->id)->whereIn('payment_type', ['other', 'full'])->sum('amount');
            $budgetDifference = ($project->budget ?? 0) - $totalOverallProjectEstimatedPayroll;
        }

        $financialStats = [
            'budget' => $project->budget ?? 0, // Tetap dikirim untuk chart
            'totalTaskHakGaji' => $totalTaskHakGaji,
            'totalOtherFullHakGaji' => $totalOtherFullHakGaji,
            'totalHakGaji' => $totalTaskHakGaji + $totalOtherFullHakGaji,
            'totalPaid' => $totalPaid,
            'remainingUnpaid' => max(0, ($totalTaskHakGaji + $totalOtherFullHakGaji) - $totalPaid),
            'budgetDifference' => $budgetDifference, // Nilainya 0 untuk PW
            
            'overviewChartData' => [
                'paidTaskTermin' => $totalPaidTaskTermin,
                'paidOtherFull' => $totalPaidOtherFull,
                'remainingUnpaid' => max(0, $totalTaskHakGaji - $totalPaidTaskTermin),
            ],
            
            'spendingVsBudgetChartData' => [
                'budget' => $project->budget ?? 0,
                'hakGaji' => $isOwner ? $totalOverallProjectEstimatedPayroll : ($totalTaskHakGaji + $totalOtherFullHakGaji),
                'paid' => $isOwner ? Payment::where('project_id', $project->id)->where('status', Payment::STATUS_APPROVED)->sum('amount') : $totalPaid,
                'isOwnerView' => $isOwner,
            ]
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'project' => $project->only('id', 'name', 'status', 'start_date', 'end_date'),
                    'is_owner' => $isOwner,
                    'stats' => [
                        'tasks' => $taskStats,
                        'financial' => $financialStats,
                    ],
                    'charts' => [
                        'task_status' => $taskStatusChartData,
                        'task_progress' => $progressChartData,
                        // Anda bisa tambahkan data chart finansial di sini jika perlu
                    ],
                    'lists' => [
                        'recent_activities' => $recentActivities,
                        'in_progress_tasks' => $inProgressTasks,
                        'team_members' => $acceptedWorkers,
                    ]
                ]
            ]);
        }

        // --- 5. Kirim Data ke View ---
        return view('projects.dashboard', compact(
            'project',
            'taskStats',
            'taskStatusChartData',
            'progressChartData',
            'inProgressTasks',
            'inProgressTasksLimit',
            'completedTasksForCalc',
            'completedTasksForCalcLimit',
            'acceptedWorkers',
            'recentActivities',
            'financialStats',
            'isOwner'
        ));
    }

    /**
     * Display team members and applicants for a project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function teamMembers(Request $request, Project $project)
{
    $this->authorize('view', $project); // Pastikan ada policy untuk view project team

    $owner = $project->owner;

    // Anggota tim yang sudah diterima
    $members = $project->workers()
        ->wherePivot('status', 'accepted')
        ->withPivot('position', 'wage_standard_id') // Sertakan wage_standard_id
        ->get();

    // Undangan yang tertunda (status 'invited')
    $pendingInvitations = $project->workers()
        ->wherePivot('status', 'invited') // Ganti 'applied' menjadi 'invited'
        ->withPivot('position') // Asumsi 'position' juga diisi saat invite
        ->get();

    if ($request->wantsJson()) {
            return response()->json([
                'owner' => $owner,
                'members' => $members,
                'pending_invitations' => $pendingInvitations
            ]);
        }

    $wageStandards = $project->wageStandards()->orderBy('job_category')->get();

    return view('projects.team', compact('project', 'owner', 'members', 'pendingInvitations', 'wageStandards'));
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