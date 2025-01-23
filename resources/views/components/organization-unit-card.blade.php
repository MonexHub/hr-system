{{-- resources/views/components/organization-unit-card.blade.php --}}
@props(['unit', 'isExpanded' => false, 'unitTypes', 'level' => 0])

<div class="relative">
    @if($level > 0)
        <div class="absolute -left-[2rem] top-0 bottom-0 border-l-2 border-gray-200 dark:border-gray-700"></div>
    @endif

    <div class="relative {{ $level > 0 ? 'ml-8' : '' }}">
        @if($level > 0)
            <div class="absolute -left-[2rem] top-1/2 w-[2rem] border-t-2 border-gray-200 dark:border-gray-700"></div>
        @endif

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600">
            <div class="p-4">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-{{ $unitTypes[$unit->unit_type]['color'] }}-600 dark:text-{{ $unitTypes[$unit->unit_type]['color'] }}-400
                                     group-hover:bg-gray-200 dark:group-hover:bg-gray-700 transition-colors duration-200">
                                <x-filament::icon
                                    :icon="$unitTypes[$unit->unit_type]['icon']"
                                    class="w-6 h-6"
                                />
                            </div>
                            @if($unit->children_count > 0)
                                <div class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700
                                            flex items-center justify-center">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $unit->children_count }}</span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">{{ $unit->name }}</h3>
                                <x-filament::badge>{{ $unit->code }}</x-filament::badge>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-3">
                                <x-filament::badge :color="$unitTypes[$unit->unit_type]['color']">
                                    {{ $unitTypes[$unit->unit_type]['label'] }}
                                </x-filament::badge>
                                <span class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <x-filament::icon
                                        icon="heroicon-s-users"
                                        class="w-4 h-4 mr-1"
                                    />
                                    {{ $unit->employees_count }} Employees
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-filament::button
                            size="sm"
                            color="gray"
                            icon="heroicon-s-eye"
                            wire:click="showViewModal({{ $unit->id }})"
                        >
                            View
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="warning"
                            icon="heroicon-s-pencil"
                            wire:click="showEditModal({{ $unit->id }})"
                        >
                            Edit
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="danger"
                            icon="heroicon-s-trash"
                            wire:click="showDeleteModal({{ $unit->id }})"
                        >
                            Delete
                        </x-filament::button>
                    </div>
                </div>

                @if($unit->children->isNotEmpty())
                    <div class="mt-4 space-y-4" x-show="$isExpanded">
                        @foreach($unit->children as $childUnit)
                            <x-organization-unit-card
                                :unit="$childUnit"
                                :is-expanded="$isExpanded"
                                :unit-types="$unitTypes"
                                :level="$level + 1"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
