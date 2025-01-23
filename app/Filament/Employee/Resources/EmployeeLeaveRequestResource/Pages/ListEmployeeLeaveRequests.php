<?php

namespace App\Filament\Employee\Resources\EmployeeLeaveRequestResource\Pages;

use App\Filament\Employee\Resources\EmployeeLeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeLeaveRequests extends ListRecords
{
    protected static string $resource = EmployeeLeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
