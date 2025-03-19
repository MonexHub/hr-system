<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Filament\Imports\EmployeeImporter;
use App\Filament\Widgets\EmployeeAttendanceSummaryWidget;
use App\Filament\Widgets\EmployeeProfileSummaryWidget;
use App\Filament\Widgets\EmploymentDistributionWidget;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderWidgets(): array
    {
        return[
            EmploymentDistributionWidget::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->importer(EmployeeImporter::class)
                ->visible(fn() => auth()->user()->can('import_employee'))
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
