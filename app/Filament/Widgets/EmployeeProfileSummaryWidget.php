<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\LeaveRequest;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeProfileSummaryWidget extends Widget
{
    protected static string $view = 'filament.widgets.employee-profile-summary-widget';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;

    public function getProfileData()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'name' => $user->name,
                'employee_code' => 'Not assigned',
                'department' => 'Not assigned',
                'job_title' => 'Not assigned',
                'manager' => 'Not assigned',
                'appointment_date' => 'Not assigned',
                'employment_status' => 'inactive',
                'contract_type' => 'Unknown',
                'profile_photo' => null,
                'years_of_service' => 0,
                'pending_leave_requests' => 0,
                'documents_count' => 0,
            ];
        }

        // Calculate years of service
        $yearsOfService = 0;
        if ($employee->appointment_date) {
            $yearsOfService = Carbon::parse($employee->appointment_date)->diffInYears(Carbon::now());
        }

        // Get pending leave requests count
        $pendingLeaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [
                LeaveRequest::STATUS_PENDING,
                LeaveRequest::STATUS_DEPARTMENT_APPROVED,
                LeaveRequest::STATUS_HR_APPROVED
            ])
            ->count();

        // Get documents count
        $documentsCount = 0;
        if (method_exists($employee, 'documents')) {
            $documentsCount = $employee->documents()->count();
        }

        return [
            'name' => $employee->full_name,
            'employee_code' => $employee->employee_code,
            'department' => $employee->department->name ?? 'Not assigned',
            'job_title' => $employee->jobTitle->name ?? 'Not assigned',
            'manager' => $employee->reportingTo->full_name ?? 'Not assigned',
            'appointment_date' => $employee->appointment_date ? $employee->appointment_date->format('M d, Y') : 'Not assigned',
            'profile_photo' => $employee->profile_photo,
            'employment_status' => $employee->employment_status,
            'contract_type' => $employee->contract_type,
            'years_of_service' => $yearsOfService,
            'pending_leave_requests' => $pendingLeaveRequests,
            'documents_count' => $documentsCount,
        ];
    }
}
