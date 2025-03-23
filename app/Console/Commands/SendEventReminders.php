<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use App\Models\Employee;
use App\Models\NotificationLog;
use App\Notifications\HolidayReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class SendEventReminders extends Command
{
    protected $signature = 'app:send-event-reminders {--debug : Show debug information}';
    protected $description = 'Send reminders for upcoming holidays and birthdays';

    public function handle()
    {
        $this->info('Starting Event Reminders...');

        // Get holidays for tomorrow
        $upcomingHolidays = Holiday::query()
            ->where('status', 'active')
            ->whereDate('date', now()->addDay())
            ->get();

        $this->info('Holidays found for tomorrow: ' . $upcomingHolidays->count());

        foreach ($upcomingHolidays as $holiday) {
            $this->sendHolidayNotifications($holiday);
        }

        $this->info('Event reminders completed!');
    }

    private function sendHolidayNotifications($holiday)
    {
        // Get employees who have enabled holiday notifications
        $employees = Employee::query()
            ->where('employment_status', 'active')
            ->whereHas('notificationPreferences', function ($query) {
                $query->where('holiday_notifications', true);
            })
            ->get();

        $notificationCount = 0;
        foreach ($employees as $employee) {
            try {
                // Get employee preferences
                $preferences = $employee->notificationPreferences;

                // Create notification log entry
                $log = new NotificationLog([
                    'employee_id' => $employee->id,
                    'type' => 'holiday',
                    'title' => $holiday->name,
                    'content' => "Holiday reminder for {$holiday->name} on {$holiday->date->format('Y-m-d')}",
                    'sent_at' => now(),
                ]);

                // Send notification based on preferences
                if ($preferences->email_notifications) {
                    $employee->notify(new HolidayReminderNotification($holiday));
                }

                $log->status = 'sent';
                $log->save();

                $notificationCount++;

                if ($this->option('debug')) {
                    $this->info("Sent holiday notification to: {$employee->email}");
                }

                // Add small delay to prevent overwhelming mail server
                usleep(100000); // 0.1 second delay

            } catch (\Exception $e) {
                $log->status = 'failed';
                $log->error_message = $e->getMessage();
                $log->save();

                Log::error('Failed to send notification', [
                    'employee' => $employee->id,
                    'error' => $e->getMessage()
                ]);

                if ($this->option('debug')) {
                    $this->error("Failed to send notification: " . $e->getMessage());
                }
            }
        }

        $this->info("Total notifications sent: $notificationCount");
    }
}
