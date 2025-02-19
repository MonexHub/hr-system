<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Console\Command;

class CreateLeaveBalancesCommand extends Command
{
    protected $signature = 'leave:create-balances';
    protected $description = 'Create leave balances for all active employees';

    public function handle()
    {
        $employees = Employee::where('employment_status', 'active')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;
        $count = 0;

        $this->info("Starting to create leave balances...");
        $bar = $this->output->createProgressBar(count($employees) * count($leaveTypes));

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
                        'created_by' => 1 // admin user
                    ]);
                    $count++;
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Created {$count} new leave balances.");
    }
}
