<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'employee_id',
        'holiday_notifications',
        'birthday_notifications',
        'email_notifications',
        'in_app_notifications',
        'preferred_language',
    ];

    protected $casts = [
        'holiday_notifications' => 'boolean',
        'birthday_notifications' => 'boolean',
        'email_notifications' => 'boolean',
        'in_app_notifications' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Helper methods
    public function canReceiveHolidayNotifications(): bool
    {
        return $this->holiday_notifications &&
            ($this->email_notifications || $this->in_app_notifications);
    }

    public function canReceiveBirthdayNotifications(): bool
    {
        return $this->birthday_notifications &&
            ($this->email_notifications || $this->in_app_notifications);
    }

    public function getPreferredChannels(): array
    {
        $channels = [];

        if ($this->email_notifications) {
            $channels[] = 'mail';
        }

        if ($this->in_app_notifications) {
            $channels[] = 'database';
        }

        return $channels;
    }
}
