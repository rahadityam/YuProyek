<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class CEOPolicy
{
    use HandlesAuthorization;

    /**
     * CEO dapat melihat semua proyek.
     */
    public function viewAnyProject(User $user)
    {
        return $user->role === 'ceo';
    }

    /**
     * CEO dapat melihat detail proyek tertentu.
     */
    public function viewProject(User $user, Project $project)
    {
        return $user->role === 'ceo';
    }

    /**
     * CEO dapat melihat daftar semua pengguna.
     */
    public function viewAnyUser(User $user)
    {
        return $user->role === 'ceo';
    }

    /**
     * CEO dapat melihat profil detail pengguna tertentu.
     */
    public function viewUser(User $user, User $targetUser)
    {
        return $user->role === 'ceo';
    }
}
