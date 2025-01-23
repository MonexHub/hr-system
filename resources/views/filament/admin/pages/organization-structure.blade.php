{{-- resources/views/filament/pages/organization-structure.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Header Actions --}}
        <div class="flex justify-between">
            <div class="flex items-center gap-4">
                {{-- Add any header actions here --}}
            </div>
            <x-filament::button
                wire:click="$dispatch('open-modal', { id: 'create-unit' })"
                icon="heroicon-m-plus"
            >
                Create Unit
            </x-filament::button>
        </div>

        {{-- Search and Filters Bar --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="flex flex-col gap-4 p-4 md:flex-row md:items-center">
                {{-- Type Filter --}}
                <div class="flex-1">
                    <select
                        wire:model.live="selectedUnitType"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                    >
                        <option value="all">Company</option>
                        @foreach($this->getViewData()['unitTypes'] as $value => $type)
                            <option value="{{ $value }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Collapse/Expand Button --}}
                <div class="flex-shrink-0">
                    <x-filament::button
                        wire:click="$toggle('expandAll')"
                        variant="secondary"
                        class="w-full justify-center md:w-auto px-4 py-2"
                    >
                        <x-filament::icon
                            :name="$this->expandAll ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'"
                            class="h-5 w-5 -ml-1 mr-2"
                        />
                        {{ $this->expandAll ? 'Collapse All' : 'Expand All' }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Organization Tree Content --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
            <div class="p-6">
                @if($this->getData()['units']->isEmpty())
                    <div class="text-center py-12">
                        <div class="flex flex-col items-center justify-center">
                            <div class="mb-4 rounded-full bg-gray-100 dark:bg-gray-800 p-3">
                                <x-filament::icon
                                    name="heroicon-o-rectangle-stack"
                                    class="h-8 w-8 text-gray-500 dark:text-gray-400"
                                />
                            </div>
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-200">
                                No organization units found
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Create your first organization unit to get started.
                            </p>
                            <div class="mt-6">
                                <x-filament::button
                                    wire:click="$dispatch('open-modal', { id: 'create-unit' })"
                                    icon="heroicon-m-plus"
                                >
                                    Create Unit
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($this->getData()['units'] as $unit)
                            <x-organization-unit-card
                                :unit="$unit"
                                :is-expanded="$this->expandAll"
                                :unit-types="$this->getViewData()['unitTypes']"
                                :level="0"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
