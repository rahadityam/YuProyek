<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Models\User;

class NewCommentOnTaskNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $commenter;

    public function __construct(Task $task, User $commenter)
    {
        $this->task = $task;
        $this->commenter = $commenter;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'commenter_name' => $this->commenter->name,
            'message' => "{$this->commenter->name} meninggalkan komentar baru pada task: '{$this->task->title}'.",
            'action_url' => route('projects.kanban', $this->task->project_id),
        ];
    }
}