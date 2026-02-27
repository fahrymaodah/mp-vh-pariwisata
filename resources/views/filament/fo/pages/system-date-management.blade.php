<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Current Date Display --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Current Business Date</p>
                    <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                        {{ \Carbon\Carbon::parse($currentDate)->format('d M Y') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($currentDate)->translatedFormat('l') }}
                    </p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Last Night Audit</p>
                    <p class="text-3xl font-bold {{ $lastAudit ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ $lastAudit ? \Carbon\Carbon::parse($lastAudit)->format('d M Y') : 'Never' }}
                    </p>
                    @if ($lastAudit)
                        <p class="mt-1 text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($lastAudit)->translatedFormat('l') }}
                        </p>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Quick Actions --}}
        <x-filament::section>
            <x-slot name="heading">Quick Date Controls</x-slot>
            <x-slot name="description">For training purposes — advance, reverse, or reset the system date.</x-slot>

            <div class="flex flex-wrap items-center justify-center gap-4">
                <x-filament::button
                    wire:click="goBackOneDay"
                    wire:confirm="Go back 1 day? This is for training purposes only."
                    color="warning"
                    icon="heroicon-o-chevron-left"
                >
                    Back 1 Day
                </x-filament::button>

                <x-filament::button
                    wire:click="advanceOneDay"
                    wire:confirm="Advance the system date by 1 day?"
                    color="success"
                    icon="heroicon-o-chevron-right"
                >
                    Advance 1 Day
                </x-filament::button>

                <x-filament::button
                    wire:click="resetToToday"
                    wire:confirm="Reset system date to today's real date?"
                    color="info"
                    icon="heroicon-o-arrow-path"
                >
                    Reset to Today
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Set Specific Date --}}
        <x-filament::section>
            <x-slot name="heading">Set Specific Date</x-slot>
            <x-slot name="description">Jump to any date for training scenarios.</x-slot>

            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Target Date</label>
                    <input
                        type="date"
                        wire:model="newDate"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                    />
                </div>
                <x-filament::button
                    wire:click="setSpecificDate"
                    wire:confirm="Set the system date to the selected date?"
                    color="primary"
                    icon="heroicon-o-calendar-days"
                >
                    Set Date
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Recent Audits --}}
        <x-filament::section>
            <x-slot name="heading">Recent Night Audits</x-slot>

            @php $audits = $this->getRecentAudits(); @endphp

            @if (count($audits) > 0)
                <div class="space-y-2">
                    @foreach ($audits as $audit)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <span class="text-sm font-medium">{{ $audit['date'] }}</span>
                            <x-filament::badge :color="$audit['color']">{{ $audit['status'] }}</x-filament::badge>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-sm text-gray-500">No night audits performed yet.</p>
            @endif
        </x-filament::section>

        {{-- Warning --}}
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-700 dark:bg-warning-900/20">
            <div class="flex items-start gap-3">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 text-warning-500" />
                <div>
                    <h3 class="text-sm font-semibold text-warning-800 dark:text-warning-200">Training Mode</h3>
                    <p class="mt-1 text-xs text-warning-600 dark:text-warning-400">
                        System date management is intended for training purposes. Changing the date affects all
                        reservation dates, check-in/check-out processing, and report generation across the system.
                        In a production environment, only Night Audit should advance the date.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
