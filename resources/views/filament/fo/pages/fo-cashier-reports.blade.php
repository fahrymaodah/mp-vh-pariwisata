<x-filament-panels::page>
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-700 dark:bg-indigo-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-indigo-100 p-2 dark:bg-indigo-500/10">
                <x-heroicon-o-chart-bar-square class="h-5 w-5 text-indigo-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">FO Cashier Reports — Accounting Menu</h3>
                <p class="text-xs text-indigo-600 dark:text-indigo-400">
                    Select a report type and adjust filters to view FO transaction journals and summaries. See also: Bill Outstanding, Over Credit Limit, Room Revenue Breakdown in the In House menu.
                </p>
            </div>
        </div>
    </div>

    {{ $this->filtersForm }}

    {{ $this->table }}
</x-filament-panels::page>
