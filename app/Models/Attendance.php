<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'check_in', 'check_out', 'status',
        'total_hours', 'standard_hours', 'overtime_hours', 'early_hours',
        'late_minutes', 'early_out_minutes', 'absence_hours', 'normal_overtime_hours',
        'weekend_overtime_hours', 'holiday_overtime_hours', 'ot1_hours', 'ot2_hours', 'ot3_hours',
        'annual_leave_hours', 'sick_leave_hours', 'casual_leave_hours',
        'maternity_leave_hours', 'compassionate_leave_hours', 'business_trip_hours',
        'compensatory_hours', 'compensatory_leave_hours', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'float',
        'standard_hours' => 'float',
        'overtime_hours' => 'float',
        'early_hours' => 'float',
        'late_minutes' => 'float',
        'early_out_minutes' => 'float',
        'absence_hours' => 'float',
        'normal_overtime_hours' => 'float',
        'weekend_overtime_hours' => 'float',
        'holiday_overtime_hours' => 'float',
        'ot1_hours' => 'float',
        'ot2_hours' => 'float',
        'ot3_hours' => 'float',
        'annual_leave_hours' => 'float',
        'sick_leave_hours' => 'float',
        'casual_leave_hours' => 'float',
        'maternity_leave_hours' => 'float',
        'compassionate_leave_hours' => 'float',
        'business_trip_hours' => 'float',
        'compensatory_hours' => 'float',
        'compensatory_leave_hours' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Import attendance data from Excel export format
     *
     * @param array $excelData Array containing parsed Excel data with employee attendance
     * @param int $year The year for the attendance records
     * @param int $month The month for the attendance records
     * @return Collection Collection of created/updated attendance records
     */
    public static function importFromExcel(array $excelData, int $year, int $month): Collection
    {
        $result = collect();

        foreach ($excelData as $row) {
            if (empty($row['Employee ID'])) {
                continue; // Skip rows without employee ID
            }

            $employeeId = $row['Employee ID'];

            // Find or create the employee if needed
            $employee = Employee::firstOrCreate(
                ['employee_id' => $employeeId],
                [
                    'name' => $row['First Name'] ?? 'Unknown',
                    'department' => $row['Department'] ?? null
                ]
            );

            // Process each day of the month
            for ($day = 1; $day <= 31; $day++) {
                // Format day with leading zero if needed
                $dayKey = $day < 10 ? "0{$day}" : "{$day}";

                // Skip if no attendance data for this day or if day doesn't exist in this month
                if (!isset($row[$dayKey]) || !self::isValidDay($year, $month, $day)) {
                    continue;
                }

                $timeEntry = $row[$dayKey];
                if (empty($timeEntry) || !strpos($timeEntry, '-')) {
                    continue; // Skip invalid time entries
                }

                // Parse check-in and check-out times
                list($checkInTime, $checkOutTime) = explode('-', $timeEntry);

                // Create date strings with year, month and day
                $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $checkInDateTime = Carbon::parse($dateString . ' ' . $checkInTime);
                $checkOutDateTime = Carbon::parse($dateString . ' ' . $checkOutTime);

                // Handle overnight shifts (if check-out is before check-in)
                if ($checkOutDateTime->lt($checkInDateTime)) {
                    $checkOutDateTime->addDay();
                }

                // Calculate metrics
                $metrics = self::calculateMetricsFromExcel($checkInDateTime, $checkOutDateTime, $row);

                // Create or update attendance record
                $attendance = self::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $dateString,
                    ],
                    array_merge([
                        'check_in' => $checkInDateTime,
                        'check_out' => $checkOutDateTime,
                    ], $metrics)
                );

                $result->push($attendance);
            }
        }

        return $result;
    }

    /**
     * Calculate attendance metrics based on Excel data
     *
     * @param Carbon $checkIn Check-in time
     * @param Carbon $checkOut Check-out time
     * @param array $rowData Excel row data with metrics
     * @return array Calculated metrics
     */
    protected static function calculateMetricsFromExcel(Carbon $checkIn, Carbon $checkOut, array $rowData): array
    {
        // Calculate total hours from time difference
        $totalMinutes = $checkOut->diffInMinutes($checkIn);
        $totalHours = round($totalMinutes / 60, 2);

        // Get metrics from Excel if available (convert from string to float)
        $lateMinutes = isset($rowData['Late In(M)']) ? (float)$rowData['Late In(M)'] : 0;
        $earlyOutMinutes = isset($rowData['Early Out(M)']) ? (float)$rowData['Early Out(M)'] : 0;
        $absenceHours = isset($rowData['Absence(H)']) ? (float)$rowData['Absence(H)'] : 0;
        $normalOvertimeHours = isset($rowData['Normal OT(H)']) ? (float)$rowData['Normal OT(H)'] : 0;
        $weekendOvertimeHours = isset($rowData['Weekend OT(H)']) ? (float)$rowData['Weekend OT(H)'] : 0;
        $holidayOvertimeHours = isset($rowData['Holiday OT(H)']) ? (float)$rowData['Holiday OT(H)'] : 0;

        // Additional metrics
        $ot1Hours = isset($rowData['OT1(H)']) ? (float)$rowData['OT1(H)'] : 0;
        $ot2Hours = isset($rowData['OT2(H)']) ? (float)$rowData['OT2(H)'] : 0;
        $ot3Hours = isset($rowData['OT3(H)']) ? (float)$rowData['OT3(H)'] : 0;
        $annualLeaveHours = isset($rowData['Annual Leave(H)']) ? (float)$rowData['Annual Leave(H)'] : 0;
        $sickLeaveHours = isset($rowData['Sick Leave(H)']) ? (float)$rowData['Sick Leave(H)'] : 0;
        $casualLeaveHours = isset($rowData['Casual Leave(H)']) ? (float)$rowData['Casual Leave(H)'] : 0;
        $maternityLeaveHours = isset($rowData['Maternity Leave(H)']) ? (float)$rowData['Maternity Leave(H)'] : 0;
        $compassionateLeaveHours = isset($rowData['Compassionate Leave(H)']) ? (float)$rowData['Compassionate Leave(H)'] : 0;
        $businessTripHours = isset($rowData['Business Trip(H)']) ? (float)$rowData['Business Trip(H)'] : 0;
        $compensatoryHours = isset($rowData['Compensatory(H)']) ? (float)$rowData['Compensatory(H)'] : 0;
        $compensatoryLeaveHours = isset($rowData['Compensatory Leave(H)']) ? (float)$rowData['Compensatory Leave(H)'] : 0;

        // Standard hours (default 8 hours, can be configured)
        $standardHours = config('attendance.working_hours.standard_hours', 8);

        // Regular hours (as in Excel) or calculated from total minus overtime
        $regularHours = isset($rowData['Regular(H)'])
            ? (float)$rowData['Regular(H)']
            : min($totalHours, $standardHours);

        // Calculate total overtime hours
        $overtimeHours = $normalOvertimeHours + $weekendOvertimeHours + $holidayOvertimeHours;

        // Determine status
        $status = self::determineStatusFromExcel(
            $checkIn,
            $checkOut,
            $lateMinutes,
            $earlyOutMinutes,
            $absenceHours,
            $overtimeHours
        );

        return [
            'total_hours' => $totalHours,
            'standard_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'late_minutes' => $lateMinutes,
            'early_out_minutes' => $earlyOutMinutes,
            'absence_hours' => $absenceHours,
            'normal_overtime_hours' => $normalOvertimeHours,
            'weekend_overtime_hours' => $weekendOvertimeHours,
            'holiday_overtime_hours' => $holidayOvertimeHours,
            'ot1_hours' => $ot1Hours,
            'ot2_hours' => $ot2Hours,
            'ot3_hours' => $ot3Hours,
            'annual_leave_hours' => $annualLeaveHours,
            'sick_leave_hours' => $sickLeaveHours,
            'casual_leave_hours' => $casualLeaveHours,
            'maternity_leave_hours' => $maternityLeaveHours,
            'compassionate_leave_hours' => $compassionateLeaveHours,
            'business_trip_hours' => $businessTripHours,
            'compensatory_hours' => $compensatoryHours,
            'compensatory_leave_hours' => $compensatoryLeaveHours,
            'status' => $status,
        ];
    }

    /**
     * Determine attendance status based on Excel metrics
     *
     * @param Carbon $checkIn Check-in time
     * @param Carbon $checkOut Check-out time
     * @param float $lateMinutes Minutes late for check-in
     * @param float $earlyOutMinutes Minutes early for check-out
     * @param float $absenceHours Hours absent
     * @param float $overtimeHours Hours of overtime
     * @return string Status ('present', 'absent', 'late', etc.)
     */
    protected static function determineStatusFromExcel(
        Carbon $checkIn,
        Carbon $checkOut,
        float  $lateMinutes,
        float  $earlyOutMinutes,
        float  $absenceHours,
        float  $overtimeHours
    ): string
    {
        $config = config('attendance.status_rules');
        $standardHours = config('attendance.working_hours.standard_hours', 8);

        // First check for absence
        if ($absenceHours > 0) {
            return 'absent';
        }

        // Check for half day
        if ($checkOut->diffInHours($checkIn) < ($standardHours / 2)) {
            return 'half_day';
        }

        // Check for late arrival
        if ($lateMinutes > 0) {
            return 'late';
        }

        // Check for early departure
        if ($earlyOutMinutes > 0) {
            return 'early_departure';
        }

        // Check for overtime
        if ($overtimeHours > 0) {
            return 'overtime';
        }

        // Default status
        return 'present';
    }

    /**
     * Check if the given date is valid for the specified year and month
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return bool Whether the day is valid for the month
     */
    protected static function isValidDay(int $year, int $month, int $day): bool
    {
        return $day <= Carbon::create($year, $month)->daysInMonth;
    }

    public static function checkIn(Employee $employee, $checkInTime = null): self
    {
        $checkInTime = $checkInTime ?: now();

        // Normalize check-in time to ensure date consistency
        $normalizedCheckIn = Carbon::parse($checkInTime)->setDate(
            $checkInTime->year,
            $checkInTime->month,
            $checkInTime->day
        );

        self::validateCheckIn($employee, $normalizedCheckIn);

        return self::create([
            'employee_id' => $employee->id,
            'date' => $normalizedCheckIn->toDateString(),
            'check_in' => $normalizedCheckIn,
            'status' => 'pending'
        ]);
    }

    /**
     * Validate check-in time and employee status
     *
     * @param Employee $employee
     * @param Carbon $checkInTime
     * @return void
     * @throws \Exception If validation fails
     */
    protected static function validateCheckIn(Employee $employee, Carbon $checkInTime): void
    {
        // Check if employee already has an active check-in
        $activeAttendance = self::where('employee_id', $employee->id)
            ->whereDate('date', $checkInTime->toDateString())
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->first();

        if ($activeAttendance) {
            throw new \Exception('Employee already checked in and has not checked out yet.');
        }
    }

    public function checkOut($checkOutTime = null): self
    {
        $checkOutTime = $checkOutTime ?: now();

        // Normalize check-out time to same date as check-in
        $normalizedCheckOut = Carbon::parse($checkOutTime)->setDate(
            $this->check_in->year,
            $this->check_in->month,
            $this->check_in->day
        );

        $this->validateCheckOut($normalizedCheckOut);

        $metrics = $this->calculateMetrics($this->check_in, $normalizedCheckOut);
        $this->update(array_merge($metrics, ['check_out' => $normalizedCheckOut]));

        $this->logAttendanceEvent('check_out', [
            'employee_id' => $this->employee_id,
            'check_out_time' => $normalizedCheckOut,
            'metrics' => $metrics
        ]);

        return $this;
    }

    protected function validateCheckOut(Carbon $checkOutTime): void
    {
        // Handle overnight shifts
        $maxWorkHours = config('attendance.working_hours.max_daily_hours', 12);
        $totalHours = $this->check_in->copy()->diffInHours($checkOutTime);

        if ($checkOutTime->lt($this->check_in)) {
            $totalHours = 24 - $totalHours;
        }

        if ($totalHours > $maxWorkHours) {
            throw new \Exception("Maximum working hours of {$maxWorkHours} exceeded.");
        }
    }

    protected function calculateMetrics(?Carbon $checkIn = null, ?Carbon $checkOut = null): array
    {
        $checkIn = $checkIn ?: $this->check_in;
        $checkOut = $checkOut ?: $this->check_out;

        if (!$checkIn || !$checkOut) {
            return [
                'total_hours' => 0,
                'early_hours' => 0,
                'standard_hours' => 0,
                'overtime_hours' => 0,
                'status' => 'pending'
            ];
        }

        $workStart = $this->getWorkTime('start_time');
        $workEnd = $this->getWorkTime('end_time');

        // Calculate total hours considering midnight crossing
        $totalMinutes = $checkOut->diffInMinutes($checkIn);
        if ($checkOut->lt($checkIn)) {
            $totalMinutes = 1440 - $totalMinutes; // 24 hours = 1440 minutes
        }
        $totalHours = $totalMinutes / 60;

        $standardHours = config('attendance.working_hours.standard_hours', 8);
        $earlyHours = max(0, $workStart->diffInMinutes($checkIn) / 60);
        $overtimeHours = max(0, $totalHours - $standardHours);

        return [
            'total_hours' => round($totalHours, 2),
            'early_hours' => round($earlyHours, 2),
            'standard_hours' => round(min($totalHours, $standardHours), 2),
            'overtime_hours' => round($overtimeHours, 2),
            'status' => $this->determineStatus($totalHours, $checkIn, $workStart)
        ];
    }

    protected function determineStatus(float $totalHours, Carbon $checkInTime, Carbon $workStartTime): string
    {
        $config = config('attendance.status_rules');
        $standardHours = config('attendance.working_hours.standard_hours', 8);

        if ($totalHours < ($config['absent']['max_hours'] ?? 2)) {
            return 'absent';
        }

        if ($totalHours < ($config['half_day']['max_hours'] ?? 4)) {
            return 'half_day';
        }

        $lateThreshold = $workStartTime->copy()->addMinutes($config['late']['grace_period'] ?? 30);
        if ($checkInTime->gt($lateThreshold)) {
            return 'late';
        }

        if ($totalHours > $standardHours) {
            return 'overtime';
        }

        return 'present';
    }

    private function getWorkTime(string $type): Carbon
    {
        $time = config("attendance.working_hours.{$type}");
        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $time);
    }

    protected function logAttendanceEvent(string $eventType, array $details): void
    {
        Log::channel('attendance')->info($eventType, array_merge($details, [
            'attendance_id' => $this->id,
            'timestamp' => now()->toDateTimeString()
        ]));
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('check_in')
            ->whereNull('check_out');
    }
}
