<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LeaveRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $leaveBalance = $this->leaveRequest->employee->getAvailableLeaveBalance($this->leaveRequest->leave_type_id);

        return (new MailMessage)
            ->subject("[{$this->leaveRequest->request_number}] New Leave Request - {$this->leaveRequest->employee->full_name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new leave request has been submitted with the following details:")
            ->line(new HtmlString("
                <strong>Request Details:</strong><br>
                • Employee: {$this->leaveRequest->employee->full_name} ({$this->leaveRequest->employee->employee_code})<br>
                • Department: {$this->leaveRequest->employee->department->name}<br>
                • Leave Type: {$this->leaveRequest->leaveType->name}<br>
                • Duration: {$this->leaveRequest->start_date->format('D, d M Y')} to {$this->leaveRequest->end_date->format('D, d M Y')}<br>
                • Total Days: {$this->leaveRequest->total_days} working days<br>
               • Current Balance: {$leaveBalance?->days_remaining ?? 0} days<br>
                • Reason: {$this->leaveRequest->reason}
            "))
            ->action('Review Request', url("/admin/leave-requests/{$this->leaveRequest->id}"))
            ->line("Please review and take necessary action.")
            ->line("Thank you for using our leave management system.");
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "New leave request submitted by {$this->leaveRequest->employee->full_name}",
            'leave_request_id' => $this->leaveRequest->id,
            'request_number' => $this->leaveRequest->request_number,
            'employee_name' => $this->leaveRequest->employee->full_name,
            'department' => $this->leaveRequest->employee->department->name,
            'leave_type' => $this->leaveRequest->leaveType->name,
            'start_date' => $this->leaveRequest->start_date->format('Y-m-d'),
            'end_date' => $this->leaveRequest->end_date->format('Y-m-d'),
            'total_days' => $this->leaveRequest->total_days,
        ];
    }


}
