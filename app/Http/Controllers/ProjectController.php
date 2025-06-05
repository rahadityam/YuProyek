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
            ->take(5) // Ambil 5 aktivitas terakhir
            ->get();

        // --- 2. Data Tugas (Statistik & Grafik) ---
        $allProjectTasks = $project->tasks()
                                ->with(['assignedUser', 'difficultyLevel', 'priorityLevel']) // Eager load relasi yang dibutuhkan
                                ->get();

        $taskStats = [
            'total'       => $allProjectTasks->count(),
            'todo'        => $allProjectTasks->where('status', 'To Do')->count(),
            'in_progress' => $allProjectTasks->where('status', 'In Progress')->count(),
            'review'      => $allProjectTasks->where('status', 'Review')->count(),
            'done'        => $allProjectTasks->where('status', 'Done')->count(),
        ];

        // Untuk card "Tugas Sedang Dikerjakan" di tab Ringkasan Tugas
        $inProgressTasksLimit = 4; // Batas tampilan item
        $inProgressTasks = $allProjectTasks
            ->where('status', 'In Progress')
            ->sortByDesc('updated_at') // Tampilkan yang terbaru diupdate
            ->take($inProgressTasksLimit);

        // Untuk card "Rekap Task Selesai (Kalkulasi)" di tab Ringkasan Keuangan
        $completedTasksForCalcLimit = 4; // Batas tampilan item
        $completedTasksForCalc = $allProjectTasks
            ->where('status', 'Done')
            // Tambahkan eager load untuk 'assignedUser' jika belum ada di $allProjectTasks dan dibutuhkan di view
            // ->loadMissing('assignedUser') // Contoh jika assignedUser tidak selalu di-load
            ->sortByDesc(function ($task) { // Urutkan berdasarkan tanggal selesai, fallback ke updated_at
                return $task->end_date ?? $task->updated_at;
            })
            ->take($completedTasksForCalcLimit);
        // Pastikan $task->calculated_value ada dan bisa diakses di view

        // Data untuk Grafik Tugas per Anggota (Stacked Bar) - tidak diubah
        $tasksByAssigneeGrouped = $allProjectTasks->groupBy('assigned_to');
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
            if ($hasUnassigned) $dataCounts[] = $unassignedTasks->where('status', $status)->count(); // Perbaikan: 'status', $status
            $datasets[] = [ 'label' => $status, 'data' => $dataCounts, 'backgroundColor' => $statusColors[array_search($status, $statusOrder)], 'borderColor' => $statusBorderColors[array_search($status, $statusOrder)], 'borderWidth' => 1 ];
        }
        $tasksByAssigneeStatusChartData = [ 'labels' => $assigneeLabels, 'datasets' => $datasets ];

        // --- 3. Data Tim --- (tidak diubah)
        $acceptedWorkers = $project->workers()->wherePivot('status', 'accepted')->get();

        // --- 4. Data Finansial (Statistik & Grafik) --- (tidak diubah)
        $budget = $project->budget ?? 0;
        $allDoneTasksValue = $allProjectTasks->where('status', 'Done')->sum('calculated_value'); // Gunakan data yang sudah ada

        $totalTaskHakGaji = $allDoneTasksValue; // Sudah dihitung dari $allProjectTasks

        $totalOtherFullHakGaji = Payment::where('project_id', $project->id)
                                 ->whereIn('payment_type', ['other', 'full'])
                                 ->sum('amount');
        $totalPaidTaskTermin = Payment::where('project_id', $project->id)
                                ->whereIn('payment_type', ['task', 'termin'])
                                ->where('status', Payment::STATUS_APPROVED)
                                ->sum('amount');
        $totalPaidOtherFull = Payment::where('project_id', $project->id)
                                 ->whereIn('payment_type', ['other', 'full'])
                                 ->where('status', Payment::STATUS_APPROVED)
                                 ->sum('amount');
        $totalHakGaji = $totalTaskHakGaji + $totalOtherFullHakGaji;
        $totalPaid = $totalPaidTaskTermin + $totalPaidOtherFull;
        $remainingUnpaid = max(0, $totalHakGaji - $totalPaid);
        $budgetDifference = $budget - $totalHakGaji;

        $financialStats = [
            'budget' => $budget,
            'totalTaskHakGaji' => $totalTaskHakGaji,
            'totalOtherFullHakGaji' => $totalOtherFullHakGaji,
            'totalHakGaji' => $totalHakGaji,
            'totalPaidTaskTermin' => $totalPaidTaskTermin,
            'totalPaidOtherFull' => $totalPaidOtherFull,
            'totalPaid' => $totalPaid,
            'remainingUnpaid' => $remainingUnpaid,
            'budgetDifference' => $budgetDifference,
            'overviewChartData' => [
                'paidTaskTermin' => $totalPaidTaskTermin,
                'paidOtherFull' => $totalPaidOtherFull,
                'remainingUnpaid' => $remainingUnpaid,
            ],
            'spendingVsBudgetChartData' => [
                'budget' => $budget,
                'hakGaji' => $totalHakGaji,
                'paid' => $totalPaid,
            ]
        ];

        // --- 5. Kirim Data ke View ---
        return view('projects.dashboard', compact(
            'project',
            'taskStats',
            'tasksByAssigneeStatusChartData',
            'inProgressTasks',                  // Untuk Tab Tugas
            'inProgressTasksLimit',             // Untuk Tab Tugas
            'completedTasksForCalc',            // Untuk Tab Keuangan
            'completedTasksForCalcLimit',       // Untuk Tab Keuangan
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