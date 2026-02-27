<x-filament-panels::page>
    <form wire:submit.prevent="processQuickCheckIn">
        {{-- Summary Info --}}
        <div class="mb-6 rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-700 dark:bg-warning-900/20">
            <div class="flex items-start gap-3">
                <x-filament::icon icon="heroicon-o-bolt" class="h-6 w-6 text-warning-500" />
                <div>
                    <h3 class="text-sm font-semibold text-warning-800 dark:text-warning-200">Walk-In Guest Quick Check-In</h3>
                    <p class="mt-1 text-xs text-warning-600 dark:text-warning-400">
                        Create a reservation and check-in the guest in one step. Fill in guest information, stay details, room assignment and rate.
                    </p>
                </div>
            </div>
        </div>

        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button type="submit" color="success" icon="heroicon-o-check-circle" size="lg">
                Process Quick Check-In
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
