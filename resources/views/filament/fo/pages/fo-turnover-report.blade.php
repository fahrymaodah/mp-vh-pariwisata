<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    @php
        $periodStyles = [
            'primary' => ['header_bg' => 'bg-primary-50 dark:bg-primary-900/20', 'header_text' => 'text-primary-700 dark:text-primary-300'],
            'success' => ['header_bg' => 'bg-success-50 dark:bg-success-900/20', 'header_text' => 'text-success-700 dark:text-success-300'],
            'info'    => ['header_bg' => 'bg-info-50 dark:bg-info-900/20', 'header_text' => 'text-info-700 dark:text-info-300'],
        ];
    @endphp

    {{-- Period Tabs --}}
    <div class="space-y-8">
        @foreach([
            ['label' => 'Daily', 'data' => $dailyData, 'color' => 'primary'],
            ['label' => 'Month to Date (MTD)', 'data' => $mtdData, 'color' => 'success'],
            ['label' => 'Year to Date (YTD)', 'data' => $ytdData, 'color' => 'info'],
        ] as $period)
        @php $style = $periodStyles[$period['color']]; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-gray-700 {{ $style['header_bg'] }}">
                <h3 class="text-lg font-semibold {{ $style['header_text'] }}">
                    {{ $period['label'] }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        ({{ $period['data']['start'] ?? '-' }} — {{ $period['data']['end'] ?? '-' }})
                    </span>
                </h3>
            </div>

            <div class="p-6">
                {{-- Revenue Summary --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <div class="text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Charges</div>
                        <div class="text-xl font-bold dark:text-gray-200">Rp {{ number_format($period['data']['room_revenue'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tax</div>
                        <div class="text-xl font-bold dark:text-gray-200">Rp {{ number_format($period['data']['tax_amount'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Service</div>
                        <div class="text-xl font-bold dark:text-gray-200">Rp {{ number_format($period['data']['service_amount'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total</div>
                        <div class="text-xl font-bold text-success-600 dark:text-success-400">Rp {{ number_format($period['data']['total_charges'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Payments</div>
                        <div class="text-xl font-bold text-info-600 dark:text-info-400">Rp {{ number_format($period['data']['payments_received'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Outstanding</div>
                        <div class="text-xl font-bold {{ ($period['data']['net_outstanding'] ?? 0) > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                            Rp {{ number_format($period['data']['net_outstanding'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- Department Breakdown --}}
                @if(!empty($period['data']['department_breakdown']))
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900">
                                <th class="px-3 py-2 text-left border dark:border-gray-700">Department</th>
                                <th class="px-3 py-2 text-center border dark:border-gray-700">Transactions</th>
                                <th class="px-3 py-2 text-right border dark:border-gray-700">Amount</th>
                                <th class="px-3 py-2 text-right border dark:border-gray-700">Tax</th>
                                <th class="px-3 py-2 text-right border dark:border-gray-700">Service</th>
                                <th class="px-3 py-2 text-right border dark:border-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($period['data']['department_breakdown'] as $dept)
                            <tr class="dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-3 py-2 border dark:border-gray-700">{{ $dept['department_name'] ?? 'N/A' }}</td>
                                <td class="px-3 py-2 text-center border dark:border-gray-700">{{ $dept['transaction_count'] }}</td>
                                <td class="px-3 py-2 text-right border dark:border-gray-700">Rp {{ number_format((float)($dept['total_amount'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right border dark:border-gray-700">Rp {{ number_format((float)($dept['total_tax'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right border dark:border-gray-700">Rp {{ number_format((float)($dept['total_service'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right border dark:border-gray-700 font-semibold">
                                    Rp {{ number_format((float)($dept['total_amount'] ?? 0) + (float)($dept['total_tax'] ?? 0) + (float)($dept['total_service'] ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No transactions for this period.</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</x-filament-panels::page>
