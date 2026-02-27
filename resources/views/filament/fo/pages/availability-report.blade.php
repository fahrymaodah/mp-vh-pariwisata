<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    {{-- Category Summary --}}
    @if(count($summary) > 0)
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Category Summary ({{ $startDate }} — {{ $endDate }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="px-3 py-2 text-left border dark:border-gray-700">Category</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Code</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Total Rooms</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Reserved</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Available</th>
                        <th class="px-3 py-2 text-right border dark:border-gray-700">Base Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary as $cat)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">{{ $cat['name'] }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $cat['code'] }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $cat['total'] }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 font-semibold
                            {{ $cat['reserved'] >= $cat['total'] ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                            {{ $cat['reserved'] }}
                        </td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 font-semibold
                            {{ $cat['available'] == 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $cat['available'] }}
                        </td>
                        <td class="px-3 py-2 text-right border dark:border-gray-700 dark:text-gray-300">
                            IDR {{ number_format((float) $cat['base_rate'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Daily Breakdown --}}
    @if($categoryId && count($dailyData) > 0)
    <div>
        <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Daily Availability</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="px-3 py-2 text-left border dark:border-gray-700">Date</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Total</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Reserved</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Available</th>
                        <th class="px-3 py-2 text-center border dark:border-gray-700">Occupancy %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyData as $date => $data)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                        </td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $data['total'] }}</td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 font-semibold
                            {{ $data['reserved'] >= $data['total'] ? 'text-red-600 dark:text-red-400' : 'dark:text-gray-300' }}">
                            {{ $data['reserved'] }}
                        </td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700 font-semibold
                            {{ $data['available'] == 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $data['available'] }}
                        </td>
                        <td class="px-3 py-2 text-center border dark:border-gray-700">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $data['occupancy_pct'] >= 90 ? 'bg-red-500' : ($data['occupancy_pct'] >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                         style="width: {{ min($data['occupancy_pct'], 100) }}%"></div>
                                </div>
                                <span class="dark:text-gray-300">{{ $data['occupancy_pct'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif(!$categoryId && is_array($dailyData) && count($dailyData) > 0)
    <div>
        <h3 class="text-lg font-semibold mb-3 dark:text-gray-200">Daily Availability — All Categories</h3>
        @foreach($dailyData as $catCode => $dates)
        <div class="mb-4">
            <h4 class="font-medium mb-2 dark:text-gray-300">{{ $catCode }}</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            @foreach($dates as $date => $data)
                            <th class="px-2 py-1 border dark:border-gray-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($date)->format('d/m') }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($dates as $date => $data)
                            <td class="px-2 py-1 text-center border dark:border-gray-700 font-semibold
                                {{ $data['available'] == 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' :
                                   ($data['occupancy_pct'] >= 70 ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' :
                                   'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400') }}">
                                {{ $data['available'] }}
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-filament-panels::page>
