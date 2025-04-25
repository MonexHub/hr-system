<x-filament::page>
    <x-filament::card>
        <div class="space-y-6">
            <h2 class="text-lg font-medium">Sync Attendance Data from ZKBiotime API</h2>
            <p class="text-sm text-gray-500">
                Use this form to sync attendance data from the ZKBiotime API. You can specify a date range and optionally
                filter by departments. The sync process will run in the background.
            </p>

            {{ $this->form }}

            <div class="flex justify-end mt-6">
                <x-filament::button type="submit" wire:click="sync">
                    Start Sync
                </x-filament::button>
            </div>
        </div>
    </x-filament::card>
</x-filament::page>
