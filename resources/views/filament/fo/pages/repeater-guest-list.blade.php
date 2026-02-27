<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Repeater Guests</div>
            <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $summaryData['total_repeaters'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Repeater Percentage</div>
            <div class="text-xl font-bold text-success-600 dark:text-success-400">{{ $summaryData['repeater_pct'] ?? 0 }}%</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg. Stays per Guest</div>
            <div class="text-xl font-bold text-info-600 dark:text-info-400">{{ $summaryData['avg_stays'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Max Stays (Top Guest)</div>
            <div class="text-xl font-bold text-warning-600 dark:text-warning-400">{{ $summaryData['max_stays'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
