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

    public static function downloadSampleData(): Response
    {
        $headers = [
            'employee_code',
            'first_name',
            'last_name',
            'middle_name',
            'gender',
            'birthdate',
            'contract_type',
            'appointment_date',
            'job_title',
            'branch',
            'department',
            'salary',
            'email'
        ];

        $sampleData = [
            [
                'EMP001',
                'John',
                'Smith',
                'Robert',
                'male',
                '15/01/1990',
                'permanent',
                '01/06/2023',
                'Software Engineer',
                'Main Office',
                'Engineering',
                '75000',
                'john.smith@company.com'
            ],
            [
                'EMP002',
                'Sarah',
                'Johnson',
                'Marie',
                'female',
                '22/03/1988',
                'contract',
                '15/07/2023',
                'Marketing Manager',
                'Downtown',
                'Marketing',
                '65000',
                'sarah.j@company.com'
            ],
            [
                'EMP003',
                'Michael',
                'Williams',
                'David',
                'male',
                '10/12/1992',
                'probation',
                '01/08/2023',
                'Sales Representative',
                'North Branch',
                'Sales',
                '45000',
                'm.williams@company.com'
            ]
        ];

        $output = fopen('php://temp', 'w+');
        fputcsv($output, $headers);
        foreach ($sampleData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="sample_employees.csv"');
    }

    public function resolveRecord(): ?Employee
    {
        return new Employee();
    }

    protected function beforeCreate(): void
    {
        DB::transaction(function () {
            // Format dates
            try {
                $this->data['birthdate'] = $this->formatDate($this->data['birthdate']);
                $this->data['appointment_date'] = $this->formatDate($this->data['appointment_date']);
            } catch (\Exception $e) {
                throw new \Exception("Invalid date format: " . $e->getMessage());
            }

            // Transform gender to lowercase
            $this->data['gender'] = strtolower($this->data['gender']);

            // Transform contract type
            $this->data['contract_type'] = match (strtolower($this->data['contract_type'])) {
                'fixed term contract', 'contract' => 'contract',
                'permanent' => 'permanent',
                'probation' => 'probation',
                default => 'undefined',
            };

            // Set default values
            $this->data['application_status'] = 'active';
            $this->data['employment_status'] = 'ACTIVE';
            $this->data['terms_of_employment'] = 'full-time';

            // Step 1: Create or find department
            $department = Department::firstOrCreate(
                ['name' => $this->data['department']],
                [
                    'code' => strtoupper(substr($this->data['department'], 0, 3)),
                    'is_active' => true,
                    'current_headcount' => 0,
                    'max_headcount' => 999
                ]
            );

            // Step 2: Create or find job title
            $jobTitle = JobTitle::firstOrCreate(
                [
                    'name' => $this->data['job_title'],
                    'department_id' => $department->id
                ],
                [
                    'is_active' => true,
                    'description' => "Position of {$this->data['job_title']}"
                ]
            );

            // Step 3: Prepare employee data
            $this->data['department_id'] = $department->id;
            $this->data['job_title_id'] = $jobTitle->id;

            // Handle salary
            $this->data['salary'] = (float) str_replace(',', '', $this->data['salary']);
            $this->data['net_salary'] = $this->data['salary'];
        });
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
}
