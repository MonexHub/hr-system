<?php

namespace App\Filament\Admin\Resources\DeductionResource\Pages;

use App\Filament\Admin\Resources\DeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeduction extends CreateRecord
{
    protected static string $resource = DeductionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
