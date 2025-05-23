<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class WageStandardCreate extends Component
{
    public Project $project;

    // Properti untuk binding form (opsional, karena submit ke controller)
    // Jika Anda ingin validasi realtime Livewire, tambahkan properti di sini
    // public $job_category = '';
    // public $task_price = '';

    public function mount(Project $project)
    {
        // Otorisasi: Pastikan hanya owner yang bisa akses
        if ($project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        $this->project = $project;
    }

    // Anda bisa menambahkan method validasi Livewire di sini jika mau
    // protected $rules = [
    //     'job_category' => 'required|string|max:255',
    //     'task_price' => 'required|numeric|min:0',
    // ];
    // public function updated($propertyName) { $this->validateOnly($propertyName); }

    // Method save() TIDAK diperlukan di sini karena form submit ke controller

    public function render()
    {
        // Hanya menampilkan view, data project sudah ada di properti publik
        return view('livewire.project.wage-standard-create');
            // ->layout('layouts.app'); // Layout otomatis dari route
    }
}