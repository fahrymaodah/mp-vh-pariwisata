<x-filament-panels::page>
    <div class="mb-4">
        {{ $this->filtersForm }}
    </div>

    {{-- Color Legend --}}
    <div class="mb-4 flex flex-wrap gap-3 text-xs">
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-500"></span> Checked In</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span> Guaranteed</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-400"></span> Confirmed</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-500"></span> 6 PM Release</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-teal-400"></span> Oral Confirmed</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-orange-400"></span> Waiting List</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-400"></span> Tentative</span>
    </div>

    {{-- Timeline Grid --}}
    <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
        <table class="text-xs border-collapse min-w-full">
            <thead>
                {{-- Month row --}}
                <tr class="bg-gray-50 dark:bg-gray-800">
                    <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-800 px-2 py-1 border-r dark:border-gray-700 min-w-[100px]" rowspan="2">
                        Room
                    </th>
                    @foreach($dates as $d)
                    <th class="px-1 py-0.5 border-r dark:border-gray-700 min-w-[36px]
                        {{ $d['isToday'] ? 'bg-blue-100 dark:bg-blue-900/40' : '' }}
                        {{ $d['isWeekend'] ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        <span class="text-[10px] text-gray-400">{{ $d['day'] }}</span>
                    </th>
                    @endforeach
                </tr>
                {{-- Day number row --}}
                <tr class="bg-gray-50 dark:bg-gray-800">
                    @foreach($dates as $d)
                    <th class="px-1 py-1 border-r dark:border-gray-700 text-center font-semibold
                        {{ $d['isToday'] ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300' : 'dark:text-gray-300' }}
                        {{ $d['isWeekend'] ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        {{ $d['label'] }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                    <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-2 py-1.5 border-r dark:border-gray-700 whitespace-nowrap">
                        <span class="font-semibold dark:text-gray-200">{{ $room['number'] }}</span>
                        <span class="text-gray-400 ml-1">({{ $room['category'] }})</span>
                    </td>
                    @foreach($dates as $d)
                    @php
                        $slot = $reservationSlots[$room['id']][$d['date']] ?? null;
                    @endphp
                    <td class="px-0 py-0.5 border-r dark:border-gray-700 text-center
                        {{ $d['isToday'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}
                        {{ $d['isWeekend'] && !$slot ? 'bg-gray-50 dark:bg-gray-800/50' : '' }}">
                        @if($slot)
                        <div class="h-6 {{ $slot['color'] }} text-white rounded-sm mx-0.5 flex items-center justify-center overflow-hidden cursor-pointer"
                             title="{{ $slot['guest_name'] }} ({{ $slot['reservation_no'] }}) | {{ $slot['arrival'] }} → {{ $slot['departure'] }} | {{ $slot['status'] }}"
                             wire:click="$dispatch('open-modal', { id: 'reservation-{{ $slot['reservation_id'] }}' })">
                            @if($slot['isFirstDay'])
                            <span class="truncate px-0.5 text-[9px] font-medium">{{ \Illuminate\Support\Str::limit($slot['guest_name'], 8) }}</span>
                            @endif
                        </div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($dates) + 1 }}" class="px-4 py-8 text-center text-gray-500">
                        No rooms found for the selected criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
