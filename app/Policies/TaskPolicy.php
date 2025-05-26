<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log; 

class TaskPolicy
{
    use HandlesAuthorization;

    // Memberikan hak akses penuh ke project owner untuk semua task di proyeknya
    public function before(User $user, $ability, $taskInstanceOrClass)
    {
        $project = null;
        if ($taskInstanceOrClass instanceof Task) {
            $project = $taskInstanceOrClass->project;
        }
        // Jika ability adalah 'create', $taskInstanceOrClass bisa jadi nama class Task::class
        // Dalam kasus ini, kita mungkin perlu $project dari request atau parameter tambahan.
        // Untuk sementara, asumsikan $project bisa didapat.
        // Jika tidak bisa didapat, permission 'create' harus di-handle secara eksplisit.
        // Atau, untuk 'create', kita bisa cek di ProjectPolicy (misal 'manageKanban').

        if ($project && $user->isProjectOwner($project)) {
            return true;
        }
    }

    public function viewAny(User $user, Project $project)
    {
        // Semua member proyek bisa lihat daftar task (misal di Kanban)
        return $user->isProjectMember($project);
    }

    public function view(User $user, Task $task)
    {
        // Semua member proyek bisa lihat detail task manapun di proyek tersebut
        return $user->isProjectMember($task->project);
    }

    public function create(User $user, Project $project) // Perhatikan, policy 'create' biasanya menerima class, bukan instance
    {
        // Project owner bisa buat task. Worker tidak bisa buat task baru berdasarkan aturan Anda.
        return $user->isProjectOwner($project);
    }

    public function update(User $user, Task $task)
    {
        // Project owner bisa update semua.
        // Worker hanya bisa update jika task diassign ke dia (misalnya untuk update field tertentu, bukan status).
        // Untuk update status akan ada method khusus.
        // if ($user->isProjectOwner($task->project)) return true;
        // return $user->id === $task->assigned_to;
        return $user->isProjectOwner($task->project);
    }

    public function updateStatus(User $user, Task $task)
{
    Log::debug("TaskPolicy@updateStatus: Checking user {$user->id} ({$user->name}) for task {$task->id} (assigned_to: {$task->assigned_to}). Project ID: {$task->project_id}");

    $isAssigned = $user->id === $task->assigned_to;

    Log::debug("TaskPolicy@updateStatus: Is user assigned to task? " . ($isAssigned ? 'Yes' : 'No'));

    if (!$isAssigned) {
        Log::warning("TaskPolicy@updateStatus: User {$user->id} is NOT authorized to update status for task {$task->id}.");
    }

    return $isAssigned;
}

    public function delete(User $user, Task $task)
    {
        // Hanya project owner yang bisa hapus task
        return $user->isProjectOwner($task->project);
    }

    public function createComment(User $user, Task $task)
    {
        // Semua member proyek bisa komentar di semua task
        return $user->isProjectMember($task->project);
    }

    public function manageAttachments(User $user, Task $task)
    {
         // Project owner bisa manage attachment
         if ($user->isProjectOwner($task->project)) return true;
         // Worker bisa tambah/hapus attachment di task yang diassign ke dia
         return $user->id === $task->assigned_to;
    }
}