<?php

namespace App\Filament\Admin\Resources\EmployeeLoanResource\Pages;

use App\Filament\Admin\Resources\EmployeeLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeLoan extends CreateRecord
{
    protected static string $resource = EmployeeLoanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
