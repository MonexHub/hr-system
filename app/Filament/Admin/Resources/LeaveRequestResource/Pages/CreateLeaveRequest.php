<?php

namespace App\Filament\Admin\Resources\LeaveRequestResource\Pages;

use App\Filament\Admin\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If HR is creating request for someone else, use the selected employee_id
        if (Auth::user()->hasRole('hr')) {
            return $data;
        }

        // For department managers or anyone else, set the employee_id based on selected employee
        // or if no employee is selected, use the logged-in user's employee
        if (!isset($data['employee_id'])) {
            $data['employee_id'] = Auth::user()->employee->id;
        }

        return $data;
    }
}
