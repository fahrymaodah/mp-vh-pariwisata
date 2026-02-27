<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-500/10">
                <x-heroicon-o-credit-card class="h-5 w-5 text-blue-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">F/O Invoice — In House Guest Bills</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Click a row to open invoice detail. From there you can post articles, process payments, cancel, transfer bills, and print.
                </p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
