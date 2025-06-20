<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Project;
use App\Models\User;

class UserInvitedToProjectNotification extends Notification
{
    use Queueable;

    protected $project;
    protected $inviter;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, User $inviter)
    {
        $this->project = $project;
        $this->inviter = $inviter;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Kita akan menyimpannya ke database. Anda juga bisa menambahkan 'mail'.
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'message' => "{$this->inviter->name} mengundang Anda untuk bergabung ke proyek: {$this->project->name}.",
            // Kita tambahkan URL aksi langsung di sini
            'action_accept_url' => route('projects.invitations.updateStatus', ['project' => $this->project, 'user' => $notifiable->id, 'action' => 'accept']),
            'action_decline_url' => route('projects.invitations.updateStatus', ['project' => $this->project, 'user' => $notifiable->id, 'action' => 'decline'])
        ];
    }
}