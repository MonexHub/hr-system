{{-- resources/views/filament/widgets/recent-notifications-widget.blade.php --}}
<x-filament-widgets::widget>
    <div class="flex items-center justify-between p-2">
        <div class="flex items-center gap-2">
            <h2 class="text-lg font-medium text-gray-900">
                Recent Notifications
            </h2>
            <span class="px-2 py-1 text-xs font-medium bg-primary-100 text-primary-700 rounded-full">
                {{ $this->notificationCountToday }}
            </span>
        </div>
        <button wire:click="refresh" class="text-gray-400 hover:text-primary-500">
            <x-heroicon-o-arrow-path class="w-5 h-5" />
        </button>
    </div>

    <div class="space-y-2 p-2">
        @forelse ($this->notifications as $notification)
            <div wire:key="{{ $notification['id'] }}"
                 class="flex items-center gap-4 p-2 bg-white rounded-lg border transition hover:bg-gray-50">
                <div @class([
                    'flex items-center justify-center w-10 h-10 rounded-full',
                    'bg-success-50' => $notification['status'] === 'sent',
                    'bg-danger-50' => $notification['status'] === 'failed',
                ])>
                    @svg($notification['icon'], 'w-5 h-5')
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ $notification['title'] }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ $notification['employee'] }}
                    </p>
                </div>

                <div class="flex flex-col items-end gap-1">
                    <x-filament::badge :color="$notification['status_color']" size="sm">
                        {{ $notification['status'] }}
                    </x-filament::badge>
                    <span class="text-xs text-gray-500">
                        {{ $notification['sent_at'] }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-4 text-sm text-gray-500 text-center">
                No recent notifications
            </div>
        @endforelse
    </div>
    <div class="p-2 flex justify-end">
        {{ $this->notifications->count() > 0 ?
            \Filament\Support\View\Components\Link::make()
                ->url(route('filament.admin.resources.notification-logs.index'))
                ->label('View all notifications')
                ->icon('heroicon-m-arrow-right')
                ->iconPosition('after')
        : null }}
    </div>
</x-filament-widgets::widget>
