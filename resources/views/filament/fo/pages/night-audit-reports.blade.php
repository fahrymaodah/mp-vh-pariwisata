<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Report Tabs --}}
        <div class="flex flex-wrap gap-2">
            @php
                $reports = [
                    'audit_history' => ['label' => 'Audit History', 'icon' => 'heroicon-o-clipboard-document-list'],
                    'in_house' => ['label' => 'In-House Guest List', 'icon' => 'heroicon-o-home'],
                    'arrivals' => ['label' => 'Arrival List', 'icon' => 'heroicon-o-arrow-right-end-on-rectangle'],
                    'departures' => ['label' => 'Departure List', 'icon' => 'heroicon-o-arrow-left-start-on-rectangle'],
                    'occupancy' => ['label' => 'Occupancy Statistics', 'icon' => 'heroicon-o-chart-bar'],
                ];
            @endphp

            @foreach ($reports as $key => $report)
                <button
                    wire:click="setReport('{{ $key }}')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ $activeReport === $key ? 'bg-primary-500 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}"
                >
                    <x-filament::icon :icon="$report['icon']" class="h-4 w-4" />
                    {{ $report['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Date filter (for arrival/departure reports) --}}
        @if (in_array($activeReport, ['arrivals', 'departures']))
            {{ $this->filtersForm }}
        @endif

        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
