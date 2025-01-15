<x-filament::button
    type="submit"
    wire:loading.attr="disabled"
    class="w-full md:w-auto"
>
    <div class="flex items-center gap-2">
        @svg('heroicon-o-check-circle', 'w-4 h-4')
        <span>Complete Profile</span>
    </div>
</x-filament::button>
