<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeeGenderDistribution extends ChartWidget
{
    protected static ?string $heading = 'Employee Gender Distribution';

    protected static ?int $sort = 3;

    // Add refreshing interval
    protected static ?string $pollingInterval = '15s';
    use HasWidgetShield;

    protected function getData(): array
    {
        // Get gender distribution
        $distribution = Employee::query()
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(fn ($item) => [
                match(strtolower($item->gender)) {
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                    default => 'Not Specified'
                } => $item->count
            ]);

        // Calculate percentages
        $total = $distribution->sum();
        $labels = $distribution->map(function ($count, $gender) use ($total) {
            $percentage = round(($count / $total) * 100, 1);
            return "$gender ($count - $percentage%)";
        });

        return [
            'datasets' => [
                [
                    'label' => 'Gender Distribution',
                    'data' => $distribution->values()->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // Blue for Male
                        '#EC4899', // Pink for Female
                        '#8B5CF6', // Purple for Other
                        '#94A3B8', // Gray for Not Specified
                    ],
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Using pie chart for distribution
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                    'labels' => [
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.label;
                        }",
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }

    // Add department filter
    protected function getFilters(): array
    {
        return [
            'department' => [
                'label' => 'Department',
                'options' => Department::pluck('name', 'id')
                    ->toArray(),
            ],
        ];
    }

    // Handle filter in getData
    protected function generateQuery()
    {
        $query = Employee::query();

        if ($this->filter) {
            $query->where('department_id', $this->filter);
        }

        return $query;
    }

    protected function getHeight(): int|string|null
    {
        return 300;
    }
}
