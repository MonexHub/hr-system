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

            .welcome-message {
                font-size: 0.85rem;
                font-weight: 600;
                color: rgba(220,169,21,1);
                margin: 0 0 6px 0;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .profile-photo {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid rgba(220,169,21,0.7);
            }

            .header-row {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 6px;
            }

            .name-title {
                flex-grow: 1;
            }

            .user-name {
                font-size: 0.9rem;
                font-weight: 700;
                color: #1f2937;
                line-height: 1.1;
                margin: 0;
            }

            .user-title {
                font-size: 0.65rem;
                color: #6b7280;
                margin: 0;
            }

            .status-badge {
                display: inline-block;
                padding: 0.1rem 0.4rem;
                border-radius: 9999px;
                font-size: 0.6rem;
                font-weight: 600;
                text-transform: capitalize;
            }

            .status-active {
                background-color: rgba(16, 185, 129, 0.1);
                color: rgb(16, 185, 129);
            }

            .status-inactive {
                background-color: rgba(239, 68, 68, 0.1);
                color: rgb(239, 68, 68);
            }

            .contract-badge {
                display: inline-block;
                padding: 0.1rem 0.4rem;
                border-radius: 9999px;
                font-size: 0.6rem;
                font-weight: 600;
                text-transform: capitalize;
                background-color: rgba(220,169,21,0.1);
                color: rgba(220,169,21,1);
                margin-left: 5px;
            }

            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .info-cell {
                display: flex;
                flex-direction: column;
            }

            .info-label {
                font-size: 0.6rem;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin: 0;
            }

            .info-value {
                font-size: 0.7rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
            }

            .dark .user-name {
                color: #f3f4f6;
            }

            .dark .user-title,
            .dark .info-label {
                color: #9ca3af;
            }

            .dark .info-value {
                color: #f3f4f6;
            }

            .dark .welcome-message {
                color: rgba(220,169,21,0.9);
            }

            .card-divider {
                border-top: 1px solid rgba(220,169,21,0.3);
                margin: 6px 0;
                opacity: 0.3;
            }

            .stats-row {
                display: flex;
                justify-content: space-between;
                margin-top: 8px;
            }

            .stat-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .stat-value {
                font-size: 0.85rem;
                font-weight: 700;
                color: rgba(220,169,21,1);
            }

            .stat-label {
                font-size: 0.55rem;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }

            .dark .stat-label {
                color: #9ca3af;
            }

            .view-profile-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.65rem;
                font-weight: 600;
                color: rgba(220,169,21,1);
                margin-top: 8px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                cursor: pointer;
                transition: all 0.2s ease;
                gap: 3px;
            }

            .view-profile-btn:hover {
                color: rgba(220,169,21,0.8);
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

            i {
                color: rgba(220,169,21,1) !important;
            }
        </style>

        <div class="card-container">
            <div class="card">
                <div class="welcome-message">
                    <i class="fas fa-hand-sparkles"></i>
                    <span>Welcome, {{ $this->getProfileData()['name'] }}!</span>
                </div>

                <div class="header-row">
                    @if($this->getProfileData()['profile_photo'])
                        <img src="{{ asset('storage/' . $this->getProfileData()['profile_photo']) }}" alt="Profile Photo" class="profile-photo">
                    @else
                        <div class="profile-photo bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ substr($this->getProfileData()['name'], 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="name-title">
                        <p class="user-name">{{ $this->getProfileData()['name'] }}</p>
                        <p class="user-title">{{ $this->getProfileData()['job_title'] }}
                            <span class="contract-badge">{{ ucfirst($this->getProfileData()['contract_type'] ?? 'Unknown') }}</span>
                        </p>
                    </div>
                    <div>
                        <span class="status-badge {{ $this->getProfileData()['employment_status'] === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ ucfirst($this->getProfileData()['employment_status'] ?? 'Unknown') }}
                        </span>
                    </div>
                </div>

                <div class="card-divider"></div>

                <div class="info-grid">
                    <div class="info-cell">
                        <p class="info-label">Employee ID</p>
                        <p class="info-value">{{ $this->getProfileData()['employee_code'] }}</p>
                    </div>
                    <div class="info-cell">
                        <p class="info-label">Department</p>
                        <p class="info-value">{{ $this->getProfileData()['department'] }}</p>
                    </div>
                    <div class="info-cell">
                        <p class="info-label">Reports To</p>
                        <p class="info-value">{{ $this->getProfileData()['manager'] }}</p>
                    </div>
                    <div class="info-cell">
                        <p class="info-label">Start Date</p>
                        <p class="info-value">{{ $this->getProfileData()['appointment_date'] }}</p>
                    </div>
                </div>

                @if(isset($this->getProfileData()['years_of_service']) || isset($this->getProfileData()['pending_leave_requests']) || isset($this->getProfileData()['documents_count']))
                    <div class="card-divider"></div>

                    <div class="stats-row">
                        <div class="stat-item">
                            <p class="stat-value">{{ number_format($this->getProfileData()['years_of_service'] ?? 0, 0) }}</p>
                            <p class="stat-label">Years of Service</p>

                        </div>
                        <div class="stat-item">
                            <p class="stat-value">{{ $this->getProfileData()['pending_leave_requests'] ?? 0 }}</p>
                            <p class="stat-label">Pending Leaves</p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-value">{{ $this->getProfileData()['documents_count'] ?? 0 }}</p>
                            <p class="stat-label">Documents</p>
                        </div>
                    </div>
                @endif

{{--                <div class="flex justify-center">--}}
{{--                    ->url(fn ($record) => route('employee.resume', $record))--}}
{{--                    <a href="{{ route('employee.resume', $employee->id) }}" class="view-profile-btn">--}}
{{--                        <i class="fas fa-user-circle text-xs"></i> View Full Profile--}}
{{--                    </a>--}}

{{--                </div>--}}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
