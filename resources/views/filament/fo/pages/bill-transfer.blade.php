<x-filament-panels::page>
    {{-- Source Invoice Summary --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        {{-- Source Bill --}}
        <div class="rounded-xl border-2 border-yellow-300 bg-yellow-50 p-4 dark:border-yellow-600 dark:bg-yellow-900/20">
            <h3 class="text-xs font-medium text-yellow-700 dark:text-yellow-400 uppercase">Source Bill (Current)</h3>
            <div class="mt-2 space-y-1">
                <p class="text-lg font-bold text-yellow-800 dark:text-yellow-300">{{ $this->record->invoice_no }}</p>
                <p class="text-sm">Room: {{ $this->record->room?->room_number ?? 'N/A' }}</p>
                <p class="text-sm">Balance: <span class="font-bold {{ (float)$this->record->balance > 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format((float)$this->record->balance, 0, ',', '.') }}</span></p>
            </div>
        </div>

        {{-- Sibling Bills --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Other Bills (Same Guest)</h3>
            <div class="mt-2 space-y-2">
                @forelse($this->siblingInvoices as $sibling)
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 p-2 dark:bg-gray-800">
                        <div>
                            <span class="text-sm font-semibold">{{ $sibling->invoice_no }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ $sibling->status->label() }}</span>
                        </div>
                        <span class="text-sm font-medium {{ (float)$sibling->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format((float)$sibling->balance, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No other bills. Create a new bill to start splitting.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Transfer Info --}}
    <div class="rounded-lg border border-orange-200 bg-orange-50 p-3 dark:border-orange-700 dark:bg-orange-900/20">
        <div class="flex items-start gap-3">
            <x-heroicon-o-arrows-right-left class="h-5 w-5 text-orange-500" />
            <p class="text-xs text-orange-700 dark:text-orange-300">
                Use "Transfer Selected Items" to move items from this bill to another. Use "New Bill" to create a split bill first.
                VHP supports up to 4 visible bills — unlimited through close-and-reopen workflow.
            </p>
        </div>
    </div>

    {{-- Items Table --}}
    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Items in {{ $this->record->invoice_no }}</h3>
    {{ $this->table }}
</x-filament-panels::page>
