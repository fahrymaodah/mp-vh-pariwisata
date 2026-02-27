<x-filament-panels::page>
    {{-- Info --}}
    <div class="mb-4 rounded-lg border border-primary-200 bg-primary-50 p-3 dark:border-primary-700 dark:bg-primary-900/20">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-envelope" class="h-5 w-5 text-primary-500" />
            <p class="text-xs text-primary-700 dark:text-primary-300">
                Manage guest messages for in-house guests. Messages with unread status show as <strong>M</strong> indicator in the Resident Guest list.
            </p>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
