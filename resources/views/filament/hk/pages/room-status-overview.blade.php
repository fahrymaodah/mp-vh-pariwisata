<x-filament-panels::page>
    @php
        $arrival = $this->getArrivalDeparture();
        $occupancy = $this->getRoomOccupancy();
        $activity = $this->getHousekeepingActivity();
    @endphp

    {{-- Arrival & Departure --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Arrival & Departure</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-lg bg-orange-50 p-3 text-center dark:bg-orange-500/10">
                <div class="text-2xl font-bold text-orange-600">{{ $arrival['departure_today'] }}</div>
                <div class="text-xs text-gray-500">Departing Today</div>
            </div>
            <div class="rounded-lg bg-green-50 p-3 text-center dark:bg-green-500/10">
                <div class="text-2xl font-bold text-green-600">{{ $arrival['departed'] }}</div>
                <div class="text-xs text-gray-500">Departed</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 text-center dark:bg-gray-500/10">
                <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $arrival['total_departure'] }}</div>
                <div class="text-xs text-gray-500">Total Departure</div>
            </div>
            <div class="rounded-lg bg-blue-50 p-3 text-center dark:bg-blue-500/10">
                <div class="text-2xl font-bold text-blue-600">{{ $arrival['checked_in_today'] }}</div>
                <div class="text-xs text-gray-500">Checked-in Today</div>
            </div>
            <div class="rounded-lg bg-indigo-50 p-3 text-center dark:bg-indigo-500/10">
                <div class="text-2xl font-bold text-indigo-600">{{ $arrival['arriving'] }}</div>
                <div class="text-xs text-gray-500">Arriving</div>
            </div>
            <div class="rounded-lg bg-purple-50 p-3 text-center dark:bg-purple-500/10">
                <div class="text-2xl font-bold text-purple-600">{{ $arrival['total_arrival'] }}</div>
                <div class="text-xs text-gray-500">Total Arrival</div>
            </div>
        </div>
    </div>

    {{-- Room Occupancy --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Room Occupancy</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-lg bg-primary-50 p-3 text-center dark:bg-primary-500/10">
                <div class="text-2xl font-bold text-primary-600">{{ $occupancy['occupied'] }}</div>
                <div class="text-xs text-gray-500">Occupied</div>
            </div>
            <div class="rounded-lg bg-red-50 p-3 text-center dark:bg-red-500/10">
                <div class="text-2xl font-bold text-red-600">{{ $occupancy['out_of_order'] }}</div>
                <div class="text-xs text-gray-500">Out of Order</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 text-center dark:bg-gray-500/10">
                <div class="text-2xl font-bold text-gray-600">{{ $occupancy['off_market'] }}</div>
                <div class="text-xs text-gray-500">Off Market</div>
            </div>
            <div class="rounded-lg bg-yellow-50 p-3 text-center dark:bg-yellow-500/10">
                <div class="text-2xl font-bold text-yellow-600">{{ $occupancy['inactive'] }}</div>
                <div class="text-xs text-gray-500">Inactive</div>
            </div>
            <div class="rounded-lg bg-cyan-50 p-3 text-center dark:bg-cyan-500/10">
                <div class="text-2xl font-bold text-cyan-600">{{ $occupancy['estimated_occupied'] }}</div>
                <div class="text-xs text-gray-500">Est. Occupied</div>
            </div>
            <div class="rounded-lg bg-emerald-50 p-3 text-center dark:bg-emerald-500/10">
                <div class="text-2xl font-bold text-emerald-600">{{ $occupancy['total_rooms'] }}</div>
                <div class="text-xs text-gray-500">Total Rooms</div>
            </div>
        </div>
    </div>

    {{-- Housekeeping Activity --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Housekeeping Activity</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
            <div class="rounded-lg bg-green-50 p-3 text-center dark:bg-green-500/10">
                <div class="text-2xl font-bold text-green-600">{{ $activity['vacant_clean'] }}</div>
                <div class="text-xs text-gray-500">Vacant Clean</div>
            </div>
            <div class="rounded-lg bg-lime-50 p-3 text-center dark:bg-lime-500/10">
                <div class="text-2xl font-bold text-lime-600">{{ $activity['vacant_clean_unchecked'] }}</div>
                <div class="text-xs text-gray-500">V. Clean Unchecked</div>
            </div>
            <div class="rounded-lg bg-blue-50 p-3 text-center dark:bg-blue-500/10">
                <div class="text-2xl font-bold text-blue-600">{{ $activity['occupied_clean'] }}</div>
                <div class="text-xs text-gray-500">Occupied Clean</div>
            </div>
            <div class="rounded-lg bg-amber-50 p-3 text-center dark:bg-amber-500/10">
                <div class="text-2xl font-bold text-amber-600">{{ $activity['occupied_dirty'] }}</div>
                <div class="text-xs text-gray-500">Occupied Dirty</div>
            </div>
            <div class="rounded-lg bg-orange-50 p-3 text-center dark:bg-orange-500/10">
                <div class="text-2xl font-bold text-orange-600">{{ $activity['vacant_dirty'] }}</div>
                <div class="text-xs text-gray-500">Vacant Dirty</div>
            </div>
            <div class="rounded-lg bg-yellow-50 p-3 text-center dark:bg-yellow-500/10">
                <div class="text-2xl font-bold text-yellow-600">{{ $activity['expected_departure'] }}</div>
                <div class="text-xs text-gray-500">Exp. Departure</div>
            </div>
            <div class="rounded-lg bg-gray-100 p-3 text-center dark:bg-gray-500/10">
                <div class="text-2xl font-bold text-gray-500">{{ $activity['dnd'] }}</div>
                <div class="text-xs text-gray-500">Do Not Disturb</div>
            </div>
            <div class="rounded-lg bg-teal-50 p-3 text-center dark:bg-teal-500/10">
                <div class="text-2xl font-bold text-teal-600">{{ $activity['total_cleaned'] }}</div>
                <div class="text-xs text-gray-500">Total Cleaned</div>
            </div>
            <div class="rounded-lg bg-red-50 p-3 text-center dark:bg-red-500/10">
                <div class="text-2xl font-bold text-red-600">{{ $activity['total_uncleaned'] }}</div>
                <div class="text-xs text-gray-500">Total Uncleaned</div>
            </div>
            <div class="rounded-lg bg-emerald-50 p-3 text-center dark:bg-emerald-500/10">
                <div class="text-2xl font-bold text-emerald-600">{{ $activity['available_today'] }}</div>
                <div class="text-xs text-gray-500">Available Today</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
