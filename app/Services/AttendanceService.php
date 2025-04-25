<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AttendanceService
{
    /**
     * Import attendance data from Excel file
     *
     * @param UploadedFile $file Excel file to import
     * @param int|null $year Year for the attendance records (defaults to current year)
     * @param int|null $month Month for the attendance records (defaults to current month)
     * @return Collection Collection of created/updated attendance records
     */
    public function importExcelAttendance(UploadedFile $file, ?int $year = null, ?int $month = null): Collection
    {
        // Use current year/month if not specified
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();

        // Get column headers (assuming they're in row 2)
        $headers = [];
        foreach ($worksheet->getRowIterator(2, 2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $columnIndex = 0;
            foreach ($cellIterator as $cell) {
                $headers[$columnIndex] = $cell->getValue();
                $columnIndex++;
            }
            break; // We only need the first row for headers
        }

        // Parse data rows
        $data = [];
        $startRow = 3; // Data starts from row 3

        foreach ($worksheet->getRowIterator($startRow) as $row) {
            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $columnIndex = 0;
            foreach ($cellIterator as $cell) {
                $headerKey = $headers[$columnIndex] ?? "column_$columnIndex";
                $rowData[$headerKey] = $cell->getValue();
                $columnIndex++;
            }

            // Skip empty rows or rows without employee ID
            if (!empty($rowData['Employee ID'])) {
                $data[] = $rowData;
            }
        }

        // Import the attendance data
        try {
            $result = Attendance::importFromExcel($data, $year, $month);
            Log::info("Successfully imported {$result->count()} attendance records");
            return $result;
        } catch (\Exception $e) {
            Log::error("Error importing attendance data: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate monthly attendance report for all employees or specific department
     *
     * @param int $year Year for the report
     * @param int $month Month for the report
     * @param string|null $department Department to filter by (optional)
     * @return Collection Collection of employee attendance data
     */
    public function generateMonthlyReport(int $year, int $month, ?string $department = null): Collection
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $query = Employee::with(['attendances' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        }]);

        if ($department) {
            $query->where('department', $department);
        }

        $employees = $query->get();

        return $employees->map(function($employee) use ($startDate, $endDate) {
            $daysInMonth = $endDate->day;
            $attendanceByDay = [];

            // Initialize empty attendance for each day
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = $startDate->copy()->addDays($day - 1);
                $attendanceByDay[$day] = [
                    'date' => $currentDate->toDateString(),
                    'check_in' => null,
                    'check_out' => null,
                    'total_hours' => 0,
                    'status' => $this->determineDayType($currentDate)
                ];
            }

            // Fill in actual attendance data
            foreach ($employee->attendances as $attendance) {
                $day = $attendance->date->day;
                $attendanceByDay[$day] = [
                    'date' => $attendance->date->toDateString(),
                    'check_in' => $attendance->check_in ? $attendance->check_in->format('H:i') : null,
                    'check_out' => $attendance->check_out ? $attendance->check_out->format('H:i') : null,
                    'total_hours' => $attendance->total_hours,
                    'status' => $attendance->status
                ];
            }

            // Calculate summary statistics
            $totalRegularHours = $employee->attendances->sum('standard_hours');
            $totalOvertimeHours = $employee->attendances->sum('overtime_hours');
            $totalAbsentDays = $employee->attendances->where('status', 'absent')->count();
            $totalLateDays = $employee->attendances->where('status', 'late')->count();

            return [
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'department' => $employee->department,
                'daily_attendance' => $attendanceByDay,
                'total_regular_hours' => $totalRegularHours,
                'total_overtime_hours' => $totalOvertimeHours,
                'total_absent_days' => $totalAbsentDays,
                'total_late_days' => $totalLateDays
            ];
        });
    }

    /**
     * Export monthly attendance data to Excel
     *
     * @param int $year Year for the report
     * @param int $month Month for the report
     * @param string|null $department Department to filter by (optional)
     * @return Spreadsheet PHPSpreadsheet object ready for export
     */
    public function exportMonthlyAttendance(int $year, int $month, ?string $department = null): Spreadsheet
    {
        $reportData = $this->generateMonthlyReport($year, $month, $department);
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
        $title = "Monthly Check-in&out - $monthName $year";
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:AF1');

        // Set headers
        $sheet->setCellValue('A2', 'Employee ID');
        $sheet->setCellValue('B2', 'First Name');
        $sheet->setCellValue('C2', 'Department');

        // Date columns
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $columnIndex = 3 + $day - 1;
            $column = $this->getExcelColumn($columnIndex);
            $dayStr = $day < 10 ? "0$day" : "$day";
            $sheet->setCellValue("{$column}2", $dayStr);
        }

        // Summary columns
        $nextCol = 3 + $daysInMonth;
        $sheet->setCellValue($this->getExcelColumn($nextCol++) . '2', 'Regular(H)');
        $sheet->setCellValue($this->getExcelColumn($nextCol++) . '2', 'Late In(M)');
        $sheet->setCellValue($this->getExcelColumn($nextCol++) . '2', 'Early Out(M)');
        $sheet->setCellValue($this->getExcelColumn($nextCol++) . '2', 'Absence(H)');
        $sheet->setCellValue($this->getExcelColumn($nextCol++) . '2', 'Normal OT(H)');
        $sheet->setCellValue($this->getExcelColumn($nextCol++) . '2', 'Weekend OT(H)');
        $sheet->setCellValue($this->getExcelColumn($nextCol++) . '2', 'Holiday OT(H)');

        // Fill data
        $rowIndex = 3;
        foreach ($reportData as $employeeData) {
            $sheet->setCellValue("A$rowIndex", $employeeData['employee_id']);
            $sheet->setCellValue("B$rowIndex", $employeeData['name']);
            $sheet->setCellValue("C$rowIndex", $employeeData['department']);

            // Daily attendance
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $attendance = $employeeData['daily_attendance'][$day] ?? null;
                $columnIndex = 3 + $day - 1;
                $column = $this->getExcelColumn($columnIndex);

                if ($attendance && $attendance['check_in'] && $attendance['check_out']) {
                    $value = $attendance['check_in'] . '-' . $attendance['check_out'];
                    $sheet->setCellValue("$column$rowIndex", $value);
                }
            }

            // Summary data
            $nextCol = 3 + $daysInMonth;

            // Get all attendances for this employee in the given month
            $employee = Employee::where('employee_id', $employeeData['employee_id'])->first();
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            // Calculate metrics
            $regularHours = $attendances->sum('standard_hours');
            $lateMinutes = $attendances->sum('late_minutes');
            $earlyOutMinutes = $attendances->sum('early_out_minutes');
            $absenceHours = $attendances->sum('absence_hours');
            $normalOTHours = $attendances->sum('normal_overtime_hours');
            $weekendOTHours = $attendances->sum('weekend_overtime_hours');
            $holidayOTHours = $attendances->sum('holiday_overtime_hours');

            $sheet->setCellValue($this->getExcelColumn($nextCol++) . $rowIndex, number_format($regularHours, 1));
            $sheet->setCellValue($this->getExcelColumn($nextCol++) . $rowIndex, number_format($lateMinutes, 1));
            $sheet->setCellValue($this->getExcelColumn($nextCol++) . $rowIndex, number_format($earlyOutMinutes, 1));
            $sheet->setCellValue($this->getExcelColumn($nextCol++) . $rowIndex, number_format($absenceHours, 1));
            $sheet->setCellValue($this->getExcelColumn($nextCol++) . $rowIndex, number_format($normalOTHours, 1));
            $sheet->setCellValue($this->getExcelColumn($nextCol++) . $rowIndex, number_format($weekendOTHours, 1));
            $sheet->setCellValue($this->getExcelColumn($nextCol++) . $rowIndex, number_format($holidayOTHours, 1));

            $rowIndex++;
        }

        // Auto-size columns
        foreach (range('A', $this->getExcelColumn($nextCol - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Determine if a day is a workday, weekend, or holiday
     *
     * @param Carbon $date
     * @return string 'workday', 'weekend', or 'holiday'
     */
    protected function determineDayType(Carbon $date): string
    {
        // Check if it's a weekend
        if ($date->isWeekend()) {
            return 'weekend';
        }

        // Check if it's a holiday (implementation depends on holiday configuration)
        $holidays = config('attendance.holidays', []);
        $dateString = $date->format('Y-m-d');

        if (in_array($dateString, $holidays)) {
            return 'holiday';
        }

        return 'workday';
    }

    /**
     * Convert column index to Excel column letter (A, B, C, ... AA, AB, etc.)
     *
     * @param int $index Column index (0-based)
     * @return string Excel column letter
     */
    protected function getExcelColumn(int $index): string
    {
        $base = ord('A');
        $letters = '';

        while ($index >= 0) {
            $letters = chr($base + ($index % 26)) . $letters;
            $index = floor($index / 26) - 1;
        }

        return $letters;
    }

    /**
     * Process check-in for an employee
     *
     * @param string $employeeId Employee ID
     * @param Carbon|null $checkInTime Check-in time (defaults to now)
     * @return Attendance
     */
    public function processCheckIn(string $employeeId, ?Carbon $checkInTime = null): Attendance
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();
        return Attendance::checkIn($employee, $checkInTime ?: now());
    }

    /**
     * Process check-out for an employee
     *
     * @param string $employeeId Employee ID
     * @param Carbon|null $checkOutTime Check-out time (defaults to now)
     * @return Attendance
     */
    public function processCheckOut(string $employeeId, ?Carbon $checkOutTime = null): Attendance
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->firstOrFail();

        return $attendance->checkOut($checkOutTime ?: now());
    }

    /**
     * Get active (checked-in but not checked-out) employees
     *
     * @return Collection
     */
    public function getActiveEmployees(): Collection
    {
        return Employee::whereHas('attendances', function($query) {
            $query->whereDate('date', now()->toDateString())
                ->whereNotNull('check_in')
                ->whereNull('check_out');
        })->with(['attendances' => function($query) {
            $query->whereDate('date', now()->toDateString())
                ->whereNotNull('check_in')
                ->whereNull('check_out');
        }])->get();
    }
}
