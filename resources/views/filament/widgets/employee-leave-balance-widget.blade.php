{{-- resources/views/filament/employee/widgets/leave-balance-widget.blade.php --}}
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

            .dark .card {
                border-color: rgba(75, 85, 99, 0.5);
                background-color: #27272a;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.6), 0 2px 4px -1px rgba(0, 0, 0, 0.4);
            }

            .widget-title {
                font-size: 0.85rem;
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

            .balance-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                margin-top: 8px;
            }

            .balance-table th {
                text-align: left;
                font-size: 0.7rem;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 6px 10px;
                border-bottom: 1px solid rgba(229, 231, 235, 0.5);
            }

            .dark .balance-table th {
                color: #9ca3af;
                border-color: rgba(75, 85, 99, 0.5);
            }

            .balance-table td {
                font-size: 0.7rem;
                color: #1f2937;
                padding: 8px 10px;
                border-bottom: 1px solid rgba(229, 231, 235, 0.3);
            }

            .dark .balance-table td {
                color: #f3f4f6;
                border-color: rgba(75, 85, 99, 0.3);
            }

            .balance-table tr:last-child td {
                border-bottom: none;
            }

            .balance-table tr:hover td {
                background-color: rgba(220,169,21,0.05);
            }

            .dark .balance-table tr:hover td {
                background-color: rgba(220,169,21,0.1);
            }

            .balance-type {
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .balance-type i {
                color: rgba(220,169,21,1) !important;
                font-size: 0.8rem;
            }

            .badge {
                display: inline-flex;
                padding: 2px 8px;
                border-radius: 9999px;
                font-size: 0.65rem;
                font-weight: 600;
                white-space: nowrap;
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

            .progress-container {
                width: 100%;
                height: 5px;
                background-color: rgba(229, 231, 235, 0.5);
                border-radius: 2px;
                overflow: hidden;
                margin-top: 4px;
            }

            .dark .progress-container {
                background-color: rgba(75, 85, 99, 0.5);
            }

            .progress-bar {
                height: 100%;
                border-radius: 2px;
            }

            .progress-low {
                background-color: rgb(16, 185, 129);
            }

            .progress-medium {
                background-color: rgb(245, 158, 11);
            }

            .progress-high {
                background-color: rgb(239, 68, 68);
            }

            .balance-container {
                max-height: 300px;
                overflow-y: auto;
            }

            .empty-balances {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                color: #6b7280;
                font-size: 0.75rem;
            }

            .dark .empty-balances {
                color: #9ca3af;
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

            .table-row {
                animation: fadeIn 0.3s ease-out forwards;
            }

            .table-row:nth-child(1) {
                animation-delay: 0.1s;
            }

            .table-row:nth-child(2) {
                animation-delay: 0.15s;
            }

            .table-row:nth-child(3) {
                animation-delay: 0.2s;
            }

            .table-row:nth-child(4) {
                animation-delay: 0.25s;
            }

            .table-row:nth-child(5) {
                animation-delay: 0.3s;
            }
        </style>

        <div class="container mx-auto">
            <div class="header-container mb-3">
                <h1 class="widget-title mb-0 pb-2">
                    <i class="fas fa-calendar-check mr-2"></i>
                    Leave Balances
                </h1>
                <div>
                    <span class="badge badge-primary">{{ now()->year }}</span>
                </div>
            </div>

            <div class="balance-container">
                @if(count($this->getLeaveBalances()) > 0)
                    <table class="balance-table">
                        <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Entitled</th>
                            <th>Taken</th>
                            <th>Pending</th>
                            <th>Available</th>
                            <th>Usage</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($this->getLeaveBalances() as $index => $balance)
                            <tr class="table-row">
                                <td>
                                    <div class="balance-type">
                                        <i class="fas {{ $balance['leave_type'] == 'Annual' ? 'fa-umbrella-beach' :
                                                ($balance['leave_type'] == 'Sick' ? 'fa-briefcase-medical' :
                                                ($balance['leave_type'] == 'Maternity' ? 'fa-baby' :
                                                ($balance['leave_type'] == 'Paternity' ? 'fa-baby-carriage' :
                                                ($balance['leave_type'] == 'Compassionate' ? 'fa-heart' : 'fa-calendar-minus')))) }}"></i>
                                        {{ $balance['leave_type'] }}
                                    </div>
                                </td>
                                <td>{{ $balance['entitled'] }}</td>
                                <td>{{ $balance['taken'] }}</td>
                                <td>{{ $balance['pending'] }}</td>
                                <td>
                                        <span class="badge {{ $balance['available'] <= 0 ? 'badge-danger' :
                                            ($balance['available'] < 5 ? 'badge-warning' : 'badge-success') }}">
                                            {{ $balance['available'] }}
                                        </span>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <span class="mr-2">{{ $balance['percentage_used'] }}%</span>
                                        <div class="progress-container">
                                            <div class="progress-bar {{ $balance['percentage_used'] < 33 ? 'progress-low' :
                                                    ($balance['percentage_used'] < 66 ? 'progress-medium' : 'progress-high') }}"
                                                 style="width: {{ $balance['percentage_used'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-balances">
                        <i class="fas fa-calendar-xmark mr-2"></i>
                        No leave balances found for the current year
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
