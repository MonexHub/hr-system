<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('employee_code')
                ->label('Employee Code'),
            ExportColumn::make('first_name')
                ->label('First Name'),
            ExportColumn::make('last_name')
                ->label('Last Name'),
            ExportColumn::make('department.name')
                ->label('Department'),
            ExportColumn::make('job_title')
                ->label('Job Title'),
            ExportColumn::make('employment_status')
                ->label('Status'),
            ExportColumn::make('appointment_date')
                ->label('Appointment Date'),
            ExportColumn::make('salary')
                ->label('Salary'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your filament export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
