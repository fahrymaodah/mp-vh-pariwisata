<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Period Filter --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                    <input type="date" wire:model.live="periodFrom"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                    <input type="date" wire:model.live="periodTo"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Report Menu --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-1">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">CRM Reports</h3>
                    @foreach ([
                        'sales-performance' => 'Sales Performance',
                        'opportunity-pipeline' => 'Opportunity Pipeline',
                        'activity-summary' => 'Activity Summary',
                        'task-completion' => 'Task Completion',
                    ] as $key => $label)
                        <button wire:click="setReport('{{ $key }}')"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm transition
                            {{ $activeReport === $key ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach

                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2 mt-4">Budget Reports</h3>
                    @foreach ([
                        'budget-vs-actual' => 'Budget vs Actual',
                        'segment-statistics' => 'Segment Statistics',
                    ] as $key => $label)
                        <button wire:click="setReport('{{ $key }}')"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm transition
                            {{ $activeReport === $key ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach

                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2 mt-4">Guest Reports</h3>
                    @foreach ([
                        'guest-production' => 'Guest Production (Top 50)',
                        'company-production' => 'Company Production',
                        'repeater-guest' => 'Repeater Guest List',
                        'guest-birthday' => 'Guest Birthday List',
                        'nationality-stats' => 'Nationality Statistics',
                    ] as $key => $label)
                        <button wire:click="setReport('{{ $key }}')"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm transition
                            {{ $activeReport === $key ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach

                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2 mt-4">Production Reports</h3>
                    @foreach ([
                        'reservation-by-sales' => 'Reservation by Sales',
                        'source-statistics' => 'Source Statistics',
                        'competitor-analysis' => 'Competitor Analysis',
                    ] as $key => $label)
                        <button wire:click="setReport('{{ $key }}')"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm transition
                            {{ $activeReport === $key ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Report Content --}}
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ match($activeReport) {
                                'sales-performance' => 'Sales Performance',
                                'opportunity-pipeline' => 'Opportunity Pipeline',
                                'activity-summary' => 'Activity Summary',
                                'task-completion' => 'Task Completion',
                                'budget-vs-actual' => 'Budget vs Actual',
                                'segment-statistics' => 'Segment Statistics',
                                'guest-production' => 'Guest Production — Top 50',
                                'company-production' => 'Company Room Production',
                                'repeater-guest' => 'Repeater Guest List',
                                'guest-birthday' => 'Guest Birthday List',
                                'nationality-stats' => 'Nationality Statistics',
                                'reservation-by-sales' => 'Reservation List by Sales',
                                'source-statistics' => 'Source Statistics',
                                'competitor-analysis' => 'Competitor Analysis',
                                default => 'Report',
                            } }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Period: {{ $periodFrom }} — {{ $periodTo }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        @if (count($this->reportData) > 0)
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        @foreach (array_keys($this->reportData[0]) as $header)
                                            <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                {{ str_replace('_', ' ', ucfirst($header)) }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($this->reportData as $row)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            @foreach ($row as $value)
                                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                    {{ $value }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                                </svg>
                                <p class="mt-2 text-sm">No data available for the selected period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
