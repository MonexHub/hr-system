<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceTrendsChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'attendanceTrendsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Monthly Attendance Trends';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get date range for the last 6 months
        $endDate = Carbon::now()->endOfMonth();
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();

        // Generate array of month names
        $months = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $months[$currentDate->format('Y-m')] = $currentDate->format('M Y');
            $currentDate->addMonth();
        }

        // Get attendance status data
        $attendanceData = Attendance::select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            'status',
            DB::raw('count(*) as count')
        )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('month', 'status')
            ->get();

        // Organize data by status
        $statusGroups = [];
        foreach ($attendanceData as $record) {
            if (!isset($statusGroups[$record->status])) {
                $statusGroups[$record->status] = [];
            }
            $statusGroups[$record->status][$record->month] = $record->count;
        }

        // Colors for different statuses
        $colors = [
            'present' => '#4CBB17', // Green
            'late' => '#DCA915',    // Gold
            'absent' => '#E34234',  // Red
            'half_day' => '#F0D786', // Light gold
            'overtime' => '#BA8E25', // Dark gold
        ];

        // Prepare series
        $series = [];
        foreach ($statusGroups as $status => $monthData) {
            $data = [];
            foreach (array_keys($months) as $month) {
                $data[] = $monthData[$month] ?? 0;
            }

            $series[] = [
                'name' => ucfirst($status),
                'data' => $data,
            ];
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
                'toolbar' => [
                    'show' => false,
                ],
                'fontFamily' => 'inherit',
                'zoom' => [
                    'enabled' => false,
                ],
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => array_values($months),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'title' => [
                    'text' => 'Month',
                    'style' => [
                        'fontSize' => '12px',
                        'fontWeight' => 'normal',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Number of Records',
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
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
            ],
            'markers' => [
                'size' => 5,
                'hover' => [
                    'size' => 7,
                ],
            ],
            'grid' => [
                'borderColor' => '#e0e0e0',
                'row' => [
                    'colors' => ['#f3f3f3', 'transparent'],
                    'opacity' => 0.5,
                ],
            ],
        ];
    }
}
