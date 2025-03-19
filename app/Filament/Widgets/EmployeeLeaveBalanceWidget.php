<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveBalanceWidget extends Widget
{
    protected static string $view = 'filament.widgets.employee-leave-balance-widget';

    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;
    protected static ?int $sort = 2;

    public function getLeaveBalances()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [];
        }

        $currentYear = Carbon::now()->year;
        $leaveBalances = $employee->leaveBalances()
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();

        $formattedBalances = [];

        foreach ($leaveBalances as $balance) {
            $availableBalance = $balance->entitled_days +
                $balance->carried_forward_days +
                $balance->additional_days -
                $balance->taken_days -
                $balance->pending_days;

            $formattedBalances[] = [
                'leave_type' => $balance->leaveType->name ?? 'Unknown',
                'entitled' => $balance->entitled_days,
                'taken' => $balance->taken_days,
                'pending' => $balance->pending_days,
                'available' => $availableBalance,
                'percentage_used' => $balance->entitled_days > 0
                    ? round(($balance->taken_days / $balance->entitled_days) * 100, 1)
                    : 0,
            ];
        }

        return $formattedBalances;
    }
}
