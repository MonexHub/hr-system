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

            .attendance-summary {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
            }

            @media (min-width: 768px) {
                .attendance-summary {
                    grid-template-columns: 1fr 1fr;
                }
            }

            .today-card, .stats-card {
                background-color: white;
                border-radius: 12px;
                box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border: 1px solid rgba(229, 231, 235, 0.5);
                overflow: hidden;
                transition: all 0.3s ease;
                padding: 16px;
                height: 100%;
            }

            .today-card:hover, .stats-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-color: rgba(220,169,21,0.2);
            }

            .dark .today-card, .dark .stats-card {
                background-color: #27272a;
                border-color: rgba(75, 85, 99, 0.5);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.6), 0 2px 4px -1px rgba(0, 0, 0, 0.4);
            }

            .dark .today-card:hover, .dark .stats-card:hover {
                border-color: rgba(220,169,21,0.3);
            }

            .section-header {
                font-size: 0.7rem;
                font-weight: 600;
                color: #6b7280;
                margin: 0 0 12px 0;
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

            /* Today's attendance styles */
            .today-status {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                padding: 10px 0;
            }

            .status-circle {
                width: 70px;
                height: 70px;
                border-radius: 50%;
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(220,169,21,0.2);
                transition: all 0.3s ease;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            }

            .today-card:hover .status-circle {
                transform: scale(1.05);
            }

            .status-present {
                background-color: rgba(16, 185, 129, 0.1);
                border-color: rgba(16, 185, 129, 0.3) !important;
            }

            .status-present i {
                color: rgb(16, 185, 129) !important;
                font-size: 1.5rem;
            }

            .status-late {
                background-color: rgba(245, 158, 11, 0.1);
                border-color: rgba(245, 158, 11, 0.3) !important;
            }

            .status-late i {
                color: rgb(245, 158, 11) !important;
                font-size: 1.5rem;
            }

            .status-absent {
                background-color: rgba(239, 68, 68, 0.1);
                border-color: rgba(239, 68, 68, 0.3) !important;
            }

            .status-absent i {
                color: rgb(239, 68, 68) !important;
                font-size: 1.5rem;
            }

            .status-leave {
                background-color: rgba(79, 70, 229, 0.1);
                border-color: rgba(79, 70, 229, 0.3) !important;
            }

            .status-leave i {
                color: rgb(79, 70, 229) !important;
                font-size: 1.5rem;
            }

            .status-pending {
                background-color: rgba(107, 114, 128, 0.1);
                border-color: rgba(107, 114, 128, 0.3) !important;
            }

            .status-pending i {
                color: rgb(107, 114, 128) !important;
                font-size: 1.5rem;
            }

            .status-text {
                font-size: 0.85rem;
                font-weight: 600;
                margin: 0;
                padding: 3px 10px;
                border-radius: 20px;
            }

            .status-present-text {
                color: rgb(16, 185, 129);
                background-color: rgba(16, 185, 129, 0.1);
                border: 1px solid rgba(16, 185, 129, 0.2);
            }

            .status-late-text {
                color: rgb(245, 158, 11);
                background-color: rgba(245, 158, 11, 0.1);
                border: 1px solid rgba(245, 158, 11, 0.2);
            }

            .status-absent-text {
                color: rgb(239, 68, 68);
                background-color: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.2);
            }

            .status-leave-text {
                color: rgb(79, 70, 229);
                background-color: rgba(79, 70, 229, 0.1);
                border: 1px solid rgba(79, 70, 229, 0.2);
            }

            .status-pending-text {
                color: rgb(107, 114, 128);
                background-color: rgba(107, 114, 128, 0.1);
                border: 1px solid rgba(107, 114, 128, 0.2);
            }

            .check-times {
                display: flex;
                justify-content: center;
                gap: 20px;
                margin-top: 16px;
                width: 100%;
                background-color: rgba(243, 244, 246, 0.3);
                padding: 10px;
                border-radius: 8px;
                border: 1px solid rgba(229, 231, 235, 0.7);
            }

            .dark .check-times {
                background-color: rgba(55, 65, 81, 0.2);
                border-color: rgba(75, 85, 99, 0.3);
            }

            .check-time {
                text-align: center;
            }

            .check-label {
                font-size: 0.6rem;
                color: #6b7280;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .dark .check-label {
                color: #9ca3af;
            }

            .check-value {
                font-size: 0.85rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
            }

            .dark .check-value {
                color: #f3f4f6;
            }

            /* Week summary styles */
            .week-summary {
                display: flex;
                justify-content: space-between;
                margin-top: 12px;
                background-color: rgba(243, 244, 246, 0.3);
                padding: 10px;
                border-radius: 8px;
                border: 1px solid rgba(229, 231, 235, 0.7);
            }

            .dark .week-summary {
                background-color: rgba(55, 65, 81, 0.2);
                border-color: rgba(75, 85, 99, 0.3);
            }

            .day-item {
                text-align: center;
                position: relative;
                transition: all 0.2s ease;
            }

            .day-item:hover {
                transform: translateY(-2px);
            }

            .day-date {
                font-size: 0.65rem;
                color: #6b7280;
                margin: 0;
                font-weight: 600;
            }

            .dark .day-date {
                color: #9ca3af;
            }

            .day-status {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                margin: 4px auto;
                border: 1px solid transparent;
                transition: all 0.2s ease;
            }

            .day-item:hover .day-status {
                transform: scale(1.1);
                box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
            }

            .day-status-present {
                background-color: rgb(16, 185, 129);
                border-color: rgba(16, 185, 129, 0.5);
            }

            .day-status-late {
                background-color: rgb(245, 158, 11);
                border-color: rgba(245, 158, 11, 0.5);
            }

            .day-status-absent {
                background-color: rgb(239, 68, 68);
                border-color: rgba(239, 68, 68, 0.5);
            }

            .day-status-leave {
                background-color: rgb(79, 70, 229);
                border-color: rgba(79, 70, 229, 0.5);
            }

            .day-status-weekend {
                background-color: rgb(209, 213, 219);
                border-color: rgba(107, 114, 128, 0.5);
            }

            .day-status-upcoming {
                background-color: transparent;
                border: 1px dashed rgba(220,169,21,0.5);
            }

            .day-tooltip {
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                background-color: #1f2937;
                color: white;
                font-size: 0.65rem;
                padding: 4px 8px;
                border-radius: 6px;
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.2s;
                z-index: 10;
                margin-bottom: 5px;
            }

            .day-tooltip:after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -4px;
                border-width: 4px;
                border-style: solid;
                border-color: #1f2937 transparent transparent transparent;
            }

            .dark .day-tooltip {
                background-color: #f3f4f6;
                color: #1f2937;
            }

            .dark .day-tooltip:after {
                border-color: #f3f4f6 transparent transparent transparent;
            }

            .day-item:hover .day-tooltip {
                opacity: 1;
                visibility: visible;
            }

            /* Month stats styles */
            .month-stats {
                margin-top: 15px;
            }

            .stats-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .stats-title {
                font-size: 0.7rem;
                font-weight: 600;
                color: #6b7280;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .dark .stats-title {
                color: #9ca3af;
            }

            .stats-percentage {
                font-size: 0.7rem;
                font-weight: 600;
                background-color: rgba(16, 185, 129, 0.1);
                color: rgb(16, 185, 129);
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                border: 1px solid rgba(16, 185, 129, 0.2);
            }

            .progress-container {
                height: 8px;
                background-color: rgba(229, 231, 235, 0.5);
                border-radius: 9999px;
                overflow: hidden;
                box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
            }

            .dark .progress-container {
                background-color: rgba(55, 65, 81, 0.5);
            }

            .progress-bar {
                height: 100%;
                border-radius: 9999px;
            }

            .progress-present {
                background-color: rgb(16, 185, 129);
            }

            .progress-late {
                background-color: rgb(245, 158, 11);
            }

            .progress-absent {
                background-color: rgb(239, 68, 68);
            }

            .progress-leave {
                background-color: rgb(79, 70, 229);
            }

            .stats-items {
                display: flex;
                justify-content: space-between;
                margin-top: 16px;
                background-color: rgba(243, 244, 246, 0.3);
                padding: 10px;
                border-radius: 8px;
                border: 1px solid rgba(229, 231, 235, 0.7);
            }

            .dark .stats-items {
                background-color: rgba(55, 65, 81, 0.2);
                border-color: rgba(75, 85, 99, 0.3);
            }

            .stat-item {
                text-align: center;
                transition: all 0.2s ease;
                padding: 5px 8px;
                border-radius: 6px;
            }

            .stat-item:hover {
                background-color: rgba(220,169,21,0.05);
                transform: translateY(-2px);
            }

            .stat-value {
                font-size: 1rem;
                font-weight: 700;
                margin: 0;
            }

            .stat-present {
                color: rgb(16, 185, 129);
            }

            .stat-late {
                color: rgb(245, 158, 11);
            }

            .stat-absent {
                color: rgb(239, 68, 68);
            }

            .stat-leave {
                color: rgb(79, 70, 229);
            }

            .stat-label {
                font-size: 0.65rem;
                color: #6b7280;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            .dark .stat-label {
                color: #9ca3af;
            }

            .no-attendance {
                text-align: center;
                padding: 25px 20px;
                background-color: rgba(243, 244, 246, 0.3);
                border-radius: 8px;
                border: 1px dashed rgba(220,169,21,0.3);
                margin: 10px 0;
            }

            .dark .no-attendance {
                background-color: rgba(55, 65, 81, 0.2);
                border-color: rgba(220,169,21,0.2);
            }

            .no-attendance-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: rgba(220,169,21,0.1);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 12px auto;
                border: 1px solid rgba(220,169,21,0.2);
            }

            .no-attendance-icon i {
                font-size: 1.2rem;
                color: rgba(220,169,21,1) !important;
            }

            .no-attendance-text {
                font-size: 0.8rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0 0 6px 0;
            }

            .dark .no-attendance-text {
                color: #f3f4f6;
            }

            .no-attendance-subtext {
                font-size: 0.7rem;
                color: #6b7280;
                margin: 0;
            }

            .dark .no-attendance-subtext {
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

            .mt-4 {
                margin-top: 1rem;
            }

            .mt-6 {
                margin-top: 1.5rem;
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

            .today-card, .stats-card {
                animation: fadeIn 0.4s ease-out forwards;
            }

            .today-card {
                animation-delay: 0.05s;
            }

            .stats-card {
                animation-delay: 0.1s;
            }

            @media (max-width: 768px) {
                .attendance-summary {
                    grid-template-columns: 1fr;
                }

                .stats-items, .check-times {
                    padding: 8px 5px;
                }

                .stat-value {
                    font-size: 0.9rem;
                }

                .check-value {
                    font-size: 0.8rem;
                }
            }
        </style>

        <div class="widget-container" wire:poll.60s="getAttendanceSummary">
            <div class="header-container">
                <h2 class="widget-title">
                    <i class="fas fa-user-clock"></i>
                    Attendance Summary
                </h2>
                <div class="update-badge">
                    <i class="fas fa-sync-alt fa-spin text-[0.6rem]"></i>
                    <span>Auto-updating</span>
                </div>
            </div>

            <div class="attendance-summary">
                <!-- Today's attendance -->
                <div class="today-card">
                    <h3 class="section-header">
                        <i class="fas fa-calendar-day"></i>
                        Today's Attendance
                    </h3>

                    @if($this->getAttendanceSummary()['today'])
                        <div class="today-status">
                            @php
                                $todayStatus = $this->getAttendanceSummary()['today']['status'];
                                $statusIcon = [
                                    'present' => 'fas fa-check-circle',
                                    'late' => 'fas fa-hourglass-half',
                                    'absent' => 'fas fa-times-circle',
                                    'leave' => 'fas fa-calendar-minus',
                                    'pending' => 'fas fa-circle-notch fa-spin',
                                ][$todayStatus] ?? 'fas fa-question-circle';
                            @endphp

                            <div class="status-circle status-{{ $todayStatus }}">
                                <i class="{{ $statusIcon }}"></i>
                            </div>
                            <p class="status-text status-{{ $todayStatus }}-text">
                                {{ ucfirst($todayStatus) }}
                            </p>

                            @if(in_array($todayStatus, ['present', 'late']))
                                <div class="check-times">
                                    <div class="check-time">
                                        <p class="check-label">Check In</p>
                                        <p class="check-value">{{ $this->getAttendanceSummary()['today']['check_in'] ?? 'N/A' }}</p>
                                    </div>
                                    <div class="check-time">
                                        <p class="check-label">Check Out</p>
                                        <p class="check-value">{{ $this->getAttendanceSummary()['today']['check_out'] ?? 'Pending' }}</p>
                                    </div>
                                    @if($this->getAttendanceSummary()['today']['total_hours'])
                                        <div class="check-time">
                                            <p class="check-label">Hours</p>
                                            <p class="check-value">{{ $this->getAttendanceSummary()['today']['total_hours'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="no-attendance">
                            <div class="no-attendance-icon">
                                <i class="fas fa-fingerprint"></i>
                            </div>
                            <p class="no-attendance-text">No Attendance Recorded</p>
                            <p class="no-attendance-subtext">No check-in has been recorded for today.</p>
                        </div>
                    @endif
                </div>

                <!-- Monthly stats -->
                <div class="stats-card">
                    <h3 class="section-header">
                        <i class="fas fa-chart-pie"></i>
                        This Month's Statistics
                    </h3>

                    <div class="month-stats">
                        <div class="stats-header">
                            <p class="stats-title">
                                <i class="fas fa-chart-line text-xs" style="color: rgba(16, 185, 129, 0.8) !important;"></i>
                                Attendance Rate
                            </p>
                            <span class="stats-percentage">{{ $this->getAttendanceSummary()['month_stats']['present_percentage'] }}%</span>
                        </div>

                        <div class="progress-container">
                            @php
                                $totalDays = $this->getAttendanceSummary()['month_stats']['total_days'] ?: 1;
                                $presentPercentage = ($this->getAttendanceSummary()['month_stats']['present'] / $totalDays) * 100;
                                $latePercentage = ($this->getAttendanceSummary()['month_stats']['late'] / $totalDays) * 100;
                                $absentPercentage = ($this->getAttendanceSummary()['month_stats']['absent'] / $totalDays) * 100;
                                $leavePercentage = ($this->getAttendanceSummary()['month_stats']['leave'] / $totalDays) * 100;
                            @endphp

                                <!-- Multi-color progress bar -->
                            <div class="progress-bar progress-present" style="width: {{ $presentPercentage }}%"></div>
                        </div>

                        <div class="stats-items">
                            <div class="stat-item">
                                <p class="stat-value stat-present">{{ $this->getAttendanceSummary()['month_stats']['present'] }}</p>
                                <p class="stat-label">Present</p>
                            </div>
                            <div class="stat-item">
                                <p class="stat-value stat-late">{{ $this->getAttendanceSummary()['month_stats']['late'] }}</p>
                                <p class="stat-label">Late</p>
                            </div>
                            <div class="stat-item">
                                <p class="stat-value stat-absent">{{ $this->getAttendanceSummary()['month_stats']['absent'] }}</p>
                                <p class="stat-label">Absent</p>
                            </div>
                            <div class="stat-item">
                                <p class="stat-value stat-leave">{{ $this->getAttendanceSummary()['month_stats']['leave'] }}</p>
                                <p class="stat-label">Leave</p>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly overview -->
                    <h3 class="section-header mt-4">
                        <i class="fas fa-calendar-week"></i>
                        This Week's Overview
                    </h3>

                    <div class="week-summary">
                        @foreach($this->getAttendanceSummary()['week_stats'] as $date => $day)
                            <div class="day-item">
                                <p class="day-date">{{ $day['date'] }}</p>
                                <div class="day-status day-status-{{ $day['status'] }}"></div>
                                <div class="day-tooltip">
                                    @if($day['status'] == 'present')
                                        Present: {{ $day['check_in'] ?? 'N/A' }} - {{ $day['check_out'] ?? 'Pending' }}
                                    @elseif($day['status'] == 'late')
                                        Late: {{ $day['check_in'] ?? 'N/A' }} - {{ $day['check_out'] ?? 'Pending' }}
                                    @elseif($day['status'] == 'absent')
                                        Absent
                                    @elseif($day['status'] == 'leave')
                                        On Leave
                                    @elseif($day['status'] == 'weekend')
                                        Weekend
                                    @elseif($day['status'] == 'upcoming')
                                        Upcoming
                                    @else
                                        {{ ucfirst($day['status']) }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

{{--            <div class="text-center mt-6">--}}
{{--                <a href="{{ url('employee/attendance') }}" class="view-all-link">--}}
{{--                    <i class="fas fa-external-link-alt text-xs"></i>--}}
{{--                    View Full Attendance History--}}
{{--                </a>--}}
{{--            </div>--}}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
