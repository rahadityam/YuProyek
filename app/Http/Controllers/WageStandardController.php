<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Payment;
use App\Models\WageStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WageStandardController extends Controller
{
    /**
     * Display a listing of the wage standards for a project.
     * Method ini mungkin tidak akan diakses langsung lagi jika semua via settings page,
     * tapi biarkan untuk referensi atau akses langsung jika diperlukan.
     */
    public function index(Project $project)
    {
        // Autorisasi: Pastikan user yang login adalah pemilik proyek
        $this->authorize('update', $project); // Menggunakan policy project untuk update settings

        $wageStandards = WageStandard::where('project_id', $project->id)
                                    ->orderBy('job_category')
                                    ->paginate(10); // Atau get() jika tidak butuh paginasi di halaman terpisah

        // Jika view ini masih dipakai (misal untuk non-JS fallback), biarkan.
        // Jika tidak, bisa dihapus atau redirect ke halaman pengaturan.
        // return view('wage-standards.index', compact('project', 'wageStandards'));

        // Redirect ke halaman pengaturan proyek, tab finansial
        return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                         ->with('info', 'Kelola standar upah melalui halaman pengaturan proyek.');
    }

    /**
     * Show the form for creating a new wage standard.
     * Tidak dipanggil lagi jika menggunakan modal di halaman settings.
     */
    // public function create(Project $project)
    // {
    //     $this->authorize('update', $project);
    //     return view('wage-standards.create', compact('project'));
    // }

    /**
     * Store a newly created wage standard in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        // --- Tambahkan pengecekan kunci proyek saat membuat ---
        $isLocked = Payment::where('project_id', $project->id)->where('status', Payment::STATUS_APPROVED)->exists();
        if ($isLocked) {
            $message = 'Tidak dapat menambah standar upah baru karena proyek sudah terkunci (ada pembayaran yang disetujui).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                            ->withErrors(['general_error' => $message]);
        }
        // --- Akhir Pengecekan ---

        $validated = $request->validate([
            'job_category' => [
                'required', 'string', 'max:255',
                Rule::unique('wage_standards')->where(function ($query) use ($project) {
                    return $query->where('project_id', $project->id);
                }),
            ],
            'task_price' => 'required|numeric|min:0',
        ]);

        $validated['project_id'] = $project->id;
        $wageStandard = WageStandard::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Standar upah berhasil ditambahkan.',
                'wageStandard' => $wageStandard
            ], 201);
        }

        return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                        ->with('success_wage_standards', 'Standar upah berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, WageStandard $wageStandard)
    {
        $this->authorize('update', $project);
        if ($wageStandard->project_id !== $project->id) { abort(404); }

        // --- Tambahkan pengecekan kunci proyek saat update ---
        $isLocked = Payment::where('project_id', $project->id)->where('status', Payment::STATUS_APPROVED)->exists();
        if ($isLocked) {
             $message = 'Tidak dapat mengubah standar upah karena proyek sudah terkunci.';
             if ($request->wantsJson() || $request->ajax()) {
                 return response()->json(['success' => false, 'message' => $message], 403);
             }
             return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                             ->withErrors(['general_error' => $message]);
        }
        // --- Akhir Pengecekan ---

        $validated = $request->validate([
            'job_category' => [
                'required', 'string', 'max:255',
                Rule::unique('wage_standards')->where(function ($query) use ($project) {
                    return $query->where('project_id', $project->id);
                })->ignore($wageStandard->id),
            ],
            'task_price' => 'required|numeric|min:0',
        ]);

        $wageStandard->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Standar upah berhasil diperbarui.',
                'wageStandard' => $wageStandard->fresh()
            ]);
        }

        return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                        ->with('success_wage_standards', 'Standar upah berhasil diperbarui.');
    }


    public function destroy(Request $request, Project $project, WageStandard $wageStandard)
    {
        $this->authorize('update', $project);
        if ($wageStandard->project_id !== $project->id) { abort(404); }

        // --- START OF FIX ---
        // Pengecekan apakah proyek terkunci
        $isLocked = Payment::where('project_id', $project->id)->where('status', Payment::STATUS_APPROVED)->exists();
        if ($isLocked) {
            $message = 'Standar upah tidak dapat dihapus karena proyek terkunci (pembayaran sudah disetujui).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 403); // 403 Forbidden
            }
            return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                            ->withErrors(['general_error' => $message]);
        }
        // --- END OF FIX ---

        $isUsed = $project->workers()->wherePivot('wage_standard_id', $wageStandard->id)->exists();
        if ($isUsed) {
            $message = 'Standar upah tidak dapat dihapus karena masih digunakan oleh anggota tim.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 409);
            }
            return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                            ->withErrors(['delete_wage_standard' => $message]);
        }

        $wageStandard->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Standar upah berhasil dihapus.']);
        }
        
        return redirect()->route('projects.pengaturan', ['project' => $project, 'active_tab' => 'financial'])
                        ->with('success_wage_standards', 'Standar upah berhasil dihapus.');
    }
}