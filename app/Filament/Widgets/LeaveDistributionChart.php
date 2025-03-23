<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LeaveDistributionChart extends ApexChartWidget
{
    use HasWidgetShield;
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'leaveDistributionChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Leave Distribution by Type';

    /**
     * Get the chart options
     */
    protected function getOptions(): array
    {
        try {
            // Get leave data with debugging
            $leaveData = $this->getLeaveDataWithDebugging();

            if (empty($leaveData['series'])) {
                // No data found, return a simple "No data" display
                return [
                    'chart' => [
                        'type' => 'pie',
                        'height' => 300,
                    ],
                    'series' => [1],
                    'labels' => ['No leave data available - See logs for details'],
                    'colors' => ['#cccccc'],
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ];
            }

            // Return the chart with actual data
            return [
                'chart' => [
                    'type' => 'pie',
                    'height' => 300,
                ],
                'series' => $leaveData['series'],
                'labels' => $leaveData['labels'],
                'colors' => $leaveData['colors'],
                'legend' => [
                    'position' => 'right',
                ],
                'plotOptions' => [
                    'pie' => [
                        'dataLabels' => [
                            'offset' => -5
                        ]
                    ]
                ],
                'title' => [
                    'text' => 'Leave Data' . ($leaveData['time_period'] ? ' - ' . $leaveData['time_period'] : ''),
                    'align' => 'left',
                    'style' => [
                        'fontSize' => '13px',
                        'fontWeight' => 'bold',
                        'color' => '#666666'
                    ],
                ],
                'dataLabels' => [
                    'enabled' => true,
                ],
                'responsive' => [
                    [
                        'breakpoint' => 600,
                        'options' => [
                            'legend' => [
                                'position' => 'bottom'
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('LeaveDistributionChart options error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Return a fallback configuration
            return [
                'chart' => [
                    'type' => 'pie',
                    'height' => 300,
                ],
                'series' => [1],
                'labels' => ['Error: ' . $e->getMessage()],
                'colors' => ['#E34234'],
            ];
        }
    }

    /**
     * Get leave data from database with extensive debugging
     *
     * @return array
     */
    protected function getLeaveDataWithDebugging(): array
    {
        // Log the constants first to verify they match what's in the database
        Log::info('Leave request status constants:', [
            'STATUS_PENDING' => LeaveRequest::STATUS_PENDING,
            'STATUS_DEPARTMENT_APPROVED' => LeaveRequest::STATUS_DEPARTMENT_APPROVED,
            'STATUS_HR_APPROVED' => LeaveRequest::STATUS_HR_APPROVED,
            'STATUS_APPROVED' => LeaveRequest::STATUS_APPROVED,
            'STATUS_REJECTED' => LeaveRequest::STATUS_REJECTED,
            'STATUS_CANCELLED' => LeaveRequest::STATUS_CANCELLED
        ]);

        // Check if there are any leave requests at all
        $totalLeaveRequests = LeaveRequest::count();
        Log::info('Total leave requests in database: ' . $totalLeaveRequests);

        if ($totalLeaveRequests === 0) {
            Log::warning('No leave requests found in the database');
            return $this->getEmptyDataStructure();
        }

        // Check status distribution
        $statusDistribution = LeaveRequest::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        Log::info('Status distribution:', $statusDistribution);

        // Check date range
        $dateRange = LeaveRequest::select(
            DB::raw('MIN(start_date) as earliest'),
            DB::raw('MAX(start_date) as latest')
        )
            ->first();

        Log::info('Date range of leave requests:', [
            'earliest' => $dateRange->earliest ?? 'none',
            'latest' => $dateRange->latest ?? 'none'
        ]);

        // Try a more lenient approach - let's try different time periods
        // First try: Last year
        $startDate = Carbon::now()->subYear()->startOfDay();
        $timePeriod = 'Last 12 Months';

        // Get leave counts by type
        $leaveData = LeaveRequest::select(
            'leave_type_id',
            DB::raw('count(*) as count')
        )
            ->whereNotNull('leave_type_id')
            ->where('start_date', '>=', $startDate)
            ->groupBy('leave_type_id')
            ->get();

        Log::info('Leave data query (all statuses, last year):', [
            'count' => $leaveData->count(),
            'data' => $leaveData->toArray()
        ]);

        // If we still don't have data, try without date restriction
        if ($leaveData->isEmpty()) {
            $leaveData = LeaveRequest::select(
                'leave_type_id',
                DB::raw('count(*) as count')
            )
                ->whereNotNull('leave_type_id')
                ->groupBy('leave_type_id')
                ->get();

            $timePeriod = 'All Time';

            Log::info('Leave data query (all statuses, all time):', [
                'count' => $leaveData->count(),
                'data' => $leaveData->toArray()
            ]);
        }

        // If we still have no data, check if leave_type_id might be NULL
        if ($leaveData->isEmpty()) {
            $nullTypesCount = LeaveRequest::whereNull('leave_type_id')->count();
            Log::warning('Leave requests with NULL leave_type_id: ' . $nullTypesCount);

            if ($nullTypesCount > 0) {
                // We have leave requests but with null type IDs
                return [
                    'series' => [$nullTypesCount],
                    'labels' => ['Unspecified Leave Type'],
                    'colors' => ['#cccccc'],
                    'time_period' => 'All Time (Null Types)'
                ];
            }

            return $this->getEmptyDataStructure();
        }

        // Get leave types
        $leaveTypeIds = $leaveData->pluck('leave_type_id')->toArray();
        $leaveTypes = LeaveType::whereIn('id', $leaveTypeIds)
            ->pluck('name', 'id')
            ->toArray();

        Log::info('Leave types found:', [
            'count' => count($leaveTypes),
            'types' => $leaveTypes
        ]);

        // If we have leave data but no matching leave types, something is wrong with the foreign keys
        if (empty($leaveTypes) && !empty($leaveTypeIds)) {
            Log::warning('Leave type IDs found in requests but no matching leave types in database', [
                'type_ids' => $leaveTypeIds
            ]);

            // Create labels from IDs
            $fakeTypes = [];
            foreach ($leaveTypeIds as $id) {
                $fakeTypes[$id] = "Type ID: {$id}";
            }
            $leaveTypes = $fakeTypes;
        }

        // Prepare data arrays
        $series = [];
        $labels = [];

        // Gold-themed colors
        $colors = [
            '#DCA915', '#F0D786', '#BA8E25', '#D9B355',
            '#AA8017', '#C7A646', '#8D6D1F', '#B5973A'
        ];

        // Build the data arrays
        foreach ($leaveData as $item) {
            $leaveTypeId = $item->leave_type_id;
            $count = (int)$item->count;

            $series[] = $count;
            $labels[] = $leaveTypes[$leaveTypeId] ?? "Type ID: {$leaveTypeId}";
        }

        return [
            'series' => $series,
            'labels' => $labels,
            'colors' => array_slice($colors, 0, count($series)),
            'time_period' => $timePeriod
        ];
    }

    /**
     * Get empty data structure
     */
    protected function getEmptyDataStructure(): array
    {
        return [
            'series' => [],
            'labels' => [],
            'colors' => [],
            'time_period' => ''
        ];
    }
}
