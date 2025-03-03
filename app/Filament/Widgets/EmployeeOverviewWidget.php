<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EmployeeOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    // Helper method to check if user has access to all employee data
    protected function canViewAllEmployeeData(): bool
    {
        return auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('hr-manager');
    }

    protected function getStats(): array
    {
        $now = now();
        $startOfMonth = $now->startOfMonth();

        // If user is admin or HR manager, show complete stats
        if ($this->canViewAllEmployeeData()) {
            return $this->getAdminStats($now, $startOfMonth);
        }

        // Otherwise show limited employee view
        return $this->getEmployeeStats($now, $startOfMonth);
    }

    // Stats for admins and HR managers - full company overview
    protected function getAdminStats($now, $startOfMonth): array
    {
        // Keeping original queries exactly as they were
        $totalActive = Employee::where('employment_status', 'active')->count();

        $newHires = Employee::where('appointment_date', '>=', $startOfMonth)->count();

        $onProbation = Employee::where('employment_status', 'probation')->count();

        $departures = Employee::whereIn('employment_status', ['terminated', 'resigned'])
            ->where('updated_at', '>=', $startOfMonth)
            ->count();

        $lastMonthTotal = Employee::where('employment_status', 'active')
            ->where('appointment_date', '<', $startOfMonth)
            ->count();

        $growthRate = $lastMonthTotal > 0
            ? (($totalActive - $lastMonthTotal) / $lastMonthTotal) * 100
            : 0;

        return [
            // Total Active Employees Stat
            Stat::make('Total Active Employees', $totalActive)
                ->description($growthRate >= 0
                    ? '+' . number_format($growthRate, 1) . '% growth'
                    : number_format($growthRate, 1) . '% decline')
                ->descriptionIcon($growthRate >= 0
                    ? 'heroicon-o-arrow-trending-up'
                    : 'heroicon-o-arrow-trending-down')
                ->color($growthRate >= 0 ? 'success' : 'danger')
                ->chart($this->getEmployeeGrowthData()),

            // New Hires Stat
            Stat::make('New Hires This Month', $newHires)
                ->description('Since ' . $startOfMonth->format('M d, Y'))
                ->icon('heroicon-o-user-plus')
                ->color('success'),

            // Probation Stat
            Stat::make('On Probation', $onProbation)
                ->description($onProbation > 0 ? 'Requires attention' : 'No probation cases')
                ->icon('heroicon-o-clock')
                ->color($onProbation > 0 ? 'warning' : 'success'),

            // Departures Stat
            Stat::make('Departures This Month', $departures)
                ->description('Since ' . $startOfMonth->format('M d, Y'))
                ->icon('heroicon-o-user-minus')
                ->color($departures > 0 ? 'danger' : 'success'),
        ];
    }

    // Stats for regular employees - department focused view
    protected function getEmployeeStats($now, $startOfMonth): array
    {
        $currentEmployee = auth()->user()->employee;
        $departmentId = $currentEmployee->department_id;

        // Department team size
        $teamSize = Employee::where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->count();

        // New team members this month
        $newTeamMembers = Employee::where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->where('appointment_date', '>=', $startOfMonth)
            ->count();

        // Department teammates on leave today
        // Using LeaveRequest model directly rather than the undefined leaves() relationship
        $onLeaveToday = Employee::where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->whereHas('leaveRequests', function($query) use ($now) {
                $query->where('status', 'approved')
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            })
            ->count();

        // Employee service years
        $serviceYears = $currentEmployee->appointment_date
            ? $now->diffInYears($currentEmployee->appointment_date)
            : 0;

        $serviceMonths = $currentEmployee->appointment_date
            ? $now->diffInMonths($currentEmployee->appointment_date) % 12
            : 0;

        return [
            // Department Team Size
            Stat::make('Your Team Size', $teamSize)
                ->description('Team members in your department')
                ->icon('heroicon-o-user-group')
                ->color('info'),

            // New Team Members
            Stat::make('New Team Members', $newTeamMembers)
                ->description('Joined this month')
                ->icon('heroicon-o-user-plus')
                ->color('success'),

            // Team Members on Leave
            Stat::make('Team Members on Leave', $onLeaveToday)
                ->description('Today')
                ->icon('heroicon-o-calendar')
                ->color($onLeaveToday > 0 ? 'warning' : 'success'),

            // Your Service Duration
            Stat::make('Your Service', $serviceYears . ($serviceYears == 1 ? ' Year' : ' Years') .
                ($serviceMonths > 0 ? ', ' . $serviceMonths . ($serviceMonths == 1 ? ' Month' : ' Months') : ''))
                ->description('Since ' . ($currentEmployee->appointment_date ? $currentEmployee->appointment_date->format('M d, Y') : 'N/A'))
                ->icon('heroicon-o-clock')
                ->color('primary'),
        ];
    }

    protected function getEmployeeGrowthData(): array
    {
        // Keeping original growth data query exactly as it was
        return Employee::where('employment_status', 'active')
            ->where('appointment_date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(appointment_date, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count')
            ->toArray();
    }

    // Method to get team growth data for specific department
    protected function getTeamGrowthData($departmentId): array
    {
        return Employee::where('employment_status', 'active')
            ->where('department_id', $departmentId)
            ->where('appointment_date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(appointment_date, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count')
            ->toArray();
    }
}
