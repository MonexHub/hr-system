<?php

namespace App\Filament\Admin\Resources\OrganizationUnitResource\Pages;

use App\Filament\Admin\Resources\OrganizationUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationUnits extends ListRecords
{
    protected static string $resource = OrganizationUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
