<?php

namespace App\Filament\Actions;

use App\Models\Employee;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportEmployeeProfileAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'export_profile';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Export Profile')
            ->color('success')
            ->icon('heroicon-o-document-arrow-down')
            ->action(fn ($record) => $this->exportProfile($record));
    }

    public function exportProfile(Employee $employee)
    {
        $data = [
            'employee' => $employee->load(['department', 'reportingTo']),
            'education' => $employee->education()->orderBy('start_date', 'desc')->get(),
            'experience' => $employee->workExperiences()->orderBy('start_date', 'desc')->get(),
            'skills' => $employee->skills()->orderBy('category')->get(),
            'leaves' => $employee->leaveRequests()
                ->whereYear('start_date', date('Y'))
                ->orderBy('start_date', 'desc')
                ->get(),
        ];

        $pdf = PDF::loadView('exports.employee-profile', $data);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "employee-profile-{$employee->employee_code}.pdf"
        );
    }
}
