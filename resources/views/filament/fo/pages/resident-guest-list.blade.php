<x-filament-panels::page>
    {{-- Summary Stats --}}
    @php $stats = $this->getInHouseStats(); @endphp
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-xl border border-success-200 bg-success-50 p-4 text-center dark:border-success-700 dark:bg-success-900/20">
            <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['total'] }}</p>
            <p class="text-xs text-success-500">Total In-House</p>
        </div>
        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-center dark:border-primary-700 dark:bg-primary-900/20">
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['regular'] }}</p>
            <p class="text-xs text-primary-500">Regular</p>
        </div>
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-center dark:border-warning-700 dark:bg-warning-900/20">
            <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $stats['room_sharer'] }}</p>
            <p class="text-xs text-warning-500">Room Sharer</p>
        </div>
        <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-center dark:border-danger-700 dark:bg-danger-900/20">
            <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $stats['vip'] }}</p>
            <p class="text-xs text-danger-500">VIP</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-900/20">
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['incognito'] }}</p>
            <p class="text-xs text-gray-500">Incognito</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center dark:border-amber-700 dark:bg-amber-900/20">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['with_messages'] }}</p>
            <p class="text-xs text-amber-500">With Messages</p>
        </div>
    </div>

    {{-- Indicator Legend --}}
    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
        <p class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Indicators:</p>
        <div class="flex flex-wrap gap-3 text-xs">
            <span><strong class="text-warning-600">M</strong> = Message waiting</span>
            <span><strong class="text-info-600">L</strong> = Guest Locator</span>
            <span><strong class="text-primary-600">G</strong> = Group/Company</span>
            <span><strong class="text-gray-600">I</strong> = Incognito</span>
            <span><strong class="text-purple-600">MB</strong> = Master Bill</span>
            <span><strong class="text-success-600">A</strong> = Allotment</span>
        </div>
    </div>

    {{-- Filters --}}
    {{ $this->filtersForm }}

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
