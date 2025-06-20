<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Payment;
use App\Models\User;

class PayslipApprovedNotification extends Notification
{
    use Queueable;

    protected $payslip;
    protected $approver;

    public function __construct(Payment $payslip, User $approver)
    {
        $this->payslip = $payslip;
        $this->approver = $approver;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payslip_id' => $this->payslip->id,
            'project_id' => $this->payslip->project_id,
            'payslip_name' => $this->payslip->payment_name,
            'approver_name' => $this->approver->name,
            'message' => "Slip gaji '{$this->payslip->payment_name}' telah disetujui oleh {$this->approver->name}.",
            'action_url' => route('projects.payslips.show', [$this->payslip->project_id, $this->payslip->id]),
        ];
    }
}