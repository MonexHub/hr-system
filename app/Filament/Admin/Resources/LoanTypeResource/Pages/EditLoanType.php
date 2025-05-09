<?php

namespace App\Filament\Admin\Resources\LoanTypeResource\Pages;

use App\Filament\Admin\Resources\LoanTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoanType extends EditRecord
{
    protected static string $resource = LoanTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
