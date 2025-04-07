<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeContractStatusNotification;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class CheckExpiringContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-expiring-contracts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // Update app/Console/Commands/CheckExpiringContracts.php

    public function handle()
    {
        // Check probation periods ending in 7 days
        $probationEnding = Employee::probationEnding(7)->get();
        $this->info("Found {$probationEnding->count()} employees with probation ending soon.");

        // Check contracts expiring in 30 days
        $contractsExpiring = Employee::where('contract_type', 'contract')
            ->contractExpiringSoon(30)
            ->get();
        $this->info("Found {$contractsExpiring->count()} employees with contracts expiring soon.");

        $hrManagers = User::role('hr_manager')->get();

        // Handle probation notifications
        foreach ($probationEnding as $employee) {
            $daysRemaining = $employee->daysUntilProbationEnds();
            $notification = new EmployeeContractStatusNotification($employee, 'probation', $daysRemaining);

            // Notify employee
            if ($employee->user && $employee->canReceiveNotifications()) {
                $employee->user->notify($notification);
            }

            // Notify HR managers
            Notification::send($hrManagers, $notification);
        }

        // Handle contract notifications
        foreach ($contractsExpiring as $employee) {
            $daysRemaining = $employee->daysUntilContractExpires();
            $notification = new EmployeeContractStatusNotification($employee, 'contract', $daysRemaining);

            // Notify employee
            if ($employee->user && $employee->canReceiveNotifications()) {
                $employee->user->notify($notification);
            }

            // Notify HR managers
            Notification::send($hrManagers, $notification);
        }

        return Command::SUCCESS;
    }
}
