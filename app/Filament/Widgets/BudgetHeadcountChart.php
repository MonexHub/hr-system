<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Log;

class BudgetHeadcountChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'budgetHeadcountChart';
    protected int | string | array $columnSpan = 'full';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Budget vs. Headcount Analysis';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        try {
            // Log start of function for debugging
            Log::info('BudgetHeadcountChart: Starting to build chart options');

            // Get ALL departments to understand what data is available
            $departments = Department::where('is_active', true)->get();
            Log::info('BudgetHeadcountChart: Found ' . $departments->count() . ' total departments');

            // Build data array for scatter plot - using all departments
            $data = [];

            foreach ($departments as $department) {
                // Even if budget is 0, include the department with at least a small value
                $budget = max((float)$department->annual_budget, 1);
                $headcount = max((int)$department->current_headcount, 1);

                $data[] = [
                    'x' => $headcount,
                    'y' => $budget,
                    'name' => $department->name,
                ];

                Log::info("BudgetHeadcountChart: Added department: {$department->name}, Headcount: {$headcount}, Budget: {$budget}");
            }

            // Log the full data array (in case it's empty)
            Log::info('BudgetHeadcountChart: Created data array with ' . count($data) . ' items');

            // Simple and focused chart options - reducing complexity
            return [
                'chart' => [
                    'type' => 'scatter',
                    'height' => 350,
                ],
                'series' => [
                    [
                        'name' => 'Departments',
                        'data' => $data,
                    ],
                ],
                'xaxis' => [
                    'title' => [
                        'text' => 'Number of Employees',
                    ],
                ],
                'yaxis' => [
                    'title' => [
                        'text' => 'Annual Budget ($)',
                    ],
                ],
                'colors' => ['#DCA915'],
                'markers' => [
                    'size' => 8, // Fixed size for simplicity
                ],
                'tooltip' => [
                    'enabled' => true,
                    'shared' => false,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('BudgetHeadcountChart error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // If there's an error, return a minimal but valid chart config
            return [
                'chart' => [
                    'type' => 'scatter',
                    'height' => 350,
                ],
                'series' => [
                    [
                        'name' => 'Error',
                        'data' => [],
                    ],
                ],
                'xaxis' => [
                    'title' => [
                        'text' => 'Error: ' . $e->getMessage(),
                    ],
                ],
            ];
        }
    }
}
