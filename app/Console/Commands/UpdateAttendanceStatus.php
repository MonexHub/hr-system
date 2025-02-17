<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;

class UpdateAttendanceStatus extends Command
{
    protected $signature = 'attendance:update-status';
    protected $description = 'Update status for all attendance records';

    public function handle()
    {
        Attendance::whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get()
            ->each(function ($attendance) {
                $attendance->save(); // This will trigger status update
            });

        $this->info('Attendance statuses updated successfully.');
    }
}
