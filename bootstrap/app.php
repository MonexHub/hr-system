<?php

use App\Mail\WeeklyContractStatusReport;
use App\Models\Employee;
use App\Models\Holiday;
use App\Notifications\BirthdayReminderNotification;
use App\Notifications\EmployeeContractStatusNotification;
use App\Notifications\HolidayReminderNotification;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Middleware\ForceJsonResponse;


use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '/api', // This sets the prefix for API routes
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // 1. Contract and Probation Status Checks (9 AM EAT)
        $schedule->job(function () {
            try {
                // Fetch employees with expiring contracts or probation
                Employee::query()
                    ->where('employment_status', 'active')
                    ->where(function ($query) {
                        // Contract expiring in next 30 days
                        $query->where(function ($q) {
                            $q->where('contract_type', 'contract')
                                ->whereNotNull('contract_end_date')
                                ->whereBetween('contract_end_date', [
                                    now(),
                                    now()->addDays(30)
                                ]);
                        })
                            // Probation ending in next 7 days
                            ->orWhere(function ($q) {
                                $q->where('contract_type', 'probation')
                                    ->whereRaw('DATE_ADD(appointment_date, INTERVAL 3 MONTH) BETWEEN ? AND ?', [
                                        now(),
                                        now()->addDays(7)
                                    ]);
                            });
                    })
                    ->each(function ($employee) {
                        $this->processContractNotifications($employee);
                    });

            } catch (\Throwable $e) {
                Log::error('Contract status check failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        })
            ->name('check-contract-status')
            ->dailyAt('09:00')
            ->timezone('Africa/Dar_es_Salaam');

        // 2. Holiday Notifications (8 AM EAT)
        $schedule->job(function () {
            try {
                Holiday::query()
                    ->where('status', 'active')
                    ->where(function ($query) {
                        // One-time holidays
                        $query->where('is_recurring', false)
                            ->whereBetween('date', [
                                now()->addDay(),
                                now()->addDays(3)
                            ]);
                    })
                    ->orWhere(function ($query) {
                        // Recurring holidays
                        $query->where('is_recurring', true)
                            ->whereRaw('DATE_FORMAT(date, "%m-%d") BETWEEN ? AND ?', [
                                now()->addDay()->format('m-d'),
                                now()->addDays(3)->format('m-d')
                            ]);
                    })
                    ->each(function ($holiday) {
                        $this->processHolidayNotifications($holiday);
                    });

            } catch (\Throwable $e) {
                Log::error('Holiday notification failed', [
                    'error' => $e->getMessage()
                ]);
            }
        })
            ->name('send-holiday-notifications')
            ->dailyAt('08:00')
            ->timezone('Africa/Dar_es_Salaam');

        // 3. Birthday Notifications (7 AM EAT)
        $schedule->job(function () {
            try {
                Employee::query()
                    ->where('employment_status', 'active')
                    ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') = ?", [
                        now()->addDay()->format('m-d')
                    ])
                    ->each(function ($birthdayEmployee) {
                        $this->processBirthdayNotifications($birthdayEmployee);
                    });

            } catch (\Throwable $e) {
                Log::error('Birthday notification failed', [
                    'error' => $e->getMessage()
                ]);
            }
        })
            ->name('send-birthday-notifications')
            ->dailyAt('07:00')
            ->timezone('Africa/Dar_es_Salaam');

        // 4. Weekly Contract Status Report (Monday 7 AM EAT)
        $schedule->job(function () {
            try {
                $report = $this->generateContractStatusReport();
                $this->sendContractStatusReport($report);

            } catch (\Throwable $e) {
                Log::error('Contract status report generation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        })
            ->name('generate-contract-status-report')
            ->weeklyOn(1, '07:00')
            ->timezone('Africa/Dar_es_Salaam');

        // 5. Cleanup Old Notifications (Midnight)
        $schedule->job(function () {
            try {
                DB::table('notification_logs')
                    ->where('created_at', '<', now()->subDays(30))
                    ->delete();

            } catch (\Throwable $e) {
                Log::error('Cleanup notification logs failed', [
                    'error' => $e->getMessage()
                ]);
            }
        })
            ->name('cleanup-notification-logs')
            ->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {
     try {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            // Render JSON for API routes or when the request expects JSON
            return Str::startsWith($request->path(), 'api/') || $request->expectsJson();
        });
     } catch (\Throwable $th) {
            //throw $th;
     }
    })
    ->create();

/**
 * Helper function to process contract notifications
 */
function processContractNotifications($employee)
{
    $daysRemaining = $employee->contract_type === 'probation'
        ? $employee->daysUntilProbationEnds()
        : $employee->daysUntilContractExpires();

    // Notify employee if they can receive notifications
    if ($employee->user && $employee->canReceiveNotifications()) {
        $employee->user->notify(
            new EmployeeContractStatusNotification(
                $employee,
                $employee->contract_type,
                $daysRemaining
            )
        );
    }

    // Notify HR managers
    \App\Models\User::role('hr_manager')->each(function ($hrManager) use ($employee, $daysRemaining) {
        $hrManager->notify(
            new EmployeeContractStatusNotification(
                $employee,
                $employee->contract_type,
                $daysRemaining
            )
        );
    });

    // Log the notification
    \App\Models\NotificationLog::create([
        'employee_id' => $employee->id,
        'type' => 'contract_status',
        'message' => "Contract status notification sent for {$employee->full_name}",
        'details' => [
            'contract_type' => $employee->contract_type,
            'days_remaining' => $daysRemaining,
            'end_date' => $employee->contract_type === 'probation'
                ? $employee->getProbationEndDate()
                : $employee->contract_end_date,
        ]
    ]);
}

/**
 * Helper function to process holiday notifications
 */
function processHolidayNotifications($holiday)
{
    Employee::query()
        ->where('employment_status', 'active')
        ->whereHas('notificationPreferences', function ($query) {
            $query->where('holiday_notifications', true);
        })
        ->each(function ($employee) use ($holiday) {
            $employee->notify(new HolidayReminderNotification($holiday));
        });
}

/**
 * Helper function to process birthday notifications
 */
function processBirthdayNotifications($birthdayEmployee)
{
    Employee::query()
        ->where('employment_status', 'active')
        ->where('id', '!=', $birthdayEmployee->id)
        ->whereHas('notificationPreferences', function ($query) {
            $query->where('birthday_notifications', true);
        })
        ->each(function ($colleague) use ($birthdayEmployee) {
            $colleague->notify(new BirthdayReminderNotification($birthdayEmployee));
        });
}

/**
 * Helper function to generate contract status report
 */
function generateContractStatusReport()
{
    return [
        'generated_at' => now()->format('Y-m-d H:i:s'),
        'probation_ending_soon' => Employee::query()
            ->where('employment_status', 'active')
            ->where('contract_type', 'probation')
            ->whereRaw('DATE_ADD(appointment_date, INTERVAL 3 MONTH) BETWEEN ? AND ?', [
                now(),
                now()->addDays(30)
            ])
            ->get()
            ->map(function ($employee) {
                return [
                    'employee' => $employee->full_name,
                    'employee_code' => $employee->employee_code,
                    'department' => $employee->department->name,
                    'end_date' => $employee->getProbationEndDate()->format('Y-m-d'),
                    'days_remaining' => $employee->daysUntilProbationEnds(),
                ];
            }),
        'contracts_expiring_soon' => Employee::query()
            ->where('employment_status', 'active')
            ->where('contract_type', 'contract')
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [
                now(),
                now()->addDays(30)
            ])
            ->get()
            ->map(function ($employee) {
                return [
                    'employee' => $employee->full_name,
                    'employee_code' => $employee->employee_code,
                    'department' => $employee->department->name,
                    'end_date' => $employee->contract_end_date->format('Y-m-d'),
                    'days_remaining' => $employee->daysUntilContractExpires(),
                ];
            }),
    ];
}

/**
 * Helper function to send contract status report
 */
function sendContractStatusReport($report)
{
    // Store report
    Storage::put(
        'reports/contract_status_' . now()->format('Y-m-d') . '.json',
        json_encode($report, JSON_PRETTY_PRINT)
    );

    // Send to HR managers
    \App\Models\User::role('hr_manager')->each(function ($hrManager) use ($report) {
        Mail::to($hrManager)->send(new WeeklyContractStatusReport($report));
    });
}
