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

    protected function getStats(): array
    {
        $now = now();
        $startOfMonth = $now->startOfMonth();

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
}
