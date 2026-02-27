<x-filament-panels::page>
    {{-- Quick Record Form --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Quick Record</h3>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="mb-1 block text-xs font-medium text-gray-500">Linen Type</label>
                <select wire:model="selectedLinenType" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Select linen type...</option>
                    @foreach($this->getLinenTypes() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-32">
                <label class="mb-1 block text-xs font-medium text-gray-500">Qty</label>
                <input type="number" wire:model="transactionQty" min="1" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="0">
            </div>
            <button wire:click="recordTransaction('incoming')" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Incoming
            </button>
            <button wire:click="recordTransaction('outgoing')" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
                - Outgoing
            </button>
        </div>
    </div>

    {{ $this->filtersForm }}

    {{ $this->table }}
</x-filament-panels::page>
