<x-filament-panels::page>
    <div class="mx-auto max-w-3xl" id="printable-invoice">
        {{-- Hotel Header --}}
        <div class="border-b-2 border-gray-800 pb-4 text-center dark:border-white">
            <h1 class="text-2xl font-bold tracking-wide">PAR HOTEL</h1>
            <p class="text-sm text-gray-500">Jl. Pariwisata No. 1 — Tel: (021) 123-4567</p>
        </div>

        {{-- Invoice Title --}}
        <div class="mt-4 text-center">
            <h2 class="text-lg font-bold uppercase">
                @if($this->format === 'regular')
                    GUEST FOLIO
                @elseif($this->format === 'oneline')
                    ONE LINE FOLIO
                @else
                    SUMMARY BY ARTICLE
                @endif
            </h2>
            <p class="text-sm text-gray-600">Invoice No: <span class="font-semibold">{{ $this->record->invoice_no }}</span></p>
        </div>

        {{-- Guest Details --}}
        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p><span class="font-semibold">Guest:</span> {{ $this->record->reservation?->guest?->full_name ?? $this->record->guest?->full_name ?? 'N/A' }}</p>
                <p><span class="font-semibold">Room:</span> {{ $this->record->room?->room_number ?? 'N/A' }}</p>
                @if($this->record->reservation?->arrangement)
                    <p><span class="font-semibold">Arrangement:</span> {{ $this->record->reservation->arrangement->code }}</p>
                @endif
            </div>
            <div class="text-right">
                @if($this->record->reservation)
                    <p><span class="font-semibold">Arrival:</span> {{ $this->record->reservation->arrival_date->format('d/m/Y') }}</p>
                    <p><span class="font-semibold">Departure:</span> {{ $this->record->reservation->departure_date->format('d/m/Y') }}</p>
                    <p><span class="font-semibold">Room Rate:</span> Rp {{ number_format((float)$this->record->reservation->room_rate, 0, ',', '.') }}</p>
                @endif
                @if($this->record->bill_address)
                    <p><span class="font-semibold">Bill To:</span> {{ $this->record->bill_address }}</p>
                @endif
            </div>
        </div>

        <hr class="my-4 border-gray-300 dark:border-gray-600">

        {{-- REGULAR FORMAT --}}
        @if($this->format === 'regular')
            <table class="w-full text-sm">
                <thead class="border-b border-gray-400">
                    <tr>
                        <th class="py-1 text-left">Date</th>
                        <th class="py-1 text-left">Art</th>
                        <th class="py-1 text-left">Description</th>
                        <th class="py-1 text-center">Qty</th>
                        <th class="py-1 text-right">Rate</th>
                        <th class="py-1 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->activeItems as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-1">{{ $item->posting_date?->format('d/m') }}</td>
                            <td class="py-1">{{ $item->article?->article_no }}</td>
                            <td class="py-1">{{ $item->description }}</td>
                            <td class="py-1 text-center">{{ $item->qty }}</td>
                            <td class="py-1 text-right">{{ number_format((float)$item->rate, 0, ',', '.') }}</td>
                            <td class="py-1 text-right">{{ number_format((float)$item->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        {{-- ONE LINE FORMAT --}}
        @elseif($this->format === 'oneline')
            <table class="w-full text-sm">
                <thead class="border-b border-gray-400">
                    <tr>
                        <th class="py-1 text-left">Department</th>
                        <th class="py-1 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->oneLineSummary as $line)
                        <tr class="border-b border-gray-100">
                            <td class="py-1">{{ $line->dept_name }}</td>
                            <td class="py-1 text-right">{{ number_format((float)$line->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        {{-- SUMMARY BY ARTICLE FORMAT --}}
        @else
            <table class="w-full text-sm">
                <thead class="border-b border-gray-400">
                    <tr>
                        <th class="py-1 text-left">Art</th>
                        <th class="py-1 text-left">Description</th>
                        <th class="py-1 text-center">Total Qty</th>
                        <th class="py-1 text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->summaryByArticle as $summary)
                        <tr class="border-b border-gray-100">
                            <td class="py-1">{{ $summary->article_id }}</td>
                            <td class="py-1">{{ $summary->description }}</td>
                            <td class="py-1 text-center">{{ $summary->total_qty }}</td>
                            <td class="py-1 text-right">{{ number_format((float)$summary->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Payments Section --}}
        @if($this->activePayments->isNotEmpty())
            <div class="mt-4">
                <h4 class="text-sm font-semibold border-b border-gray-400 pb-1">Payments</h4>
                <table class="w-full text-sm">
                    <tbody>
                        @foreach($this->activePayments as $pmt)
                            <tr class="border-b border-gray-100">
                                <td class="py-1">{{ $pmt->method->label() }}</td>
                                <td class="py-1">{{ $pmt->article?->name }}</td>
                                <td class="py-1 text-right">Rp {{ number_format((float)$pmt->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Totals --}}
        <div class="mt-4 border-t-2 border-gray-800 pt-2 dark:border-white">
            <div class="flex justify-between text-sm">
                <span>Total Sales:</span>
                <span class="font-semibold">Rp {{ number_format((float)$this->record->total_sales, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Total Payment:</span>
                <span class="font-semibold">Rp {{ number_format((float)$this->record->total_payment, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-base font-bold mt-1">
                <span>Balance Due:</span>
                <span class="{{ (float)$this->record->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    Rp {{ number_format((float)$this->record->balance, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-center text-xs text-gray-400">
            <p>Printed at: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Thank you for staying at PAR Hotel</p>
        </div>
    </div>

    {{-- Print Button --}}
    <div class="mt-4 flex justify-center gap-3 print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Print
        </button>
        <button onclick="window.history.back()" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
            Back
        </button>
    </div>
</x-filament-panels::page>
