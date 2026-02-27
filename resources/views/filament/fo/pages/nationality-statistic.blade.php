<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Guests</div>
            <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $summaryData['total_guests'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nationalities</div>
            <div class="text-xl font-bold text-info-600 dark:text-info-400">{{ $summaryData['total_nationalities'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4 col-span-2">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Top Nationality</div>
            <div class="text-xl font-bold dark:text-gray-200">
                {{ $summaryData['top_nationality'] ?? '-' }}
                <span class="text-sm font-normal text-gray-500">({{ $summaryData['top_count'] ?? 0 }} guests)</span>
            </div>
        </div>
    </div>

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
