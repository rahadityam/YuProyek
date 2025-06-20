<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder; // Pastikan ini di-import

class ActivityController extends Controller
{
    /**
     * Display the activity log for a project.
     * Handles both initial page load and subsequent AJAX requests for filtering and sorting.
     */
    public function index(Project $project, Request $request)
    {
        $this->authorize('view', $project); // Pastikan ada policy yang sesuai

        $query = ActivityLog::where('project_id', $project->id)
                            ->with('user'); // Eager load user

        // --- Filtering Logic ---
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function($subq) use ($searchTerm){
                      $subq->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // --- Sorting Logic ---
        $sortField = $request->input('sort_by', 'created_at'); // Ganti ke sort_by
        $sortDirection = $request->input('sort_dir', 'desc'); // Ganti ke sort_dir

        // Daftar field yang aman untuk disortir
        $allowedSorts = [
            'action' => 'action',
            'created_at' => 'created_at'
        ];

        if (array_key_exists($sortField, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sortField], $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc'); // Default sort
        }

        $logs = $query->paginate(20)->withQueryString();

        // --- Respons AJAX ---
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('projects.partials._activity_log_table', compact('logs'))->render(),
                'pagination_html' => $logs->links('vendor.pagination.tailwind')->toHtml(),
            ]);
        }

        // --- Respons Halaman Biasa ---
        $projectUsers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get();
        $projectUsers->prepend($project->owner);

        $availableActions = ActivityLog::where('project_id', $project->id)->distinct()->pluck('action');

        return view('projects.activity', [
            'project' => $project,
            'logs' => $logs,
            'projectUsers' => $projectUsers,
            'availableActions' => $availableActions,
            'request' => $request
        ]);
    }
}