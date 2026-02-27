<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-pink-100 p-2 dark:bg-pink-500/10">
                <x-heroicon-o-heart class="h-5 w-5 text-pink-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-300">Guest Preference List</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Special preferences and requests for in-house guests. Record new preferences to ensure personalized guest service.
                </p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
