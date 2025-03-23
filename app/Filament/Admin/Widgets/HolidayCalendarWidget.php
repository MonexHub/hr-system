<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Holiday;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

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

    // Helper method to check if user has access to all employees
    protected function canViewAllEmployees(): bool
    {
        return auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('hr-manager');
    }

    // Cache upcoming events for performance
    protected function getCachedEvents(): Collection
    {
        // Use different cache keys for different user roles
        $cacheKey = $this->canViewAllEmployees()
            ? 'upcoming_events_all'
            : 'upcoming_events_employee_' . auth()->user()->employee->id;

        return cache()->remember($cacheKey, now()->addHours(12), function () {
            return $this->getUpcomingEvents();
        });
    }

    public function getUpcomingEvents(): Collection
    {
        $events = collect();

        // Get upcoming holidays (visible to everyone)
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

        // Get upcoming birthdays based on user role
        $birthdaysQuery = Employee::where('employment_status', 'active')
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') BETWEEN ? AND ?", [
                now()->format('m-d'),
                now()->addDays(30)->format('m-d')
            ]);

        // For regular employees, only show their own birthday and department colleagues
        if (!$this->canViewAllEmployees()) {
            $currentEmployee = auth()->user()->employee;

            $birthdaysQuery->where(function(Builder $query) use ($currentEmployee) {
                // Include employee's own birthday
                $query->where('id', $currentEmployee->id)
                    // Include teammates or department colleagues
                    ->orWhere('department_id', $currentEmployee->department_id);
            });
        }

        $birthdays = $birthdaysQuery->get()
            ->map(function ($employee) {
                $birthdayThisYear = Carbon::parse($employee->birthdate)->setYear(now()->year);
                if ($birthdayThisYear->isPast()) {
                    $birthdayThisYear->addYear();
                }

                // Check if it's the current user's own birthday
                $isOwnBirthday = auth()->user()->employee->id === $employee->id;

                return [
                    'id' => 'birthday_' . $employee->id,
                    'title' => sprintf(
                        app()->getLocale() === 'sw' ? '%s Siku ya Kuzaliwa' : '%s\'s Birthday',
                        $isOwnBirthday
                            ? (app()->getLocale() === 'sw' ? 'Yako' : 'Your')
                            : $employee->full_name
                    ),
                    'date' => $birthdayThisYear,
                    'type' => 'birthday',
                    'badge' => $isOwnBirthday ? 'danger' : 'primary', // Highlight own birthday
                    'description' => sprintf(
                        app()->getLocale() === 'sw' ? 'Siku ya kuzaliwa ya %s' : '%s\'s birthday celebration',
                        $isOwnBirthday
                            ? (app()->getLocale() === 'sw' ? 'yako' : 'your')
                            : $employee->full_name
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

    // Get widget title based on user role
    public function getWidgetTitle(): string
    {
        if ($this->canViewAllEmployees()) {
            return app()->getLocale() === 'sw' ? 'Kalenda ya Matukio' : 'Event Calendar';
        }

        return app()->getLocale() === 'sw' ? 'Kalenda ya Matukio ya Timu' : 'Team Event Calendar';
    }
}
