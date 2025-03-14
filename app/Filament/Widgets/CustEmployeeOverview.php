<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustEmployeeOverview extends Widget
{
    protected static string $view = 'filament.widgets.cust-employee-overview';

    // Set polling interval to 4 seconds as in your original template
    protected static ?string $pollingInterval = '4s';

    protected int | string | array $columnSpan = 'full';
    // Sort order for dashboard
    protected static ?int $sort = 2;



    // These properties will be available in the view

    public $totalEmployees = 0;
    public $activeEmployees = 0;
    public $inactiveEmployees = 0;
    public $totalSalaries = 0;
    public $stats = [];

    public function mount()
    {
        $this->calculateEmployeeStats();
    }

    // Determine if user can view all employee data
    protected function canViewAllEmployeeData(): bool
    {
        return auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('hr-manager');
    }

    public function calculateEmployeeStats()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastWeekDate = $now->copy()->subWeek();

        // If the user doesn't have sufficient permissions, restrict the calculations
        if (!$this->canViewAllEmployeeData()) {
            $this->calculateDepartmentStats();
            return;
        }

        // Total employees (all statuses)
        $this->totalEmployees = Employee::count();

        // Active employees (using 'active' status like in the example)
        $this->activeEmployees = Employee::where('employment_status', 'active')->count();

        // Inactive employees (combining terminated, resigned, and other non-active statuses)
        $this->inactiveEmployees = Employee::whereNotIn('employment_status', ['active', 'probation'])->count();

        // Total salaries (only from active employees)
        $this->totalSalaries = Employee::where('employment_status', 'active')->sum('salary');

        // Get counts from last week for comparison
        $lastWeekTotalEmployees = Employee::where('created_at', '<', $lastWeekDate)->count();
        $lastWeekActiveEmployees = Employee::where('employment_status', 'active')
            ->where('updated_at', '<', $lastWeekDate)
            ->count();
        $lastWeekInactiveEmployees = Employee::whereNotIn('employment_status', ['active', 'probation'])
            ->where('updated_at', '<', $lastWeekDate)
            ->count();
        $lastWeekTotalSalaries = Employee::where('employment_status', 'active')
            ->where('updated_at', '<', $lastWeekDate)
            ->sum('salary');

        // Calculate percentage changes
        $this->stats = [
            'total_employees' => $this->calculatePercentageChange($this->totalEmployees, $lastWeekTotalEmployees),
            'active_employees' => $this->calculatePercentageChange($this->activeEmployees, $lastWeekActiveEmployees),
            'inactive_employees' => $this->calculatePercentageChange($this->inactiveEmployees, $lastWeekInactiveEmployees),
            'total_salaries' => $this->calculatePercentageChange($this->totalSalaries, $lastWeekTotalSalaries),
        ];
    }

    // Calculate stats for the user's department only
    protected function calculateDepartmentStats(): void
    {




        $currentEmployee = auth()->user()->employee;
        $departmentId = $currentEmployee->department_id;
        $now = now();
        $lastWeekDate = $now->copy()->subWeek();

        // Department level queries
        $this->totalEmployees = Employee::where('department_id', $departmentId)->count();
        $this->activeEmployees = Employee::where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->count();
        $this->inactiveEmployees = Employee::where('department_id', $departmentId)
            ->whereNotIn('employment_status', ['active', 'probation'])
            ->count();
        $this->totalSalaries = 0; // Default to 0 for department view (salary info restricted)

        // Last week stats for department
        $lastWeekTotalEmployees = Employee::where('department_id', $departmentId)
            ->where('created_at', '<', $lastWeekDate)
            ->count();
        $lastWeekActiveEmployees = Employee::where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->where('updated_at', '<', $lastWeekDate)
            ->count();
        $lastWeekInactiveEmployees = Employee::where('department_id', $departmentId)
            ->whereNotIn('employment_status', ['active', 'probation'])
            ->where('updated_at', '<', $lastWeekDate)
            ->count();

        // Calculate percentage changes
        $this->stats = [
            'total_employees' => $this->calculatePercentageChange($this->totalEmployees, $lastWeekTotalEmployees),
            'active_employees' => $this->calculatePercentageChange($this->activeEmployees, $lastWeekActiveEmployees),
            'inactive_employees' => $this->calculatePercentageChange($this->inactiveEmployees, $lastWeekInactiveEmployees),
            // No salary stats for department level
        ];
    }

    /**
     * Calculate percentage change between current and previous values
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return [
                'percentageChange' => 100,
                'isGrowth' => true
            ];
        }

        $percentageChange = (($current - $previous) / $previous) * 100;

        return [
            'percentageChange' => abs($percentageChange),
            'isGrowth' => $percentageChange >= 0
        ];
    }

    // Optional: method to get employee growth data for charts if needed
    protected function getEmployeeGrowthData(): array
    {
        return Employee::where('employment_status', 'active')
            ->where('appointment_date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(appointment_date, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count')
            ->toArray();
    }

    // Method to get team growth data for specific department
    protected function getTeamGrowthData($departmentId): array
    {
        return Employee::where('employment_status', 'active')
            ->where('department_id', $departmentId)
            ->where('appointment_date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(appointment_date, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count')
            ->toArray();
    }
}
