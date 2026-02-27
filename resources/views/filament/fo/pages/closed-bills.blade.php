<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-gray-100 p-2 dark:bg-gray-500/10">
                <x-heroicon-o-lock-closed class="h-5 w-5 text-gray-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-300">Closed Guest Bills</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Bills that have been settled and closed. Use the Reopen action to reactivate a bill for corrections or additional postings.
                </p>
            </div>
        </div>
    </div>

    {{ $this->filtersForm }}

    {{ $this->table }}
</x-filament-panels::page>
