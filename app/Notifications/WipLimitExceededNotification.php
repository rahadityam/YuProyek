<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WipLimitExceededNotification extends Notification
{
    use Queueable;

    // FIX: Change visibility from 'protected' to 'public'
    public $project;
    public $task;
    public $worker;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, Task $task, User $worker)
    {
        $this->project = $project;
        $this->task = $task;
        $this->worker = $worker;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  object  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  object  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'worker_id' => $this->worker->id,
            'worker_name' => $this->worker->name,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'wip_limit' => $this->project->wip_limits,
            'message' => "{$this->worker->name} tried to exceed the WIP limit of {$this->project->wip_limits} in project '{$this->project->name}'.",
            'action_url' => route('projects.kanban', $this->project),
        ];
    }
}