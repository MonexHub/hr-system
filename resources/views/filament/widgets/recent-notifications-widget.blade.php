.notification-list-container {
max-height: 300px;
overflow-y: auto;
}{{-- resources/views/filament/widgets/RecentNotificationsWidget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

        <style>
            /* Compact card styles with original width */
            .card-container {
                position: relative;
                max-width: 100%;
                width: 100%; /* Maintain original width */
            }

            .card {
                background-color: white;
                border-radius: 12px;
                box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border: 1px solid rgba(229, 231, 235, 0.5);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            .dark .card {
                border-color: rgba(75, 85, 99, 0.5);
                background-color: #27272a;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.6), 0 2px 4px -1px rgba(0, 0, 0, 0.4);
            }

            .widget-title {
                font-size: 0.8rem;
                font-weight: 700;
                color: rgba(220,169,21,1);
                margin-bottom: 0;
                position: relative;
                padding-bottom: 0.5rem;
                display: inline-block;
            }

            .widget-title:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 50px;
                height: 3px;
                background: linear-gradient(90deg, rgba(220,169,21,1) 0%, rgba(220,169,21,0.5) 100%);
                border-radius: 2px;
            }

            .dark .widget-title {
                color: rgba(220,169,21,0.9);
            }

            .header-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 6px;
                border-bottom: 1px solid rgba(220,169,21,0.3);
                padding-bottom: 0.5rem;
                opacity: 0.3;
            }

            .dark .header-container {
                border-color: rgba(75, 85, 99, 0.5);
            }

            .notification-item {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.5rem;
                border-radius: 0.5rem;
                border: 1px solid rgba(229, 231, 235, 0.5);
                margin-bottom: 0.5rem;
                transition: all 0.3s ease;
            }

            .notification-item:hover {
                background-color: rgba(220,169,21,0.05);
                border-color: rgba(220,169,21,0.2);
                transform: translateY(-2px);
            }

            .dark .notification-item {
                border-color: rgba(75, 85, 99, 0.5);
            }

            .dark .notification-item:hover {
                background-color: rgba(220,169,21,0.1);
                border-color: rgba(220,169,21,0.3);
            }

            .notification-icon {
                width: 2rem;
                height: 2rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
            }

            .notification-icon i {
                color: rgba(220,169,21,1) !important;
            }

            .notification-content {
                flex: 1;
                min-width: 0;
            }

            .notification-title {
                font-size: 0.65rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dark .notification-title {
                color: #f3f4f6;
            }

            .notification-meta {
                font-size: 0.55rem;
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .user-initials {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.5rem;
                height: 1.5rem;
                border-radius: 50%;
                background-color: rgba(220,169,21,0.2);
                color: rgba(220,169,21,1);
                font-size: 0.6rem;
                font-weight: 700;
                margin-right: 0.5rem;
                border: 1px solid rgba(220,169,21,0.5);
            }

            .dark .notification-meta {
                color: #9ca3af;
            }

            .notification-time {
                font-size: 0.55rem;
                color: #6b7280;
                font-family: monospace;
            }

            .dark .notification-time {
                color: #9ca3af;
            }

            .notification-status {
                font-size: 0.55rem;
                font-weight: 600;
                padding: 0.1rem 0.4rem;
                border-radius: 9999px;
                text-transform: capitalize;
            }

            .status-sent {
                background-color: rgba(220,169,21,0.1);
                color: rgba(220,169,21,1);
            }

            .status-failed {
                background-color: rgba(239, 68, 68, 0.1);
                color: rgb(239, 68, 68);
            }

            .all-notifications-link {
                display: inline-flex;
                align-items: center;
                font-size: 0.6rem;
                font-weight: 600;
                color: rgba(220,169,21,1);
                margin-top: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                transition: all 0.2s ease;
                gap: 0.25rem;
            }

            .all-notifications-link:hover {
                color: rgba(220,169,21,0.8);
            }

            .empty-notifications {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                color: #6b7280;
                font-size: 0.65rem;
            }

            .dark .empty-notifications {
                color: #9ca3af;
            }

            .notification-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
                padding: 0.2rem 0.5rem;
                border-radius: 9999px;
                font-size: 0.55rem;
                font-weight: 600;
                background-color: rgba(220,169,21,0.1);
                color: rgba(220,169,21,1);
                border: 1px solid rgba(220,169,21,0.2);
            }

            .dark .notification-badge {
                background-color: rgba(220,169,21,0.15);
                border-color: rgba(220,169,21,0.3);
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .notification-item {
                animation: fadeIn 0.3s ease-out forwards;
            }

            .notification-item:nth-child(1) {
                animation-delay: 0.1s;
            }

            .notification-item:nth-child(2) {
                animation-delay: 0.15s;
            }

            .notification-item:nth-child(3) {
                animation-delay: 0.2s;
            }

            .notification-item:nth-child(4) {
                animation-delay: 0.25s;
            }

            .notification-item:nth-child(5) {
                animation-delay: 0.3s;
            }
        </style>

        <div class="container mx-auto">
            <div class="header-container mb-3">
                <h1 class="widget-title mb-0 pb-2">Recent Notifications</h1>
                <div class="notification-badge">
                    <i class="fas fa-sparkles text-xs"></i>
                    <span>Today: {{ $this->getNotificationCountToday() }}</span>
                </div>
            </div>

            <div class="notifications-list p-2 notification-list-container">
                @foreach($this->getNotifications() as $index => $notification)
                    <div class="notification-item">
                        <div class="notification-icon" style="background-color: {{ $notification['status'] === 'sent' ? 'rgba(220,169,21,0.1)' : 'rgba(239, 68, 68, 0.1)' }}; border: 1px solid {{ $notification['status'] === 'sent' ? 'rgba(220,169,21,0.3)' : 'rgba(239, 68, 68, 0.3)' }};">
                            <i class="{{ str_replace('heroicon-o-', 'fas fa-', $notification['icon']) }}" style="color: {{ $notification['status'] === 'sent' ? 'rgba(220,169,21,1)' : 'rgb(239, 68, 68)' }} !important;"></i>
                        </div>

                        <div class="notification-content">
                            <p class="notification-title">{{ $notification['title'] }}</p>
                            <div class="notification-meta">
                                <div class="flex items-center">
                                    <span class="user-initials">
                                        {{ strtoupper(substr($notification['employee'], 0, 1) . (isset(explode(' ', $notification['employee'])[1]) ? substr(explode(' ', $notification['employee'])[1], 0, 1) : '')) }}
                                    </span>
                                    <span>{{ $notification['employee'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <span class="notification-status {{ $notification['status'] === 'sent' ? 'status-sent' : 'status-failed' }}">
                                {{ $notification['status'] }}
                            </span>
                            <span class="notification-time">
                                {{ $notification['sent_at'] }}
                            </span>
                        </div>
                    </div>
                @endforeach

                @if($this->getNotifications()->isEmpty())
                    <div class="empty-notifications">
                        <i class="fas fa-inbox mr-2"></i>
                        No recent notifications
                    </div>
                @endif
            </div>

            <div class="p-2 mt-2 text-center">
                <a href="{{ route('filament.admin.resources.notification-logs.index') }}" class="all-notifications-link">
                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                    View all notifications
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
