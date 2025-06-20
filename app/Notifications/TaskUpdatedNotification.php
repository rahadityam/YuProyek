<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Models\User;

class TaskUpdatedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $updater;

    public function __construct(Task $task, User $updater)
    {
        $this->task = $task;
        $this->updater = $updater;
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
            'updater_name' => $this->updater->name,
            'message' => "{$this->updater->name} memperbarui detail pada task: '{$this->task->title}'.",
            'action_url' => route('projects.kanban', $this->task->project_id),
        ];
    }
}