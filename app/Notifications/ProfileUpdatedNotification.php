<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProfileUpdatedNotification extends Notification
{
    use Queueable;

    public $updatedUser;
    public $actor;

    /**
     * Create a new notification instance.
     *
     * @param User $updatedUser The user whose profile was updated.
     * @param User $actor The user who performed the update.
     */
    public function __construct(User $updatedUser, User $actor)
    {
        $this->updatedUser = $updatedUser;
        $this->actor = $actor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Pesan akan disesuaikan tergantung siapa penerimanya
        $message = "Your profile was updated by {$this->actor->name}.";
        if ($notifiable->id !== $this->updatedUser->id) { // Notifikasi untuk PM
            $message = "Profile for {$this->updatedUser->name} was updated by {$this->actor->name}.";
        }
        if ($this->actor->id === $this->updatedUser->id) { // User mengedit profilnya sendiri
             $message = "Your profile has been successfully updated.";
             if ($notifiable->id !== $this->updatedUser->id) { // Notifikasi untuk PM dari user yg edit profil sendiri
                 $message = "{$this->updatedUser->name} has updated their profile.";
             }
        }

        return [
            'updated_user_id' => $this->updatedUser->id,
            'updated_user_name' => $this->updatedUser->name,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'message' => $message,
            'action_url' => route('profile.edit'), // Link ke halaman edit profil
        ];
    }
}