<?php

namespace App\Filament\Admin\Resources\EmployeeFlatDataResource\Pages;

use App\Filament\Admin\Resources\EmployeeFlatDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeFlatData extends ListRecords
{
    protected static string $resource = EmployeeFlatDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
