<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        {{ $this->filtersForm }}

        {{-- Instructions --}}
        <x-filament::section>
            <div class="flex items-center gap-4 text-sm flex-wrap">
                <div class="flex items-center gap-1">
                    <x-filament::badge color="success">Settled / Ready</x-filament::badge>
                    <span class="text-gray-500">= All bills balanced, ready for C/O</span>
                </div>
                <div class="flex items-center gap-1">
                    <x-filament::badge color="danger">Outstanding</x-filament::badge>
                    <span class="text-gray-500">= Bills have unpaid balance</span>
                </div>
                <div class="flex items-center gap-1">
                    <x-filament::badge color="gray">No Bill</x-filament::badge>
                    <span class="text-gray-500">= No invoice found</span>
                </div>
            </div>
        </x-filament::section>

        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
