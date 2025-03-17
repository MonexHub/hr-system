<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Department;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class EmploymentDistributionWidget extends Widget
{
    protected static string $view = 'filament.widgets.employment-distribution-widget';

    // Set a default polling interval (optional)
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    public function calculateDistributionStats()
    {
        try {
            // Count employees by contract type
            $contractTypes = Employee::where('employment_status', 'active')
                ->select('contract_type', DB::raw('count(*) as count'))
                ->groupBy('contract_type')
                ->pluck('count', 'contract_type')
                ->toArray();

            // Get the department with the most employees
            $largestDepartment = Department::where('is_active', true)
                ->orderBy('current_headcount', 'desc')
                ->first();

            // Get departments with no employees
            $emptyDepartments = Department::where('is_active', true)
                ->where('current_headcount', 0)
                ->count();

            // Calculate gender diversity (assumes a gender field on employees table)
            $genderDistribution = Employee::where('employment_status', 'active')
                ->select('gender', DB::raw('count(*) as count'))
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();

            return [
                'permanent_employees' => $contractTypes['permanent'] ?? 0,
                'contract_employees' => $contractTypes['contract'] ?? 0,
                'probation_employees' => $contractTypes['probation'] ?? 0,
                'largest_department' => $largestDepartment ? [
                    'name' => $largestDepartment->name,
                    'count' => $largestDepartment->current_headcount
                ] : null,
                'empty_departments' => $emptyDepartments,
                'gender_distribution' => $genderDistribution
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error calculating distribution stats: ' . $e->getMessage());

            return [
                'permanent_employees' => 0,
                'contract_employees' => 0,
                'probation_employees' => 0,
                'largest_department' => null,
                'empty_departments' => 0,
                'gender_distribution' => []
            ];
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.employment-distribution-widget', [
            'stats' => $this->calculateDistributionStats(),
        ]);
    }
}
