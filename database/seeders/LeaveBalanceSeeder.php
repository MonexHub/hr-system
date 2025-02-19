<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveBalanceSeeder extends Seeder
{
    public function run()
    {
        // First, let's clean up any existing balances
        DB::table('leave_balances')->truncate();

        // Get admin user for created_by
        $admin = User::role('super_admin')->first();
        if (!$admin) {
            throw new \Exception('Super admin user not found. Please run UserEmployeeSeeder first.');
        }

        // Get all active employees
        $employees = Employee::where('employment_status', 'active')->get();

        // Get all active leave types
        $leaveTypes = LeaveType::where('is_active', true)->get();

        if ($leaveTypes->isEmpty()) {
            throw new \Exception('No active leave types found. Please run LeaveTypeSeeder first.');
        }

        $currentYear = now()->year;
        $balancesCreated = 0;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                // Check if balance already exists
                $exists = LeaveBalance::where([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $currentYear
                ])->exists();

                if (!$exists) {
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'entitled_days' => $leaveType->max_days_per_year ?? 0,
                        'carried_forward_days' => 0,
                        'additional_days' => 0,
                        'taken_days' => 0,
                        'pending_days' => 0,
                        'year' => $currentYear,
                        'remarks' => "Initial balance for {$currentYear}",
                        'created_by' => $admin->id
                    ]);
                    $balancesCreated++;
                }
            }
        }

        // Log the results
        $this->command->info("Created {$balancesCreated} leave balances for {$employees->count()} employees and {$leaveTypes->count()} leave types.");

        // Verify the results
        $totalBalances = LeaveBalance::count();
        $expectedBalances = $employees->count() * $leaveTypes->count();

        if ($totalBalances !== $expectedBalances) {
            $this->command->warn("Warning: Found {$totalBalances} balances, expected {$expectedBalances}");
        } else {
            $this->command->info("Verification successful: {$totalBalances} balances found as expected.");
        }
    }
}
