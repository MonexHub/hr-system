<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LeaveRequestPendingApproval extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $approverRole
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $leaveBalance = $this->leaveRequest->employee->getAvailableLeaveBalance($this->leaveRequest->leave_type_id);
        $previousApprovals = $this->getPreviousApprovals();
        $jobTitle = $this->leaveRequest->employee->jobTitle ? $this->leaveRequest->employee->jobTitle->name : 'N/A';
        $departmentName = $this->leaveRequest->employee->department ? $this->leaveRequest->employee->department->name : 'N/A';
        $remainingDays = $leaveBalance ? $leaveBalance->days_remaining : 0;

        return (new MailMessage)
            ->subject("[{$this->leaveRequest->request_number}] Leave Request Pending Your Approval")
            ->greeting("Hello {$notifiable->name},")
            ->line("A leave request requires your approval.")
            ->line(new HtmlString("
                <strong>Request Details:</strong><br>
                • Employee: {$this->leaveRequest->employee->full_name} ({$this->leaveRequest->employee->employee_code})<br>
                • Department: {$departmentName}<br>
                • Position: {$jobTitle}<br>
                • Leave Type: {$this->leaveRequest->leaveType->name}<br>
                • Duration: {$this->leaveRequest->start_date->format('D, d M Y')} to {$this->leaveRequest->end_date->format('D, d M Y')}<br>
                • Total Days: {$this->leaveRequest->total_days} working days<br>
                • Current Balance: {$remainingDays} days<br>
                • Reason: {$this->leaveRequest->reason}
            "))
            ->when($previousApprovals, function (MailMessage $message) use ($previousApprovals) {
                return $message->line(new HtmlString("
                    <strong>Previous Approvals:</strong><br>
                    {$previousApprovals}
                "));
            })
            ->action('Review Request', url("/admin/leave-requests/{$this->leaveRequest->id}"))
            ->line("Please review and take necessary action at your earliest convenience.");
    }

    private function getPreviousApprovals(): ?string
    {
        $approvals = [];

        if ($this->leaveRequest->department_approved_by) {
            $approver = $this->leaveRequest->departmentApprover?->name ?? 'Unknown';
            $approvals[] = "• Department Head: {$approver} - " .
                $this->leaveRequest->department_approved_at->format('D, d M Y H:i') .
                ($this->leaveRequest->department_remarks ? "<br>  Remarks: {$this->leaveRequest->department_remarks}" : "");
        }

        if ($this->leaveRequest->hr_approved_by) {
            $approver = $this->leaveRequest->hrApprover?->name ?? 'Unknown';
            $approvals[] = "• HR Manager: {$approver} - " .
                $this->leaveRequest->hr_approved_at->format('D, d M Y H:i') .
                ($this->leaveRequest->hr_remarks ? "<br>  Remarks: {$this->leaveRequest->hr_remarks}" : "");
        }

        return empty($approvals) ? null : implode("<br>", $approvals);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "Leave request {$this->leaveRequest->request_number} pending your approval",
            'leave_request_id' => $this->leaveRequest->id,
            'request_number' => $this->leaveRequest->request_number,
            'employee_name' => $this->leaveRequest->employee->full_name,
            'department' => $this->leaveRequest->employee->department?->name ?? 'N/A',
            'approver_role' => $this->approverRole,
            'current_status' => $this->leaveRequest->status,
        ];
    }
}
