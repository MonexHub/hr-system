{{-- resources/views/filament/widgets/RecentNotificationsWidget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section class="!p-0 !rounded-xl !border-0 !ring-1 !ring-gray-200 dark:!ring-gray-700 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between gap-4 p-6 bg-primary-50/40 dark:bg-slate-900 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg sm:text-xl font-bold tracking-tight flex items-center gap-2 text-primary-700 dark:text-primary-400">
                <x-heroicon-o-bell-alert class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                Recent Notifications
            </h2>

            <div class="flex gap-2 items-center">
                <x-filament::badge
                    icon="heroicon-o-sparkles"
                    color="primary"
                    class="ring-1 ring-primary-600/20 dark:ring-primary-400/30 bg-primary-100/50 dark:bg-primary-400/10"
                >
                    <span class="text-primary-900 dark:text-primary-200">
                        Today: {{ $this->getNotificationCountToday() }}
                    </span>
                </x-filament::badge>
            </div>
        </div>

        <div class="p-4 space-y-2">
            @foreach($this->getNotifications() as $notification)
                <div class="group flex items-center gap-4 p-4 rounded-lg transition-all hover:bg-gray-50/50 dark:hover:bg-gray-800 border border-gray-200/50 dark:border-gray-700">
                    <div @class([
                        'flex items-center justify-center w-10 h-10 rounded-full ring-1 ring-inset',
                        'bg-success-50/50 ring-success-200/50 dark:bg-success-900/20 dark:ring-success-800/30' => $notification['status'] === 'sent',
                        'bg-danger-50/50 ring-danger-200/50 dark:bg-danger-900/20 dark:ring-danger-800/30' => $notification['status'] === 'failed',
                    ])>
                        @svg($notification['icon'], 'w-5 h-5 ' . ($notification['status'] === 'sent' ?
                        'text-success-600 dark:text-success-400' :
                        'text-danger-600 dark:text-danger-400'))
                    </div>

                    <div class="flex-1 min-w-0 space-y-0.5">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                            {{ $notification['title'] }}
                        </p>
                        <div class="flex items-center gap-2 text-sm">
                            <x-heroicon-o-user-circle class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ $notification['employee'] }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5">
                        <x-filament::badge
                            :color="$notification['status_color']"
                            size="sm"
                            class="ring-1 ring-{!! $notification['status_color'] !!}-500/20 dark:ring-{!! $notification['status_color'] !!}-400/30"
                        >
                            {{ $notification['status'] }}
                        </x-filament::badge>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                            {{ $notification['sent_at'] }}
                        </span>
                    </div>
                </div>
            @endforeach

            @if($this->getNotifications()->isEmpty())
                <div class="flex items-center justify-center p-6 text-sm text-gray-600 dark:text-gray-400">
                    <x-heroicon-o-inbox class="w-5 h-5 mr-2 text-gray-400 dark:text-gray-500" />
                    No recent notifications
                </div>
            @endif
        </div>

        <div class="p-4 border-t border-gray-200/50 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-900">
            <x-filament::link
                href="{{ route('filament.admin.resources.notification-logs.index') }}"
                icon="heroicon-s-arrow-top-right-on-square"
                class="font-medium text-primary-700 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
            >
                View all notifications
            </x-filament::link>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
