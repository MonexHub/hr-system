<div>
    <x-filament::section>
        <h2 class="text-xl font-bold mb-4">Application Settings</h2>

        <form wire:submit="saveSettings">
            {{ $this->form }}

            <div class="mt-6 flex flex-wrap gap-4 justify-end items-center">
                @if($this->confirm_reset ?? false)
                    <x-filament::button
                        wire:click="resetSettings"
                        color="danger"
                        type="button"
                    >
                        {{ __('Reset All Settings') }}
                    </x-filament::button>
                @endif

                <x-filament::button
                    type="submit"
                    color="primary"
                >
                    {{ __('Save Settings') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</div>
