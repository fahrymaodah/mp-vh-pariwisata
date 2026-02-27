<x-filament-panels::page>
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-500/10">
                <x-heroicon-o-banknotes class="h-5 w-5 text-amber-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Deposit Administration</h3>
                <p class="text-xs text-amber-600 dark:text-amber-400">
                    Manage advance deposit payments from guests. Record payments using the Pay action. Voucher numbers will appear in Booking Journal and Payment Journal reports.
                </p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
