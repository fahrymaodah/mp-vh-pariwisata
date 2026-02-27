<x-filament-panels::page>
    {{-- Info Banner --}}
    <div class="mb-6 rounded-xl border border-danger-200 bg-danger-50 p-4 dark:border-danger-700 dark:bg-danger-900/20">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-user-group" class="h-6 w-6 text-danger-500" />
            <div>
                <h3 class="text-sm font-semibold text-danger-800 dark:text-danger-200">Group Check-Out</h3>
                <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                    <strong>Auto Check-Out:</strong> Automatically check-out all checked-in members of a group. All bills must be settled (zero balance).<br>
                    <strong>Manual:</strong> Click "View Members" to check-out individual members one by one from the reservation detail page.
                </p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    {{ $this->filtersForm }}

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
