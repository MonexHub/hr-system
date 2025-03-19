{{-- resources/views/filament/widgets/holiday-calendar-widget.blade.php --}}
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

            .events-badge {
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

            .dark .events-badge {
                background-color: rgba(220,169,21,0.15);
                border-color: rgba(220,169,21,0.3);
            }

            .empty-events {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                color: #6b7280;
                font-size: 0.65rem;
                background-color: rgba(229, 231, 235, 0.3);
                border-radius: 0.5rem;
                margin: 0.75rem 0;
                border: 1px solid rgba(229, 231, 235, 0.5);
            }

            .dark .empty-events {
                color: #9ca3af;
                background-color: rgba(75, 85, 99, 0.2);
                border-color: rgba(75, 85, 99, 0.5);
            }

            .event-container {
                border-bottom: 1px solid rgba(229, 231, 235, 0.5);
                transition: all 0.3s ease;
            }

            .event-container:last-child {
                border-bottom: none;
            }

            .event-container:hover {
                background-color: rgba(220,169,21,0.05);
            }

            .dark .event-container {
                border-color: rgba(75, 85, 99, 0.5);
            }

            .dark .event-container:hover {
                background-color: rgba(220,169,21,0.1);
            }

            .event-header {
                width: 100%;
                padding: 0.75rem 1rem;
                text-align: left;
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
                cursor: pointer;
                transition: all 0.3s ease;
                outline: none;
            }

            .event-header:focus {
                background-color: rgba(220,169,21,0.05);
            }

            .dark .event-header:focus {
                background-color: rgba(220,169,21,0.1);
            }

            .event-title {
                font-size: 0.65rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dark .event-title {
                color: #f3f4f6;
            }

            .event-meta {
                font-size: 0.55rem;
                color: #6b7280;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 0.25rem;
            }

            .dark .event-meta {
                color: #9ca3af;
            }

            .event-meta-item {
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .event-body {
                padding: 0 1rem 1rem 1rem;
                margin-top: -0.5rem;
            }

            .event-description {
                font-size: 0.6rem;
                color: #4b5563;
                border-left: 2px solid rgba(220,169,21,0.5);
                padding-left: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .dark .event-description {
                color: #9ca3af;
                border-left-color: rgba(220,169,21,0.3);
            }

            .event-participants {
                font-size: 0.55rem;
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 0.25rem;
                margin-top: 0.5rem;
            }

            .dark .event-participants {
                color: #9ca3af;
            }

            .events-container {
                max-height: 300px;
                overflow-y: auto;
            }

            .footer {
                font-size: 0.55rem;
                color: #6b7280;
                padding: 0.75rem 1rem;
                border-top: 1px solid rgba(229, 231, 235, 0.5);
                background-color: rgba(229, 231, 235, 0.1);
            }

            .dark .footer {
                color: #9ca3af;
                border-color: rgba(75, 85, 99, 0.5);
                background-color: rgba(75, 85, 99, 0.2);
            }

            .day-badge {
                display: inline-flex;
                padding: 0.1rem 0.4rem;
                border-radius: 9999px;
                font-size: 0.55rem;
                font-weight: 600;
                text-transform: capitalize;
            }

            .badge-success {
                background-color: rgba(16, 185, 129, 0.1);
                color: rgb(16, 185, 129);
            }

            .badge-warning {
                background-color: rgba(245, 158, 11, 0.1);
                color: rgb(245, 158, 11);
            }

            .badge-danger {
                background-color: rgba(239, 68, 68, 0.1);
                color: rgb(239, 68, 68);
            }

            .badge-primary {
                background-color: rgba(220,169,21,0.1);
                color: rgba(220,169,21,1);
            }

            .rotate-icon {
                transition: transform 0.3s ease;
            }

            .rotate-180 {
                transform: rotate(180deg);
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

            .event-container {
                animation: fadeIn 0.3s ease-out forwards;
            }
        </style>

        <div class="container mx-auto">
            <div class="header-container mb-3">
                <h1 class="widget-title mb-0 pb-2">
                    <i class="fas fa-calendar mr-2"></i>
                    {{ app()->getLocale() === 'sw' ? 'Matukio Yanayokuja' : 'Upcoming Events' }}
                </h1>
                <div class="events-badge">
                    <i class="fas fa-sparkles text-xs"></i>
                    <span>{{ $this->events->count() }} {{ app()->getLocale() === 'sw' ? 'Matukio' : 'Events' }}</span>
                </div>
            </div>

            @if($this->events->isEmpty())
                <div class="empty-events">
                    <i class="fas fa-calendar-xmark mr-2"></i>
                    {{ app()->getLocale() === 'sw'
                        ? 'Hakuna matukio yanayokuja kwa siku 30 zijazo'
                        : 'No upcoming events in the next 30 days'
                    }}
                </div>
            @else
                <div class="events-container">
                    @foreach($this->events as $event)
                        <div x-data="{ open: false }" class="event-container">
                            <button
                                @click="open = !open"
                                class="event-header"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="day-badge {{ $event['badge'] === 'primary' ? 'badge-primary' : ($event['badge'] === 'danger' ? 'badge-danger' : ($event['badge'] === 'warning' ? 'badge-warning' : 'badge-success')) }}">
                                            {{ $event['daysUntil'] }}
                                        </span>

                                        <span class="event-title">
                                            {{ $event['title'] }}
                                        </span>
                                    </div>

                                    <div class="event-meta">
                                        <span class="event-meta-item">
                                            <i class="fas fa-clock text-xs" style="color: rgba(220,169,21,0.8) !important;"></i>
                                            {{ \Carbon\Carbon::parse($event['date'])->format('M j, Y') }}
                                        </span>

                                        @if(isset($event['location']))
                                            <span class="text-gray-400 dark:text-gray-600">•</span>
                                            <span class="event-meta-item">
                                                <i class="fas fa-map-pin text-xs" style="color: rgba(220,169,21,0.8) !important;"></i>
                                                {{ $event['location'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="shrink-0 rotate-icon" :class="{ 'rotate-180': open }">
                                    <i class="fas fa-chevron-down text-xs" style="color: rgba(220,169,21,0.8) !important;"></i>
                                </div>
                            </button>

                            <div x-show="open" x-collapse class="event-body">
                                @if(isset($event['description']) && $event['description'])
                                    <div class="event-description">
                                        {{ $event['description'] }}
                                    </div>
                                @endif

                                @if(isset($event['participants']))
                                    <div class="event-participants">
                                        <i class="fas fa-users text-xs" style="color: rgba(220,169,21,0.8) !important;"></i>
                                        {{ implode(', ', $event['participants']) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="footer">
                <i class="fas fa-info-circle text-xs mr-1" style="color: rgba(220,169,21,0.8) !important;"></i>
                {{ app()->getLocale() === 'sw'
                    ? '* Yaliyoonyeshwa ni matukio ya siku 30 zijazo'
                    : '* Showing events within next 30 days'
                }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
