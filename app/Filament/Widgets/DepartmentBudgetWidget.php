<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class DepartmentBudgetWidget extends Widget
{
    protected static string $view = 'filament.widgets.department-budget-widget';

    // Set a default polling interval (optional)
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;

    public function calculateBudgetStats()
    {
        try {
            // Get total annual budget across all departments
            $totalBudget = Department::where('is_active', true)
                ->sum('annual_budget');

            // Get the top department by budget
            $topDepartment = Department::where('is_active', true)
                ->orderBy('annual_budget', 'desc')
                ->first();

            // Calculate average budget per employee
            $totalEmployees = Employee::where('employment_status', 'active')->count();
            $avgBudgetPerEmployee = $totalEmployees > 0 ? ($totalBudget / $totalEmployees) : 0;

            // Count departments with low headcount utilization but high budget
            $departmentsWithLowUtilization = Department::where('is_active', true)
                ->where('max_headcount', '>', 0)
                ->whereRaw('(current_headcount / max_headcount) < 0.7') // Less than 70% utilized
                ->where('annual_budget', '>', 0)
                ->count();

            return [
                'total_budget' => $totalBudget,
                'top_department' => $topDepartment ? [
                    'name' => $topDepartment->name,
                    'budget' => $topDepartment->annual_budget
                ] : null,
                'avg_budget_per_employee' => $avgBudgetPerEmployee,
                'departments_with_low_utilization' => $departmentsWithLowUtilization
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error calculating budget stats: ' . $e->getMessage());
            return [
                'total_budget' => 0,
                'top_department' => null,
                'avg_budget_per_employee' => 0,
                'departments_with_low_utilization' => 0
            ];
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.department-budget-widget', [
            'stats' => $this->calculateBudgetStats(),
        ]);
    }
}
