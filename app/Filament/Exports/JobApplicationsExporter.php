<?php

namespace App\Filament\Exports;

use App\Models\JobApplication;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class JobApplicationsExporter extends Exporter
{
    protected static ?string $model = JobApplication::class;


    public static function getColumns(): array
    {
        return [
            ExportColumn::make('jobPosting.title')
                ->label('Position'),

            ExportColumn::make('jobPosting.department.name')
                ->label('Department'),

            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),

            ExportColumn::make('current_position')
                ->label('Current Position'),

            ExportColumn::make('current_company')
                ->label('Current Company'),

            ExportColumn::make('experience_years')
                ->label('Years of Experience'),

            ExportColumn::make('education_level')
                ->label('Education'),

            ExportColumn::make('expected_salary')
                ->label('Expected Salary'),

            ExportColumn::make('status'),

            ExportColumn::make('created_at')
                ->label('Application Date'),

            ExportColumn::make('reviewer.name')
                ->label('Reviewed By'),

            ExportColumn::make('reviewed_at')
                ->label('Review Date'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your job applications export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
