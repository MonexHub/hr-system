{{-- resources/views/filament/widgets/holiday-calendar-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section class="!p-0 !rounded-lg !border-0 !ring-1 !ring-gray-200 dark:!ring-gray-700 bg-white dark:bg-gray-900" s>
        <div class="flex items-center justify-between gap-4 p-6 bg-primary-50/30 dark:bg-slate-900 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg sm:text-xl font-bold tracking-tight flex items-center gap-2 text-primary-600 dark:text-primary-400">
                <x-heroicon-o-calendar class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                {{ app()->getLocale() === 'sw' ? 'Matukio Yanayokuja' : 'Upcoming Events' }}
            </h2>

            <div class="flex gap-2 items-center">
                <x-filament::badge
                    icon="heroicon-o-sparkles"
                    color="primary"
                    class="ring-1 ring-primary-500/20 dark:ring-primary-400/20"
                >
                    {{ $this->events->count() }} {{ app()->getLocale() === 'sw' ? 'Matukio' : 'Events' }}
                </x-filament::badge>
            </div>
        </div>

        @if($this->events->isEmpty())
            <x-filament::section class="mx-6 mb-6 bg-gray-50/50 dark:bg-gray-700/50 ring-1 ring-gray-200 dark:ring-gray-700">
                <div class="flex items-center justify-center p-4 text-sm text-gray-600 dark:text-gray-300">
                    <x-heroicon-o-calendar class="w-5 h-5 mr-2 text-gray-400 dark:text-gray-500" />
                    {{ app()->getLocale() === 'sw'
                        ? 'Hakuna matukio yanayokuja kwa siku 30 zijazo'
                        : 'No upcoming events in the next 30 days'
                    }}
                </div>
            </x-filament::section>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700 overflow-y-auto max-h-96">
                @foreach($this->events as $event)
                    <div x-data="{ open: false }" class="group hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                        <button
                            @click="open = !open"
                            class="w-full px-6 py-4 text-left flex items-center gap-4 focus:ring-2 focus:ring-primary-500/50 focus:outline-none"
                        >
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3">
                                    <x-filament::badge
                                        :color="$event['badge']"
                                        class="shrink-0 ring-1 ring-{!! $event['badge'] !!}-500/20 dark:ring-{!! $event['badge'] !!}-400/20"
                                    >
                                        {{ $event['daysUntil'] }}
                                    </x-filament::badge>

                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                        {{ $event['title'] }}
                                    </span>
                                </div>

                                <div class="mt-2 flex items-center gap-2 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        <x-heroicon-o-clock class="w-4 h-4 inline mr-1 text-gray-500 dark:text-gray-400" />
                                        {{ \Carbon\Carbon::parse($event['date'])->format('M j, Y') }}
                                    </span>

                                    @if(isset($event['location']))
                                        <span class="text-gray-400 dark:text-gray-600">•</span>
                                        <span class="text-gray-600 dark:text-gray-400">
                                            <x-heroicon-o-map-pin class="w-4 h-4 inline mr-1 text-gray-500 dark:text-gray-400" />
                                            {{ $event['location'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0 transform transition-transform" :class="{ 'rotate-180': open }">
                                <x-heroicon-o-chevron-down class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                            </div>
                        </button>

                        <div x-show="open" x-collapse class="px-6 pb-4 -mt-2">
                            @if(isset($event['description']) && $event['description'])
                                <div class="prose prose-sm max-w-none border-l-2 border-primary-200 dark:border-primary-400/50 pl-4 text-gray-700 dark:text-gray-300">
                                    {{ $event['description'] }}
                                </div>
                            @endif

                            @if(isset($event['participants']))
                                <div class="mt-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-user-group class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                    {{ implode(', ', $event['participants']) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ app()->getLocale() === 'sw'
                    ? '* Yaliyoonyeshwa ni matukio ya siku 30 zijazo'
                    : '* Showing events within next 30 days'
                }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
