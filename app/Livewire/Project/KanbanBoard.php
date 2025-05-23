<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;

class KanbanBoard extends Component
{
    public Project $project;
    public $users; // Data untuk filter/modal
    public $difficultyLevels; // Data untuk filter/modal
    public $priorityLevels; // Data untuk filter/modal

    // Listener untuk me-refresh board jika ada task baru/diedit dari modal
    // Nama event 'taskSaved' harus cocok dengan event yang di-dispatch dari Javascript modal
    protected $listeners = ['taskSaved' => '$refresh'];

    public function mount(Project $project)
    {
        $this->project = $project;
        // Load data statis sekali saat komponen dimuat
        $this->users = $this->getProjectUsers($project);
        $this->difficultyLevels = $project->difficultyLevels()
                                        ->orderBy('display_order')
                                        ->get(['id', 'name', 'value', 'color', 'display_order']);
        $this->priorityLevels = $project->priorityLevels()
                                      ->orderBy('display_order')
                                      ->get(['id', 'name', 'value', 'color', 'display_order']);
    }

    private function getProjectUsers(Project $project)
    {
         $workerIds = $project->workers()->wherePivot('status', 'accepted')->pluck('users.id')->toArray();
         // Tambahkan owner_id ke daftar user yang relevan
         $userIds = array_unique(array_merge([$project->owner_id], $workerIds));
         return User::whereIn('id', $userIds)->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        // Ambil data task setiap kali render (karena bisa berubah)
        // Eager load relasi yang dibutuhkan oleh task card dan modal
        $tasks = $this->project->tasks()
                    ->with([
                        'difficultyLevel:id,name,value,color',
                        'priorityLevel:id,name,value,color',
                        'assignedUser:id,name',
                        // Load relasi lain jika perlu di task card
                    ])
                    ->withCount('attachments') // Hitung attachment
                    ->orderBy('order') // Urutkan berdasarkan order di kolom
                    ->get();

        // Grouping berdasarkan status untuk view
        $tasksByStatus = $tasks->groupBy('status');

        return view('livewire.project.kanban-board', [
            'tasks' => $tasks, // Bisa juga hanya kirim $tasksByStatus
            'tasksByStatus' => $tasksByStatus,
            // project, users, difficultyLevels, priorityLevels otomatis tersedia dari public property
        ]);
    }

    // Catatan: Logika untuk batch update (drag-drop) masih ditangani
    // oleh TaskController@batchUpdate melalui request AJAX dari Javascript.
    // Jika ingin memindahkannya ke Livewire, perlu method di sini
    // dan event listener dari Javascript Sortable.js.
}