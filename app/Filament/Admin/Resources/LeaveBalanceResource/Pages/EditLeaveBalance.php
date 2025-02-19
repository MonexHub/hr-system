<?php

namespace App\Filament\Admin\Resources\LeaveBalanceResource\Pages;

use App\Filament\Admin\Resources\LeaveBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeaveBalance extends EditRecord
{
    protected static string $resource = LeaveBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
