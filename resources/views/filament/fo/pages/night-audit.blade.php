<x-filament-panels::page>
    <div class="space-y-6">
        {{-- System Date Info --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Business Date</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $this->getSystemDate() }}</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Last Night Audit</p>
                    <p class="text-2xl font-bold {{ $this->getLastAuditDate() ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ $this->getLastAuditDate() ?? 'Never' }}
                    </p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    @if ($auditCompleted)
                        <x-filament::badge color="success" class="text-lg">Completed</x-filament::badge>
                    @elseif ($auditStatus === 'in_progress')
                        <x-filament::badge color="warning" class="text-lg">In Progress</x-filament::badge>
                    @elseif ($auditStatus === 'failed')
                        <x-filament::badge color="danger" class="text-lg">Failed</x-filament::badge>
                    @else
                        <x-filament::badge color="gray" class="text-lg">Not Started</x-filament::badge>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Statistics --}}
        @php $stats = $this->getStatistics(); @endphp
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">Rooms Occupied</p>
                <p class="text-xl font-bold text-info-600">{{ $stats['rooms_occupied'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">Rooms Available</p>
                <p class="text-xl font-bold text-success-600">{{ $stats['rooms_available'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">Occupancy Rate</p>
                <p class="text-xl font-bold text-warning-600">{{ $stats['occupancy_rate'] }}%</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">Today Revenue</p>
                <p class="text-xl font-bold text-primary-600">IDR {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Pre-Audit Checklist --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between">
                    <span>Pre-Night Audit Checklist</span>
                    <x-filament::badge color="{{ $this->getAllChecklistPassed() ? 'success' : 'warning' }}">
                        {{ $this->getChecklistPassCount() }} / {{ $this->getChecklistTotalCount() }} Passed
                    </x-filament::badge>
                </div>
            </x-slot>

            <div class="space-y-2">
                @foreach ($checklist as $item)
                    <div class="flex items-center gap-3 rounded-lg border p-3 {{ $item['passed'] ? 'border-success-200 bg-success-50 dark:border-success-700 dark:bg-success-900/20' : 'border-danger-200 bg-danger-50 dark:border-danger-700 dark:bg-danger-900/20' }}">
                        @if ($item['passed'])
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6 text-success-500" />
                        @else
                            <x-filament::icon icon="heroicon-o-x-circle" class="h-6 w-6 text-danger-500" />
                        @endif
                        <div class="flex-1">
                            <p class="text-sm font-medium {{ $item['passed'] ? 'text-success-800 dark:text-success-200' : 'text-danger-800 dark:text-danger-200' }}">
                                Step {{ $item['step'] }}: {{ $item['label'] }}
                            </p>
                            <p class="text-xs {{ $item['passed'] ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ $item['detail'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- Run Midnight Program --}}
        @if (! $auditCompleted)
            <x-filament::section>
                <x-slot name="heading">Run Midnight Program</x-slot>

                <div class="space-y-4">
                    <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-700 dark:bg-warning-900/20">
                        <div class="flex items-start gap-3">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 text-warning-500" />
                            <div>
                                <h3 class="text-sm font-semibold text-warning-800 dark:text-warning-200">Important</h3>
                                <p class="text-xs text-warning-600 dark:text-warning-400">
                                    The Midnight Program will:
                                </p>
                                <ul class="mt-1 list-inside list-disc text-xs text-warning-600 dark:text-warning-400">
                                    <li>Post room charges to all in-house guest bills</li>
                                    <li>Post fix-cost articles (daily)</li>
                                    <li>Mark pending arrivals as No-Show</li>
                                    <li>Cancel 6PM release reservations</li>
                                    <li>Update rooms to Expected Departure</li>
                                    <li>Advance the system date to the next day</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <x-filament::button
                            wire:click="runMidnightProgram"
                            wire:confirm="Are you sure you want to run the Night Audit Midnight Program? This action cannot be undone."
                            color="danger"
                            size="xl"
                            icon="heroicon-o-moon"
                        >
                            Run Midnight Program
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- Audit Result --}}
        @if ($lastAudit && $auditCompleted)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 text-success-500" />
                        <span>Audit Result — {{ $lastAudit->audit_date->format('d M Y') }}</span>
                    </div>
                </x-slot>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-gray-500">Started At</p>
                        <p class="font-medium">{{ $lastAudit->started_at?->format('H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Completed At</p>
                        <p class="font-medium">{{ $lastAudit->completed_at?->format('H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Occupancy Rate</p>
                        <p class="font-medium">{{ $lastAudit->occupancy_rate }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total Revenue</p>
                        <p class="font-medium">IDR {{ number_format((float) $lastAudit->total_revenue, 0, ',', '.') }}</p>
                    </div>
                </div>

                @if ($lastAudit->notes)
                    <div class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500">Audit Notes</p>
                        <pre class="mt-1 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">{{ $lastAudit->notes }}</pre>
                    </div>
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
