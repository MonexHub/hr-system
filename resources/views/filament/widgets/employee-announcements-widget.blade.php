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

            .section-header {
                font-size: 0.7rem;
                font-weight: 600;
                color: #6b7280;
                margin: 16px 0 12px 0;
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

            .tabs-container {
                display: flex;
                gap: 2px;
                margin: 12px 0;
                border-radius: 10px;
                overflow: hidden;
                background-color: rgba(229, 231, 235, 0.5);
                padding: 2px;
                border: 1px solid rgba(229, 231, 235, 0.8);
            }

            .dark .tabs-container {
                background-color: rgba(75, 85, 99, 0.2);
                border-color: rgba(75, 85, 99, 0.4);
            }

            .tab-button {
                flex: 1;
                text-align: center;
                padding: 8px 10px;
                font-size: 0.7rem;
                font-weight: 600;
                background-color: white;
                color: #6b7280;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .tab-button i {
                font-size: 0.75rem;
            }

            .tab-button.active {
                background-color: rgba(220,169,21,0.1);
                color: rgba(220,169,21,1);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                border: 1px solid rgba(220,169,21,0.2);
            }

            .tab-button:hover:not(.active) {
                background-color: rgba(243, 244, 246, 0.8);
            }

            .dark .tab-button {
                background-color: #27272a;
                color: #9ca3af;
            }

            .dark .tab-button.active {
                background-color: rgba(220,169,21,0.15);
                color: rgba(220,169,21,1);
                border-color: rgba(220,169,21,0.3);
            }

            .dark .tab-button:hover:not(.active) {
                background-color: rgba(55, 65, 81, 0.8);
            }

            .announcements-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .announcement-item {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
                padding: 12px;
                transition: all 0.3s ease;
                border: 1px solid rgba(229, 231, 235, 0.5);
            }

            .announcement-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-color: rgba(220,169,21,0.2);
            }

            .dark .announcement-item {
                background-color: #27272a;
                border-color: rgba(75, 85, 99, 0.5);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .dark .announcement-item:hover {
                border-color: rgba(220,169,21,0.3);
            }

            .announcement-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 8px;
            }

            .announcement-title {
                font-size: 0.75rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 5px;
                line-height: 1.4;
            }

            .announcement-title i {
                color: rgba(220,169,21,1) !important;
                font-size: 0.8rem;
            }

            .dark .announcement-title {
                color: #f3f4f6;
            }

            .announcement-badge {
                font-size: 0.6rem;
                font-weight: 600;
                color: #ef4444;
                background-color: rgba(239, 68, 68, 0.1);
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                border: 1px solid rgba(239, 68, 68, 0.2);
            }

            .announcement-time {
                font-size: 0.6rem;
                color: #6b7280;
                padding: 0.15rem 0.5rem;
                background-color: rgba(243, 244, 246, 0.5);
                border-radius: 9999px;
                white-space: nowrap;
                border: 1px solid rgba(229, 231, 235, 0.5);
            }

            .dark .announcement-time {
                color: #9ca3af;
                background-color: rgba(55, 65, 81, 0.2);
                border-color: rgba(75, 85, 99, 0.3);
            }

            .announcement-content {
                font-size: 0.7rem;
                color: #4b5563;
                margin: 8px 0 0 0;
                line-height: 1.5;
                position: relative;
                padding-left: 16px;
                border-left: 2px solid rgba(220,169,21,0.2);
            }

            .announcement-content p {
                margin: 0;
                margin-bottom: 0.5rem;
            }

            .announcement-content ul {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
                padding-left: 1.5rem;
            }

            .announcement-content li {
                margin-bottom: 0.25rem;
            }

            .dark .announcement-content {
                color: #d1d5db;
            }

            .team-members {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .team-member {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                background-color: white;
                border-radius: 10px;
                transition: all 0.3s ease;
                border: 1px solid rgba(229, 231, 235, 0.5);
                box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
            }

            .team-member:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-color: rgba(220,169,21,0.2);
            }

            .dark .team-member {
                background-color: #27272a;
                border-color: rgba(75, 85, 99, 0.5);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .dark .team-member:hover {
                border-color: rgba(220,169,21,0.3);
            }

            .member-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                position: relative;
                flex-shrink: 0;
            }

            .avatar-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 50%;
                border: 2px solid rgba(220,169,21,0.5);
            }

            .avatar-text {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background-color: rgba(243, 244, 246, 0.8);
                color: rgba(220,169,21,1);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                font-weight: 700;
                border: 2px solid rgba(220,169,21,0.5);
            }

            .dark .avatar-text {
                background-color: rgba(55, 65, 81, 0.5);
                color: rgba(220,169,21,0.9);
            }

            .online-indicator {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background-color: #10b981;
                position: absolute;
                bottom: 0;
                right: 0;
                border: 2px solid white;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            }

            .dark .online-indicator {
                border-color: #27272a;
            }

            .member-info {
                flex-grow: 1;
                min-width: 0;
            }

            .member-name {
                font-size: 0.75rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dark .member-name {
                color: #f3f4f6;
            }

            .member-title {
                font-size: 0.65rem;
                color: #6b7280;
                margin: 2px 0 0 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dark .member-title {
                color: #9ca3af;
            }

            .empty-state {
                background-color: rgba(243, 244, 246, 0.3);
                border-radius: 10px;
                padding: 20px;
                text-align: center;
                margin: 10px 0;
                border: 1px dashed rgba(220,169,21,0.3);
            }

            .dark .empty-state {
                background-color: rgba(55, 65, 81, 0.2);
                border-color: rgba(220,169,21,0.2);
            }

            .empty-icon {
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

            .empty-icon i {
                font-size: 1.2rem;
                color: rgba(220,169,21,1) !important;
            }

            .empty-title {
                font-size: 0.8rem;
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 6px;
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

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
                animation: fadeIn 0.3s ease-in-out;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(5px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .mt-4 {
                margin-top: 1rem;
            }

            .mt-6 {
                margin-top: 1.5rem;
            }

            .text-center {
                text-align: center;
            }

            i {
                color: rgba(220,169,21,1) !important;
            }

            @media (max-width: 768px) {
                .announcement-header {
                    flex-direction: column;
                    gap: 5px;
                }

                .announcement-time {
                    align-self: flex-start;
                }
            }
        </style>

        <div class="widget-container" x-data="{ activeTab: 'announcements' }">
            <div class="header-container">
                <h2 class="widget-title">
                    <i class="fas fa-bullhorn"></i>
                    Team & Announcements
                </h2>
            </div>

            <div class="tabs-container">
                <button class="tab-button" :class="{ 'active': activeTab === 'announcements' }" @click="activeTab = 'announcements'">
                    <i class="fas fa-bullhorn"></i> Announcements
                </button>
                <button class="tab-button" :class="{ 'active': activeTab === 'team' }" @click="activeTab = 'team'">
                    <i class="fas fa-users"></i> Team
                </button>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-content" :class="{ 'active': activeTab === 'announcements' }">
                <!-- Company Announcements -->
                <h3 class="section-header">
                    <i class="fas fa-building"></i>
                    Company Announcements
                </h3>

                @if(count($this->getAnnouncementsData()['company_announcements']) > 0)
                    <div class="announcements-list">
                        @foreach($this->getAnnouncementsData()['company_announcements'] as $announcement)
                            <div class="announcement-item">
                                <div class="announcement-header">
                                    <p class="announcement-title">
                                        <i class="{{ $announcement['icon'] }}"></i>
                                        {{ $announcement['title'] }}
                                        @if($announcement['is_important'])
                                            <span class="announcement-badge">Important</span>
                                        @endif
                                    </p>
                                    <span class="announcement-time">{{ $announcement['created_at']->diffForHumans() }}</span>
                                </div>
                                <div class="announcement-content">{!! $announcement['content'] !!}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <p class="empty-title">No Company Announcements</p>
                        <p class="empty-text">There are no company announcements at the moment.</p>
                    </div>
                @endif

                <!-- Team Announcements -->
                @if(count($this->getAnnouncementsData()['team_announcements']) > 0)
                    <h3 class="section-header">
                        <i class="fas fa-users"></i>
                        {{ $this->getAnnouncementsData()['department'] ?? 'Team' }} Announcements
                    </h3>

                    <div class="announcements-list">
                        @foreach($this->getAnnouncementsData()['team_announcements'] as $announcement)
                            <div class="announcement-item">
                                <div class="announcement-header">
                                    <p class="announcement-title">
                                        <i class="{{ $announcement['icon'] }}"></i>
                                        {{ $announcement['title'] }}
                                    </p>
                                    <span class="announcement-time">{{ $announcement['created_at']->diffForHumans() }}</span>
                                </div>
                                <div class="announcement-content">{!! $announcement['content'] !!}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Team Tab -->
            <div class="tab-content" :class="{ 'active': activeTab === 'team' }">
                <h3 class="section-header">
                    <i class="fas fa-users"></i>
                    {{ $this->getAnnouncementsData()['department'] ?? 'Team' }} Members
                </h3>

                @if(count($this->getAnnouncementsData()['team_members']) > 0)
                    <div class="team-members">
                        @foreach($this->getAnnouncementsData()['team_members'] as $member)
                            <div class="team-member">
                                <div class="member-avatar">
                                    @if($member['profile_photo'])
                                        <img src="{{ asset('storage/' . $member['profile_photo']) }}" alt="{{ $member['name'] }}" class="avatar-img">
                                    @else
                                        <div class="avatar-text">{{ substr($member['name'], 0, 1) }}</div>
                                    @endif

                                    @if($member['is_online'])
                                        <div class="online-indicator"></div>
                                    @endif
                                </div>
                                <div class="member-info">
                                    <p class="member-name">{{ $member['name'] }}</p>
                                    <p class="member-title">{{ $member['job_title'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="empty-title">No Team Members Found</p>
                        <p class="empty-text">There are no other team members in your department.</p>
                    </div>
                @endif
            </div>

            <div class="text-center mt-6">
                <a href="{{ url('admin/announcements') }}" class="view-all-link">
                    <i class="fas fa-external-link-alt text-xs"></i>
                    View All Announcements
                </a>
            </div>


        </div>
    </x-filament::section>
</x-filament-widgets::widget>
