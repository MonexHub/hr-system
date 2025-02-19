<?php

namespace App\Helpers;

use Carbon\Carbon;

class AppraisalPeriodHelper
{
    public static function generatePeriods(int $yearsBack = 1): array
    {
        $periods = [];
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - $yearsBack;

        // Generate periods for each year
        for ($year = $currentYear; $year >= $startYear; $year--) {
            // Add Annual Review
            $periods["$year-ANNUAL"] = "Annual Review $year";

            // Add Quarters
            for ($quarter = 4; $quarter >= 1; $quarter--) {
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;

                $startDate = Carbon::create($year, $startMonth, 1);
                $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth();

                // Skip future quarters
                if ($startDate->isFuture()) {
                    continue;
                }

                $periods["$year-Q$quarter"] = sprintf(
                    'Q%d %d (%s - %s)',
                    $quarter,
                    $year,
                    $startDate->format('M'),
                    $endDate->format('M')
                );
            }
        }

        return $periods;
    }

    public static function getCurrentPeriod(): string
    {
        $now = Carbon::now();
        $quarter = ceil($now->month / 3);
        return $now->year . '-Q' . $quarter;
    }

    public static function getPeriodDates(string $period): array
    {
        [$year, $type] = explode('-', $period);

        if ($type === 'ANNUAL') {
            return [
                'start' => Carbon::create($year, 1, 1)->startOfDay(),
                'end' => Carbon::create($year, 12, 31)->endOfDay(),
            ];
        }

        $quarter = (int) substr($type, 1);
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        return [
            'start' => Carbon::create($year, $startMonth, 1)->startOfDay(),
            'end' => Carbon::create($year, $endMonth, 1)->endOfMonth()->endOfDay(),
        ];
    }
}
