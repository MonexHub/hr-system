<?php

namespace App\Filament\Admin\Resources\OrganizationUnitResource\Pages;

use App\Filament\Admin\Resources\OrganizationUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationUnit extends EditRecord
{
    protected static string $resource = OrganizationUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
