<?php

namespace App\Filament\Widgets;

use App\Models\NotificationLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class RecentNotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-notifications-widget';

    protected int | string | array $columnSpan = 'half';
    protected static ?int $sort = 2;

    // Add polling for real-time updates
    protected static ?string $pollingInterval = '15s';

    public function getNotifications()
    {
        return NotificationLog::query()
            ->with('employee')
            ->latest('sent_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'title' => $log->title,
                    'employee' => $log->employee->full_name,
                    'type' => $log->type,
                    'status' => $log->status,
                    'sent_at' => Carbon::parse($log->sent_at)->diffForHumans(),
                    'status_color' => match($log->status) {
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'warning'
                    },
                    'icon' => match($log->type) {
                        'holiday' => 'heroicon-m-calendar',
                        'birthday' => 'heroicon-m-cake',
                        default => 'heroicon-m-bell'
                    }
                ];
            });
    }

    public function getNotificationCountToday(): int
    {
        return NotificationLog::whereDate('sent_at', today())->count();
    }
}
