<?php

namespace App\Notifications;

use App\Models\Holiday;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HolidayReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $holiday;

    public function __construct(Holiday $holiday)
    {
        $this->holiday = $holiday;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isSwahili = $notifiable->preferred_language === 'sw';

        return (new MailMessage)
            ->subject($isSwahili ? 'Likizo Inayokuja' : 'Upcoming Holiday')
            ->greeting($isSwahili ? 'Habari ' . $notifiable->first_name : 'Hello ' . $notifiable->first_name)
            ->line($isSwahili
                ? 'Tunakukumbusha kuhusu likizo inayokuja:'
                : 'This is a reminder about an upcoming holiday:'
            )
            ->line($isSwahili ? $this->holiday->name_sw : $this->holiday->name)
            ->line($isSwahili
                ? 'Tarehe: ' . $this->holiday->date->format('l, F j, Y')
                : 'Date: ' . $this->holiday->date->format('l, F j, Y')
            );
    }

    public function toDatabase($notifiable): array
    {
        $isSwahili = $notifiable->preferred_language === 'sw';

        return [
            'holiday_id' => $this->holiday->id,
            'title' => $isSwahili ? $this->holiday->name_sw : $this->holiday->name,
            'message' => $isSwahili
                ? 'Likizo inakuja tarehe ' . $this->holiday->date->format('d/m/Y')
                : 'Holiday coming up on ' . $this->holiday->date->format('d/m/Y'),
            'type' => 'holiday',
        ];
    }
}
