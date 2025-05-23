<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\WageStandard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WageStandardIndex extends Component
{
    use WithPagination;

    public Project $project;
    public $searchTerm = ''; // Untuk fitur pencarian (opsional)

    protected $paginationTheme = 'tailwind';

    // Agar pagination dan search tersimpan di URL
    protected $queryString = [
        'searchTerm' => ['except' => '', 'as' => 'search'],
        'page' => ['except' => 1]
    ];

    // Listener untuk refresh jika ada data baru/update/delete
    protected $listeners = ['wageStandardUpdated' => '$refresh'];

    public function mount(Project $project)
    {
        // Otorisasi: Pastikan hanya owner yang bisa akses manajemen standar upah
        if ($project->owner_id !== Auth::id()) {
            // Redirect atau tampilkan pesan error
            return redirect()->route('projects.pengaturan', $project) // Kembali ke pengaturan utama
                   ->with('error', 'Unauthorized to manage wage standards.');
            // atau abort(403);
        }
        $this->project = $project;
    }

    // Method untuk menghapus standar upah
    public function deleteWageStandard($id)
    {
         // Otorisasi lagi untuk keamanan
         if ($this->project->owner_id !== Auth::id()) {
             session()->flash('error_message', 'Unauthorized action.');
             return;
         }

        $wageStandard = WageStandard::where('project_id', $this->project->id)->find($id);

        if (!$wageStandard) {
            session()->flash('error_message', 'Wage standard not found.');
            return;
        }

         // Opsional: Cek apakah standar ini sedang digunakan di project_users
         $isUsed = \App\Models\ProjectUser::where('wage_standard_id', $id)->exists();
         if ($isUsed) {
             session()->flash('error_message', 'Cannot delete: This wage standard is currently assigned to team members.');
             return;
         }

        try {
            $jobCategory = $wageStandard->job_category; // Simpan nama untuk pesan
            $wageStandard->delete();
            session()->flash('success_message', "Wage standard '{$jobCategory}' deleted successfully.");
            // $this->emit('wageStandardUpdated'); // Emit event jika perlu (atau biarkan $refresh otomatis)
            // Livewire akan otomatis me-render ulang setelah ini
        } catch (\Exception $e) {
            Log::error("Error deleting wage standard {$id}: " . $e->getMessage());
            session()->flash('error_message', 'Failed to delete wage standard.');
        }
    }

    // Reset page jika search term berubah
    public function updatingSearchTerm()
    {
        $this->resetPage();
    }


    public function render()
    {
        // Query standar upah dengan pagination dan search (jika ada)
        $query = WageStandard::where('project_id', $this->project->id);

        if (!empty($this->searchTerm)) {
            $query->where('job_category', 'like', '%' . $this->searchTerm . '%');
        }

        $wageStandards = $query->orderBy('job_category')->paginate(10); // Jumlah item per halaman

        return view('livewire.project.wage-standard-index', [
            'wageStandards' => $wageStandards
        ]);
    }
}