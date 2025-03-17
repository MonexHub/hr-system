<x-filament-widgets::widget>
    <x-filament::section>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

        <style>
            /* Matching the consistent card styles */
            .card-container {
                position: relative;
                height: 100%;
            }

            .card {
                background-color: white;
                border-radius: 12px;
                box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                position: relative;
                overflow: hidden;
                max-width: 100%;
                transition: all 0.3s ease;
                border: 1px solid rgba(229, 231, 235, 0.5);
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            .dark .card {
                background-color: #1f2937;
                border-color: rgba(75, 85, 99, 0.5);
                box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.7), 0 4px 6px -2px rgba(0, 0, 0, 0.5);
            }

            .icon-bg {
                width: 46px;
                height: 46px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, rgba(220,169,21,0.15) 0%, rgba(220,169,21,0.25) 100%);
                border: 2px solid rgba(220,169,21,0.7);
                box-shadow: 0 4px 6px -1px rgba(220,169,21,0.15);
                transition: all 0.3s ease;
            }

            .card:hover .icon-bg {
                transform: scale(1.05);
                background: linear-gradient(135deg, rgba(220,169,21,0.2) 0%, rgba(220,169,21,0.35) 100%);
            }

            .card-title {
                color: #6b7280;
                font-size: 0.8rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 0.5rem;
            }

            .dark .card-title {
                color: #9ca3af;
            }

            .card-value {
                font-size: 1.75rem;
                font-weight: 700;
                color: #111827;
                margin-top: 0.25rem;
                transition: all 0.3s ease;
                line-height: 1.1;
            }

            .card:hover .card-value {
                color: rgba(220,169,21,1);
            }

            .dark .card-value {
                color: #f3f4f6;
            }

            .dark .card:hover .card-value {
                color: rgba(220,169,21,1);
            }

            .card-divider {
                border-top: 1px solid rgba(220,169,21,0.3);
                margin: 12px 0;
                opacity: 0.3;
            }

            .widget-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #111827;
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
                color: #f3f4f6;
            }

            .card-tooltip {
                visibility: hidden;
                width: 250px;
                background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
                color: #ffffff;
                text-align: left;
                border-radius: 10px;
                padding: 14px;
                position: absolute;
                z-index: 100;
                bottom: 125%;
                left: 50%;
                margin-left: -125px;
                opacity: 0;
                transition: opacity 0.3s, transform 0.3s;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
                font-size: 0.875rem;
                line-height: 1.5;
                transform: translateY(10px);
                border: 1px solid rgba(75, 85, 99, 0.3);
                pointer-events: none;
            }

            .card-tooltip::after {
                content: "";
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -8px;
                border-width: 8px;
                border-style: solid;
                border-color: #111827 transparent transparent transparent;
            }

            .card-container:hover .card-tooltip {
                visibility: visible;
                opacity: 1;
                transform: translateY(0);
            }

            .card i {
                font-size: 1.25rem;
                transition: all 0.3s ease;
                color: rgba(220,169,21,1) !important; /* Force gold color on all icons */
            }

            .card:hover i {
                transform: scale(1.1);
            }

            .card-content {
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 0.75rem;
            }

            @media (max-width: 640px) {
                .stats-grid {
                    grid-template-columns: 1fr;
                }
            }

            .header-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.75rem;
                border-bottom: 1px solid rgba(229, 231, 235, 0.5);
                padding-bottom: 0.5rem;
            }

            .dark .header-container {
                border-color: rgba(75, 85, 99, 0.5);
            }

            .refresh-hint {
                font-size: 0.7rem;
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .dark .refresh-hint {
                color: #9ca3af;
            }

            .refresh-icon {
                animation: spin 2s linear infinite;
                color: rgba(220,169,21,0.7) !important; /* Force gold color */
            }

            /* Fix for info-circle icon */
            .fa-info-circle {
                color: rgba(220,169,21,0.8) !important; /* Force gold color */
            }

            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
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

            .card {
                animation: fadeIn 0.5s ease-out forwards;
            }

            .card:nth-child(1) {
                animation-delay: 0.1s;
            }

            .card:nth-child(2) {
                animation-delay: 0.2s;
            }

            .card:nth-child(3) {
                animation-delay: 0.3s;
            }

            .card:nth-child(4) {
                animation-delay: 0.4s;
            }
        </style>

        <div class="container mx-auto p-4" wire:poll.30s="calculateOrganizationalHealth">
            <div class="header-container mb-3">
                <h1 class="widget-title mb-0 pb-2">Organizational Health Snapshot</h1>
                <div class="refresh-hint">
                    <i class="fas fa-sync-alt refresh-icon"></i>
                    <span>Auto-refreshes every 30 seconds</span>
                </div>
            </div>

            <div class="stats-grid">
                @php
                    $cards = [
                        'departments_count' => [
                            'title' => 'Active Departments',
                            'icon' => 'fas fa-building',
                            'amount' => number_format(isset($stats['departments_count']) ? $stats['departments_count'] : 0),
                            'description' => 'Total number of active departments'
                        ],
                        'avg_headcount_utilization' => [
                            'title' => 'Headcount Utilization',
                            'icon' => 'fas fa-users-cog',
                            'amount' => (isset($stats['avg_headcount_utilization']) ? $stats['avg_headcount_utilization'] : 0) . '%',
                            'description' => 'Average headcount utilization across departments'
                        ],
                        'pending_leave_requests' => [
                            'title' => 'Pending Leave Requests',
                            'icon' => 'fas fa-calendar-check',
                            'amount' => number_format(isset($stats['pending_leave_requests']) ? $stats['pending_leave_requests'] : 0),
                            'description' => 'Leave requests awaiting approval'
                        ],
                        'departments_at_capacity' => [
                            'title' => 'Depts at Capacity',
                            'icon' => 'fas fa-exclamation-triangle',
                            'amount' => number_format(isset($stats['departments_at_capacity']) ? $stats['departments_at_capacity'] : 0),
                            'description' => 'Departments at or exceeding headcount capacity'
                        ]
                    ];
                @endphp

                @foreach ($cards as $key => $card)
                    <div class="card-container relative">
                        <div class="card p-4">
                            <div class="card-content">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h5 class="card-title mb-1">{{ $card['title'] }}</h5>
                                        <h2 class="card-value" wire:key="count-{{ $key }}">
                                            {{ $card['amount'] }}
                                        </h2>
                                    </div>
                                    <div class="icon-bg">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </div>
                                </div>

                                <div class="card-divider mt-3 opacity-30"></div>

                                <div class="text-xs text-gray-500 mt-2">
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Click for details
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-tooltip">
                            <div class="font-semibold mb-2">{{ $card['title'] }}</div>
                            <div>{{ $card['description'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
