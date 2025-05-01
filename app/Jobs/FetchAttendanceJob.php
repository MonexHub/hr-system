<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Services\ZKBiotimeService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startDate;
    protected $endDate;
    protected $departments;

    /**
     * Create a new job instance.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $departments
     */
    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $departments = null)
    {
        $this->startDate = $startDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $endDate ?? now()->format('Y-m-d');
        $this->departments = $departments ?? '1,3,4,5,6,7,8,9,10,11,12,13'; // Default departments
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting attendance fetch job', [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'departments' => $this->departments
            ]);

            $biotimeService = app(ZKBiotimeService::class);
            $response = $biotimeService->getMonthlyPunchReport([
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'departments' => $this->departments,
                'page_size' => 100, // Fetch more records per page
            ]);

            // Improved response validation
            if (!$response) {
                Log::error('No response from API');
                return;
            }

            // Extract the actual data array where employee records are
            $attendanceRecords = null;

            if (isset($response['data']['data'])) {
                // New format with nested data.data
                $attendanceRecords = $response['data']['data'];
            } elseif (isset($response['data']) && is_array($response['data'])) {
                // Directly in data array
                $attendanceRecords = $response['data'];
            }

            if (!$attendanceRecords) {
                Log::error('Could not locate attendance data in API response', [
                    'response_keys' => is_array($response) ? array_keys($response) : gettype($response)
                ]);
                return;
            }

            // Check if we actually got employee records
            if (empty($attendanceRecords)) {
                Log::warning('API returned zero employee records');
                return;
            }

            // Get pagination info
            $totalPages = 1;
            $currentPage = 1;

            if (isset($response['data']['count']) && isset($response['data']['next'])) {
                $totalPages = ceil($response['data']['count'] / 100);
            }

            Log::info('Processing page 1 of employee data', [
                'total_pages' => $totalPages,
                'records_count' => count($attendanceRecords)
            ]);

            $this->processAttendanceData($attendanceRecords);

            // Process all pages
            while ($currentPage < $totalPages) {
                $currentPage++;

                Log::info("Fetching page {$currentPage} of employee data");

                $response = $biotimeService->getMonthlyPunchReport([
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'departments' => $this->departments,
                    'page' => $currentPage,
                    'page_size' => 100,
                ]);

                if (!$response) {
                    Log::warning("Failed to fetch page {$currentPage}");
                    continue;
                }

                // Extract the actual data array again for this page
                $attendanceRecords = null;

                if (isset($response['data']['data'])) {
                    $attendanceRecords = $response['data']['data'];
                } elseif (isset($response['data']) && is_array($response['data'])) {
                    $attendanceRecords = $response['data'];
                }

                if (!$attendanceRecords) {
                    Log::warning("Could not locate attendance data in API response for page {$currentPage}");
                    continue;
                }

                $this->processAttendanceData($attendanceRecords);
            }

            Log::info('Attendance data fetched and processed successfully', [
                'date_range' => "{$this->startDate} to {$this->endDate}",
                'total_pages' => $totalPages
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetch attendance job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process attendance data from API response
     *
     * @param array $attendanceRecords
     */
    protected function processAttendanceData(array $attendanceRecords): void
    {
        $processedCount = 0;
        $skippedCount = 0;

        foreach ($attendanceRecords as $record) {
            try {
                $employeeCode = $record['emp_code'] ?? null;

                if (!$employeeCode) {
                    $skippedCount++;
                    continue;
                }

                // Find the employee by employee_code
                $employee = Employee::where('external_employee_id', $employeeCode)->first();
                // If employee not found, try to create a new one
                if (!$employee) {
                    $employee = $this->createEmployeeFromRecord($record);
                    if (!$employee) {
                        $skippedCount++;
                        continue;
                    }
                }

                $entriesProcessed = false;

                // Process daily attendance records
                foreach ($record['days'] as $date => $timeEntry) {
                    // Skip empty time entries but log them for debugging
                    if (empty($timeEntry)) {
                        Log::debug('Skipping empty time entry', [
                            'employee' => $employeeCode,
                            'date' => $date
                        ]);
                        continue;
                    }

                    $this->processAttendanceEntry($employee, $date, $timeEntry, $record);
                    $entriesProcessed = true;
                }

                if ($entriesProcessed) {
                    $processedCount++;
                } else {
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $skippedCount++;
                Log::error('Error processing attendance record', [
                    'record' => $record,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Attendance processing summary', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total' => count($attendanceRecords)
        ]);
    }

    /**
     * Create a new employee from the API record
     *
     * @param array $record
     * @return Employee|null
     */

    protected function createEmployeeFromRecord(array $record): ?Employee
    {
        try {
            // Find or create department by name
            $department = Department::firstOrCreate(
                ['name' => $record['dept_name']],
                ['description' => 'Auto-created from API']
            );

            // Find or create job title by name within department
            $jobTitle = JobTitle::firstOrCreate(
                [
                    'name' => $record['position_name'],
                    'department_id' => $department->id
                ],
                [
                    'description' => 'Auto-created from API',
                    'is_active' => true,
                    'net_salary_min' => 0,
                    'net_salary_max' => 0
                ]
            );

            $employeeData = [
                'employee_code' => $record['emp_code'],
                'external_employee_id' => $record['emp_code'],
                'original_employee_code' => $record['emp_code'],
                'first_name' => $record['first_name'] ?? '',
                'last_name' => $record['last_name'] ?? '',
                'gender' => $this->mapGender($record['gender'] ?? null),
                'department_id' => $department->id,
                'job_title_id' => $jobTitle->id,
                'employment_status' => 'active',
                'contract_type' => 'permanent',
                'appointment_date' => now(),
                // Add other required fields with defaults
                'birthdate' => '1970-01-01',
                'salary' => 0,
                'branch' => 'unassigned'
            ];

            return Employee::create($employeeData);
        } catch (\Exception $e) {
            Log::error('Failed to create employee', [
                'error' => $e->getMessage(),
                'record' => $record
            ]);
            return null;
        }
    }

    private function mapGender(?string $gender): string
    {
        return match (strtoupper($gender)) {
            'M' => 'male',
            'F' => 'female',
            default => 'other',
        };
    }

    /**
     * Process single attendance entry for a specific date
     *
     * @param Employee $employee
     * @param string $date
     * @param string $timeEntry
     * @param array $record
     */
    protected function processAttendanceEntry(Employee $employee, string $date, string $timeEntry, array $record): void
    {
        try {
            // Log detailed info about the entry being processed
            Log::debug('Processing attendance entry', [
                'employee' => $employee->employee_code,
                'date' => $date,
                'time_entry' => $timeEntry,
                'time_entry_length' => strlen($timeEntry)
            ]);

            // Check if the time entry follows the expected format (e.g., "08:30-17:30")
            if (!preg_match('/^\d{2}:\d{2}-\d{2}:\d{2}$/', $timeEntry)) {
                Log::warning('Invalid time entry format', [
                    'employee' => $employee->employee_code,
                    'date' => $date,
                    'time_entry' => $timeEntry
                ]);
                return;
            }

            [$checkInTime, $checkOutTime] = explode('-', $timeEntry);

            $dateObj = Carbon::parse($date);
            $checkInDateTime = Carbon::parse("$date $checkInTime");
            $checkOutDateTime = Carbon::parse("$date $checkOutTime");

            // Handle overnight shifts
            if ($checkOutDateTime->lt($checkInDateTime)) {
                $checkOutDateTime->addDay();
            }

            // Calculate metrics more explicitly
            $totalMinutes = $checkOutDateTime->diffInMinutes($checkInDateTime);
            $totalHours = $totalMinutes / 60;

            Log::debug('Calculated attendance metrics', [
                'employee' => $employee->employee_code,
                'date' => $date,
                'check_in' => $checkInDateTime->toDateTimeString(),
                'check_out' => $checkOutDateTime->toDateTimeString(),
                'total_minutes' => $totalMinutes,
                'total_hours' => $totalHours
            ]);

            // Create or update attendance record in a transaction to prevent partial updates
            \DB::transaction(function () use ($employee, $dateObj, $checkInDateTime, $checkOutDateTime, $totalHours, $record) {
                $attendance = Attendance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $dateObj->toDateString(),
                    ],
                    [
                        'check_in' => $checkInDateTime,
                        'check_out' => $checkOutDateTime,
                        'total_hours' => $totalHours,
                        'standard_hours' => (float)($record['regular_hours'] ?? 0),
                        'overtime_hours' => (float)(($record['normal_overtime_hours'] ?? 0) +
                            ($record['weekend_overtime_hours'] ?? 0) +
                            ($record['holiday_overtime_hours'] ?? 0)),
                        'late_minutes' => (float)($record['minutes_late'] ?? 0),
                        'early_out_minutes' => (float)($record['early_timeout'] ?? 0),
                        'absence_hours' => (float)($record['absent_hours'] ?? 0),
                        'normal_overtime_hours' => (float)($record['normal_overtime_hours'] ?? 0),
                        'weekend_overtime_hours' => (float)($record['weekend_overtime_hours'] ?? 0),
                        'holiday_overtime_hours' => (float)($record['holiday_overtime_hours'] ?? 0),
                        'ot1_hours' => (float)($record['overtime_1'] ?? 0),
                        'ot2_hours' => (float)($record['overtime_2'] ?? 0),
                        'ot3_hours' => (float)($record['overtime_3'] ?? 0),
                        'annual_leave_hours' => (float)($record['annual_leave_hours'] ?? 0),
                        'sick_leave_hours' => (float)($record['sick_leave_hours'] ?? 0),
                        'casual_leave_hours' => (float)($record['casual_leave_hours'] ?? 0),
                        'maternity_leave_hours' => (float)($record['maternity_leave_hours'] ?? 0),
                        'compassionate_leave_hours' => (float)($record['compensatory_leave_hours'] ?? 0),
                        'business_trip_hours' => (float)($record['business_trip_hours'] ?? 0),
                        'compensatory_hours' => (float)($record['compensatory_hours'] ?? 0),
                        'compensatory_leave_hours' => (float)($record['compensatory_leave_hours'] ?? 0),
                        'status' => $this->determineStatus(
                            (float)($record['minutes_late'] ?? 0),
                            (float)($record['early_timeout'] ?? 0),
                            (float)($record['absent_hours'] ?? 0),
                            $checkInDateTime,
                            $checkOutDateTime
                        ),
                    ]
                );

                Log::info('Attendance record saved', [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'date' => $dateObj->toDateString(),
                    'attendance_id' => $attendance->id
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to process attendance entry', [
                'employee' => $employee->employee_code,
                'date' => $date,
                'time_entry' => $timeEntry,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Determine attendance status based on metrics
     *
     * @param float $lateMinutes
     * @param float $earlyOutMinutes
     * @param float $absenceHours
     * @param Carbon $checkIn
     * @param Carbon $checkOut
     * @return string
     */
    protected function determineStatus(float  $lateMinutes, float $earlyOutMinutes, float $absenceHours,
                                       Carbon $checkIn, Carbon $checkOut): string
    {
        if ($absenceHours > 0) {
            return 'absent';
        }

        $totalHours = $checkOut->diffInMinutes($checkIn) / 60;

        if ($totalHours < 4) {
            return 'half_day';
        }

        if ($lateMinutes > 0) {
            return 'late';
        }

        if ($earlyOutMinutes > 0) {
            return 'early_departure';
        }

        if ($totalHours > 8) {
            return 'overtime';
        }

        if ($checkIn->isWeekend()) {
            return 'weekend';
        }

        return 'present';
    }
}
