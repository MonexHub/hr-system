<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DepartmentHeadcountChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'departmentHeadcountChart';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;


    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Department Headcount';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $departments = Department::where('is_active', true)
            ->orderBy('current_headcount', 'desc')
            ->limit(10)
            ->get(['name', 'current_headcount', 'max_headcount']);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'toolbar' => [
                    'show' => false,
                ],
                'fontFamily' => 'inherit',
            ],
            'series' => [
                [
                    'name' => 'Current Headcount',
                    'data' => $departments->pluck('current_headcount')->toArray(),
                ],
                [
                    'name' => 'Maximum Headcount',
                    'data' => $departments->pluck('max_headcount')->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $departments->pluck('name')->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Number of Employees',
                    'style' => [
                        'fontSize' => '12px',
                        'fontWeight' => 'normal',
                    ],
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#DCA915', '#F0D786'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                    'columnWidth' => '60%',
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'title' => [
                'text' => 'Top 10 Departments by Headcount',
                'align' => 'left',
                'style' => [
                    'fontSize' => '13px',
                    'fontWeight' => 'bold',
                    'color' => '#666666'
                ],
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function(value) { return value + " employees" }',
                ],
            ],
        ];
    }
}
