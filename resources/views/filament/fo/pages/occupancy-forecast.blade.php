<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Sellable Rooms</div>
            <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $summaryData['total_rooms'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg. Occupancy</div>
            <div class="text-xl font-bold text-info-600 dark:text-info-400">{{ $summaryData['avg_occupancy'] ?? 0 }}%</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Peak Occupancy</div>
            <div class="text-xl font-bold text-success-600 dark:text-success-400">{{ $summaryData['max_occupancy'] ?? 0 }}%</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Lowest Occupancy</div>
            <div class="text-xl font-bold text-warning-600 dark:text-warning-400">{{ $summaryData['min_occupancy'] ?? 0 }}%</div>
        </div>
    </div>

    {{-- Forecast Table --}}
    @if(!empty($forecastData))
    <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900">
                        <th class="px-3 py-2 text-left border dark:border-gray-700">Period</th>
                        @if($forecastMode === 'daily')
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Day</th>
                        @else
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Duration</th>
                        @endif
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Rooms Booked</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Rooms Available</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Occupancy</th>
                        <th class="px-3 py-2 text-right border dark:border-gray-700">Est. Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forecastData as $row)
                    <tr class="dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="px-3 py-2 border dark:border-gray-700 font-medium">{{ $row['label'] }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 text-gray-500">{{ $row['day'] }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700">{{ $row['rooms_booked'] }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700">{{ max(0, $row['rooms_available']) }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700">
                            <span class="inline-flex items-center gap-1">
                                @php $occ = $row['occupancy']; @endphp
                                <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $occ >= 80 ? 'bg-success-500' : ($occ >= 50 ? 'bg-warning-500' : 'bg-danger-500') }}" style="width: {{ min(100, $occ) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold {{ $occ >= 80 ? 'text-success-600 dark:text-success-400' : ($occ >= 50 ? 'text-warning-600 dark:text-warning-400' : 'text-danger-600 dark:text-danger-400') }}">
                                    {{ $occ }}%
                                </span>
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right border dark:border-gray-700">Rp {{ number_format($row['est_revenue'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <p class="text-lg">No forecast data available.</p>
        <p class="text-sm mt-2">Ensure there are active rooms configured.</p>
    </div>
    @endif
</x-filament-panels::page>
