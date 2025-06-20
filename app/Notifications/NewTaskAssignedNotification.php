<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Models\User;

class NewTaskAssignedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $assigner;

    public function __construct(Task $task, User $assigner)
    {
        $this->task = $task;
        $this->assigner = $assigner;
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
            'assigner_name' => $this->assigner->name,
            'message' => "{$this->assigner->name} menugaskan Anda untuk task baru: '{$this->task->title}'.",
            'action_url' => route('projects.kanban', $this->task->project_id), // Link ke Kanban
        ];
    }
}