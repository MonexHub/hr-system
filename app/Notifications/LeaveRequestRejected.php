<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LeaveRequestRejected extends Notification
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $rejectorRole,
        public string $reason
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $rejector = match($this->rejectorRole) {
            'department_head' => 'Department Head',
            'hr_manager' => 'HR Manager',
            'chief_executive_officer' => 'CEO',
            default => 'Unknown'
        };

        return (new MailMessage)
            ->subject("[{$this->leaveRequest->request_number}] Leave Request Rejected")
            ->greeting("Hello {$notifiable->name},")
            ->line("We regret to inform you that your leave request has been rejected.")
            ->line(new HtmlString("
                <strong>Request Details:</strong><br>
                • Request Number: {$this->leaveRequest->request_number}<br>
                • Leave Type: {$this->leaveRequest->leaveType->name}<br>
                • Duration: {$this->leaveRequest->start_date->format('D, d M Y')} to {$this->leaveRequest->end_date->format('D, d M Y')}<br>
                • Total Days: {$this->leaveRequest->total_days} working days
            "))
            ->line(new HtmlString("
                <strong>Rejection Details:</strong><br>
                • Rejected By: {$rejector}<br>
                • Reason: {$this->reason}
            "))
            ->action('View Request', url("/admin/leave-requests/{$this->leaveRequest->id}"))
            ->line("If you have any questions, please contact your supervisor or HR department.")
            ->line("Thank you for your understanding.");
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "Leave request {$this->leaveRequest->request_number} has been rejected",
            'leave_request_id' => $this->leaveRequest->id,
            'request_number' => $this->leaveRequest->request_number,
            'rejector_role' => $this->rejectorRole,
            'reason' => $this->reason,
            'status' => 'rejected',
        ];
    }
}
