<x-filament-panels::page>
    <div class="space-y-6">
        @if ($activeTutorialId)
            {{-- Active Tutorial Overlay --}}
            <x-filament::section>
                <div class="space-y-4">
                    {{-- Progress Bar --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">
                            Step {{ $currentStep + 1 }} of {{ count($activeSteps) }}
                        </h3>
                        <x-filament::button
                            wire:click="closeTutorial"
                            color="gray"
                            size="sm"
                        >
                            Close Tutorial
                        </x-filament::button>
                    </div>

                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                        <div
                            class="h-2 rounded-full bg-primary-500 transition-all duration-300"
                            style="width: {{ count($activeSteps) > 0 ? (($currentStep + 1) / count($activeSteps)) * 100 : 0 }}%"
                        ></div>
                    </div>

                    @if (isset($activeSteps[$currentStep]))
                        <div class="rounded-lg border border-primary-200 bg-primary-50 p-6 dark:border-primary-800 dark:bg-primary-900/20">
                            <h4 class="text-xl font-bold text-primary-700 dark:text-primary-300">
                                {{ $activeSteps[$currentStep]['title'] ?? 'Step ' . ($currentStep + 1) }}
                            </h4>
                            <p class="mt-3 text-gray-700 dark:text-gray-300">
                                {{ $activeSteps[$currentStep]['content'] ?? '' }}
                            </p>
                            @if (! empty($activeSteps[$currentStep]['element']))
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-medium">Target:</span>
                                    <code class="rounded bg-gray-200 px-1 dark:bg-gray-700">{{ $activeSteps[$currentStep]['element'] }}</code>
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Navigation --}}
                    <div class="flex items-center justify-between">
                        <x-filament::button
                            wire:click="previousStep"
                            color="gray"
                            :disabled="$currentStep === 0"
                        >
                            Previous
                        </x-filament::button>

                        <x-filament::button
                            wire:click="nextStep"
                            color="primary"
                        >
                            {{ $currentStep < count($activeSteps) - 1 ? 'Next' : 'Complete' }}
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @else
            {{-- Tutorial List --}}
            @if ($tutorials->isEmpty())
                <x-filament::section>
                    <div class="py-12 text-center">
                        <x-heroicon-o-book-open class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No tutorials available</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            No tutorials have been created for this module yet.
                        </p>
                    </div>
                </x-filament::section>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($tutorials as $tutorial)
                        <x-filament::section>
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $tutorial['title'] }}
                                    </h3>
                                    @if ($tutorial['is_completed'])
                                        <span class="inline-flex items-center rounded-full bg-success-100 px-2 py-1 text-xs font-medium text-success-700 dark:bg-success-900 dark:text-success-300">
                                            Completed
                                        </span>
                                    @elseif ($tutorial['current_step'] > 0)
                                        <span class="inline-flex items-center rounded-full bg-warning-100 px-2 py-1 text-xs font-medium text-warning-700 dark:bg-warning-900 dark:text-warning-300">
                                            In Progress
                                        </span>
                                    @endif
                                </div>

                                @if ($tutorial['description'])
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $tutorial['description'] }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">
                                        {{ $tutorial['steps_count'] }} steps
                                    </span>

                                    <x-filament::button
                                        wire:click="startTutorial({{ $tutorial['id'] }})"
                                        size="sm"
                                        :color="$tutorial['is_completed'] ? 'gray' : 'primary'"
                                    >
                                        {{ $tutorial['is_completed'] ? 'Restart' : ($tutorial['current_step'] > 0 ? 'Continue' : 'Start') }}
                                    </x-filament::button>
                                </div>
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
