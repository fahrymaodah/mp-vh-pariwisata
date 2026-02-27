<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Reservations</div>
            <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $summaryData['total_reservations'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Revenue</div>
            <div class="text-xl font-bold text-success-600 dark:text-success-400">Rp {{ number_format($summaryData['total_revenue'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg. Rate</div>
            <div class="text-xl font-bold text-info-600 dark:text-info-400">Rp {{ number_format($summaryData['avg_rate'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4 col-span-2">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Top Segment</div>
            <div class="text-xl font-bold dark:text-gray-200">
                {{ $summaryData['top_segment'] ?? '-' }}
                <span class="text-sm font-normal text-gray-500">({{ $summaryData['top_count'] ?? 0 }} reservations)</span>
            </div>
        </div>
    </div>

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
