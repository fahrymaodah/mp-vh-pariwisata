<x-filament-panels::page>
    {{-- Info Banner --}}
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-6 w-6 text-amber-500" />
            <div>
                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Reactivate & Re-Check-In</h3>
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                    <strong>Reactivate:</strong> Restore a cancelled or no-show reservation back to Confirmed status.<br>
                    <strong>Re-Check-In:</strong> Reverse an accidental check-out. The room must still be available and no new transactions on the bill.
                </p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    {{ $this->filtersForm }}

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
