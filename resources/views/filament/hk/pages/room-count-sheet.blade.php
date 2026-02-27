<x-filament-panels::page>
    @php
        $countData = $this->getCountData();
        $floors = $countData['floors'];
        $statuses = $countData['statuses'];
        $data = $countData['data'];
        $summary = $countData['summary'];
        $floorTotals = $countData['floor_totals'];
        $grandTotal = $countData['grand_total'];
    @endphp

    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800">
                    <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                    @foreach($floors as $floor)
                        <th class="px-3 py-2 text-center font-semibold text-red-600 dark:text-red-400">Floor {{ $floor }}</th>
                    @endforeach
                    <th class="px-3 py-2 text-center font-bold text-gray-800 dark:text-gray-200">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statuses as $status)
                    <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                @switch($status->color())
                                    @case('success') bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 @break
                                    @case('lime') bg-lime-50 text-lime-700 dark:bg-lime-500/10 dark:text-lime-400 @break
                                    @case('warning') bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400 @break
                                    @case('orange') bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400 @break
                                    @case('info') bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 @break
                                    @case('primary') bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 @break
                                    @case('danger') bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 @break
                                    @case('gray') bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400 @break
                                @endswitch
                            ">
                                {{ $status->label() }}
                            </span>
                        </td>
                        @foreach($floors as $floor)
                            <td class="px-3 py-2 text-center text-blue-600 dark:text-blue-400 font-medium">
                                {{ $data[$floor][$status->value] ?? 0 }}
                            </td>
                        @endforeach
                        <td class="px-3 py-2 text-center font-bold text-gray-800 dark:text-gray-200">
                            {{ $summary[$status->value] ?? 0 }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                    <td class="px-3 py-2 font-bold text-gray-800 dark:text-gray-200">TOTAL</td>
                    @foreach($floors as $floor)
                        <td class="px-3 py-2 text-center font-bold text-gray-800 dark:text-gray-200">
                            {{ $floorTotals[$floor] ?? 0 }}
                        </td>
                    @endforeach
                    <td class="px-3 py-2 text-center font-bold text-green-700 dark:text-green-400 text-lg">
                        {{ $grandTotal }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-filament-panels::page>
