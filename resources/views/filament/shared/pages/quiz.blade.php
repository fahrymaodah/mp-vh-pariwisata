<x-filament-panels::page>
    <div class="space-y-6">
        @if ($activeQuizId && $result)
            {{-- Quiz Results --}}
            <x-filament::section>
                <div class="space-y-6 text-center">
                    <div>
                        @if ($result['passed'])
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-success-100 dark:bg-success-900">
                                <x-heroicon-o-check-circle class="h-10 w-10 text-success-600 dark:text-success-400" />
                            </div>
                            <h2 class="mt-4 text-2xl font-bold text-success-600 dark:text-success-400">Quiz Passed!</h2>
                        @else
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-900">
                                <x-heroicon-o-x-circle class="h-10 w-10 text-danger-600 dark:text-danger-400" />
                            </div>
                            <h2 class="mt-4 text-2xl font-bold text-danger-600 dark:text-danger-400">Not Passed</h2>
                        @endif
                    </div>

                    <div class="text-4xl font-bold">{{ round($result['score'], 1) }}%</div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Passing score: {{ $result['passing_score'] }}%
                    </p>

                    {{-- Answer Review --}}
                    <div class="mx-auto max-w-2xl space-y-3 text-left">
                        @foreach ($questions as $question)
                            <div @class([
                                'rounded-lg border p-4',
                                'border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-900/20' =>
                                    isset($answers[$question->id]) && $answers[$question->id] === $result['correct_answers'][$question->id],
                                'border-danger-300 bg-danger-50 dark:border-danger-700 dark:bg-danger-900/20' =>
                                    !isset($answers[$question->id]) || $answers[$question->id] !== $result['correct_answers'][$question->id],
                            ])>
                                <p class="font-medium">{{ $question->question }}</p>
                                <p class="mt-1 text-sm">
                                    Your answer: <span class="font-medium">{{ $answers[$question->id] ?? '(no answer)' }}</span>
                                </p>
                                @if (!isset($answers[$question->id]) || $answers[$question->id] !== $result['correct_answers'][$question->id])
                                    <p class="mt-1 text-sm text-success-700 dark:text-success-300">
                                        Correct: {{ $result['correct_answers'][$question->id] }}
                                    </p>
                                @endif
                                @if ($question->explanation)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 italic">
                                        {{ $question->explanation }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <x-filament::button wire:click="closeQuiz" color="primary">
                        Back to Quizzes
                    </x-filament::button>
                </div>
            </x-filament::section>

        @elseif ($activeQuizId && $activeQuiz)
            {{-- Active Quiz --}}
            <x-filament::section>
                <x-slot name="heading">{{ $activeQuiz->title }}</x-slot>
                @if ($activeQuiz->description)
                    <x-slot name="description">{{ $activeQuiz->description }}</x-slot>
                @endif

                <div class="space-y-6">
                    @foreach ($questions as $index => $question)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <p class="font-medium">
                                <span class="text-primary-600 dark:text-primary-400">{{ $index + 1 }}.</span>
                                {{ $question->question }}
                            </p>

                            <div class="mt-3 space-y-2">
                                @if ($question->type === 'multiple_choice' && is_array($question->options))
                                    @foreach ($question->options as $option)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800
                                            {{ isset($answers[$question->id]) && $answers[$question->id] === ($option['option'] ?? $option) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                            <input
                                                type="radio"
                                                name="question_{{ $question->id }}"
                                                value="{{ $option['option'] ?? $option }}"
                                                wire:model.live="answers.{{ $question->id }}"
                                                class="text-primary-600"
                                            >
                                            <span>{{ $option['option'] ?? $option }}</span>
                                        </label>
                                    @endforeach
                                @elseif ($question->type === 'true_false')
                                    @foreach (['true' => 'True', 'false' => 'False'] as $value => $label)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800
                                            {{ isset($answers[$question->id]) && $answers[$question->id] === $value ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                            <input
                                                type="radio"
                                                name="question_{{ $question->id }}"
                                                value="{{ $value }}"
                                                wire:model.live="answers.{{ $question->id }}"
                                                class="text-primary-600"
                                            >
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between border-t pt-4 dark:border-gray-700">
                        <x-filament::button wire:click="closeQuiz" color="gray">
                            Cancel
                        </x-filament::button>

                        <x-filament::button
                            wire:click="submitQuiz"
                            wire:confirm="Submit your answers? You cannot change them after submission."
                            color="primary"
                        >
                            Submit Quiz
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>

        @else
            {{-- Quiz List --}}
            @if ($quizzes->isEmpty())
                <x-filament::section>
                    <div class="py-12 text-center">
                        <x-heroicon-o-question-mark-circle class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No quizzes available</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            No quizzes have been created for this module yet.
                        </p>
                    </div>
                </x-filament::section>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($quizzes as $quiz)
                        <x-filament::section>
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $quiz['title'] }}
                                    </h3>
                                    @if ($quiz['last_passed'] === true)
                                        <span class="inline-flex items-center rounded-full bg-success-100 px-2 py-1 text-xs font-medium text-success-700 dark:bg-success-900 dark:text-success-300">
                                            Passed
                                        </span>
                                    @elseif ($quiz['last_score'] !== null)
                                        <span class="inline-flex items-center rounded-full bg-danger-100 px-2 py-1 text-xs font-medium text-danger-700 dark:bg-danger-900 dark:text-danger-300">
                                            {{ $quiz['last_score'] }}%
                                        </span>
                                    @endif
                                </div>

                                @if ($quiz['description'])
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $quiz['description'] }}
                                    </p>
                                @endif

                                <div class="flex items-center gap-3 text-xs text-gray-400">
                                    <span>{{ $quiz['questions_count'] }} questions</span>
                                    <span>Pass: {{ $quiz['passing_score'] }}%</span>
                                    @if ($quiz['time_limit'])
                                        <span>{{ $quiz['time_limit'] }} min</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::button
                                        wire:click="startQuiz({{ $quiz['id'] }})"
                                        size="sm"
                                        color="primary"
                                    >
                                        {{ $quiz['last_score'] !== null ? 'Retry' : 'Start' }}
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
