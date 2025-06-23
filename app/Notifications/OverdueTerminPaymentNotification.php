<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PaymentTerm;

class OverdueTerminPaymentNotification extends Notification
{
    use Queueable;

    public $term;

    /**
     * Create a new notification instance.
     */
    public function __construct(PaymentTerm $term)
    {
        $this->term = $term;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Kirim ke database notifikasi
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->term->project_id,
            'project_name' => $this->term->project->name,
            'term_id' => $this->term->id,
            'term_name' => $this->term->name,
            'term_end_date' => $this->term->end_date->format('d M Y'),
            'message' => "Pembayaran untuk Termin '{$this->term->name}' pada proyek '{$this->term->project->name}' telah melewati batas waktu 2 hari.",
            'action_url' => route('projects.payslips.history', $this->term->project), // Link ke riwayat slip gaji proyek
        ];
    }
}