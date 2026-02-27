<x-filament-panels::page>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-700 dark:bg-red-900/20">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5 text-red-500" />
            <p class="text-xs text-red-700 dark:text-red-300">
                Outstanding bills with open status for all in-house guests, sorted by room number. Red balance indicates unpaid amount.
            </p>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
