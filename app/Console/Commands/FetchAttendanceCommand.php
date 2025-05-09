<?php

namespace App\Console\Commands;

use App\Jobs\FetchAttendanceJob;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class FetchAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:fetch
                            {--start= : Start date (format: Y-m-d)}
                            {--end= : End date (format: Y-m-d)}
                            {--departments= : Comma separated department IDs}
                            {--job-name= : Custom name for the job (for logging)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch attendance data from ZKBiotime API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start');
        $endDate = $this->option('end');
        $departments = $this->option('departments');
        $jobName = $this->option('job-name');

        if (!$startDate) {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }

        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        }

        $this->info("Fetching attendance data from {$startDate} to {$endDate}");

        if ($departments) {
            $this->info("For departments: {$departments}");
        } else {
            $this->info("For all departments");
        }

        // Dispatch the job and get the Job instance back
        $job = FetchAttendanceJob::dispatch($startDate, $endDate, $departments);

        if ($jobName) {
            // Store the job name in the database or cache for reference if needed
            // This is a workaround since Laravel job instances don't have a direct 'name' property
            Cache::put("job_name:{$job->id}", $jobName, now()->addDay());
        }

        $this->info('Attendance fetch job dispatched successfully.');
    }
}
