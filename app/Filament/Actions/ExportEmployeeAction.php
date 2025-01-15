<?php

namespace App\Filament\Actions;

use Filament\Tables\Actions\BulkAction;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportEmployeeAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'export_employees';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Export Selected')
            ->color('success')
            ->icon('heroicon-o-document-arrow-down')
            ->action(fn ($records) => $this->export($records))
            ->deselectRecordsAfterCompletion();
    }

    public function export($records)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'Employee Code',
            'Full Name',
            'Department',
            'Job Title',
            'Email',
            'Phone',
            'Status',
            'Start Date'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        // Add data
        $row = 2;
        foreach ($records as $employee) {
            $data = [
                $employee->employee_code,
                $employee->full_name,
                $employee->department->name,
                $employee->job_title,
                $employee->email,
                $employee->phone_number,
                $employee->employment_status,
                $employee->appointment_date?->format('Y-m-d'),
            ];

            $sheet->fromArray([$data], null, 'A' . $row);
            $row++;
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'employees-' . date('Y-m-d-His') . '.xlsx';
        $tempPath = storage_path('app/public/temp/' . $fileName);

        if (!file_exists(storage_path('app/public/temp'))) {
            mkdir(storage_path('app/public/temp'), 0777, true);
        }

        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend();
    }
}
