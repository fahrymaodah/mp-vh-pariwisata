<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->filtersForm }}
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Charges</div>
            <div class="text-xl font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($summaryData['total_charges'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Payments</div>
            <div class="text-xl font-bold text-success-600 dark:text-success-400">Rp {{ number_format($summaryData['total_payments'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Net Difference</div>
            <div class="text-xl font-bold {{ ($summaryData['net_difference'] ?? 0) > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                Rp {{ number_format($summaryData['net_difference'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Open Invoices</div>
            <div class="text-xl font-bold text-warning-600 dark:text-warning-400">{{ $summaryData['open_invoices'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Closed Today</div>
            <div class="text-xl font-bold text-info-600 dark:text-info-400">{{ $summaryData['closed_today'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Payment Method & Cashier Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Payment Methods --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <h3 class="text-sm font-semibold dark:text-gray-200">Payment Methods</h3>
            </div>
            <div class="p-4">
                @forelse($summaryData['payment_methods'] ?? [] as $method)
                <div class="flex justify-between items-center py-2 border-b last:border-0 dark:border-gray-700">
                    <div>
                        <span class="text-sm font-medium dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $method['method'])) }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">({{ $method['count'] }} txn)</span>
                    </div>
                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">Rp {{ number_format($method['total'], 0, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No payments</p>
                @endforelse
            </div>
        </div>

        {{-- Cashier Breakdown --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <h3 class="text-sm font-semibold dark:text-gray-200">Cashier Breakdown</h3>
            </div>
            <div class="p-4">
                @forelse($summaryData['cashier_breakdown'] ?? [] as $cashier)
                <div class="flex justify-between items-center py-2 border-b last:border-0 dark:border-gray-700">
                    <div>
                        <span class="text-sm font-medium dark:text-gray-200">{{ $cashier['name'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">({{ $cashier['count'] }} txn)</span>
                    </div>
                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">Rp {{ number_format($cashier['total'], 0, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No cashier data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Payment Transactions Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h3 class="text-sm font-semibold dark:text-gray-200">Payment Transactions</h3>
        </div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
