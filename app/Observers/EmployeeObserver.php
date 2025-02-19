<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        // Get all active leave types
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;

        // Create leave balance for each leave type
        foreach ($leaveTypes as $leaveType) {
            LeaveBalance::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'entitled_days' => $leaveType->max_days_per_year ?? 0,
                'carried_forward_days' => 0,
                'additional_days' => 0,
                'taken_days' => 0,
                'pending_days' => 0,
                'year' => $currentYear,
                'remarks' => "Initial balance for {$currentYear}",
                'created_by' => auth()->id() ?? 1 // fallback to admin if no user is authenticated
            ]);
        }
    }
}
