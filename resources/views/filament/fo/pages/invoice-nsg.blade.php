<x-filament-panels::page>
    <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 dark:border-purple-700 dark:bg-purple-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-500/10">
                <x-heroicon-o-user-plus class="h-5 w-5 text-purple-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-purple-800 dark:text-purple-300">Non-Stay Guest Invoices</h3>
                <p class="text-xs text-purple-600 dark:text-purple-400">
                    For guests who are not staying at the hotel but make transactions. Must select department first. Guest must be registered as Outsider in Guest Card File.
                </p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
