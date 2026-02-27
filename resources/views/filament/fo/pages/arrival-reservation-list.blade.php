<x-filament-panels::page>
    <div class="mb-4">
        {{ $this->filtersForm }}
    </div>

    <div class="mb-4 flex flex-wrap gap-3 text-sm">
        <span class="inline-flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-red-500"></span>
            <span>VIP</span>
        </span>
        <span class="inline-flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-purple-500"></span>
            <span>Long Stay (7+ nights)</span>
        </span>
        <span class="inline-flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
            <span>Room Sharer</span>
        </span>
        <span class="inline-flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
            <span>Day Use</span>
        </span>
        <span class="inline-flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-gray-500"></span>
            <span>Incognito</span>
        </span>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
