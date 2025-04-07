<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceSummaryWidget extends Widget
{
    protected static string $view = 'filament.widgets.employee-attendance-summary-widget';
    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';
    use HasWidgetShield;

//    protected static ?int $sort = 1;

    public function getAttendanceSummary()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'today' => null,
                'week_stats' => [],
                'month_stats' => [
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'leave' => 0,
                    'total_days' => 0,
                    'present_percentage' => 0,
                ],
            ];
        }

        // Get today's attendance
        $today = Carbon::today();
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->first();

        // Get this week's attendance
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();

        $weekData = [];
        $period = CarbonPeriod::create($startOfWeek, $endOfWeek);

        foreach ($period as $date) {
            // Skip future dates
            if ($date->isAfter($today)) {
                $weekData[$date->format('Y-m-d')] = [
                    'date' => $date->format('D'),
                    'status' => 'upcoming',
                ];
                continue;
            }

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $date->toDateString())
                ->first();

            if ($attendance) {
                $weekData[$date->format('Y-m-d')] = [
                    'date' => $date->format('D'),
                    'status' => $attendance->status,
                    'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : null,
                    'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : null,
                    'total_hours' => $attendance->total_hours,
                ];
            } else {
                // Check if it's a weekend
                if ($date->isWeekend()) {
                    $weekData[$date->format('Y-m-d')] = [
                        'date' => $date->format('D'),
                        'status' => 'weekend',
                    ];
                } else {
                    $weekData[$date->format('Y-m-d')] = [
                        'date' => $date->format('D'),
                        'status' => 'absent',
                    ];
                }
            }
        }

        // Get monthly stats
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = min($today, $today->copy()->endOfMonth());

        $workingDays = 0;
        $presentDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $leaveDays = 0;

        $monthPeriod = CarbonPeriod::create($startOfMonth, $endOfMonth);
        foreach ($monthPeriod as $date) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            $workingDays++;

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $date->toDateString())
                ->first();

            if ($attendance) {
                if ($attendance->status == 'present') {
                    $presentDays++;
                } elseif ($attendance->status == 'late') {
                    $lateDays++;
                    $presentDays++; // Late is still considered present
                } elseif ($attendance->status == 'absent') {
                    $absentDays++;
                } elseif ($attendance->status == 'leave') {
                    $leaveDays++;
                }
            } else {
                // If no record, count as absent
                $absentDays++;
            }
        }

        $presentPercentage = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0;

        return [
            'today' => $todayAttendance ? [
                'status' => $todayAttendance->status,
                'check_in' => $todayAttendance->check_in ? Carbon::parse($todayAttendance->check_in)->format('h:i A') : null,
                'check_out' => $todayAttendance->check_out ? Carbon::parse($todayAttendance->check_out)->format('h:i A') : null,
                'total_hours' => $todayAttendance->total_hours,
            ] : null,
            'week_stats' => $weekData,
            'month_stats' => [
                'present' => $presentDays,
                'late' => $lateDays,
                'absent' => $absentDays,
                'leave' => $leaveDays,
                'total_days' => $workingDays,
                'present_percentage' => $presentPercentage,
            ],
        ];
    }
}
