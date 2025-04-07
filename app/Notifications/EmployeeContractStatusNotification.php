<?php

namespace App\Notifications;

use App\Models\Employee;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class EmployeeContractStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Employee $employee;
    protected string $type;
    protected int $daysRemaining;
    protected ?Carbon $endDate;

    /**
     * Create a new notification instance.
     *
     * @param Employee $employee
     * @param string $type 'probation' or 'contract'
     * @param int $daysRemaining
     */
    public function __construct(Employee $employee, string $type, int $daysRemaining)
    {
        $this->employee = $employee;
        $this->type = $type;
        $this->daysRemaining = $daysRemaining;
        $this->endDate = $type === 'probation'
            ? $employee->getProbationEndDate()
            : $employee->contract_end_date;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        // Add mail channel if email is available
        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        // Add custom filament channel for in-app notifications
        if (method_exists($notifiable, 'receivesBroadcastNotificationsOn')) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $isHrManager = $notifiable->hasRole('hr_manager');
        $isEmployee = $notifiable->id === $this->employee->user_id;

        $subject = $this->getNotificationSubject($isHrManager);
        $message = $this->getNotificationMessage($isHrManager);
        $endDate = $this->endDate->format('d/m/Y');

        return (new MailMessage)
            ->subject($subject)
            ->greeting($isEmployee ? "Hello {$this->employee->first_name}," : "Hello,")
            ->line($message)
            ->line($this->type === 'probation'
                ? "Probation end date: {$endDate}"
                : "Contract end date: {$endDate}")
            ->line("Days remaining: {$this->daysRemaining}")
            ->line($this->getActionRequiredMessage($isHrManager))
            ->action(
                $isHrManager ? 'View Employee Profile' : 'View Your Profile',
                url(route('filament.admin.resources.employees.view', $this->employee))
            )
            ->line($this->getClosingMessage($isHrManager));
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        $isHrManager = $notifiable->hasRole('hr_manager');

        return [
            'employee_id' => $this->employee->id,
            'type' => $this->type,
            'title' => $this->getNotificationSubject($isHrManager),
            'message' => $this->getNotificationMessage($isHrManager),
            'days_remaining' => $this->daysRemaining,
            'end_date' => $this->endDate,
            'status' => 'pending',
            'for_hr' => $isHrManager,
            'action_required' => true,
        ];
    }

    /**
     * Get the Filament representation of the notification.
     */
    public function toFilament($notifiable): FilamentNotification
    {
        $isHrManager = $notifiable->hasRole('hr_manager');

        return FilamentNotification::make()
            ->title($this->getNotificationSubject($isHrManager))
            ->icon($this->type === 'probation' ? 'heroicon-o-academic-cap' : 'heroicon-o-document-text')
            ->iconColor('warning')
            ->body($this->getNotificationMessage($isHrManager))
            ->actions([
                Action::make('view')
                    ->label('View Profile')
                    ->url(route('filament.admin.resources.employees.view', $this->employee))
                    ->button(),
                Action::make('dismiss')
                    ->label('Dismiss')
                    ->color('gray')
                    ->close(),
            ])
            ->duration(10000) // 10 seconds
            ->danger();
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $isHrManager = $notifiable->hasRole('hr_manager');

        return [
            'employee_id' => $this->employee->id,
            'type' => $this->type,
            'title' => $this->getNotificationSubject($isHrManager),
            'message' => $this->getNotificationMessage($isHrManager),
            'days_remaining' => $this->daysRemaining,
            'end_date' => $this->endDate->format('Y-m-d'),
        ];
    }

    /**
     * Get the notification subject based on recipient type.
     */
    private function getNotificationSubject(bool $isHrManager): string
    {
        if ($this->type === 'probation') {
            return $isHrManager
                ? "Probation Period Ending: {$this->employee->full_name}"
                : "Your Probation Period is Ending Soon";
        }

        return $isHrManager
            ? "Contract Expiring: {$this->employee->full_name}"
            : "Your Employment Contract is Expiring Soon";
    }

    /**
     * Get the notification message based on recipient type.
     */
    private function getNotificationMessage(bool $isHrManager): string
    {
        $name = $isHrManager ? "{$this->employee->full_name}'s" : "Your";
        $endDate = $this->endDate->format('d/m/Y');

        if ($this->type === 'probation') {
            return "{$name} probation period will end on {$endDate} ({$this->daysRemaining} days remaining). "
                . ($isHrManager
                    ? "Please schedule a performance review and make a decision regarding their continued employment."
                    : "Your supervisor will schedule a performance review before the end of your probation period.");
        }

        return "{$name} employment contract will expire on {$endDate} ({$this->daysRemaining} days remaining). "
            . ($isHrManager
                ? "Please review and initiate the contract renewal process if applicable."
                : "Please contact HR regarding the possibility of contract renewal.");
    }

    /**
     * Get action required message based on recipient type.
     */
    private function getActionRequiredMessage(bool $isHrManager): string
    {
        if ($this->type === 'probation') {
            return $isHrManager
                ? "Required actions:\n- Schedule performance review\n- Prepare evaluation documents\n- Make employment decision"
                : "Please ensure you:\n- Prepare for your performance review\n- Complete any pending assignments\n- Document your achievements";
        }

        return $isHrManager
            ? "Required actions:\n- Review performance history\n- Assess renewal requirements\n- Initiate renewal process if applicable"
            : "Please ensure you:\n- Update your personnel file\n- Schedule a meeting with your supervisor\n- Discuss contract renewal options";
    }

    /**
     * Get closing message based on recipient type.
     */
    private function getClosingMessage(bool $isHrManager): string
    {
        if ($this->type === 'probation') {
            return $isHrManager
                ? "Please ensure all necessary evaluations and documentation are completed before the probation end date."
                : "Your supervisor will contact you to schedule your probation review meeting.";
        }

        return $isHrManager
            ? "Please ensure the contract renewal process is initiated well before the expiration date if renewal is intended."
            : "Please contact HR if you have any questions about your contract status.";
    }

    /**
     * Get the urgency level of the notification.
     */
    private function getUrgencyLevel(): string
    {
        if ($this->daysRemaining <= 7) {
            return 'high';
        }
        if ($this->daysRemaining <= 14) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend($notifiable): bool
    {
        // Don't send if the employee is no longer active
        if ($this->employee->employment_status !== 'active') {
            return false;
        }

        // Don't send if the date has already passed
        if ($this->endDate->isPast()) {
            return false;
        }

        // Don't send duplicate notifications within 24 hours
        $recentNotification = $notifiable->notifications()
            ->where('type', self::class)
            ->where('data->employee_id', $this->employee->id)
            ->where('data->type', $this->type)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        return !$recentNotification;
    }
}
