<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Department;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;

class ContractDistributionChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'contractDistributionChart';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Employee Contract Distribution';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get the top 5 departments by headcount
        $topDepartments = Department::where('is_active', true)
            ->orderBy('current_headcount', 'desc')
            ->limit(5)
            ->pluck('id')
            ->toArray();

        // Get contract distribution by department
        $contractData = Employee::whereIn('department_id', $topDepartments)
            ->where('employment_status', 'active')
            ->select('department_id', 'contract_type', DB::raw('count(*) as count'))
            ->groupBy('department_id', 'contract_type')
            ->get();

        // Get department names
        $departmentNames = Department::whereIn('id', $topDepartments)
            ->pluck('name', 'id')
            ->toArray();

        // Contract types we want to track
        $contractTypes = ['permanent', 'contract', 'probation'];

        // Prepare series by contract type
        $series = [];
        $colors = [
            'permanent' => '#DCA915',  // Gold
            'contract' => '#F0D786',   // Light gold
            'probation' => '#BA8E25',  // Dark gold
        ];

        foreach ($contractTypes as $type) {
            $data = [];
            foreach ($topDepartments as $deptId) {
                $count = $contractData->where('department_id', $deptId)
                    ->where('contract_type', $type)
                    ->sum('count');
                $data[] = $count;
            }

            $series[] = [
                'name' => ucfirst($type),
                'data' => $data,
            ];
        }

        // Prepare categories (department names)
        $categories = array_map(function ($deptId) use ($departmentNames) {
            return $departmentNames[$deptId] ?? 'Unknown';
        }, $topDepartments);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'stacked' => true,
                'toolbar' => [
                    'show' => false,
                ],
                'fontFamily' => 'inherit',
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'title' => [
                    'text' => 'Department',
                    'style' => [
                        'fontSize' => '12px',
                        'fontWeight' => 'normal',
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
            'colors' => array_values($colors),
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'borderRadius' => 3,
                    'columnWidth' => '60%',
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
            ],
            'title' => [
                'text' => 'Top 5 Departments by Headcount',
                'align' => 'left',
                'style' => [
                    'fontSize' => '13px',
                    'fontWeight' => 'bold',
                    'color' => '#666666'
                ],
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function(val) { return val + " employees" }',
                ],
            ],
            'grid' => [
                'borderColor' => '#e0e0e0',
            ],
        ];
    }
}
