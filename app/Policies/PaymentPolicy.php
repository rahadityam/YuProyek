<?php

namespace App\Policies;

use App\Models\Payment; // Ini adalah model Slip Gaji
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Project $project) // Untuk melihat riwayat slip gaji
    {
        return $user->id === $project->owner_id;
    }

    public function view(User $user, Payment $payment)
    {
        // Owner bisa lihat semua slip di proyeknya.
        // Worker hanya bisa lihat slip gaji miliknya.
        return $user->id === $payment->project->owner_id || $user->id === $payment->user_id;
    }

    public function create(User $user, Project $project) // Untuk membuat draft slip gaji
    {
        return $user->id === $project->owner_id;
    }

    public function approve(User $user, Payment $payment)
    {
        return $user->id === $payment->project->owner_id && $payment->status === Payment::STATUS_DRAFT;
    }

    public function delete(User $user, Payment $payment) // Untuk hapus draft
    {
        return $user->id === $payment->project->owner_id && $payment->status === Payment::STATUS_DRAFT;
    }
}