<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use App\Models\WageStandard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Untuk logging error

class TeamMembers extends Component
{
    public Project $project;
    public $owner;
    public $members; // Koleksi anggota aktif
    public $applicants; // Koleksi pelamar
    public $wageStandards; // Untuk dropdown standar gaji

    // Properti untuk menyimpan assignment standar gaji sementara di frontend
    public $memberWageAssignments = [];

    // Listener untuk me-refresh data jika ada perubahan status anggota atau standar gaji
    protected $listeners = ['teamUpdated' => 'loadTeamData', 'wageStandardUpdated' => 'loadTeamData'];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->loadTeamData(); // Load data awal
    }

    public function loadTeamData()
    {
        // Load ulang data project untuk memastikan relasi ter-update
        $this->project->load(['owner', 'workers', 'wageStandards']); // Eager load relasi

        $this->owner = $this->project->owner;

        // Pisahkan workers berdasarkan status
        $allWorkers = $this->project->workers; // Akses dari relasi yang sudah di-load
        $this->members = $allWorkers->where('pivot.status', 'accepted')
                                    ->sortBy('name'); // Urutkan berdasarkan nama
        $this->applicants = $allWorkers->where('pivot.status', 'applied')
                                      ->sortBy('created_at'); // Urutkan pelamar berdasarkan waktu

        $this->wageStandards = $this->project->wageStandards()
                                             ->orderBy('job_category')->get();

        // Inisialisasi atau update assignment wage standard di frontend
        $currentAssignments = $this->memberWageAssignments; // Simpan state sementara
        $this->memberWageAssignments = [];
        foreach ($this->members as $member) {
            // Gunakan nilai yang sudah ada di state jika ada, atau dari DB
            $this->memberWageAssignments[$member->id] = $currentAssignments[$member->id] ?? ($member->pivot->wage_standard_id ?? '');
        }
    }

    // Method untuk mengupdate standar gaji anggota tim
    public function updateMemberWage($memberId, $wageStandardId)
    {
        // Validasi sederhana (bisa diperketat)
        if (!Auth::user() || $this->project->owner_id !== Auth::id()) {
            session()->flash('error_message', 'Unauthorized action.');
            return;
        }

        $member = $this->members->find($memberId);
        if (!$member) {
            session()->flash('error_message', 'Member not found.');
            return;
        }

        // Jika memilih "-- Select wage --", set ke null
        $wageStandardId = ($wageStandardId === '' || $wageStandardId === null) ? null : (int)$wageStandardId;

        // Validasi wage standard (jika tidak null)
        if ($wageStandardId !== null) {
            $wageStandardExists = $this->wageStandards->contains('id', $wageStandardId);
            if (!$wageStandardExists) {
                 session()->flash('error_message', 'Invalid wage standard selected.');
                 // Kembalikan nilai select ke nilai semula
                 $this->memberWageAssignments[$memberId] = $member->pivot->wage_standard_id ?? '';
                 return;
            }
        }

        try {
            // Update pivot table
            $this->project->workers()->updateExistingPivot($memberId, [
                'wage_standard_id' => $wageStandardId
            ]);

            // Update state lokal agar tampilan konsisten (meskipun akan refresh)
            $member->pivot->wage_standard_id = $wageStandardId;
            $this->memberWageAssignments[$memberId] = $wageStandardId ?? '';


            session()->flash('success_message', 'Wage standard for ' . $member->name . ' updated.');
            // $this->emit('wageStandardUpdated'); // Emit event jika ada komponen lain yang perlu update
             $this->loadTeamData(); // Refresh data setelah update berhasil

        } catch (\Exception $e) {
            Log::error("Error updating member wage standard for member {$memberId} in project {$this->project->id}: " . $e->getMessage());
            session()->flash('error_message', 'Failed to update wage standard. Please try again.');
            // Kembalikan nilai select ke nilai semula jika gagal
             $this->memberWageAssignments[$memberId] = $member->pivot->wage_standard_id ?? '';
        }
    }

    // Method untuk accept/reject (memanggil action Controller)
    // Kita tetap gunakan form biasa untuk action ini agar lebih sederhana
    // Jika ingin pakai Livewire, perlu method accept/reject di sini
    // dan ganti form di view dengan wire:click

    // Method untuk remove member (memanggil action Controller)
    // Sama seperti accept/reject, bisa tetap pakai form atau pindah ke Livewire

    public function render()
    {
        // Data sudah diload di mount/loadTeamData, tinggal render view
        return view('livewire.project.team-members');
            // ->layout('layouts.app'); // Layout otomatis
    }
}