<?php

return [
    'working_hours' => [
        'start_time' => '08:30:00',
        'end_time' => '17:00:00',
        'standard_hours' => 8,
        'max_daily_hours' => 14,
        'check_in_window' => [
            'max_early' => 120,  // minutes (2 hours before shift)
            'max_late' => 180    // minutes (3 hours after shift start)
        ],
        'shifts' => [
            'day' => [
                'start' => '06:00:00',
                'end' => '18:00:00'
            ],
            'night' => [
                'start' => '18:00:00',
                'end' => '06:00:00'
            ]
        ],
        'break_time' => [
            'duration' => 60,     // minutes
            'start' => '13:00:00',
            'end' => '14:00:00'
        ]
    ],

    'status_options' => [
        'pending' => 'Pending',
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
        'half_day' => 'Half Day',
        'overtime' => 'Overtime',
        'night_shift' => 'Night Shift',
        'weekend' => 'Weekend Shift',
        'holiday' => 'Holiday Shift'
    ],

    'status_colors' => [
        'primary' => ['pending'],
        'success' => ['present', 'overtime'],
        'danger' => ['absent'],
        'warning' => ['late', 'half_day'],
        'info' => ['night_shift', 'weekend', 'holiday']
    ],

    'calculation_rules' => [
        'absent_threshold' => 4,     // hours
        'half_day_threshold' => 6,   // hours
        'late_grace_period' => 30,   // minutes
        'overtime_rules' => [
            'min_daily' => 1,        // minimum hours to qualify for overtime
            'max_daily' => 4,        // maximum overtime hours per day
            'max_weekly' => 20       // maximum overtime hours per week
        ],
        'night_shift' => [
            'start_hour' => 18,      // 6 PM
            'end_hour' => 6,         // 6 AM
            'minimum_hours' => 6      // minimum hours for night shift rate
        ]
    ],

    'overtime' => [
        'minimum_daily' => 1,        // hours required to qualify for overtime
        'rates' => [
            'weekday' => 1.5,        // normal overtime rate
            'weekend' => 2.0,        // weekend rate
            'holiday' => 2.5,        // public holiday rate
            'night' => 1.75          // night shift rate (6PM-6AM)
        ],
        'combinations' => [
            'holiday_night' => 3.0,   // holiday + night shift
            'weekend_night' => 2.5    // weekend + night shift
        ]
    ],

    'holidays' => [
        'new_years' => '01-01',
        'independence_day' => '12-09',
        'christmas' => '12-25',
        'boxing_day' => '12-26',
        'zanzibar_revolution' => '01-12',
        'karume_day' => '04-07',
        'union_day' => '04-26',
        'labour_day' => '05-01',
        'nane_nane' => '08-08',
        'nyerere_day' => '10-14'
    ],

    'validation' => [
        'max_continuous_hours' => 12,    // maximum continuous working hours
        'min_rest_between_shifts' => 11, // minimum hours between shifts
        'max_weekly_hours' => 45,        // maximum regular hours per week
        'weekend_days' => [6, 0],        // Saturday = 6, Sunday = 0
    ],

    'rounding' => [
        'hours' => 2,                    // decimal places for hours
        'minutes' => 'nearest_15'        // round to nearest 15 minutes
    ]
];
