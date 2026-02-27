<div class="space-y-3">
    @forelse ($deposits as $deposit)
        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $deposit->payment_method }}
                    @if ($deposit->voucher_no)
                        <span class="text-xs text-gray-500">({{ $deposit->voucher_no }})</span>
                    @endif
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $deposit->payment_date?->format('d/m/Y') }} — by {{ $deposit->user?->name ?? 'System' }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-success-600 dark:text-success-400">
                    Rp {{ number_format((float) $deposit->amount, 0, ',', '.') }}
                </p>
            </div>
        </div>
    @empty
        <p class="text-center text-sm text-gray-500 dark:text-gray-400">No deposit payments recorded yet.</p>
    @endforelse

    @if ($deposits->isNotEmpty())
        <div class="mt-2 border-t border-gray-200 pt-2 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Paid</span>
                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                    Rp {{ number_format($deposits->sum('amount'), 0, ',', '.') }}
                </span>
            </div>
        </div>
    @endif
</div>
