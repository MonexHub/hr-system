<?php

namespace App\Filament\Widgets;

use App\Models\NotificationLog;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class RecentNotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-notifications-widget';

    protected int | string | array $columnSpan = 'half';
    use HasWidgetShield;
//    protected static ?int $sort = 2;

    // Add polling for real-time updates
    protected static ?string $pollingInterval = '15s';

    // Helper method to check if user has access to all employees' notifications
    protected function canViewAllNotifications(): bool
    {
        return auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('hr-manager');
    }

    public function getNotifications()
    {
        $query = NotificationLog::query()
            ->with('employee')
            ->latest('sent_at')
            ->limit(5);

        // Filter notifications for regular employees
        if (!$this->canViewAllNotifications()) {
            $query->whereHas('employee', function (Builder $query) {
                $query->where('id', auth()->user()->employee->id);
            });
        }

        return $query->get()
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
        $query = NotificationLog::whereDate('sent_at', today());

        // Filter counts for regular employees
        if (!$this->canViewAllNotifications()) {
            $query->whereHas('employee', function (Builder $query) {
                $query->where('id', auth()->user()->employee->id);
            });
        }

        return $query->count();
    }

    // Get widget title based on user role
    public function getWidgetTitle(): string
    {
        if ($this->canViewAllNotifications()) {
            return 'Recent Notifications';
        }

        return 'Your Recent Notifications';
    }

    // New method to check if should render widget
    public static function canView(): bool
    {
        // Always show to all users - everyone should see their notifications
        return true;
    }
}
