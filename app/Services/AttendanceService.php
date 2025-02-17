<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class AttendanceService
{
    public function bulkCheckIn(array $employeeIds, Carbon $checkInTime): array
    {
        $results = [];

        foreach ($employeeIds as $employeeId) {
            try {
                $employee = Employee::findOrFail($employeeId);

                // Check for existing attendance
                $existingAttendance = Attendance::where('employee_id', $employeeId)
                    ->whereDate('date', $checkInTime->toDateString())
                    ->first();

                if ($existingAttendance) {
                    throw new \Exception("Employee already has attendance record for this date");
                }

                // Create new attendance record
                Attendance::create([
                    'employee_id' => $employeeId,
                    'date' => $checkInTime->toDateString(),
                    'check_in' => $checkInTime,
                    'status' => 'pending'
                ]);

                $results[] = [
                    'employee_id' => $employeeId,
                    'status' => 'success',
                    'message' => 'Successfully checked in'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'employee_id' => $employeeId,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    public function markAbsentEmployees(string $date, ?int $departmentId = null, bool $excludeLeave = true): array
    {
        $query = Employee::query()
            ->where('employment_status', 'active')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId));

        // Exclude employees on leave if requested
        if ($excludeLeave) {
            $query->whereDoesntHave('leaves', function ($q) use ($date) {
                $q->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->where('status', 'approved');
            });
        }

        // Get employees without attendance records for the date
        $absentEmployees = $query->whereDoesntHave('attendances', function ($q) use ($date) {
            $q->whereDate('date', $date);
        })->get();

        // Create absent attendance records
        foreach ($absentEmployees as $employee) {
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => $date,
                'status' => 'absent',
                'notes' => 'Automatically marked as absent'
            ]);
        }

        return $absentEmployees->pluck('id')->toArray();
    }

    public function generateAttendanceReport(
        string $startDate,
        string $endDate,
        ?int $departmentId = null,
        string $reportType = 'summary'
    ): array {
        // Validate dates
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            throw new \Exception('End date must be after start date');
        }

        // Generate report based on type
        return match($reportType) {
            'summary' => $this->generateSummaryReport($start, $end, $departmentId),
            'detailed' => $this->generateDetailedReport($start, $end, $departmentId),
            'overtime' => $this->generateOvertimeReport($start, $end, $departmentId),
            'late' => $this->generateLateArrivalsReport($start, $end, $departmentId),
            default => throw new \Exception('Invalid report type')
        };
    }

    protected function generateSummaryReport(Carbon $start, Carbon $end, ?int $departmentId): array
    {
        $query = Attendance::query()
            ->with(['employee.department'])
            ->whereBetween('date', [$start, $end])
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
            });

        $data = $query->get()->groupBy('employee_id')->map(function ($attendances) {
            $employee = $attendances->first()->employee;

            return [
                'Employee ID' => $employee->id,
                'Employee Name' => $employee->first_name . ' ' . $employee->last_name,
                'Department' => $employee->department->name,
                'Total Days' => $attendances->count(),
                'Present Days' => $attendances->where('status', 'present')->count(),
                'Absent Days' => $attendances->where('status', 'absent')->count(),
                'Late Days' => $attendances->where('status', 'late')->count(),
                'Half Days' => $attendances->where('status', 'half_day')->count(),
                'Overtime Days' => $attendances->where('status', 'overtime')->count(),
                'Total Hours' => round($attendances->sum('total_hours'), 2),
                'Overtime Hours' => round($attendances->sum('overtime_hours'), 2),
                'Average Hours/Day' => round($attendances->avg('total_hours'), 2)
            ];
        })->values()->toArray();

        return $data;
    }

    protected function generateDetailedReport(Carbon $start, Carbon $end, ?int $departmentId): array
    {
        return Attendance::query()
            ->with(['employee.department'])
            ->whereBetween('date', [$start, $end])
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
            })
            ->get()
            ->map(function ($attendance) {
                return [
                    'Date' => $attendance->date->format('Y-m-d'),
                    'Employee ID' => $attendance->employee->id,
                    'Employee Name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
                    'Department' => $attendance->employee->department->name,
                    'Check In' => $attendance->check_in?->format('H:i') ?? '-',
                    'Check Out' => $attendance->check_out?->format('H:i') ?? '-',
                    'Total Hours' => round($attendance->total_hours, 2),
                    'Standard Hours' => round($attendance->standard_hours, 2),
                    'Overtime Hours' => round($attendance->overtime_hours, 2),
                    'Status' => ucfirst($attendance->status),
                    'Notes' => $attendance->notes
                ];
            })
            ->toArray();
    }

    protected function generateOvertimeReport(Carbon $start, Carbon $end, ?int $departmentId): array
    {
        return Attendance::query()
            ->with(['employee.department'])
            ->whereBetween('date', [$start, $end])
            ->where('overtime_hours', '>', 0)
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
            })
            ->get()
            ->map(function ($attendance) {
                $isWeekend = in_array($attendance->date->dayOfWeek, [0, 6]);
                $overtimeRate = $isWeekend
                    ? config('attendance.overtime.rates.weekend', 2.0)
                    : config('attendance.overtime.rates.weekday', 1.5);

                return [
                    'Date' => $attendance->date->format('Y-m-d'),
                    'Employee ID' => $attendance->employee->id,
                    'Employee Name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
                    'Department' => $attendance->employee->department->name,
                    'Day Type' => $isWeekend ? 'Weekend' : 'Weekday',
                    'Check In' => $attendance->check_in->format('H:i'),
                    'Check Out' => $attendance->check_out->format('H:i'),
                    'Standard Hours' => round($attendance->standard_hours, 2),
                    'Overtime Hours' => round($attendance->overtime_hours, 2),
                    'Overtime Rate' => $overtimeRate,
                    'Equivalent Hours' => round($attendance->overtime_hours * $overtimeRate, 2)
                ];
            })
            ->toArray();
    }

    protected function generateLateArrivalsReport(Carbon $start, Carbon $end, ?int $departmentId): array
    {
        $workStartTime = config('attendance.working_hours.start_time');
        $graceMinutes = config('attendance.calculation_rules.late_grace_period', 30);

        return Attendance::query()
            ->with(['employee.department'])
            ->whereBetween('date', [$start, $end])
            ->where('status', 'late')
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
            })
            ->get()
            ->map(function ($attendance) use ($workStartTime, $graceMinutes) {
                $expectedStart = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $workStartTime);
                $graceTime = $expectedStart->copy()->addMinutes($graceMinutes);
                $minutesLate = $attendance->check_in->diffInMinutes($graceTime);

                return [
                    'Date' => $attendance->date->format('Y-m-d'),
                    'Employee ID' => $attendance->employee->id,
                    'Employee Name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
                    'Department' => $attendance->employee->department->name,
                    'Expected Start' => $expectedStart->format('H:i'),
                    'Grace Time' => $graceTime->format('H:i'),
                    'Actual Check In' => $attendance->check_in->format('H:i'),
                    'Minutes Late' => $minutesLate,
                    'Notes' => $attendance->notes
                ];
            })
            ->toArray();
    }
}
