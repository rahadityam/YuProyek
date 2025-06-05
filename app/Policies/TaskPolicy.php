<?php
namespace App\Policies;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Payment; // Import Payment model
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class TaskPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability, $taskInstanceOrClass)
    {
        $project = null;
        if ($taskInstanceOrClass instanceof Task) {
            $project = $taskInstanceOrClass->project;
        }

        if ($project && $user->isProjectOwner($project)) {
            // Project owner bisa melakukan hampir semua hal, TAPI kita akan override untuk updateStatus jika sudah dibayar
            if ($ability === 'updateStatus' && $taskInstanceOrClass instanceof Task) {
                // Jika task sudah dibayar dan payment approved, owner pun tidak bisa ubah status
                if ($taskInstanceOrClass->payment_id !== null) {
                    $payment = $taskInstanceOrClass->payment()->first(); // Ambil payment terkait
                    if ($payment && $payment->status === Payment::STATUS_APPROVED) {
                        Log::debug("TaskPolicy@before (denying owner): User {$user->id} (owner) tried to updateStatus on PAID task {$taskInstanceOrClass->id}.");
                        return false; // Tolak meskipun owner
                    }
                }
            }
            return true; // Untuk ability lain, owner diizinkan
        }
    }

    public function viewAny(User $user, Project $project)
    {
        return $user->isProjectMember($project);
    }

    public function view(User $user, Task $task)
    {
        return $user->isProjectMember($task->project);
    }

    public function create(User $user, Project $project)
    {
        return $user->isProjectOwner($project);
    }

    public function update(User $user, Task $task)
    {
        // Project owner bisa update semua (sudah dihandle `before`, kecuali jika paid)
        // Jika task sudah dibayar (payment_id ada & payment status approved), tidak boleh diupdate field apapun oleh siapapun.
        if ($task->payment_id !== null) {
            $payment = $task->payment()->first();
            if ($payment && $payment->status === Payment::STATUS_APPROVED) {
                Log::debug("TaskPolicy@update: Attempt to update PAID task {$task->id} by user {$user->id}. Denied.");
                return false;
            }
        }
        // Jika belum dibayar, project owner bisa update.
        // Untuk worker, tergantung kebutuhan (misal hanya field tertentu, bukan yang krusial).
        // Saat ini, logic `before` sudah mengizinkan owner. Jika ingin worker juga bisa update (selama belum paid),
        // Anda bisa tambahkan `|| $user->id === $task->assigned_to` di sini.
        // Namun, dengan adanya `before` untuk owner, baris ini efektifnya hanya untuk worker.
        // Kita asumsikan worker tidak bisa update field umum, hanya status (melalui updateStatus).
        // return $user->id === $task->assigned_to;
        return $user->isProjectOwner($task->project); // Owner bisa update jika belum paid (worker tidak)
    }

    public function updateStatus(User $user, Task $task)
    {
        Log::debug("TaskPolicy@updateStatus: Checking user {$user->id} ({$user->name}) for task {$task->id} (assigned_to: {$task->assigned_to}, payment_id: {$task->payment_id}). Project ID: {$task->project_id}");

        // 1. Cek apakah task sudah dibayar (payment_id ada dan payment status 'approved')
        if ($task->payment_id !== null) {
            // Eager load payment jika belum atau query langsung
            $payment = $task->relationLoaded('payment') ? $task->payment : $task->payment()->first();
            if ($payment && $payment->status === Payment::STATUS_APPROVED) {
                Log::warning("TaskPolicy@updateStatus: User {$user->id} cannot update status for PAID task {$task->id}.");
                return false; // Tidak bisa update status jika sudah dibayar dan disetujui
            }
            Log::debug("TaskPolicy@updateStatus: Task {$task->id} has payment_id {$task->payment_id}, but payment status is not 'approved' (or payment not found). Current payment status: " . ($payment ? $payment->status : 'N/A'));
        }

        // 2. Jika belum dibayar, cek apakah user adalah Project Owner (sudah dihandle oleh `before`)
        // Jika `before` tidak mengizinkan, maka kode ini tidak akan tercapai untuk owner.
        // Jadi, kita hanya perlu cek untuk worker.

        // 3. Cek apakah user adalah worker yang diassign ke task tersebut
        $isAssigned = $user->id === $task->assigned_to;
        Log::debug("TaskPolicy@updateStatus: Is user assigned to task? " . ($isAssigned ? 'Yes' : 'No'));

        if (!$isAssigned) {
            Log::warning("TaskPolicy@updateStatus: User {$user->id} is NOT authorized to update status for task {$task->id} (not assigned).");
        }
        return $isAssigned; // Hanya worker yang diassign (dan task belum dibayar)
    }

    public function delete(User $user, Task $task)
    {
        // Hanya project owner yang bisa hapus task, TAPI tidak jika sudah dibayar & approved
        if ($task->payment_id !== null) {
            $payment = $task->payment()->first();
            if ($payment && $payment->status === Payment::STATUS_APPROVED) {
                Log::debug("TaskPolicy@delete: Attempt to delete PAID task {$task->id} by user {$user->id}. Denied.");
                return false;
            }
        }
        return $user->isProjectOwner($task->project);
    }

    public function createComment(User $user, Task $task)
    {
        return $user->isProjectMember($task->project);
    }

    public function manageAttachments(User $user, Task $task)
    {
         // Jika task sudah dibayar dan payment approved, attachment tidak boleh di-manage lagi
         if ($task->payment_id !== null) {
            $payment = $task->payment()->first();
            if ($payment && $payment->status === Payment::STATUS_APPROVED) {
                Log::debug("TaskPolicy@manageAttachments: Attempt to manage attachments for PAID task {$task->id} by user {$user->id}. Denied.");
                return false;
            }
        }
         // Project owner bisa manage attachment
         if ($user->isProjectOwner($task->project)) return true;
         // Worker bisa tambah/hapus attachment di task yang diassign ke dia
         return $user->id === $task->assigned_to;
    }
}