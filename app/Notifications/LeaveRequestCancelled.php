<?php


namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected LeaveRequest $leaveRequest)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Leave Request Cancelled')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your leave request has been cancelled.')
            ->line('Leave Type: ' . $this->leaveRequest->leaveType->name)
            ->line('Duration: ' . $this->leaveRequest->start_date->format('d M Y') . ' to ' . $this->leaveRequest->end_date->format('d M Y'))
            ->line('Cancellation Reason: ' . $this->leaveRequest->cancellation_reason)
            ->action('View Details', url('/employee/leave-requests/' . $this->leaveRequest->id));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => 'Your leave request has been cancelled',
            'leave_request_id' => $this->leaveRequest->id,
            'type' => 'leave_request_cancelled',
        ];
    }
}
