<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Models\User;

class TaskStatusChangedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $oldStatus;
    protected $newStatus;
    protected $updater;

    public function __construct(Task $task, string $oldStatus, string $newStatus, User $updater)
    {
        $this->task = $task;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
            'message' => "Status task '{$this->task->title}' diubah dari '{$this->oldStatus}' menjadi '{$this->newStatus}' oleh {$this->updater->name}.",
            'action_url' => route('projects.kanban', $this->task->project_id),
        ];
    }
}