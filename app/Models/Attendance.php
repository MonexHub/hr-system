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
        'total_hours', 'standard_hours', 'overtime_hours', 'early_hours', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'float',
        'standard_hours' => 'float',
        'overtime_hours' => 'float',
        'early_hours' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
