<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        {{ $this->filtersForm }}

        {{-- Summary --}}
        <div class="grid grid-cols-4 gap-4">
            @php
                $totalRooms = \App\Models\Room::where('is_active', true)->count();
                $vacantClean = \App\Models\Room::where('is_active', true)->where('status', 'vacant_clean')->count();
                $vacantDirty = \App\Models\Room::where('is_active', true)->where('status', 'vacant_dirty')->count();
                $occupied = \App\Models\Room::where('is_active', true)->whereIn('status', ['occupied_clean', 'occupied_dirty'])->count();
                $ooo = \App\Models\Room::where('is_active', true)->where('status', 'out_of_order')->count();
            @endphp
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-success-600">{{ $vacantClean }}</div>
                    <div class="text-xs text-gray-500">Vacant Clean</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-warning-600">{{ $vacantDirty }}</div>
                    <div class="text-xs text-gray-500">Vacant Dirty</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-info-600">{{ $occupied }}</div>
                    <div class="text-xs text-gray-500">Occupied</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-danger-600">{{ $ooo }}</div>
                    <div class="text-xs text-gray-500">Out of Order</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
