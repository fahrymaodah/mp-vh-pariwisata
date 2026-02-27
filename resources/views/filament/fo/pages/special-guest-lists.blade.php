<x-filament-panels::page>
    {{-- List Type Info --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-purple-50 p-2 dark:bg-purple-500/10">
                <x-heroicon-o-star class="h-5 w-5 text-purple-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Special Guest Lists</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Use the filter above to switch between Walk-In, Foreign, Compliment, and ABF (Breakfast) guest lists.
                </p>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    {{ $this->filtersForm }}

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
