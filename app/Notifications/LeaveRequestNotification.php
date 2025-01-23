<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class LeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected LeaveRequest $leaveRequest,
        protected string $type
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return match($this->type) {
            'new_request' => $this->newRequestMail(),
            'manager_approval' => $this->managerApprovalMail(),
            'status_update' => $this->statusUpdateMail(),
            'request_rejected' => $this->rejectedMail(),
            default => $this->defaultMail(),
        };
    }

    protected function newRequestMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('🔔 New Leave Request: Action Required')
            ->greeting("Hello {$this->leaveRequest->employee->reportingTo->first_name},")
            ->line("**Action Required**: New leave request submitted by {$this->leaveRequest->employee->full_name}")
            ->line("**Request Details:**")
            ->line("• Leave Type: {$this->leaveRequest->leaveType->name}")
            ->line("• Period: {$this->leaveRequest->start_date->format('D, d M Y')} - {$this->leaveRequest->end_date->format('D, d M Y')}")
            ->line("• Days Requested: {$this->leaveRequest->days_taken} days")
            ->line("• Reason: {$this->leaveRequest->reason}")
            ->action('Review Request', url("/admin/leave-requests/{$this->leaveRequest->id}"))
            ->line("Please review and take action on this request.");
    }

    protected function managerApprovalMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('👥 Leave Request: Manager Approved - HR Review Required')
            ->greeting("Hello HR Team,")
            ->line("A leave request has been approved by the line manager and requires HR review")
            ->line("**Request Details:**")
            ->line("• Employee: {$this->leaveRequest->employee->full_name}")
            ->line("• Department: {$this->leaveRequest->employee->department->name}")
            ->line("• Approved By: {$this->leaveRequest->managerApprover->name}")
            ->action('Review Request', url("/admin/leave-requests/{$this->leaveRequest->id}"));
    }

    protected function statusUpdateMail(): MailMessage
    {
        $status = match($this->leaveRequest->status) {
            'approved' => '✅ Approved',
            'rejected' => '❌ Rejected',
            default => $this->leaveRequest->status
        };

        return (new MailMessage)
            ->subject("Leave Request {$status}")
            ->greeting("Hello {$this->leaveRequest->employee->first_name},")
            ->line("Your leave request has been {$this->leaveRequest->status}")
            ->line("**Request Details:**")
            ->line("• Leave Type: {$this->leaveRequest->leaveType->name}")
            ->line("• Period: {$this->leaveRequest->start_date->format('D, d M Y')} - {$this->leaveRequest->end_date->format('D, d M Y')}")
            ->when($this->leaveRequest->rejection_reason, function (MailMessage $mail) {
                return $mail->line("• Rejection Reason: {$this->leaveRequest->rejection_reason}");
            })
            ->action('View Details', url("/employee/leave-requests/{$this->leaveRequest->id}"));
    }

    protected function rejectedMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Leave Request Rejected')
            ->line("A leave request for {$this->leaveRequest->employee->full_name} has been rejected.")
            ->line("Reason: {$this->leaveRequest->rejection_reason}")
            ->action('View Details', url("/admin/leave-requests/{$this->leaveRequest->id}"));
    }

    protected function defaultMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Leave Request Update')
            ->line('There has been an update to a leave request.')
            ->action('View Request', url("/admin/leave-requests/{$this->leaveRequest->id}"));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'type' => $this->type,
            'message' => match($this->type) {
                'new_request' => "New leave request from {$this->leaveRequest->employee->full_name}",
                'manager_approval' => "Leave request approved by manager for {$this->leaveRequest->employee->full_name}",
                'status_update' => "Your leave request has been {$this->leaveRequest->status}",
                'request_rejected' => "Leave request for {$this->leaveRequest->employee->full_name} was rejected",
                default => 'Leave request update',
            },
        ];
    }

    public function toFilament(object $notifiable): ?FilamentNotification
    {
        return FilamentNotification::make()
            ->title(match($this->type) {
                'new_request' => 'New Leave Request',
                'manager_approval' => 'Manager Approved Leave Request',
                'status_update' => 'Leave Request Update',
                'request_rejected' => 'Leave Request Rejected',
                default => 'Leave Request Notification',
            })
            ->body($this->getDatabaseMessage())
            ->actions([
              Action::make('view')
                    ->button()
                    ->url(route('filament.admin.resources.leave-requests.view', [
                        'record' => $this->leaveRequest->id,
                    ])),
            ])
            ->status(match($this->type) {
                'new_request' => 'warning',
                'manager_approval' => 'info',
                'status_update' => $this->leaveRequest->status === 'approved' ? 'success' : 'danger',
                'request_rejected' => 'danger',
                default => 'info',
            });
    }

    protected function getDatabaseMessage(): string
    {
        return match($this->type) {
            'new_request' => "New leave request from {$this->leaveRequest->employee->full_name}",
            'manager_approval' => "Leave request approved by manager for {$this->leaveRequest->employee->full_name}",
            'status_update' => "Your leave request has been {$this->leaveRequest->status}",
            'request_rejected' => "Leave request for {$this->leaveRequest->employee->full_name} was rejected",
            default => 'Leave request update',
        };
    }
}
