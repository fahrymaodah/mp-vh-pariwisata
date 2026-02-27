<x-filament-panels::page>
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-700 dark:bg-blue-900/20">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-500/10">
                <x-heroicon-o-bolt class="h-5 w-5 text-blue-500" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Quick Posting to Guest Bill</h3>
                <p class="text-xs text-blue-600 dark:text-blue-400">
                    Post the same article to multiple rooms at once. Select department &rarr; article &rarr; add rooms with quantities. Useful for recurring charges like internet, minibar, or restaurant bills.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">How to use Quick Posting</h3>
        <ol class="list-inside list-decimal space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <li>Click the <strong>"Quick Post"</strong> button above.</li>
            <li>Select the <strong>Department</strong> for the article you want to post.</li>
            <li>Select the <strong>Article</strong> from the department's list.</li>
            <li>Add one or more <strong>rooms</strong> and enter the quantity and price (optional — leave blank for default).</li>
            <li>Click <strong>Submit</strong> to post the article to all selected rooms.</li>
        </ol>
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-500">
            Each posting will be recorded in the guest's open FO Invoice. If no open invoice exists, one will be created automatically.
        </p>
    </div>
</x-filament-panels::page>
