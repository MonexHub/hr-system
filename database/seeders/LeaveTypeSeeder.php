<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Regular annual leave entitlement',
                'requires_attachment' => false,
                'is_paid' => true,
                'min_days_before_request' => 3,
                'max_days_per_request' => 14,
                'max_days_per_year' => 21,
                'requires_ceo_approval' => false,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave with doctor\'s certificate',
                'requires_attachment' => true,
                'is_paid' => true,
                'min_days_before_request' => 0,
                'max_days_per_request' => 30,
                'max_days_per_year' => 60,
                'requires_ceo_approval' => false,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Maternity leave for female employees',
                'requires_attachment' => true,
                'is_paid' => true,
                'min_days_before_request' => 30,
                'max_days_per_request' => 90,
                'max_days_per_year' => 90,
                'requires_ceo_approval' => true,
            ],
            [
                'name' => 'Study Leave',
                'description' => 'Leave for academic purposes',
                'requires_attachment' => true,
                'is_paid' => false,
                'min_days_before_request' => 30,
                'max_days_per_request' => 180,
                'max_days_per_year' => 180,
                'requires_ceo_approval' => true,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::updateOrCreate(
                ['name' => $type['name']], // Key to check for duplicates
                array_merge($type, [
                    'created_by' => $admin->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
