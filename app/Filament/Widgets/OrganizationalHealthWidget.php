<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class OrganizationalHealthWidget extends Widget
{
    protected static string $view = 'filament.widgets.organizational-health-widget';

    // Set a default polling interval (optional)
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;

    public function calculateOrganizationalHealth()
    {
        return [
            'departments_count' => Department::where('is_active', true)->count(),
            'avg_headcount_utilization' => $this->calculateHeadcountUtilization(),
            'pending_leave_requests' => LeaveRequest::where('status', 'pending')->count(),
            'departments_at_capacity' => Department::where('is_active', true)
                ->whereRaw('current_headcount >= max_headcount')
                ->where('max_headcount', '>', 0)
                ->count(),
        ];
    }

    protected function calculateHeadcountUtilization()
    {
        $departments = Department::where('is_active', true)
            ->where('max_headcount', '>', 0)
            ->get();

        if ($departments->isEmpty()) {
            return 0;
        }

        $totalUtilization = $departments->sum(function ($department) {
            return ($department->current_headcount / $department->max_headcount) * 100;
        });

        return round($totalUtilization / $departments->count(), 2);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $stats = $this->calculateOrganizationalHealth();

        // Make sure we have a headcount utilization value to pass to the view
        if (!isset($stats['avg_headcount_utilization'])) {
            $stats['avg_headcount_utilization'] = 0;
        }

        return view('filament.widgets.organizational-health-widget', [
            'stats' => $stats,
        ]);
    }
}
