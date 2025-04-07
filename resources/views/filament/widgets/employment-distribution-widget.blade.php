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
                padding: 10px;
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

            .icon-bg {
                width: 40px;
                height: 40px;
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
                font-size: 0.65rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 0.5rem;
            }

            .dark .card-title {
                color: #9ca3af;
            }

            .card-value {
                font-size: 0.85rem;
                font-weight: 700;
                color: #1f2937;
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

            .card-subtitle {
                font-size: 0.6rem;
                color: #6b7280;
                margin-top: 0.25rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }

            .dark .card-subtitle {
                color: #9ca3af;
            }

            .card-divider {
                border-top: 1px solid rgba(220,169,21,0.3);
                margin: 6px 0;
                opacity: 0.3;
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

            .card-tooltip {
                visibility: hidden;
                width: 250px;
                color: #1f2937;
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
                font-size: 0.65rem;
                line-height: 1.5;
                transform: translateY(10px);
                border: 1px solid rgba(229, 231, 235, 0.5);
                pointer-events: none;
                background-color: white;
            }

            .dark .card-tooltip {
                background-color: #27272a;
                color: #f3f4f6;
                border-color: rgba(75, 85, 99, 0.5);
            }

            .card-tooltip::after {
                content: "";
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -8px;
                border-width: 8px;
                border-style: solid;
                border-color: white transparent transparent transparent;
            }

            .dark .card-tooltip::after {
                border-color: #27272a transparent transparent transparent;
            }

            .card-container:hover .card-tooltip {
                visibility: visible;
                opacity: 1;
                transform: translateY(0);
            }

            .card i {
                font-size: 0.85rem;
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
                gap: 8px;
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
                margin-bottom: 6px;
                border-bottom: 1px solid rgba(220,169,21,0.3);
                padding-bottom: 0.5rem;
                opacity: 0.3;
            }

            .dark .header-container {
                border-color: rgba(75, 85, 99, 0.5);
            }

            .refresh-hint {
                font-size: 0.6rem;
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .dark .refresh-hint {
                color: #9ca3af;
            }

            .refresh-icon {
                animation: spin 2s linear infinite;
                color: rgba(220,169,21,0.7) !important; /* Force gold color */
            }

            .info-text {
                font-size: 0.55rem;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .dark .info-text {
                color: #9ca3af;
            }

            /* Specific styles for gender badges */
            .gender-distribution {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-top: 6px;
                flex-wrap: wrap;
            }

            .gender-badge {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 2px 5px;
                border-radius: 6px;
                font-size: 0.55rem;
                font-weight: 500;
            }

            .male-badge {
                background-color: rgba(59, 130, 246, 0.1);
                color: rgb(59, 130, 246);
            }

            .female-badge {
                background-color: rgba(236, 72, 153, 0.1);
                color: rgb(236, 72, 153);
            }

            .other-badge {
                background-color: rgba(107, 114, 128, 0.1);
                color: rgb(107, 114, 128);
            }

            .card-tooltip .border-t {
                border-color: rgba(229, 231, 235, 0.2);
            }

            .dark .card-tooltip .border-t {
                border-color: rgba(75, 85, 99, 0.3);
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

        <div class="container mx-auto p-4" wire:poll.30s="calculateDistributionStats">
            <div class="header-container mb-3">
                <h1 class="widget-title mb-0 pb-2">Employment Distribution</h1>
                <div class="refresh-hint">
                    <i class="fas fa-sync-alt refresh-icon"></i>
                    <span>Auto-refreshes every 30 seconds</span>
                </div>
            </div>

            <div class="stats-grid">
                @php
                    $cards = [
                        'contract_types' => [
                            'title' => 'Contract Types',
                            'icon' => 'fas fa-file-signature',
                            'amount' => number_format(
                                (isset($stats['permanent_employees']) ? $stats['permanent_employees'] : 0) +
                                (isset($stats['contract_employees']) ? $stats['contract_employees'] : 0) +
                                (isset($stats['probation_employees']) ? $stats['probation_employees'] : 0)
                            ),
                            'description' => 'Distribution of employees by contract type',
                            'has_details' => true,
                            'details' => [
                                'Permanent' => isset($stats['permanent_employees']) ? $stats['permanent_employees'] : 0,
                                'Contract' => isset($stats['contract_employees']) ? $stats['contract_employees'] : 0,
                                'Probation' => isset($stats['probation_employees']) ? $stats['probation_employees'] : 0
                            ]
                        ],
                        'largest_department' => [
                            'title' => 'Largest Department',
                            'icon' => 'fas fa-users',
                            'amount' => isset($stats['largest_department']['count']) ? number_format($stats['largest_department']['count']) : 0,
                            'subtitle' => isset($stats['largest_department']['name']) ? $stats['largest_department']['name'] : 'N/A',
                            'description' => 'Department with the highest number of employees'
                        ],
                        'empty_departments' => [
                            'title' => 'Empty Departments',
                            'icon' => 'fas fa-folder-open',
                            'amount' => isset($stats['empty_departments']) ? number_format($stats['empty_departments']) : 0,
                            'description' => 'Departments with zero employees assigned'
                        ],
                        'gender_diversity' => [
                            'title' => 'Gender Distribution',
                            'icon' => 'fas fa-venus-mars',
                            'amount' => number_format(
                                array_sum(isset($stats['gender_distribution']) ? $stats['gender_distribution'] : [])
                            ),
                            'has_gender_badges' => true,
                            'gender_data' => isset($stats['gender_distribution']) ? $stats['gender_distribution'] : [],
                            'description' => 'Distribution of employees by gender'
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
                                        @if(isset($card['subtitle']))
                                            <p class="card-subtitle">{{ $card['subtitle'] }}</p>
                                        @endif

                                        @if(isset($card['has_gender_badges']) && $card['has_gender_badges'])
                                            <div class="gender-distribution">
                                                @if(isset($card['gender_data']['male']))
                                                    <span class="gender-badge male-badge">
                                                        <i class="fas fa-mars"></i> {{ $card['gender_data']['male'] }}
                                                    </span>
                                                @endif

                                                @if(isset($card['gender_data']['female']))
                                                    <span class="gender-badge female-badge">
                                                        <i class="fas fa-venus"></i> {{ $card['gender_data']['female'] }}
                                                    </span>
                                                @endif

                                                @if(isset($card['gender_data']['other']))
                                                    <span class="gender-badge other-badge">
                                                        <i class="fas fa-genderless"></i> {{ $card['gender_data']['other'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="icon-bg">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </div>
                                </div>

                                <div class="card-divider mt-3"></div>

                                <div class="mt-2">
                                    <span class="inline-flex items-center info-text">
                                        <i class="fas fa-info-circle mr-1 text-xs"></i>
                                        Click for details
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-tooltip">
                            <div class="font-semibold mb-2">{{ $card['title'] }}</div>
                            <div>{{ $card['description'] }}</div>

                            @if(isset($card['has_details']) && $card['has_details'] && isset($card['details']))
                                <div class="mt-2 pt-2 border-t">
                                    @foreach($card['details'] as $label => $value)
                                        <div class="flex justify-between items-center mt-1">
                                            <span>{{ $label }}:</span>
                                            <span class="font-medium">{{ number_format($value) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(isset($card['subtitle']))
                                <div class="mt-2 font-medium">{{ $card['subtitle'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
