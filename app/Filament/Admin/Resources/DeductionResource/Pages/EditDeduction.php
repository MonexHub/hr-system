<?php

namespace App\Filament\Admin\Resources\DeductionResource\Pages;

use App\Filament\Admin\Resources\DeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeduction extends EditRecord
{
    protected static string $resource = DeductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
