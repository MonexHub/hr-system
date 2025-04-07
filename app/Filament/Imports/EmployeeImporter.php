<?php

namespace App\Filament\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('employee_code')
                ->rules(['required', 'string', 'max:255', 'unique:employees,employee_code'])
                ->requiredMapping(),

            ImportColumn::make('first_name')
                ->rules(['required', 'string', 'max:255'])
                ->requiredMapping(),

            ImportColumn::make('last_name')
                ->rules(['required', 'string', 'max:255'])
                ->requiredMapping(),

            ImportColumn::make('middle_name')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('gender')
                ->rules(['required'])
                ->requiredMapping(),

            ImportColumn::make('birthdate')
                ->rules(['required', 'string', 'max:255'])
                ->requiredMapping(),

            ImportColumn::make('contract_type')
                ->rules(['required', 'in:permanent,contract,probation,undefined'])
                ->requiredMapping(),

            ImportColumn::make('appointment_date')
                ->rules(['required', 'string', 'max:255'])
                ->requiredMapping(),

            ImportColumn::make('job_title')
                ->rules(['required', 'string', 'max:255'])
                ->requiredMapping(),

            ImportColumn::make('branch')
                ->rules(['required', 'string', 'max:255'])
                ->requiredMapping(),

            ImportColumn::make('department')
                ->rules(['required', 'string', 'max:255'])
                ->requiredMapping(),

            ImportColumn::make('salary')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0'])
                ->requiredMapping(),

            ImportColumn::make('email')
                ->rules(['required', 'email', 'max:255', 'unique:employees,email'])
                ->requiredMapping(),
        ];
    }

    public function mutateBeforeCreate(array $data): array
    {
        Log::info('🔥 mutateBeforeCreate() is being called!', $data);

        try {
            // Format dates
            $data['birthdate'] = $this->formatDate($data['birthdate']);
            $data['appointment_date'] = $this->formatDate($data['appointment_date']);

            // Transform gender to lowercase
            $data['gender'] = strtolower($data['gender']);

            // Transform contract type
            $data['contract_type'] = match (strtolower($data['contract_type'])) {
                'fixed term contract', 'contract' => 'contract',
                'permanent' => 'permanent',
                'probation' => 'probation',
                default => 'undefined',
            };

            // Set default values
            $data['application_status'] = 'active';
            $data['employment_status'] = 'ACTIVE';
            $data['terms_of_employment'] = 'full-time';

            // Ensure department and job title are correctly mapped
            $department = Department::firstOrCreate(
                ['name' => $data['department']],
                [
                    'code' => strtoupper(substr($data['department'], 0, 3)),
                    'is_active' => true,
                    'current_headcount' => 0,
                    'max_headcount' => 999
                ]
            );

            // First try to find existing job title by name only
            $jobTitle = JobTitle::where('name', $data['job_title'])->first();

            if (!$jobTitle) {
                // Create new job title if it doesn't exist
                $jobTitle = JobTitle::create([
                    'name' => $data['job_title'],
                    'department_id' => $department->id,
                    'is_active' => true,
                    'description' => "Position of {$data['job_title']}"
                ]);
            } else {
                // Update department if needed
                if ($jobTitle->department_id !== $department->id) {
                    $jobTitle->department_id = $department->id;
                    $jobTitle->save();
                }
            }

            // Remove original text fields
            unset($data['department']);
            unset($data['job_title']);

            // Assign relationship IDs
            $data['department_id'] = $department->id;
            $data['job_title_id'] = $jobTitle->id;

            // Convert salary
            $data['salary'] = (float) str_replace(',', '', $data['salary']);
            $data['net_salary'] = $data['salary'];

            Log::info('✅ Final Transformed Data:', $data);

        } catch (\Throwable $th) {
            Log::error('❌ Error in mutateBeforeCreate(): ' . $th->getMessage());
            throw new \Exception('Import failed: ' . $th->getMessage());
        }

        return $data;
    }

    function arrayToEmployeeModel(array $data): Employee
{
    Log::info('🔄 Transforming Array to Employee Model:', $data);

    // Convert salary to float
    $salary = isset($data['salary']) ? (float) str_replace(',', '', $data['salary']) : 0;

    // Create Employee model instance
    $employee = new Employee();

    // Assign transformed values
    $employee->employee_code = $data['employee_code'];
    $employee->first_name = $data['first_name'] ?? 'Unknown';
    $employee->last_name = $data['last_name'] ?? 'Unknown';
    $employee->middle_name = $data['middle_name'] ?? null;
    $employee->gender = strtolower($data['gender'] ?? 'unknown');
    $employee->birthdate = $data['birthdate'];
    $employee->contract_type = $data['contract_type'];
    $employee->appointment_date = $data['appointment_date'];
    $employee->branch = $data['branch'] ?? 'Unknown';
    $employee->department_id = $data['department_id'];
    $employee->job_title_id = $data['job_title_id'];
    $employee->salary = $salary;
    $employee->net_salary = $salary;
    $employee->email = $data['email'] ?? null;
    $employee->application_status = 'active';
    $employee->employment_status = 'ACTIVE';
    $employee->terms_of_employment = 'full-time';

 try {
    unset($employee->department);
    unset($employee->job_title);
 } catch (\Throwable $th) {
    Log::error('❌ Error in arrayToEmployeeModel(): ' . $th->getMessage());
    // throw new \Exception('Import failed: ' . $th->getMessage());
 }

    Log::info('✅ Employee Model Ready:', $employee->toArray());

    return $employee;
}

    protected function formatDate($date): string
    {
        $formats = [
            'd/m/Y',     // 14/01/1995
            'd-M-y',     // 14-Feb-21
            'd-M-Y',     // 14-Feb-2021
            'd/m/y',     // 14/01/95
            'Y-m-d',     // 1995-01-14
            'd-m-Y',     // 14-01-1995
        ];

        foreach ($formats as $format) {
            try {
                $carbonDate = Carbon::createFromFormat($format, $date);
                if ($carbonDate !== false) {
                    return $carbonDate->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        throw new \Exception("Could not parse date: {$date}");
    }

    protected function afterCreate(): void
    {
        Log::info('🔄 afterCreate() was triggered for employee:', ['id' => $this->record->id]);

        // Update department headcount
        $this->record->department?->incrementHeadcount();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): Employee
{
   $employeeRecord= $this->mutateBeforeCreate($this->data); // Ensure the function is called manually
    return $this->arrayToEmployeeModel($employeeRecord);
}
}
