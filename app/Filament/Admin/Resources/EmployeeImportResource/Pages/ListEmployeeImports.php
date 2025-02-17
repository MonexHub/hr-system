<?php

namespace App\Filament\Admin\Resources\EmployeeImportResource\Pages;

use App\Filament\Admin\Resources\EmployeeImportResource;
use App\Filament\Imports\EmployeeImporter;
use App\Filament\Imports\EmployeeImportImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeImports extends ListRecords
{
    protected static string $resource = EmployeeImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(EmployeeImportImporter::class)
                ->maxRows(1000) // Optional: limit rows per import
                ->chunkSize(100) // Process 100 records at a time
        ];
    }
}
