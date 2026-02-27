<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info --}}
        <x-filament::section>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                <span>Search checked-out guests by date range, name, or room number. Click "View Bill" to see the closed invoice.</span>
            </div>
        </x-filament::section>

        {{-- Filters --}}
        {{ $this->filtersForm }}

        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
