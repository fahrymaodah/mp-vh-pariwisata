<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        {{ $this->filtersForm }}

        {{-- Instructions --}}
        <x-filament::section>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-1">
                    <x-filament::badge color="success">Check-In</x-filament::badge>
                    <span class="text-gray-500">= Process check-in (room must be assigned)</span>
                </div>
                <div class="flex items-center gap-1">
                    <x-filament::badge color="warning">Assign Room</x-filament::badge>
                    <span class="text-gray-500">= Assign or change room before check-in</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-block w-3 h-3 rounded-full bg-success-500"></span>
                    <span class="text-gray-500">Room assigned</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-block w-3 h-3 rounded-full bg-danger-500"></span>
                    <span class="text-gray-500">No room</span>
                </div>
            </div>
        </x-filament::section>

        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
