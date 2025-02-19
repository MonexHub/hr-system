<?php

namespace App\Filament\Admin\Resources\EmployeeFlatDataResource\Pages;

use App\Filament\Admin\Resources\EmployeeFlatDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeFlatData extends EditRecord
{
    protected static string $resource = EmployeeFlatDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
