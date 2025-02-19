<?php

namespace App\Notifications;

use App\Models\PerformanceAppraisal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppraisalSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected PerformanceAppraisal $appraisal,
        protected string $type
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return match($this->type) {
            'submitted' => $this->submittedMail($notifiable),
            'supervisor_approved' => $this->supervisorApprovedMail($notifiable),
            'hr_approved' => $this->hrApprovedMail($notifiable),
            'completed' => $this->completedMail($notifiable),
            default => throw new \InvalidArgumentException('Invalid notification type'),
        };
    }

    protected function submittedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Performance Appraisal Submitted')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new performance appraisal has been submitted for your review.')
            ->line("Employee: {$this->appraisal->employee->full_name}")
            ->line("Period: {$this->appraisal->evaluation_period}")
            ->line("Overall Rating: {$this->appraisal->overall_rating}")
            ->action('Review Appraisal', url("/admin/performance-appraisals/{$this->appraisal->id}"));
    }

    protected function supervisorApprovedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Performance Appraisal Approved by Supervisor')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A performance appraisal has been approved by the supervisor and needs HR review.')
            ->line("Employee: {$this->appraisal->employee->full_name}")
            ->line("Supervisor: {$this->appraisal->supervisor->full_name}")
            ->line("Overall Rating: {$this->appraisal->overall_rating}")
            ->action('Review Appraisal', url("/admin/performance-appraisals/{$this->appraisal->id}"));
    }

    protected function hrApprovedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Performance Appraisal Approved by HR')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your performance appraisal has been approved by HR.')
            ->line("Overall Rating: {$this->appraisal->overall_rating}")
            ->line("Final Comments: {$this->appraisal->hr_comments}")
            ->action('View Appraisal', url("/admin/performance-appraisals/{$this->appraisal->id}"));
    }

    protected function completedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Performance Appraisal Completed')
            ->greeting('Hello ' . $notifiable->name)
            ->line('The performance appraisal process has been completed.')
            ->line("Employee: {$this->appraisal->employee->full_name}")
            ->line("Final Rating: {$this->appraisal->overall_rating}")
            ->action('View Appraisal', url("/admin/performance-appraisals/{$this->appraisal->id}"));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'appraisal_id' => $this->appraisal->id,
            'type' => $this->type,
            'message' => $this->getDatabaseMessage(),
            'employee' => $this->appraisal->employee->full_name,
            'rating' => $this->appraisal->overall_rating,
        ];
    }

    protected function getDatabaseMessage(): string
    {
        return match($this->type) {
            'submitted' => 'New appraisal submitted for review',
            'supervisor_approved' => 'Appraisal approved by supervisor',
            'hr_approved' => 'Appraisal approved by HR',
            'completed' => 'Appraisal process completed',
            default => 'Appraisal status updated',
        };
    }
}
