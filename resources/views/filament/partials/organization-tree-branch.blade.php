{{-- resources/views/filament/partials/organization-tree-branch.blade.php --}}
<div class="relative">
    {{-- Main Unit Card --}}
    <div class="group rounded-lg bg-gray-900 hover:bg-gray-800 transition-colors {{ $level > 0 ? 'ml-6' : '' }}">
        <div class="p-4">
            <div class="flex items-center">
                {{-- Icon & Title Section --}}
                <div class="flex items-center flex-1 min-w-0">
                    <div @class([
                        'flex-shrink-0 p-2 rounded-lg',
                        'bg-primary-500/10' => $unit->unit_type === 'company',
                        'bg-success-500/10' => $unit->unit_type === 'division',
                        'bg-warning-500/10' => $unit->unit_type === 'department',
                        'bg-info-500/10' => $unit->unit_type === 'team',
                        'bg-secondary-500/10' => $unit->unit_type === 'unit',
                    ])>
                        <x-dynamic-component
                            :component="'heroicon-m-' . str_replace('heroicon-o-', '', $unitTypes[$unit->unit_type]['icon'])"
                            @class([
                                'w-5 h-5',
                                'text-primary-400' => $unit->unit_type === 'company',
                                'text-success-400' => $unit->unit_type === 'division',
                                'text-warning-400' => $unit->unit_type === 'department',
                                'text-info-400' => $unit->unit_type === 'team',
                                'text-secondary-400' => $unit->unit_type === 'unit',
                            ])
                        />
                    </div>

                    <div class="ml-4 min-w-0">
                        <div class="flex items-center">
                            <p class="text-sm font-medium text-white truncate">
                                {{ $unit->name }}
                            </p>
                            <span class="ml-2 px-2 py-0.5 text-xs rounded-lg bg-gray-700 text-gray-300">
                                {{ $unit->code }}
                            </span>
                        </div>
                        <div class="flex items-center mt-1 space-x-2 text-xs">
                            <span class="text-gray-400">
                                {{ $unitTypes[$unit->unit_type]['label'] }}
                            </span>
                            @if($unit->employees_count > 0)
                                <span class="text-gray-600">•</span>
                                <span class="text-gray-400">
                                    {{ $unit->employees_count }} {{ Str::plural('employee', $unit->employees_count) }}
                                </span>
                            @endif
                            @if($unit->max_headcount > 0)
                                <span class="text-gray-600">•</span>
                                <span class="text-gray-400">
                                    {{ $unit->current_headcount }}/{{ $unit->max_headcount }} headcount
                                </span>
                            @endif
                            @if($unit->headEmployee)
                                <span class="text-gray-600">•</span>
                                <span class="text-gray-400">
                                    Head: {{ $unit->headEmployee->full_name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <x-filament::button
                        size="xs"
                        color="gray"
                        icon="heroicon-m-eye"
                        wire:click="showViewModal({{ $unit->id }})"
                        tooltip="View Details"
                    />

                    <x-filament::button
                        size="xs"
                        color="primary"
                        icon="heroicon-m-pencil-square"
                        wire:click="showEditModal({{ $unit->id }})"
                        tooltip="Edit Unit"
                    />

                    <x-filament::button
                        size="xs"
                        color="success"
                        icon="heroicon-m-user-plus"
                        wire:click="showAddEmployeeModal({{ $unit->id }})"
                        tooltip="Add Employee"
                    />

                    @if($unit->unit_type === 'department')
                        <x-filament::button
                            size="xs"
                            color="warning"
                            icon="heroicon-m-chart-bar"
                            wire:click="showHeadcountModal({{ $unit->id }})"
                            tooltip="Manage Headcount"
                        />
                    @endif

                    <x-filament::button
                        size="xs"
                        color="danger"
                        icon="heroicon-m-trash"
                        wire:click="showDeleteModal({{ $unit->id }})"
                        tooltip="Delete Unit"
                    />
                </div>
            </div>
        </div>

        {{-- Children Section --}}
        @if($unit->children_count > 0 && $unit->children->isNotEmpty())
            <div class="border-t border-gray-800">
                <div class="p-3 space-y-3">
                    @foreach($unit->children->sortBy('order_index') as $child)
                        @include('filament.partials.organization-tree-branch', [
                            'unit' => $child,
                            'level' => $level + 1,
                            'unitTypes' => $unitTypes
                        ])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
