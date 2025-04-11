<?php

namespace App\Filament\Admin\Resources\PayeeResource\Pages;

use App\Filament\Admin\Resources\PayeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayees extends ListRecords
{
    protected static string $resource = PayeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Use a standard Action instead of DeleteAction
            Actions\CreateAction::make()
                ->label('Create New Tax Bracket'),
        ];
    }

}
