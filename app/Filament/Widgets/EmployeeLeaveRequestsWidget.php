<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\LeaveRequest;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveRequestsWidget extends Widget
{
    protected static string $view = 'filament.widgets.employee-leave-requests-widget';

    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;

    protected static ?int $sort = 3;

    public function getLeaveRequests()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'pending' => [],
                'upcoming' => [],
                'recent' => []
            ];
        }

        // Get pending requests
        $pendingRequests = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [
                LeaveRequest::STATUS_PENDING,
                LeaveRequest::STATUS_DEPARTMENT_APPROVED,
                LeaveRequest::STATUS_HR_APPROVED
            ])
            ->with('leaveType')
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get();

        // Get upcoming approved leaves
        $upcomingRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->where('start_date', '>', Carbon::today())
            ->with('leaveType')
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        // Get recently completed or active leaves
        $recentRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->where(function($query) {
                $query->where('end_date', '>=', Carbon::today()->subDays(30))
                    ->orWhere('start_date', '<=', Carbon::today())
                    ->where('end_date', '>=', Carbon::today());
            })
            ->with('leaveType')
            ->orderBy('start_date', 'desc')
            ->limit(3)
            ->get();

        return [
            'pending' => $pendingRequests->map(function($request) {
                return [
                    'id' => $request->id,
                    'request_number' => $request->request_number,
                    'leave_type' => $request->leaveType->name ?? 'Unknown',
                    'start_date' => $request->start_date->format('M d, Y'),
                    'end_date' => $request->end_date->format('M d, Y'),
                    'total_days' => $request->total_days,
                    'status' => $request->status,
                    'status_badge' => $this->getStatusBadge($request->status),
                    'days_until' => $this->getDaysUntil($request->start_date),
                ];
            }),
            'upcoming' => $upcomingRequests->map(function($request) {
                return [
                    'id' => $request->id,
                    'request_number' => $request->request_number,
                    'leave_type' => $request->leaveType->name ?? 'Unknown',
                    'start_date' => $request->start_date->format('M d, Y'),
                    'end_date' => $request->end_date->format('M d, Y'),
                    'total_days' => $request->total_days,
                    'days_until' => $this->getDaysUntil($request->start_date),
                ];
            }),
            'recent' => $recentRequests->map(function($request) {
                return [
                    'id' => $request->id,
                    'request_number' => $request->request_number,
                    'leave_type' => $request->leaveType->name ?? 'Unknown',
                    'start_date' => $request->start_date->format('M d, Y'),
                    'end_date' => $request->end_date->format('M d, Y'),
                    'total_days' => $request->total_days,
                    'is_active' => $request->start_date->isPast() && $request->end_date->isFuture(),
                ];
            }),
        ];
    }

    protected function getStatusBadge($status)
    {
        $badges = [
            LeaveRequest::STATUS_PENDING => [
                'color' => 'warning',
                'label' => 'Pending'
            ],
            LeaveRequest::STATUS_DEPARTMENT_APPROVED => [
                'color' => 'info',
                'label' => 'Dept. Approved'
            ],
            LeaveRequest::STATUS_HR_APPROVED => [
                'color' => 'info',
                'label' => 'HR Approved'
            ],
            LeaveRequest::STATUS_APPROVED => [
                'color' => 'success',
                'label' => 'Approved'
            ],
            LeaveRequest::STATUS_REJECTED => [
                'color' => 'danger',
                'label' => 'Rejected'
            ],
            LeaveRequest::STATUS_CANCELLED => [
                'color' => 'secondary',
                'label' => 'Cancelled'
            ],
        ];

        return $badges[$status] ?? ['color' => 'secondary', 'label' => $status];
    }

    protected function getDaysUntil($startDate)
    {
        $today = Carbon::today();
        $start = Carbon::parse($startDate)->startOfDay();

        if ($start->isPast()) {
            return 0;
        }

        return $today->diffInDays($start);
    }
}
