<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Student Selector --}}
        <x-filament::section>
            <x-slot name="heading">Select Student</x-slot>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($students as $student)
                    <button
                        wire:click="selectStudent({{ $student->id }})"
                        @class([
                            'flex flex-col items-center rounded-lg border p-3 text-center transition-colors',
                            'border-primary-500 bg-primary-50 dark:bg-primary-900/20' => $selectedStudentId === $student->id,
                            'border-gray-200 hover:border-primary-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-primary-600 dark:hover:bg-gray-800' => $selectedStudentId !== $student->id,
                        ])
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            {{ strtoupper(substr($student->name, 0, 2)) }}
                        </div>
                        <span class="mt-2 text-xs font-medium truncate w-full">{{ $student->name }}</span>
                    </button>
                @endforeach

                @if ($students->isEmpty())
                    <div class="col-span-full text-center text-sm text-gray-500 dark:text-gray-400 py-8">
                        No students registered yet.
                    </div>
                @endif
            </div>
        </x-filament::section>

        @if ($selectedStudentId && ! empty($studentStats))
            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Scenarios --}}
                <x-filament::section>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Scenarios</p>
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            {{ $studentStats['scenarios_completed'] }}/{{ $studentStats['scenarios_total'] }}
                        </p>
                        <p class="text-xs text-gray-400">Avg Score: {{ $studentStats['avg_scenario_score'] }}%</p>
                    </div>
                </x-filament::section>

                {{-- Quizzes --}}
                <x-filament::section>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quizzes Passed</p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400">
                            {{ $studentStats['quizzes_passed'] }}/{{ $studentStats['quizzes_total'] }}
                        </p>
                        <p class="text-xs text-gray-400">Avg Score: {{ $studentStats['avg_quiz_score'] }}%</p>
                    </div>
                </x-filament::section>

                {{-- Tutorials --}}
                <x-filament::section>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tutorials</p>
                        <p class="text-2xl font-bold text-info-600 dark:text-info-400">
                            {{ $studentStats['tutorials_completed'] }}/{{ $studentStats['tutorials_total'] }}
                        </p>
                        <p class="text-xs text-gray-400">Completed</p>
                    </div>
                </x-filament::section>

                {{-- Recent Activity --}}
                <x-filament::section>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Activity (7 days)</p>
                        <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">
                            {{ $studentStats['recent_activities'] }}
                        </p>
                        <p class="text-xs text-gray-400">Actions logged</p>
                    </div>
                </x-filament::section>
            </div>

            {{-- Module Breakdown --}}
            @if (! empty($studentStats['module_breakdown']))
                <x-filament::section>
                    <x-slot name="heading">Module Activity Breakdown</x-slot>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @php
                            $moduleLabels = ['fo' => 'Front Office', 'hk' => 'Housekeeping', 'sales' => 'Sales', 'telop' => 'TelOp'];
                            $moduleColors = ['fo' => 'primary', 'hk' => 'success', 'sales' => 'warning', 'telop' => 'info'];
                        @endphp
                        @foreach ($moduleLabels as $key => $label)
                            <div class="rounded-lg border border-gray-200 p-4 text-center dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                <p class="text-xl font-bold text-{{ $moduleColors[$key] }}-600 dark:text-{{ $moduleColors[$key] }}-400">
                                    {{ $studentStats['module_breakdown'][$key] ?? 0 }}
                                </p>
                                <p class="text-xs text-gray-400">actions</p>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif
        @endif
    </div>
</x-filament-panels::page>
