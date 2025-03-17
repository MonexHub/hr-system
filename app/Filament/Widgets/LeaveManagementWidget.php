<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Department;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class LeaveManagementWidget extends Widget
{
    protected static string $view = 'filament.widgets.leave-management-widget';

    // Set a default polling interval (optional)
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    public function calculateLeaveStats()
    {
        $now = Carbon::now();

        return [
            'pending_requests' => LeaveRequest::where('status', 'pending')->count(),
            'department_approval' => LeaveRequest::where('status', 'department_approved')->count(),
            'hr_approval' => LeaveRequest::where('status', 'hr_approved')->count(),
            'today_absences' => $this->calculateTodayAbsences(),
        ];
    }

    protected function calculateTodayAbsences()
    {
        $today = Carbon::today();

        return LeaveRequest::where('status', 'approved')
            ->where(function ($query) use ($today) {
                $query->where(function ($query) use ($today) {
                    $query->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today);
                });
            })

            ->count();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.leave-management-widget', [
            'stats' => $this->calculateLeaveStats(),
        ]);
    }
}
