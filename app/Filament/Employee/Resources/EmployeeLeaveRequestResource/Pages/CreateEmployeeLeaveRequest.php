<?php

namespace App\Filament\Employee\Resources\EmployeeLeaveRequestResource\Pages;

use App\Filament\Employee\Resources\EmployeeLeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEmployeeLeaveRequest extends CreateRecord
{
    protected static string $resource = EmployeeLeaveRequestResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['employee_id'] = Auth::user()->employee->id;
        $data['status'] = 'pending';

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);
            $data['days_taken'] = $startDate->diffInDays($endDate) + 1;
        }

        return $data;
    }

}
