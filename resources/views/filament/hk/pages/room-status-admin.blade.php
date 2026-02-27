<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-green-100 p-2 dark:bg-green-500/10">
                <x-heroicon-o-arrow-path class="h-5 w-5 text-green-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-300">Room Status Administration</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    View all rooms and change their housekeeping status. Use the Display filter to show rooms by status and Sort to reorder.
                </p>
            </div>
        </div>
    </div>

    {{ $this->filtersForm }}

    {{ $this->table }}
</x-filament-panels::page>
