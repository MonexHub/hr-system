<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Department;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeeDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Employee Distribution';

    protected static ?int $sort = 2;

    // Add refreshing interval
    protected static ?string $pollingInterval = '15s';

    // Add chart filters
    protected function getFilters(): array
    {
        return [
            'view' => [
                'label' => 'View By',
                'options' => [
                    'department' => 'Department',
                    'contract_type' => 'Contract Type',
                    'employment_status' => 'Employment Status',
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        // Get selected filter or default to department
        $filterType = $this->filter ?? 'department';

        // Base query
        $query = Employee::query();

        // Build distribution based on filter
        switch ($filterType) {
            case 'department':
                $distribution = $query
                    ->select('department_id', DB::raw('count(*) as count'))
                    ->groupBy('department_id')
                    ->with('department')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                            $item->department?->name ?? 'Unassigned' => $item->count
                    ]);
                break;

            case 'contract_type':
                $distribution = $query
                    ->select('contract_type', DB::raw('count(*) as count'))
                    ->groupBy('contract_type')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        ucfirst($item->contract_type) => $item->count
                    ]);
                break;

            case 'employment_status':
                $distribution = $query
                    ->select('employment_status', DB::raw('count(*) as count'))
                    ->groupBy('employment_status')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        ucfirst($item->employment_status) => $item->count
                    ]);
                break;
        }

        // Generate colors
        $colors = [
            '#1E40AF', // Blue
            '#059669', // Green
            '#DC2626', // Red
            '#7C3AED', // Purple
            '#EA580C', // Orange
            '#0891B2', // Cyan
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $distribution->values()->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $distribution->count()),
                ],
            ],
            'labels' => $distribution->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }

    // Customize chart height
    protected function getHeight(): int|string|null
    {
        return 400;
    }
}
