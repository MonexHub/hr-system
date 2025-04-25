<?php

namespace App\Console\Commands;

// app/Console/Commands/ReconcileEmployeeCodes.php

use App\Models\Employee;
use App\Models\Department;
use App\Services\ZKBiotimeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileEmployeeCodes extends Command
{
    protected $signature = 'employees:reconcile';
    protected $description = 'Map API emp_codes to existing employees';
    public function handle(): void
    {
        $zkService = new ZKBiotimeService();
        $apiEmployees = $zkService->fetchAllEmployees();
        $unmatched = [];

        foreach ($apiEmployees as $apiEmployee) {
            // Step 1: Normalize data to handle inconsistencies
            $firstName = trim(strtolower($apiEmployee['first_name'] ?? ''));
            $lastName = trim(strtolower($apiEmployee['last_name'] ?? ''));
            $deptCode = $apiEmployee['dept_code'] ?? 'UNASSIGNED'; // Fallback value

            // Step 2: Find or create the department
            $department = Department::firstOrCreate(
                ['code' => $deptCode],
                ['name' => $apiEmployee['dept_name'] ?? 'Unassigned']
            );

            // Step 3: Flexible employee matching
            $employee = Employee::whereRaw('LOWER(first_name) = ?', [$firstName])
                ->whereRaw('LOWER(last_name) = ?', [$lastName])
                ->where('department_id', $department->id)
                ->first();

            // Fallback: Search by emp_code (case-insensitive)
            if (!$employee) {
                $employee = Employee::whereRaw('LOWER(employee_code) = LOWER(?)', [$apiEmployee['emp_code']])
                    ->first();

            }
            // Fallback: Search by department and name

            // Step 4: Update or log unmatched
            if ($employee) {
                $employee->update(['employee_code' => $apiEmployee['emp_code']]);
                $this->info("Updated: {$employee->id} => {$apiEmployee['emp_code']}");
            } else {
                $unmatched[] = $apiEmployee['emp_code'];
                Log::warning('Unmatched employee', [
                    'emp_code' => $apiEmployee['emp_code'],
                    'name' => "{$firstName} {$lastName}",
                    'department' => $department->name
                ]);
            }
        }

        // Step 5: Generate a report for manual review
        if (!empty($unmatched)) {
            $this->warn("Unmatched emp_codes: " . implode(', ', $unmatched));
            $this->generateCSVReport($unmatched);
        }
    }

    protected function generateCSVReport(array $unmatchedCodes): void
    {
        $filename = storage_path("logs/unmatched_employees_" . now()->format('Ymd_His') . ".csv");
        $handle = fopen($filename, 'w');
        fputcsv($handle, ['emp_code', 'first_name', 'last_name', 'department']);

        foreach ($unmatchedCodes as $code) {
            $apiEmployee = collect($apiEmployees)->firstWhere('emp_code', $code);
            fputcsv($handle, [
                $code,
                $apiEmployee['first_name'] ?? 'N/A',
                $apiEmployee['last_name'] ?? 'N/A',
                $apiEmployee['dept_name'] ?? 'N/A'
            ]);
        }

        fclose($handle);
        $this->info("Report generated: $filename");
    }

}
