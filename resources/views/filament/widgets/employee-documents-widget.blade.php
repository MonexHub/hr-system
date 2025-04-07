<x-filament-widgets::widget>
    <x-filament::section>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

        <style>
            .widget-container {
                width: 100%;
            }

            .widget-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .widget-title {
                font-size: 0.85rem;
                font-weight: 700;
                color: #111827;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .dark .widget-title {
                color: #f3f4f6;
            }

            .widget-title i {
                color: rgba(220,169,21,1) !important;
            }

            .doc-count {
                font-size: 0.7rem;
                color: #6b7280;
                background-color: rgba(220,169,21,0.1);
                padding: 0.1rem 0.4rem;
                border-radius: 9999px;
                display: flex;
                align-items: center;
                gap: 3px;
            }

            .dark .doc-count {
                color: #9ca3af;
                background-color: rgba(220,169,21,0.2);
            }

            .section-title {
                font-size: 0.7rem;
                font-weight: 600;
                color: #6b7280;
                margin: 0 0 8px 0;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .section-title i {
                color: rgba(220,169,21,0.8) !important;
                font-size: 0.7rem;
            }

            .dark .section-title {
                color: #9ca3af;
            }

            .documents-list {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-bottom: 15px;
            }

            .document-item {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                padding: 8px 10px;
                display: flex;
                align-items: center;
                gap: 10px;
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .document-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .dark .document-item {
                background-color: #1f2937;
            }

            .file-icon {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                flex-shrink: 0;
            }

            .file-icon i {
                color: rgba(220,169,21,1) !important;
            }

            .file-type {
                font-size: 0.55rem;
                font-weight: 700;
                color: rgba(220,169,21,1);
                background-color: rgba(220,169,21,0.1);
                padding: 0.1rem 0.25rem;
                border-radius: 3px;
                text-transform: uppercase;
                margin-top: 2px;
                display: inline-block;
            }

            .doc-info {
                flex-grow: 1;
                min-width: 0;
            }

            .doc-title {
                font-size: 0.7rem;
                font-weight: 600;
                color: #111827;
                margin: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dark .doc-title {
                color: #f3f4f6;
            }

            .doc-meta {
                font-size: 0.6rem;
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 6px;
                margin-top: 2px;
            }

            .dark .doc-meta {
                color: #9ca3af;
            }

            .doc-meta-divider {
                width: 3px;
                height: 3px;
                border-radius: 50%;
                background-color: #9ca3af;
            }

            .dark .doc-meta-divider {
                background-color: #6b7280;
            }

            .doc-actions {
                display: flex;
                gap: 5px;
            }

            .doc-action {
                width: 22px;
                height: 22px;
                border-radius: 4px;
                background-color: #f3f4f6;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background-color 0.2s;
            }

            .doc-action:hover {
                background-color: #e5e7eb;
            }

            .dark .doc-action {
                background-color: #374151;
            }

            .dark .doc-action:hover {
                background-color: #4b5563;
            }

            .doc-action i {
                font-size: 0.7rem;
                color: #4b5563 !important;
            }

            .dark .doc-action i {
                color: #d1d5db !important;
            }

            .doc-type-summary {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 15px;
            }

            .doc-type-item {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                padding: 8px;
                flex: 1;
                min-width: 80px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .dark .doc-type-item {
                background-color: #1f2937;
            }

            .doc-type-count {
                font-size: 1.1rem;
                font-weight: 700;
                color: rgba(220,169,21,1);
                margin: 0;
            }

            .doc-type-name {
                font-size: 0.6rem;
                color: #6b7280;
                margin: 0;
                text-align: center;
            }

            .dark .doc-type-name {
                color: #9ca3af;
            }

            .expiry-item {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                padding: 8px 10px;
                display: flex;
                align-items: center;
                gap: 10px;
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .expiry-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .dark .expiry-item {
                background-color: #1f2937;
            }

            .expiry-warning {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .warning-urgent {
                background-color: rgba(239, 68, 68, 0.1);
            }

            .warning-urgent i {
                color: rgb(239, 68, 68) !important;
            }

            .warning-warning {
                background-color: rgba(245, 158, 11, 0.1);
            }

            .warning-warning i {
                color: rgb(245, 158, 11) !important;
            }

            .warning-info {
                background-color: rgba(59, 130, 246, 0.1);
            }

            .warning-info i {
                color: rgb(59, 130, 246) !important;
            }

            .days-remaining {
                padding: 0.1rem 0.4rem;
                border-radius: 9999px;
                font-size: 0.6rem;
                font-weight: 600;
            }

            .days-urgent {
                background-color: rgba(239, 68, 68, 0.1);
                color: rgb(239, 68, 68);
            }

            .days-warning {
                background-color: rgba(245, 158, 11, 0.1);
                color: rgb(245, 158, 11);
            }

            .days-info {
                background-color: rgba(59, 130, 246, 0.1);
                color: rgb(59, 130, 246);
            }

            .empty-state {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                padding: 12px;
                text-align: center;
                margin: 10px 0;
            }

            .dark .empty-state {
                background-color: #1f2937;
            }

            .empty-icon {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: rgba(220,169,21,0.1);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 8px auto;
            }

            .empty-icon i {
                font-size: 0.8rem;
                color: rgba(220,169,21,1) !important;
            }

            .empty-title {
                font-size: 0.75rem;
                font-weight: 600;
                color: #111827;
                margin-bottom: 3px;
            }

            .dark .empty-title {
                color: #f3f4f6;
            }

            .empty-text {
                font-size: 0.65rem;
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
                margin-top: 10px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                cursor: pointer;
                transition: all 0.2s ease;
                gap: 3px;
            }

            .view-all-link:hover {
                color: rgba(220,169,21,0.8);
            }

            i {
                color: rgba(220,169,21,1) !important;
            }
        </style>

        <div class="widget-container">
            <div class="widget-header">
                <h2 class="widget-title">
                    <i class="fas fa-file-alt"></i>
                    My Documents
                </h2>
                <div class="doc-count">
                    <i class="fas fa-copy text-[0.6rem]"></i>
                    <span>{{ $this->getDocuments()['total_count'] ?? 0 }} Documents</span>
                </div>
            </div>

            <!-- Document types summary -->
            @if(count($this->getDocuments()['count_by_type']) > 0)
                <h3 class="section-title">
                    <i class="fas fa-folder"></i>
                    Document Categories
                </h3>

                <div class="doc-type-summary">
                    @foreach($this->getDocuments()['count_by_type'] as $type => $count)
                        <div class="doc-type-item">
                            <p class="doc-type-count">{{ $count }}</p>
                            <p class="doc-type-name">{{ Str::title($type) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Recent documents -->
            <h3 class="section-title">
                <i class="fas fa-clock"></i>
                Recent Documents
            </h3>

            @if(count($this->getDocuments()['recent']) > 0)
                <div class="documents-list">
                    @foreach($this->getDocuments()['recent'] as $document)
                        <div class="document-item">
                            <div class="file-icon">
                                <i class="{{ $this->getFileIcon($document['file_extension']) }}"></i>
                            </div>
                            <div class="doc-info">
                                <p class="doc-title">{{ $document['title'] }}</p>
                                <div class="doc-meta">
                                    <span>{{ $document['file_extension'] }}</span>
                                    <span class="doc-meta-divider"></span>
                                    <span>{{ $document['file_size'] }}</span>
                                    <span class="doc-meta-divider"></span>
                                    <span>{{ $document['created_at'] }}</span>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <a href="{{ asset('storage/' . $document['file_path']) }}" target="_blank" class="doc-action" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ asset('storage/' . $document['file_path']) }}" download class="doc-action" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <p class="empty-title">No Documents Found</p>
                    <p class="empty-text">You don't have any documents uploaded yet.</p>
                </div>
            @endif

            <!-- Documents expiring soon -->
            @if(count($this->getDocuments()['expires_soon']) > 0)
                <h3 class="section-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Expiring Soon
                </h3>

                <div class="documents-list">
                    @foreach($this->getDocuments()['expires_soon'] as $document)
                        @php
                            $warningClass = $document['days_remaining'] <= 30
                                ? 'warning-urgent'
                                : ($document['days_remaining'] <= 60 ? 'warning-warning' : 'warning-info');

                            $daysClass = $document['days_remaining'] <= 30
                                ? 'days-urgent'
                                : ($document['days_remaining'] <= 60 ? 'days-warning' : 'days-info');
                        @endphp
                        <div class="expiry-item">
                            <div class="expiry-warning {{ $warningClass }}">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="doc-info">
                                <p class="doc-title">{{ $document['title'] }}</p>
                                <div class="doc-meta">
                                    <span>{{ $document['document_type'] }}</span>
                                    <span class="doc-meta-divider"></span>
                                    <span>Expires: {{ $document['expiry_date'] }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="days-remaining {{ $daysClass }}">
                                    {{ $document['days_remaining'] }} days left
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="text-center mt-3">
                <a href="{{ url('employee/documents') }}" class="view-all-link">
                    <i class="fas fa-external-link-alt text-xs"></i>
                    View All Documents
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
