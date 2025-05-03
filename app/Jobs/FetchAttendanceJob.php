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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

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

            // Preload all employees with external_employee_id, using pluck for memory efficiency, log duplicates separately if needed
            $employeesByExternalId = Employee::whereNotNull('external_employee_id')
                ->select('id', 'employee_code', 'external_employee_id', 'first_name', 'last_name')
                ->get()
                ->unique('external_employee_id')
                ->keyBy('external_employee_id');

            Log::info('Loaded employees by external ID', [
                'keys' => $employeesByExternalId->keys()
            ]);

            // Stream paginated API data using a generator for reduced memory
            foreach ($this->attendanceApiPages($biotimeService) as $pageIdx => $attendanceRecords) {
                if ($pageIdx === 0) {
                    Log::info('Processing page 1 of employee data', [
                        'records_count' => count($attendanceRecords)
                    ]);
                } else {
                    Log::info("Processing page " . ($pageIdx+1) . " of employee data", [
                        'records_count' => count($attendanceRecords)
                    ]);
                }
                $this->processAttendanceData($attendanceRecords, $employeesByExternalId);
            }
            Log::info('Attendance data fetched and processed successfully', [
                'date_range' => "{$this->startDate} to {$this->endDate}"
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetch attendance job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Generator to stream paginated attendance API data in chunks.
     * @param ZKBiotimeService $biotimeService
     * @return \Generator
     */
    protected function attendanceApiPages(ZKBiotimeService $biotimeService): \Generator
    {
        $page = 1;
        $pageSize = 400;
        $totalPages = null;
        do {
            $params = [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'departments' => $this->departments,
                'page' => $page,
                'page_size' => $pageSize,
            ];
            $response = $biotimeService->getMonthlyPunchReport($params);
            if (!$response) {
                Log::warning("No response from API for page $page");
                break;
            }
            $attendanceRecords = null;
            if (isset($response['data']['data'])) {
                $attendanceRecords = $response['data']['data'];
            } elseif (isset($response['data']) && is_array($response['data'])) {
                $attendanceRecords = $response['data'];
            }
            if (!$attendanceRecords) {
                Log::warning("Could not locate attendance data in API response for page $page");
                break;
            }
            if ($totalPages === null) {
                if (isset($response['data']['count']) && $response['data']['count'] > 0) {
                    $totalPages = ceil($response['data']['count'] / $pageSize);
                } else {
                    $totalPages = 1;
                }
            }
            yield $attendanceRecords;
            $page++;
        } while ($totalPages === null || $page <= $totalPages);
    }


    /**
     * Process attendance data from API response
     *
     * @param array $attendanceRecords
     * @param \Illuminate\Support\Collection $employeesByExternalId
     */

    protected function processAttendanceData(array $attendanceRecords, $employeesByExternalId): void
    {
        $processedCount = 0;
        $skippedCount = 0;
        $employeeNotFoundCount = 0;
        $emptyTimeEntryCount = 0;
        $debugMode = config('app.debug');

        foreach ($attendanceRecords as $record) {
            try {
                // Only keep fields needed for mapping and insertion
                $employeeCode = $record['emp_code'] ?? null;
                if (!$employeeCode || !isset($employeesByExternalId[$employeeCode])) {
                    $employeeNotFoundCount++;
                    $skippedCount++;
                    continue;
                }
                $employee = $employeesByExternalId[$employeeCode];
                // Only log in debug mode
                if ($debugMode) {
                    Log::debug('Matched employee for record', [
                        'emp_code_from_api' => $employeeCode,
                        'employee_id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'external_employee_id' => $employee->external_employee_id,
                        'full_name' => $employee->first_name . ' ' . $employee->last_name
                    ]);
                }
                $entriesProcessed = false;
                $days = $record['days'] ?? [];
                $dayCount = count($days);
                $emptyDayCount = 0;
                foreach ($days as $date => $timeEntry) {
                    if (empty($timeEntry)) {
                        $emptyDayCount++;
                        $emptyTimeEntryCount++;
                        continue;
                    }
                    $this->processAttendanceEntry($employee, $date, $timeEntry, $record);
                    $entriesProcessed = true;
                }
                if ($dayCount > 0 && $emptyDayCount === $dayCount) {
                    Log::info('Employee has all empty time entries', [
                        'employee_id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'external_id' => $employee->external_employee_id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'day_count' => $dayCount
                    ]);
                }
                if ($entriesProcessed) {
                    $processedCount++;
                } else {
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $skippedCount++;
                Log::error('Error processing attendance record', [
                    'record' => json_encode($record),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        Log::info('Attendance processing summary', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'employee_not_found' => $employeeNotFoundCount,
            'empty_time_entries' => $emptyTimeEntryCount,
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
            $totalMinutes = $checkOutDateTime->diffInMinutes($checkInDateTime,true);
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
            DB::transaction(function () use ($employee, $dateObj, $checkInDateTime, $checkOutDateTime, $totalHours, $record) {
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
                            'notes'=> "1. Employee was late for {$record['minutes_late']} minutes.". ", 2. Employee left early for {$record['early_timeout']} minutes.",
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

        $totalHours = $checkOut->diffInMinutes($checkIn,true) / 60;

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
            return 'overtime';
        }

        return 'present';
    }
}
