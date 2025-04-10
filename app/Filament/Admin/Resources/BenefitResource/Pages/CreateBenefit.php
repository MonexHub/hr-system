<?php

namespace App\Filament\Admin\Resources\BenefitResource\Pages;

use App\Filament\Admin\Resources\BenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBenefit extends CreateRecord
{
    protected static string $resource = BenefitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
