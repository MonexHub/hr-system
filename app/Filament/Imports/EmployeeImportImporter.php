<?php

namespace App\Filament\Imports;

use App\Models\EmployeeImport;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class EmployeeImportImporter extends Importer
{
    protected static ?string $model = EmployeeImport::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('employee_code')
                ->rules(['max:255']),
            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('middle_name')
                ->rules(['max:255']),
            ImportColumn::make('gender'),
            ImportColumn::make('birthdate')
                ->rules(['string',
                    'regex:/\b(
            (?:0[1-9]|[12][0-9]|3[01])[-\/.](?:0[1-9]|1[0-2])[-\/.](?:\d{2}|\d{4}) |  # DD-MM-YYYY or DD-MM-YY
            (?:\d{2}|\d{4})[-\/.](?:0[1-9]|1[0-2])[-\/.](?:0[1-9]|[12][0-9]|3[01]) |  # YYYY-MM-DD or YY-MM-DD
            (?:0[1-9]|1[0-2])[-\/.](?:0[1-9]|[12][0-9]|3[01])[-\/.](?:\d{2}|\d{4}) |  # MM-DD-YYYY or MM-DD-YY
            (?:0[1-9]|1[0-2])\s(?:0[1-9]|[12][0-9]|3[01]),?\s(?:\d{4}|\d{2}) |         # MM DD, YYYY
            (?:0[1-9]|[12][0-9]|3[01])\s(?:January|February|March|April|May|June|July|August|September|October|November|December)\s\d{4} | # DD Month YYYY
            (?:January|February|March|April|May|June|July|August|September|October|November|December)\s(?:0[1-9]|[12][0-9]|3[01]),?\s\d{4} | # Month DD, YYYY
            (?:0[1-9]|[12][0-9]|3[01])[-](?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[-](?:\d{4}) # DD-MMM-YYYY
        )\b/ix']),
            ImportColumn::make('contract_type'),
            ImportColumn::make('appointment_date')
                ->rules(['string',
                    'regex:/\b(
            (?:0[1-9]|[12][0-9]|3[01])[-\/.](?:0[1-9]|1[0-2])[-\/.](?:\d{2}|\d{4}) |  # DD-MM-YYYY or DD-MM-YY
            (?:\d{2}|\d{4})[-\/.](?:0[1-9]|1[0-2])[-\/.](?:0[1-9]|[12][0-9]|3[01]) |  # YYYY-MM-DD or YY-MM-DD
            (?:0[1-9]|1[0-2])[-\/.](?:0[1-9]|[12][0-9]|3[01])[-\/.](?:\d{2}|\d{4}) |  # MM-DD-YYYY or MM-DD-YY
            (?:0[1-9]|1[0-2])\s(?:0[1-9]|[12][0-9]|3[01]),?\s(?:\d{4}|\d{2}) |         # MM DD, YYYY
            (?:0[1-9]|[12][0-9]|3[01])\s(?:January|February|March|April|May|June|July|August|September|October|November|December)\s\d{4} | # DD Month YYYY
            (?:January|February|March|April|May|June|July|August|September|October|November|December)\s(?:0[1-9]|[12][0-9]|3[01]),?\s\d{4} | # Month DD, YYYY
            (?:0[1-9]|[12][0-9]|3[01])[-](?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[-](?:\d{4}) # DD-MMM-YYYY
        )\b/ix']),
            ImportColumn::make('job_title')
                ->rules(['max:255']),
            ImportColumn::make('branch')
                ->rules(['max:255']),
            ImportColumn::make('department')
                ->rules(['max:255']),
            ImportColumn::make('salary')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),

        ];
    }

    public function resolveRecord(): ?EmployeeImport
    {
        // return EmployeeImport::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new EmployeeImport();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee import import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
