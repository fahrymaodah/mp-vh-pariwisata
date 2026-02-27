<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Warning Banner --}}
        <x-filament::section>
            <div class="flex items-center gap-3 text-warning-600 dark:text-warning-400">
                <x-heroicon-o-exclamation-triangle class="h-6 w-6 shrink-0" />
                <div>
                    <p class="font-semibold">Warning: Data Reset</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        This page allows you to reset module data for training purposes.
                        Truncated data cannot be recovered. Use with caution.
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Module Cards --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($modules as $key => $label)
                <x-filament::section>
                    <x-slot name="heading">{{ $label }}</x-slot>

                    <div class="space-y-3">
                        @if (isset($recordCounts[$key]))
                            <div class="space-y-1">
                                @foreach ($recordCounts[$key] as $table => $count)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', ucfirst($table)) }}</span>
                                        <span @class([
                                            'font-mono text-xs px-2 py-0.5 rounded-full',
                                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => $count === 0,
                                            'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' => $count > 0,
                                        ])>
                                            {{ number_format($count) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            @php
                                $totalRecords = array_sum($recordCounts[$key]);
                            @endphp

                            <div class="border-t pt-3 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Total: {{ number_format($totalRecords) }} records</span>
                                    <x-filament::button
                                        wire:click="resetModule('{{ $key }}')"
                                        wire:confirm="Are you sure you want to reset all {{ $label }} data? This action cannot be undone."
                                        color="danger"
                                        size="sm"
                                        :disabled="$totalRecords === 0"
                                    >
                                        Reset
                                    </x-filament::button>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-400">No tables configured.</p>
                        @endif
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        {{-- Full Reset --}}
        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-danger-600 dark:text-danger-400">Full Data Reset</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Reset ALL module data and re-seed with initial training data.
                    </p>
                </div>
                <x-filament::button
                    wire:click="resetAll"
                    wire:confirm="Are you absolutely sure? This will reset ALL module data across the entire system. This action cannot be undone."
                    color="danger"
                    icon="heroicon-o-arrow-path"
                >
                    Reset All Modules
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
