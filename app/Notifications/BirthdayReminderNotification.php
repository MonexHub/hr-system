<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BirthdayReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $employee;

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isSwahili = $notifiable->preferred_language === 'sw';

        if ($notifiable->id === $this->employee->id) {
            // Birthday person notification
            return (new MailMessage)
                ->subject($isSwahili ? 'Siku ya Kuzaliwa Yako!' : 'Your Birthday!')
                ->greeting($isSwahili
                    ? 'Heri ya Siku ya Kuzaliwa ' . $this->employee->first_name
                    : 'Happy Birthday ' . $this->employee->first_name
                )
                ->line($isSwahili
                    ? 'Tunakutakia siku njema ya kusherehekea!'
                    : 'Wishing you a fantastic day of celebration!'
                );
        } else {
            // Colleague notification
            return (new MailMessage)
                ->subject($isSwahili
                    ? 'Siku ya Kuzaliwa ya ' . $this->employee->full_name
                    : $this->employee->full_name . '\'s Birthday'
                )
                ->line($isSwahili
                    ? 'Mwenzako ' . $this->employee->full_name . ' anasherehekea siku yake ya kuzaliwa leo!'
                    : 'Your colleague ' . $this->employee->full_name . ' is celebrating their birthday today!'
                );
        }
    }

    public function toDatabase($notifiable): array
    {
        $isSwahili = $notifiable->preferred_language === 'sw';

        if ($notifiable->id === $this->employee->id) {
            return [
                'title' => $isSwahili ? 'Heri ya Siku ya Kuzaliwa!' : 'Happy Birthday!',
                'message' => $isSwahili
                    ? 'Tunakutakia siku njema ya kusherehekea!'
                    : 'Have a wonderful celebration!',
                'type' => 'birthday_self',
            ];
        } else {
            return [
                'title' => $isSwahili
                    ? 'Siku ya Kuzaliwa - ' . $this->employee->full_name
                    : 'Birthday - ' . $this->employee->full_name,
                'message' => $isSwahili
                    ? 'Mwenzako anasherehekea siku yake ya kuzaliwa leo!'
                    : 'Your colleague is celebrating their birthday today!',
                'type' => 'birthday_colleague',
            ];
        }
    }
}
