<x-filament-panels::page>
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-700 dark:bg-emerald-900/20">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5 text-emerald-500" />
            <p class="text-xs text-emerald-700 dark:text-emerald-300">
                Revenue breakdown per occupied room showing Lodging, F&B, Other Revenue, and Fix Cost totals.
            </p>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
