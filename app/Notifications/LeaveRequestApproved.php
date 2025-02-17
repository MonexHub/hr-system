<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LeaveRequestApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $leaveRequest;
    protected $approverRole;
    protected $remarks;

    public function __construct(LeaveRequest $leaveRequest, string $approverRole, string $remarks)
    {
        $this->leaveRequest = $leaveRequest;
        $this->approverRole = $approverRole;
        $this->remarks = $remarks;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $stage = ucwords(str_replace('_', ' ', $this->approverRole));

        // Convert enum to string value
        $status = $this->leaveRequest->status->value ?? 'approved';

        return (new MailMessage)
            ->subject("[{$this->leaveRequest->request_number}] Leave Request {$status} - {$stage} Approval")
            ->greeting("Hello {$notifiable->name},")
            ->line(new HtmlString("
                <strong>Leave Request Update:</strong><br>
                Your leave request has been approved by the {$stage} with the following details:
            "))
            ->line(new HtmlString("
                <strong>Request Number:</strong> {$this->leaveRequest->request_number}<br>
                <strong>Leave Type:</strong> {$this->leaveRequest->leaveType->name}<br>
                <strong>Start Date:</strong> {$this->leaveRequest->start_date->format('d M Y')}<br>
                <strong>End Date:</strong> {$this->leaveRequest->end_date->format('d M Y')}<br>
                <strong>Total Days:</strong> {$this->leaveRequest->total_days}<br>
                <strong>Status:</strong> " . ucwords($status) . "<br>
                <strong>Remarks:</strong> {$this->remarks}
            "))
            ->line('Thank you for using our leave management system.');
    }

    public function toArray($notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => "Your leave request has been approved by the {$this->approverRole}",
            'status' => $this->leaveRequest->status->value ?? 'approved',
            'remarks' => $this->remarks,
        ];
    }
}
