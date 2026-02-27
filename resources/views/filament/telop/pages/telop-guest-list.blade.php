<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Display Filter Buttons --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'reservation' => 'Reservation',
                    'resident' => 'Resident (In-House)',
                    'arrival' => 'Arrival Today',
                    'depart' => 'Depart Today',
                    'departed' => 'Departed',
                    'all' => 'All',
                ] as $key => $label)
                    <button wire:click="setDisplayFilter('{{ $key }}')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition
                        {{ $displayFilter === $key
                            ? 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 ring-1 ring-cyan-400'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Guest Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
