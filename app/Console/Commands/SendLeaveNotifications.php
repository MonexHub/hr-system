<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendLeaveNotifications extends Command
{

    protected $signature = 'app:send-leave-notifications';
    protected $description = 'Process and send leave request notifications';

    public function handle(): void
    {
        try {
            // Get pending leave requests and process notifications
            $pendingRequests = LeaveRequest::where('status', 'pending')->get();

            foreach ($pendingRequests as $request) {
                $request->employee->reportingTo->notify(
                    new \App\Notifications\LeaveRequestNotification($request, 'new_request')
                );
            }

            Log::info('Leave notifications sent successfully', ['count' => $pendingRequests->count()]);
            $this->info('Leave notifications sent successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to send leave notifications', [
                'error' => $e->getMessage()
            ]);
            $this->error('Failed to send notifications: ' . $e->getMessage());
        }
    }
}
