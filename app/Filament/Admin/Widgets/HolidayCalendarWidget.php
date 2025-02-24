<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Holiday;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class HolidayCalendarWidget extends Widget
{
    // Change this line to match your blade file name exactly
    protected static string $view = 'filament.widgets.holiday-calendar-widget';
    protected static ?int $sort = 3;
//    protected int | string | array $columnSpan = 'full';

    protected int | string | array $columnSpan = 'half';
    public Collection $events;
    public function mount()
    {
        $this->events = $this->getCachedEvents();
    }



    // Cache upcoming events for performance
    protected function getCachedEvents(): Collection
    {
        return cache()->remember('upcoming_events', now()->addHours(12), function () {
            return $this->getUpcomingEvents();
        });
    }

    public function getUpcomingEvents(): Collection
    {
        $events = collect();

        // Get upcoming holidays
        $holidays = Holiday::upcoming(30)
            ->where('status', 'active')
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => 'holiday_' . $holiday->id,
                    'title' => app()->getLocale() === 'sw' ? $holiday->name_sw : $holiday->name,
                    'description' => app()->getLocale() === 'sw' ? $holiday->description_sw : $holiday->description,
                    'date' => $holiday->next_occurrence,
                    'type' => $holiday->type,
                    'badge' => match($holiday->type) {
                        'religious' => 'warning',
                        'company' => 'info',
                        default => 'success',
                    },
                    'daysUntil' => $this->getDaysUntil($holiday->next_occurrence)
                ];
            });

        // Get upcoming birthdays
        $birthdays = Employee::where('employment_status', 'active')
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') BETWEEN ? AND ?", [
                now()->format('m-d'),
                now()->addDays(30)->format('m-d')
            ])
            ->get()
            ->map(function ($employee) {
                $birthdayThisYear = Carbon::parse($employee->birthdate)->setYear(now()->year);
                if ($birthdayThisYear->isPast()) {
                    $birthdayThisYear->addYear();
                }

                return [
                    'id' => 'birthday_' . $employee->id,
                    'title' => sprintf(
                        app()->getLocale() === 'sw' ? '%s Siku ya Kuzaliwa' : '%s\'s Birthday',
                        $employee->full_name
                    ),
                    'date' => $birthdayThisYear,
                    'type' => 'birthday',
                    'badge' => 'primary',
                    'description' => sprintf(
                        app()->getLocale() === 'sw' ? 'Siku ya kuzaliwa ya %s' : '%s\'s birthday celebration',
                        $employee->full_name
                    ),
                    'daysUntil' => $this->getDaysUntil($birthdayThisYear)
                ];
            });

        return $events->concat($holidays)
            ->concat($birthdays)
            ->sortBy('date')
            ->values();
    }

    protected function getDaysUntil($date): string
    {
        $days = now()->startOfDay()->diffInDays(Carbon::parse($date)->startOfDay());

        if ($days === 0) {
            return app()->getLocale() === 'sw' ? 'Leo' : 'Today';
        } elseif ($days === 1) {
            return app()->getLocale() === 'sw' ? 'Kesho' : 'Tomorrow';
        }

        return app()->getLocale() === 'sw'
            ? sprintf('Siku %d zijazo', $days)
            : sprintf('In %d days', $days);
    }
}
