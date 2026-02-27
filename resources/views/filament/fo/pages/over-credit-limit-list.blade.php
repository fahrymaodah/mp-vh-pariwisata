<x-filament-panels::page>
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 text-amber-500" />
            <p class="text-xs text-amber-700 dark:text-amber-300">
                Guests whose outstanding balance exceeds their credit limit. <strong class="text-red-600">OVER LIMIT</strong> = exceeded, <strong class="text-yellow-600">WARNING</strong> = above 80%.
            </p>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
