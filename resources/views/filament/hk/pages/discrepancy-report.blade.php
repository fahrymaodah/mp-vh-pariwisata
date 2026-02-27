<x-filament-panels::page>
    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-500/10">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-300">
                    Discrepancy Report — {{ $this->getDiscrepancyCount() }} issue(s)
                </h3>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Rooms where Front Office reservation status doesn't match Housekeeping room status. Resolve discrepancies by updating room status or checking reservation records.
                </p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
