<?php

namespace App\Filament\Imports;

use App\Models\Employee;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('employee_code')
                ->label('Employee Code')

                ->rules(['unique:employees,employee_code']),
            ImportColumn::make('first_name')
                ->label('First Name'),

            ImportColumn::make('last_name')
                ->label('Last Name'),
            ImportColumn::make('middle_name')
                ->label('Middle Name'),
            ImportColumn::make('gender')
                ->label('Gender')
                ->rules(['in:male,female,other']),
            ImportColumn::make('birthdate')
                ->label('Birth Date')
                ->type('date'),
            ImportColumn::make('job_title')
                ->label('Job Title'),
            ImportColumn::make('department_id')
                ->label('Department')
                ->relationship(),
            ImportColumn::make('employment_status')
                ->label('Status')
                ->rules(['in:active,on_leave,suspended,terminated']),
            ImportColumn::make('contract_type')
                ->label('Contract Type')
                ->rules(['in:permanent,contract,temporary,intern']),
            ImportColumn::make('appointment_date')
                ->label('Appointment Date')
                ->type('date'),
            ImportColumn::make('salary')
                ->label('Salary')
                ->numeric(),
        ];
    }

    public function resolveRecord(): ?Employee
    {
        // return Employee::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Employee();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;

        if ($failed > 0) {
            return "Import completed with {$failed} failures. {$successful} employees were imported successfully.";
        }

        return "Successfully imported {$successful} employees.";
    }
}
