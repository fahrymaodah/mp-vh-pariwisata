<x-filament-panels::page>
    @php $cal = $this->getCalendarData(); @endphp

    <div class="space-y-4">
        {{-- Navigation --}}
        <div class="flex items-center justify-between">
            <x-filament::button wire:click="previousMonth" icon="heroicon-o-chevron-left" size="sm" color="gray">
                Previous
            </x-filament::button>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $cal['monthName'] }}</h2>
            <x-filament::button wire:click="nextMonth" icon-position="after" icon="heroicon-o-chevron-right" size="sm" color="gray">
                Next
            </x-filament::button>
        </div>

        {{-- Calendar Grid --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            {{-- Day headers --}}
            <div class="grid grid-cols-7 bg-gray-100 dark:bg-gray-800">
                @foreach ($cal['dayHeaders'] as $header)
                    <div class="px-2 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 last:border-r-0">
                        {{ $header }}
                    </div>
                @endforeach
            </div>

            {{-- Weeks --}}
            @foreach ($cal['weeks'] as $week)
                <div class="grid grid-cols-7 border-t border-gray-200 dark:border-gray-700">
                    @foreach ($week as $day)
                        <div class="min-h-[100px] border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-1
                            {{ $day && $day['is_today'] ? 'bg-primary-50 dark:bg-primary-950' : 'bg-white dark:bg-gray-900' }}">
                            @if ($day)
                                <div class="text-xs font-semibold {{ $day['is_today'] ? 'text-primary-600' : 'text-gray-500 dark:text-gray-400' }} mb-1">
                                    {{ $day['day'] }}
                                </div>
                                @foreach ($day['schedules'] as $schedule)
                                    <div class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded px-1 py-0.5 mb-0.5 truncate" title="{{ $schedule['label'] }}">
                                        {{ $schedule['time'] }} {{ $schedule['label'] }}
                                    </div>
                                @endforeach
                                @foreach ($day['tasks'] as $task)
                                    <div class="text-xs bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 rounded px-1 py-0.5 mb-0.5 truncate" title="{{ $task['title'] }}">
                                        &#9744; {{ $task['title'] }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="flex gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-100 dark:bg-blue-900 border border-blue-300"></span> Schedule</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-100 dark:bg-amber-900 border border-amber-300"></span> Task</span>
        </div>
    </div>
</x-filament-panels::page>
