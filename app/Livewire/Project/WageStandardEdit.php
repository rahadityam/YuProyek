<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use App\Models\Project;
use App\Models\WageStandard;
use Illuminate\Support\Facades\Auth;

class WageStandardEdit extends Component
{
    public Project $project;
    public WageStandard $wageStandard; // Instance model yang akan diedit

    // Properti untuk binding form (opsional, tapi bisa berguna untuk pre-fill)
    // Jika pakai submit ke controller, ini hanya untuk tampilan awal
    public $job_category = '';
    public $task_price = '';

    public function mount(Project $project, WageStandard $wageStandard)
    {
        // Otorisasi: Pastikan hanya owner yang bisa akses & wage standard milik project ini
        if ($project->owner_id !== Auth::id() || $wageStandard->project_id !== $project->id) {
            abort(403, 'Unauthorized action.');
        }

        $this->project = $project;
        $this->wageStandard = $wageStandard;

        // Isi properti form dengan data awal dari model
        $this->job_category = $this->wageStandard->job_category;
        $this->task_price = $this->wageStandard->task_price; // Ambil sebagai number/string
    }

    // Validasi bisa ditambahkan jika ingin realtime feedback
    // protected $rules = [ ... ];
    // public function updated($propertyName) { ... }

    // Method update() TIDAK diperlukan di sini

    public function render()
    {
        // Hanya menampilkan view, data sudah ada di properti publik
        return view('livewire.project.wage-standard-edit');
    }
}