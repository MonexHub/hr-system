<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification as FilamentNotification;

class LeaveRequestNotification extends Notification
{
    use Queueable;

    protected LeaveRequest $leaveRequest;
    protected string $type;
    protected ?string $remarks;

    public const TYPE_PENDING_HOD = 'pending_hod';
    public const TYPE_PENDING_HR = 'pending_hr';
    public const TYPE_PENDING_CEO = 'pending_ceo';
    public const TYPE_APPROVED = 'approved';
    public const TYPE_REJECTED = 'rejected';
    public const TYPE_CANCELLED = 'cancelled';

    public function __construct(LeaveRequest $leaveRequest, string $type, ?string $remarks = null)
    {
        $this->leaveRequest = $leaveRequest;
        $this->type = $type;
        $this->remarks = $remarks;

        $this->leaveRequest->loadMissing(['employee', 'employee.user', 'leaveType']);
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $employee = $this->leaveRequest->employee;

        try {
            $mailContent = $this->prepareMailContent($employee);
            return $this->buildMailMessage($mailContent);
        } catch (\Exception $e) {
            report($e);
            return $this->buildFallbackMailMessage();
        }
    }

    protected function prepareMailContent($employee): array
    {
        $startDate = Carbon::parse($this->leaveRequest->start_date)->format('F d, Y');
        $endDate = Carbon::parse($this->leaveRequest->end_date)->format('F d, Y');

        return [
            'employeeName' => $employee->full_name,
            'leaveType' => $this->leaveRequest->leaveType->name,
            'duration' => [
                'days' => $this->leaveRequest->total_days,
                'start' => $startDate,
                'end' => $endDate
            ],
            'reason' => $this->leaveRequest->reason,
            'status' => $this->getRequestStatusDetails($employee->full_name)
        ];
    }

    protected function buildMailMessage(array $content): MailMessage
    {
        $message = (new MailMessage)
            ->subject($content['status']['subject'])
            ->greeting("Dear {$content['employeeName']},")
            ->line($content['status']['line']);

        // Add leave request details
        $message->line("\nLeave Request Details:")
            ->line("**Leave Type:** {$content['leaveType']}")
            ->line("**Duration:** {$content['duration']['days']} days")
            ->line("**Period:** {$content['duration']['start']} to {$content['duration']['end']}")
            ->line("**Reason:** {$content['reason']}");

        // Add remarks only if they exist and for specific status types
        if ($this->remarks && in_array($this->type, [self::TYPE_REJECTED, self::TYPE_CANCELLED])) {
            $message->line("\n**Remarks:** {$this->remarks}");
        }

        $message->action(
            'View Leave Request',
            url("/admin/leave-requests/{$this->leaveRequest->id}")
        );

        // Add footer message
        $message->line($this->getFooterMessage());

        return $message;
    }

    protected function buildFallbackMailMessage(): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Leave Request System Notification')
            ->greeting('Important Notice')
            ->line('There has been an update to your leave request.')
            ->line('Please log into the system to view the details.');
    }

    protected function getRequestStatusDetails(string $employeeName): array
    {
        $statusMap = [
            self::TYPE_PENDING_HOD => [
                'subject' => 'Leave Request Submitted - Pending Department Head Review',
                'line' => "Your leave request has been submitted and is awaiting approval from your Department Head."
            ],
            self::TYPE_PENDING_HR => [
                'subject' => 'Leave Request Update - Under HR Review',
                'line' => "Your leave request has been approved by your Department Head and is now under HR review."
            ],
            self::TYPE_PENDING_CEO => [
                'subject' => 'Leave Request Update - Awaiting CEO Approval',
                'line' => "Your leave request has been reviewed by HR and is now awaiting CEO approval."
            ],
            self::TYPE_APPROVED => [
                'subject' => 'Leave Request Approved',
                'line' => "Your leave request has been approved and processed."
            ],
            self::TYPE_REJECTED => [
                'subject' => 'Leave Request Not Approved',
                'line' => "Your leave request has not been approved."
            ],
            self::TYPE_CANCELLED => [
                'subject' => 'Leave Request Cancelled',
                'line' => "Your leave request has been cancelled."
            ]
        ];

        return $statusMap[$this->type] ?? [
            'subject' => 'Leave Request Status Update',
            'line' => 'There has been an update to your leave request.'
        ];
    }

    protected function getFooterMessage(): string
    {
        $messages = [
            self::TYPE_APPROVED => 'Please ensure proper handover of your responsibilities before your leave period.',
            self::TYPE_REJECTED => 'If you have any questions about this decision, please discuss with your supervisor.',
            self::TYPE_CANCELLED => 'You may submit a new leave request if needed.',
            'default' => 'You will be notified of any updates to your request.'
        ];

        return $messages[$this->type] ?? $messages['default'];
    }

    public function toArray($notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'request_number' => $this->leaveRequest->request_number,
            'employee_name' => $this->leaveRequest->employee->full_name,
            'type' => $this->type,
            'message' => $this->getNotificationMessage($this->leaveRequest->employee->full_name),
            'remarks' => $this->remarks,
            'created_at' => now()->toDateTimeString()
        ];
    }

    protected function getNotificationMessage(string $employeeName): string
    {
        $messages = [
            self::TYPE_PENDING_HOD => "Leave request from {$employeeName} is pending Department Head review",
            self::TYPE_PENDING_HR => "Leave request from {$employeeName} is under HR review",
            self::TYPE_PENDING_CEO => "Leave request from {$employeeName} is awaiting CEO approval",
            self::TYPE_APPROVED => "Leave request for {$employeeName} has been approved",
            self::TYPE_REJECTED => "Leave request for {$employeeName} was not approved",
            self::TYPE_CANCELLED => "Leave request for {$employeeName} has been cancelled"
        ];

        return $messages[$this->type] ?? "Leave request status updated for {$employeeName}";
    }
}
