<x-filament-panels::page>
    {{-- Invoice Header Info --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        {{-- Bill Info --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bill Info</h3>
            <div class="mt-2 space-y-1">
                <p class="text-sm"><span class="font-semibold">Bill #:</span> {{ $this->record->invoice_no }}</p>
                <p class="text-sm"><span class="font-semibold">Room:</span> {{ $this->record->room?->room_number ?? 'N/A' }}</p>
                <p class="text-sm"><span class="font-semibold">Type:</span>
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                        {{ $this->record->type->value === 'guest' ? 'bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-400' : 'bg-purple-50 text-purple-700 dark:bg-purple-400/10 dark:text-purple-400' }}">
                        {{ $this->record->type->label() }}
                    </span>
                </p>
                <p class="text-sm"><span class="font-semibold">Status:</span>
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                        {{ match($this->record->status->value) {
                            'open' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-400/10 dark:text-yellow-400',
                            'printed' => 'bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-400',
                            'closed' => 'bg-green-50 text-green-700 dark:bg-green-400/10 dark:text-green-400',
                            'reopened' => 'bg-red-50 text-red-700 dark:bg-red-400/10 dark:text-red-400',
                            default => 'bg-gray-50 text-gray-700'
                        } }}">
                        {{ $this->record->status->label() }}
                    </span>
                </p>
                @if($this->record->reservation?->arrangement)
                    <p class="text-sm"><span class="font-semibold">Argt:</span> {{ $this->record->reservation->arrangement->code }}</p>
                @endif
                <p class="text-sm"><span class="font-semibold">Room Rate:</span> Rp {{ number_format((float)($this->record->reservation?->room_rate ?? 0), 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Guest Info --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Guest</h3>
            <div class="mt-2 space-y-1">
                <p class="text-sm font-bold text-gray-950 dark:text-white">{{ $this->record->reservation?->guest?->full_name ?? $this->record->guest?->full_name ?? 'N/A' }}</p>
                @if($this->record->bill_address)
                    <p class="text-xs text-gray-500">{{ $this->record->bill_address }}</p>
                @endif
                @if($this->record->comments)
                    <p class="text-xs italic text-gray-400">{{ $this->record->comments }}</p>
                @endif
                @if($this->record->reservation)
                    <p class="text-sm"><span class="font-semibold">Arrival:</span> {{ $this->record->reservation->arrival_date->format('d/m/Y') }}</p>
                    <p class="text-sm"><span class="font-semibold">Departure:</span> {{ $this->record->reservation->departure_date->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Balance</h3>
            <div class="mt-2 space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Total Sales:</span>
                    <span class="text-sm font-semibold">Rp {{ number_format((float)$this->record->total_sales, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Total Payment:</span>
                    <span class="text-sm font-semibold text-green-600">Rp {{ number_format((float)$this->record->total_payment, 0, ',', '.') }}</span>
                </div>
                <hr class="border-gray-200 dark:border-white/10">
                <div class="flex justify-between">
                    <span class="text-base font-bold">Balance:</span>
                    <span class="text-base font-bold {{ (float)$this->record->balance > 0 ? 'text-red-600' : ((float)$this->record->balance < 0 ? 'text-yellow-600' : 'text-green-600') }}">
                        Rp {{ number_format((float)$this->record->balance, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Payment entries --}}
            @if($this->payments->isNotEmpty())
                <h4 class="mt-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Payments</h4>
                <div class="mt-1 space-y-1">
                    @foreach($this->payments as $pmt)
                        <div class="flex justify-between text-xs {{ $pmt->is_cancelled ? 'line-through text-gray-400' : '' }}">
                            <span>{{ $pmt->method->label() }} {{ $pmt->reference_no ? "({$pmt->reference_no})" : '' }}</span>
                            <span class="font-medium {{ $pmt->is_cancelled ? 'text-gray-400' : 'text-green-600' }}">Rp {{ number_format((float)$pmt->amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div class="mt-4">
        <h3 class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">Invoice Items (Sales Postings)</h3>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
