<?php

namespace App\Reports;

use App\Models\Department;
use Illuminate\Support\Collection;

class HeadcountReport
{
    /**
     * Generate organization-wide headcount report
     */
    public function generateOrganizationReport(): array
    {
        $departments = Department::with('employees')->get();

        return [
            'summary' => [
                'total_departments' => $departments->count(),
                'total_headcount' => $departments->sum('current_headcount'),
                'total_capacity' => $departments->sum('max_headcount'),
                'overall_utilization' => $this->calculateOverallUtilization($departments),
                'departments_at_capacity' => $departments->filter(fn ($dept) => !$dept->hasAvailableHeadcount())->count(),
            ],
            'department_breakdown' => $departments->map->getHeadcountSummary(),
            'employment_types' => $this->getEmploymentTypeBreakdown(),
            'cost_analysis' => $this->generateCostAnalysis($departments),
            'trending' => $this->generateTrendingData(),
        ];
    }

    /**
     * Calculate overall headcount utilization
     */
    private function calculateOverallUtilization(Collection $departments): float
    {
        $totalMax = $departments->sum('max_headcount');
        if ($totalMax === 0) return 0;

        $totalCurrent = $departments->sum('current_headcount');
        return round(($totalCurrent / $totalMax) * 100, 2);
    }

    /**
     * Get breakdown by employment type
     */
    private function getEmploymentTypeBreakdown(): array
    {
        return [
            'permanent' => Department::withSum('employees as count', fn ($q) =>
            $q->where('contract_type', 'permanent'))->get(),
            'contract' => Department::withSum('employees as count', fn ($q) =>
            $q->where('contract_type', 'contract'))->get(),
            'probation' => Department::withSum('employees as count', fn ($q) =>
            $q->where('employment_status', 'probation'))->get(),
        ];
    }

    /**
     * Generate cost analysis
     */
    private function generateCostAnalysis(Collection $departments): array
    {
        return [
            'total_salary_cost' => $departments->sum(fn ($dept) =>
            $dept->employees()->sum('salary')),
            'average_cost_per_employee' => $departments->average(fn ($dept) =>
            $dept->getCostPerHeadcount()),
            'department_costs' => $departments->mapWithKeys(fn ($dept) => [
                $dept->name => [
                    'total_cost' => $dept->employees()->sum('salary'),
                    'average_cost' => $dept->getCostPerHeadcount(),
                    'budget_utilization' => $dept->annual_budget > 0
                        ? ($dept->employees()->sum('salary') / $dept->annual_budget) * 100
                        : 0,
                ]
            ]),
        ];
    }

    /**
     * Generate trending data
     */
    private function generateTrendingData(): array
    {
        $months = collect(range(0, 11))->map(function ($month) {
            $date = now()->subMonths($month)->format('Y-m');
            return [
                'month' => $date,
                'headcount' => Department::withCount(['employees' => function ($query) use ($date) {
                    $query->whereDate('created_at', '<=', $date);
                }])->sum('employees_count'),
            ];
        })->reverse()->values();

        return [
            'monthly_headcount' => $months,
            'growth_rate' => $this->calculateGrowthRate($months),
        ];
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate(Collection $months): float
    {
        if ($months->count() < 2) return 0;

        $firstMonth = $months->first()['headcount'];
        $lastMonth = $months->last()['headcount'];

        if ($firstMonth === 0) return 0;

        return round((($lastMonth - $firstMonth) / $firstMonth) * 100, 2);
    }
}
