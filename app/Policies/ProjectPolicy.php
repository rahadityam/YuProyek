<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    // Project Owner bisa melakukan apa saja di proyeknya
    public function before(User $user, $ability, $projectInstanceOrClass)
    {
        // Pastikan $projectInstanceOrClass adalah instance dari Project
        if ($projectInstanceOrClass instanceof Project) {
            if ($user->isProjectOwner($projectInstanceOrClass)) {
                return true;
            }
        } elseif (is_string($projectInstanceOrClass) && $projectInstanceOrClass === Project::class) {
            // Untuk 'create' atau 'viewAny' yang mungkin tidak menerima instance project
            // Tidak ada rule khusus 'before' di sini, biarkan method spesifik yang handle
        }
    }

    public function viewAny(User $user)
    {
        return true; // Semua user terautentikasi bisa lihat daftar proyek (index)
    }

    public function view(User $user, Project $project)
    {
        // Owner bisa lihat, worker bisa lihat jika dia member proyek itu
        return $user->isProjectOwner($project) || $project->workers()->where('user_id', $user->id)->wherePivot('status', 'accepted')->exists();
    }

    public function create(User $user)
    {
        // Hanya project owner (secara peran umum) yang bisa buat proyek baru
        return $user->role === 'project_owner';
    }

    public function update(User $user, Project $project)
    {
        // Hanya owner yang bisa update info proyek
        return $user->id === $project->owner_id;
    }

    public function delete(User $user, Project $project)
    {
        return $user->id === $project->owner_id;
    }

    public function viewKanban(User $user, Project $project)
    {
        // Owner dan member proyek bisa lihat kanban
        return $user->isProjectOwner($project) || $project->workers()->where('user_id', $user->id)->wherePivot('status', 'accepted')->exists();
    }

    public function manageKanban(User $user, Project $project) // Untuk create/update/delete task di kanban
    {
        return $user->id === $project->owner_id; // Hanya owner yang bisa manage semua task
        // Worker akan dicek per task di TaskPolicy
    }

    public function viewPayslipCreation(User $user, Project $project)
    {
        return $user->id === $project->owner_id;
    }

    public function viewPayrollCalculation(User $user, Project $project)
    {
        // Owner dan member bisa lihat kalkulasi payroll umum
         return $user->isProjectOwner($project) || $project->workers()->where('user_id', $user->id)->wherePivot('status', 'accepted')->exists();
    }

    public function viewSettings(User $user, Project $project)
    {
        return $user->id === $project->owner_id;
    }
}