<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info --}}
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-700 dark:bg-warning-900/20">
            <div class="flex items-start gap-3">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 text-warning-500" />
                <div>
                    <h3 class="text-sm font-semibold text-warning-800 dark:text-warning-200">Early Check-Out List</h3>
                    <p class="mt-1 text-xs text-warning-600 dark:text-warning-400">
                        Guests who checked out before their originally planned departure date. The "Nights Lost" column shows
                        how many nights remain unused.
                    </p>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        {{ $this->filtersForm }}

        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
