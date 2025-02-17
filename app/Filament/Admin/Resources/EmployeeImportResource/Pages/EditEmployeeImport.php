<?php

namespace App\Filament\Admin\Resources\EmployeeImportResource\Pages;

use App\Filament\Admin\Resources\EmployeeImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeImport extends EditRecord
{
    protected static string $resource = EmployeeImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
