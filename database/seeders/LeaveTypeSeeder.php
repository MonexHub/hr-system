<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'description' => 'Regular annual leave entitlement',
                'max_days' => 21,
                'min_days_per_request' => 1,
                'max_days_per_request' => 14,
                'is_paid' => true,
                'requires_attachment' => false,
                'advance_notice_days' => 7,
                'can_carry_forward' => true,
                'max_carry_forward_days' => 5,
                'cycle_type' => 'annual',
                'color' => '#4CAF50',
                'is_active' => true,
            ],
            // ... other leave types ...
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::updateOrCreate(
                ['code' => $type['code']], // Check if exists using code
                array_merge($type, [
                    'created_by' => 1,
                    'updated_by' => 1,
                ])
            );
        }

        // Get the count of all leave types
        $totalLeaveTypes = LeaveType::count();
        $this->command->info("Leave types seeded successfully! Total leave types: {$totalLeaveTypes}");
    }

    /**
     * Get additional leave types if needed
     */
    protected function getAdditionalLeaveTypes(): array
    {
        return [
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Medical related leave',
                'max_days' => 14,
                'min_days_per_request' => 1,
                'is_paid' => true,
                'requires_attachment' => true,
                'requires_medical_certificate' => true,
                'advance_notice_days' => 0,
                'cycle_type' => 'annual',
                'color' => '#F44336',
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Maternity related leave',
                'max_days' => 90,
                'min_days_per_request' => 84,
                'max_days_per_request' => 90,
                'is_paid' => true,
                'requires_attachment' => true,
                'requires_medical_certificate' => true,
                'advance_notice_days' => 30,
                'is_gender_specific' => true,
                'applicable_gender' => 'female',
                'cycle_type' => 'lifetime',
                'color' => '#E91E63',
                'is_active' => true,
            ],
            // Add more if needed
        ];
    }

    /**
     * Clear all existing leave types
     */
    protected function clearExistingLeaveTypes(): void
    {
        if ($this->command->confirm('Do you want to clear existing leave types before seeding?')) {
            LeaveType::query()->delete();
            $this->command->info('Cleared existing leave types.');
        }
    }

    /**
     * Add a single leave type
     */
    protected function addLeaveType(array $type): void
    {
        try {
            LeaveType::updateOrCreate(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_by' => 1,
                    'updated_by' => 1,
                ])
            );
        } catch (\Exception $e) {
            $this->command->error("Error adding leave type {$type['code']}: " . $e->getMessage());
        }
    }
}
