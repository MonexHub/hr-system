<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Holiday extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_sw',
        'description',
        'description_sw',
        'date',
        'is_recurring',
        'type',
        'status',
        'send_notification'
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'send_notification' => 'boolean'
    ];

    // Scope for upcoming holidays
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where(function($q) use ($days) {
            // For non-recurring holidays
            $q->where('is_recurring', false)
                ->whereBetween('date', [now(), now()->addDays($days)]);
        })->orWhere(function($q) use ($days) {
            // For recurring holidays, check the date within the current year
            $q->where('is_recurring', true)
                ->whereRaw('DATE_FORMAT(date, "%m-%d") BETWEEN ? AND ?', [
                    now()->format('m-d'),
                    now()->addDays($days)->format('m-d')
                ]);
        })->where('status', 'active');
    }

    // Get next occurrence of a holiday
    public function getNextOccurrenceAttribute()
    {
        if (!$this->is_recurring) {
            return $this->date;
        }

        $nextDate = $this->date->setYear(now()->year);
        if ($nextDate->isPast()) {
            $nextDate->addYear();
        }
        return $nextDate;
    }

    // Get days until next occurrence
    public function getDaysUntilAttribute()
    {
        return now()->startOfDay()->diffInDays($this->next_occurrence);
    }

    // Check if holiday is within next week
    public function getIsUpcomingAttribute()
    {
        return $this->days_until <= 7;
    }

    // Check if holiday is today
    public function getIsTodayAttribute()
    {
        return $this->next_occurrence->isToday();
    }

    // Get localized name based on locale
    public function getLocalizedNameAttribute()
    {
        return app()->getLocale() === 'sw' && $this->name_sw
            ? $this->name_sw
            : $this->name;
    }

    // Get localized description based on locale
    public function getLocalizedDescriptionAttribute()
    {
        return app()->getLocale() === 'sw' && $this->description_sw
            ? $this->description_sw
            : $this->description;
    }

    // Get formatted date
    public function getFormattedDateAttribute()
    {
        return $this->next_occurrence->format('l, F j, Y');
    }

    // Seeder data for Tanzanian holidays
    public static function getDefaultHolidays()
    {
        return [
            [
                'name' => 'New Year\'s Day',
                'name_sw' => 'Mwaka Mpya',
                'date' => '2024-01-01',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Zanzibar Revolution Day',
                'name_sw' => 'Mapinduzi ya Zanzibar',
                'date' => '2024-01-12',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Karume Day',
                'name_sw' => 'Siku ya Karume',
                'date' => '2024-04-07',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Union Day',
                'name_sw' => 'Siku ya Muungano',
                'date' => '2024-04-26',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Workers Day',
                'name_sw' => 'Siku ya Wafanyakazi',
                'date' => '2024-05-01',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Saba Saba Day',
                'name_sw' => 'Siku ya Saba Saba',
                'date' => '2024-07-07',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Nane Nane Day',
                'name_sw' => 'Siku ya Nane Nane',
                'date' => '2024-08-08',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Nyerere Day',
                'name_sw' => 'Siku ya Nyerere',
                'date' => '2024-10-14',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Independence Day',
                'name_sw' => 'Siku ya Uhuru',
                'date' => '2024-12-09',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Christmas Day',
                'name_sw' => 'Siku ya Krismas',
                'date' => '2024-12-25',
                'type' => 'public',
                'is_recurring' => true,
            ],
            [
                'name' => 'Boxing Day',
                'name_sw' => 'Siku ya Boxing',
                'date' => '2024-12-26',
                'type' => 'public',
                'is_recurring' => true,
            ],
            // Religious holidays - dates vary by year
            [
                'name' => 'Eid al-Fitr',
                'name_sw' => 'Sikukuu ya Eid al-Fitr',
                'date' => '2024-04-10', // Example date - will need updating yearly
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name' => 'Eid al-Adha',
                'name_sw' => 'Sikukuu ya Eid al-Adha',
                'date' => '2024-06-17', // Example date - will need updating yearly
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name' => 'Good Friday',
                'name_sw' => 'Ijumaa Kuu',
                'date' => '2024-03-29', // Example date - will need updating yearly
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name' => 'Easter Monday',
                'name_sw' => 'Jumatatu ya Pasaka',
                'date' => '2024-04-01', // Example date - will need updating yearly
                'type' => 'religious',
                'is_recurring' => false,
            ],
        ];
    }
}
