<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    {{-- Summary Report --}}
    @if(($reportData['type'] ?? '') === 'summary')
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach($reportData['metrics'] as $metric)
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $metric['label'] }}</div>
            <div class="text-xl font-bold dark:text-gray-200">{{ $metric['value'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Category Report --}}
    @if(($reportData['type'] ?? '') === 'category')
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Category</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Total</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Active</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Room Nights</th>
                    <th class="px-3 py-2 text-right border dark:border-gray-700">Avg Rate</th>
                    <th class="px-3 py-2 text-right border dark:border-gray-700">Est. Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['rows'] as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">{{ $row['category'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['total'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['active'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['room_nights'] }}</td>
                    <td class="px-3 py-2 text-right border dark:border-gray-700 dark:text-gray-300">IDR {{ number_format($row['avg_rate'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right border dark:border-gray-700 dark:text-gray-300">IDR {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Segment Report --}}
    @if(($reportData['type'] ?? '') === 'segment')
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Segment</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Total</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Active</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Room Nights</th>
                    <th class="px-3 py-2 text-right border dark:border-gray-700">Est. Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['rows'] as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">{{ $row['segment'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['total'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['active'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['room_nights'] }}</td>
                    <td class="px-3 py-2 text-right border dark:border-gray-700 dark:text-gray-300">IDR {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Status Report --}}
    @if(($reportData['type'] ?? '') === 'status')
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Status</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Count</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Room Nights</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['rows'] as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-{{ $row['color'] }}-500"></span>
                            {{ $row['status'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300 font-semibold">{{ $row['count'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['room_nights'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Cancellation Report --}}
    @if(($reportData['type'] ?? '') === 'cancellation')
    @if(count($reportData['rows']) === 0)
    <div class="text-center py-8 text-gray-500">No cancellations in this period.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Res. No</th>
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Guest</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Cat.</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Arrival</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Departure</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Cancelled At</th>
                    <th class="px-3 py-2 text-left border dark:border-gray-700">By</th>
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['rows'] as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300 font-mono">{{ $row['reservation_no'] }}</td>
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">{{ $row['guest'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['category'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['arrival'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['departure'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['cancelled_at'] }}</td>
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">{{ $row['cancelled_by'] }}</td>
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">{{ $row['reason'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @endif

    {{-- No-Show Report --}}
    @if(($reportData['type'] ?? '') === 'no_show')
    @if(count($reportData['rows']) === 0)
    <div class="text-center py-8 text-gray-500">No no-shows in this period.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Res. No</th>
                    <th class="px-3 py-2 text-left border dark:border-gray-700">Guest</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Cat.</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Arrival</th>
                    <th class="px-3 py-2 text-center border dark:border-gray-700">Nights</th>
                    <th class="px-3 py-2 text-right border dark:border-gray-700">Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['rows'] as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300 font-mono">{{ $row['reservation_no'] }}</td>
                    <td class="px-3 py-2 border dark:border-gray-700 dark:text-gray-300">{{ $row['guest'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['category'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['arrival'] }}</td>
                    <td class="px-3 py-2 text-center border dark:border-gray-700 dark:text-gray-300">{{ $row['nights'] }}</td>
                    <td class="px-3 py-2 text-right border dark:border-gray-700 dark:text-gray-300">IDR {{ number_format((float) $row['rate'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @endif

    {{-- Forecast Report --}}
    @if(($reportData['type'] ?? '') === 'forecast')
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-2 py-2 text-left border dark:border-gray-700">Date</th>
                    @foreach($reportData['categories'] as $cat)
                    <th class="px-2 py-2 text-center border dark:border-gray-700">{{ $cat }}</th>
                    @endforeach
                    <th class="px-2 py-2 text-center border dark:border-gray-700 font-bold">Occ. Total</th>
                    <th class="px-2 py-2 text-center border dark:border-gray-700 font-bold">Occ. %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['dates'] as $day)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-2 py-1.5 border dark:border-gray-700 whitespace-nowrap dark:text-gray-300">{{ $day['date'] }}</td>
                    @foreach($reportData['categories'] as $cat)
                    @php $catData = $day['categories'][$cat] ?? ['occupied' => 0, 'total' => 0, 'available' => 0]; @endphp
                    <td class="px-2 py-1.5 text-center border dark:border-gray-700
                        {{ $catData['available'] == 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'dark:text-gray-300' }}">
                        {{ $catData['occupied'] }}/{{ $catData['total'] }}
                    </td>
                    @endforeach
                    <td class="px-2 py-1.5 text-center border dark:border-gray-700 font-semibold dark:text-gray-300">
                        {{ $day['total_occupied'] }}/{{ $day['total_rooms'] }}
                    </td>
                    <td class="px-2 py-1.5 text-center border dark:border-gray-700 font-bold
                        {{ $day['occupancy_pct'] >= 90 ? 'text-red-600 dark:text-red-400' : ($day['occupancy_pct'] >= 70 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                        {{ $day['occupancy_pct'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-filament-panels::page>
