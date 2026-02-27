<x-filament-panels::page>
    {{-- Info Banner --}}
    <div class="mb-6 rounded-xl border border-info-200 bg-info-50 p-4 dark:border-info-700 dark:bg-info-900/20">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-user-group" class="h-6 w-6 text-info-500" />
            <div>
                <h3 class="text-sm font-semibold text-info-800 dark:text-info-200">Group Check-In</h3>
                <p class="mt-1 text-xs text-info-600 dark:text-info-400">
                    <strong>Auto Check-In:</strong> Automatically check-in all members of a group that have rooms assigned.<br>
                    <strong>Manual:</strong> Click "View Members" to check-in individual members one by one from the reservation detail page.
                </p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    {{ $this->filtersForm }}

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
