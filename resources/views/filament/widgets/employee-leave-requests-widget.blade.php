<x-filament-widgets::widget>
    <x-filament::section>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

        <style>
            /* Compact card styles with original width */
            .widget-container {
                position: relative;
                max-width: 100%;
                width: 100%;
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

            .widget-title {
                font-size: 0.85rem;
                font-weight: 700;
                color: rgba(220,169,21,1);
                margin-bottom: 0;
                position: relative;
                padding-bottom: 0.5rem;
                display: inline-block;
                display: flex;
                align-items: center;
                gap: 8px;
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

            .update-badge {
                font-size: 0.6rem;
                color: #6b7280;
                background-color: rgba(220,169,21,0.1);
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                display: flex;
                align-items: center;
                gap: 5px;
                border: 1px solid rgba(220,169,21,0.2);
            }

            .dark .update-badge {
                color: #9ca3af;
                background-color: rgba(220,169,21,0.15);
                border-color: rgba(220,169,21,0.3);
            }

            .section-header {
                font-size: 0.7rem;
                font-weight: 600;
                color: #6b7280;
                margin: 16px 0 10px 0;
                padding-left: 8px;
                border-left: 2px solid rgba(220,169,21,0.5);
                display: flex;
                align-items: center;
                gap: 6px;
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }

            .section-header i {
                color: rgba(220,169,21,0.8) !important;
                font-size: 0.7rem;
            }

            .dark .section-header {
                color: #9ca3af;
            }

            .requests-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0 8px;
            }

            .request-row {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
                transition: all 0.3s ease;
                border: 1px solid rgba(229, 231, 235, 0.5);
            }

            .request-row:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-color: rgba(220,169,21,0.2);
            }

            .dark .request-row {
                background-color: #27272a;
                border-color: rgba(75, 85, 99, 0.5);
            }

            .dark .request-row:hover {
                border-color: rgba(220,169,21,0.3);
            }

            .request-row td {
                padding: 10px 12px;
                font-size: 0.7rem;
                color: #4b5563;
            }

            .dark .request-row td {
                color: #d1d5db;
            }

            .request-row td:first-child {
                border-top-left-radius: 8px;
                border-bottom-left-radius: 8px;
                padding-left: 16px;
            }

            .request-row td:last-child {
                border-top-right-radius: 8px;
                border-bottom-right-radius: 8px;
                padding-right: 16px;
            }

            .leave-type {
                font-weight: 600;
                color: #1f2937;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .leave-type-icon {
                width: 24px;
                height: 24px;
                border-radius: 6px;
                background: rgba(220,169,21,0.1);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .leave-type-icon i {
                font-size: 0.65rem;
                color: rgba(220,169,21,1) !important;
            }

            .dark .leave-type {
                color: #f3f4f6;
            }

            .leave-dates {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .date-arrow {
                color: rgba(220,169,21,0.6) !important;
                font-size: 0.6rem;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                font-size: 0.6rem;
                font-weight: 600;
                text-transform: capitalize;
                gap: 4px;
            }

            .status-badge i {
                font-size: 0.6rem;
            }

            .status-warning {
                background-color: rgba(245, 158, 11, 0.1);
                color: rgb(245, 158, 11);
                border: 1px solid rgba(245, 158, 11, 0.2);
            }

            .status-info {
                background-color: rgba(59, 130, 246, 0.1);
                color: rgb(59, 130, 246);
                border: 1px solid rgba(59, 130, 246, 0.2);
            }

            .status-success {
                background-color: rgba(16, 185, 129, 0.1);
                color: rgb(16, 185, 129);
                border: 1px solid rgba(16, 185, 129, 0.2);
            }

            .status-danger {
                background-color: rgba(239, 68, 68, 0.1);
                color: rgb(239, 68, 68);
                border: 1px solid rgba(239, 68, 68, 0.2);
            }

            .status-secondary {
                background-color: rgba(107, 114, 128, 0.1);
                color: rgb(107, 114, 128);
                border: 1px solid rgba(107, 114, 128, 0.2);
            }

            .days-tag {
                display: inline-flex;
                align-items: center;
                font-weight: 600;
                background-color: rgba(220,169,21,0.1);
                color: rgba(220,169,21,1);
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                font-size: 0.65rem;
                border: 1px solid rgba(220,169,21,0.2);
            }

            .active-now {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                background-color: rgba(16, 185, 129, 0.1);
                color: rgb(16, 185, 129);
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                font-size: 0.65rem;
                font-weight: 600;
                border: 1px solid rgba(16, 185, 129, 0.2);
            }

            .active-now i {
                color: rgb(16, 185, 129) !important;
                font-size: 0.5rem;
            }

            .days-until {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                font-size: 0.65rem;
                font-weight: 600;
                background-color: rgba(220,169,21,0.1);
                color: rgba(220,169,21,1);
                border: 1px solid rgba(220,169,21,0.2);
            }

            .days-until i {
                font-size: 0.6rem;
            }

            .empty-state {
                background-color: rgba(243, 244, 246, 0.3);
                border-radius: 10px;
                padding: 16px;
                text-align: center;
                margin: 10px 0;
                border: 1px dashed rgba(220,169,21,0.3);
            }

            .dark .empty-state {
                background-color: rgba(55, 65, 81, 0.2);
                border-color: rgba(220,169,21,0.2);
            }

            .empty-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: rgba(220,169,21,0.1);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 10px auto;
                border: 1px solid rgba(220,169,21,0.2);
            }

            .empty-icon i {
                font-size: 1rem;
                color: rgba(220,169,21,1) !important;
            }

            .empty-title {
                font-size: 0.8rem;
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 4px;
            }

            .dark .empty-title {
                color: #f3f4f6;
            }

            .empty-text {
                font-size: 0.7rem;
                color: #6b7280;
                margin: 0;
            }

            .dark .empty-text {
                color: #9ca3af;
            }

            .view-all-link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.65rem;
                font-weight: 600;
                color: rgba(220,169,21,1);
                margin-top: 16px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                cursor: pointer;
                transition: all 0.2s ease;
                gap: 5px;
                background-color: rgba(220,169,21,0.05);
                padding: 0.5rem 1rem;
                border-radius: 8px;
                border: 1px solid rgba(220,169,21,0.1);
            }

            .view-all-link:hover {
                color: rgba(220,169,21,1);
                background-color: rgba(220,169,21,0.1);
                border-color: rgba(220,169,21,0.2);
            }

            .requests-section {
                margin-bottom: 20px;
            }

            .requests-container {
                max-height: none;
                overflow-y: visible;
            }

            i {
                color: rgba(220,169,21,1) !important;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(8px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .request-row {
                animation: fadeIn 0.4s ease-out forwards;
            }

            .request-row:nth-child(1) {
                animation-delay: 0.05s;
            }

            .request-row:nth-child(2) {
                animation-delay: 0.1s;
            }

            .request-row:nth-child(3) {
                animation-delay: 0.15s;
            }

            .request-row:nth-child(4) {
                animation-delay: 0.2s;
            }

            .request-row:nth-child(5) {
                animation-delay: 0.25s;
            }

            @media (max-width: 768px) {
                .requests-table {
                    table-layout: fixed;
                }

                .request-row td {
                    padding: 8px;
                }

                .leave-type {
                    font-size: 0.65rem;
                }
            }
        </style>

        <div class="widget-container" wire:poll.60s="getLeaveRequests">
            <div class="header-container">
                <h2 class="widget-title">
                    <i class="fas fa-clipboard-list"></i>
                    Leave Requests
                </h2>
                <div class="update-badge">
                    <i class="fas fa-sync-alt fa-spin text-[0.6rem]"></i>
                    <span>Auto-updating</span>
                </div>
            </div>

            <!-- Pending Requests Section -->
            <div class="requests-section">
                <h3 class="section-header">
                    <i class="fas fa-hourglass-half"></i>
                    Pending Approval
                </h3>

                <div class="requests-container">
                    @if(count($this->getLeaveRequests()['pending']) > 0)
                        <table class="requests-table">
                            <tbody>
                            @foreach($this->getLeaveRequests()['pending'] as $request)
                                <tr class="request-row" wire:key="pending-{{ $request['id'] }}">
                                    <td style="width: 30%">
                                        <div class="leave-type">
                                            <div class="leave-type-icon">
                                                <i class="fas {{ getLeaveTypeIcon($request['leave_type']) }}"></i>
                                            </div>
                                            <span>{{ $request['leave_type'] }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 35%">
                                        <div class="leave-dates">
                                            <span>{{ $request['start_date'] }}</span>
                                            <i class="fas fa-arrow-right date-arrow"></i>
                                            <span>{{ $request['end_date'] }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 15%">
                                        <span class="days-tag">{{ $request['total_days'] }} {{ Str::plural('day', $request['total_days']) }}</span>
                                    </td>
                                    <td style="width: 20%; text-align: right;">
                                        <span class="status-badge status-{{ $request['status_badge']['color'] }}">
                                            @if($request['status_badge']['color'] === 'warning')
                                                <i class="fas fa-clock"></i>
                                            @elseif($request['status_badge']['color'] === 'info')
                                                <i class="fas fa-thumbs-up"></i>
                                            @elseif($request['status_badge']['color'] === 'success')
                                                <i class="fas fa-check-circle"></i>
                                            @elseif($request['status_badge']['color'] === 'danger')
                                                <i class="fas fa-times-circle"></i>
                                            @else
                                                <i class="fas fa-info-circle"></i>
                                            @endif
                                            {{ $request['status_badge']['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <p class="empty-title">No Pending Requests</p>
                            <p class="empty-text">You don't have any leave requests pending approval.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upcoming Leave Section -->
            <div class="requests-section">
                <h3 class="section-header">
                    <i class="fas fa-calendar-alt"></i>
                    Upcoming Leave
                </h3>

                <div class="requests-container">
                    @if(count($this->getLeaveRequests()['upcoming']) > 0)
                        <table class="requests-table">
                            <tbody>
                            @foreach($this->getLeaveRequests()['upcoming'] as $request)
                                <tr class="request-row" wire:key="upcoming-{{ $request['id'] }}">
                                    <td style="width: 30%">
                                        <div class="leave-type">
                                            <div class="leave-type-icon">
                                                <i class="fas {{ getLeaveTypeIcon($request['leave_type']) }}"></i>
                                            </div>
                                            <span>{{ $request['leave_type'] }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 35%">
                                        <div class="leave-dates">
                                            <span>{{ $request['start_date'] }}</span>
                                            <i class="fas fa-arrow-right date-arrow"></i>
                                            <span>{{ $request['end_date'] }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 15%">
                                        <span class="days-tag">{{ $request['total_days'] }} {{ Str::plural('day', $request['total_days']) }}</span>
                                    </td>
                                    <td style="width: 20%; text-align: right;">
                                        <span class="days-until">
                                            <i class="fas fa-calendar-day"></i>
                                            @if($request['days_until'] === 0)
                                                Today
                                            @elseif($request['days_until'] === 1)
                                                Tomorrow
                                            @else
                                                In {{ $request['days_until'] }} days
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <p class="empty-title">No Upcoming Leave</p>
                            <p class="empty-text">You don't have any approved upcoming leave.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent/Active Leave Section -->
            <div class="requests-section">
                <h3 class="section-header">
                    <i class="fas fa-history"></i>
                    Recent & Active Leave
                </h3>

                <div class="requests-container">
                    @if(count($this->getLeaveRequests()['recent']) > 0)
                        <table class="requests-table">
                            <tbody>
                            @foreach($this->getLeaveRequests()['recent'] as $request)
                                <tr class="request-row" wire:key="recent-{{ $request['id'] }}">
                                    <td style="width: 30%">
                                        <div class="leave-type">
                                            <div class="leave-type-icon">
                                                <i class="fas {{ getLeaveTypeIcon($request['leave_type']) }}"></i>
                                            </div>
                                            <span>{{ $request['leave_type'] }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 35%">
                                        <div class="leave-dates">
                                            <span>{{ $request['start_date'] }}</span>
                                            <i class="fas fa-arrow-right date-arrow"></i>
                                            <span>{{ $request['end_date'] }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 15%">
                                        <span class="days-tag">{{ $request['total_days'] }} {{ Str::plural('day', $request['total_days']) }}</span>
                                    </td>
                                    <td style="width: 20%; text-align: right;">
                                        @if($request['is_active'])
                                            <span class="active-now">
                                                <i class="fas fa-circle"></i>
                                                Active Now
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-hourglass-end"></i>
                            </div>
                            <p class="empty-title">No Recent Leave</p>
                            <p class="empty-text">You haven't taken any leave recently.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center mt-6">
                <a href="{{ url('employee/leave-requests') }}" class="view-all-link">
                    <i class="fas fa-external-link-alt text-xs"></i>
                    View All Leave Requests
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@php
    function getLeaveTypeIcon($leaveType) {
        $leaveType = strtolower($leaveType);

        if (str_contains($leaveType, 'annual') || str_contains($leaveType, 'vacation')) {
            return 'fa-umbrella-beach';
        } elseif (str_contains($leaveType, 'sick')) {
            return 'fa-briefcase-medical';
        } elseif (str_contains($leaveType, 'maternity')) {
            return 'fa-baby';
        } elseif (str_contains($leaveType, 'paternity')) {
            return 'fa-baby-carriage';
        } elseif (str_contains($leaveType, 'compassionate') || str_contains($leaveType, 'bereavement')) {
            return 'fa-heart';
        } elseif (str_contains($leaveType, 'study') || str_contains($leaveType, 'education')) {
            return 'fa-graduation-cap';
        } elseif (str_contains($leaveType, 'unpaid')) {
            return 'fa-hand-holding-usd';
        } else {
            return 'fa-calendar-minus';
        }
    }
@endphp
