<x-filament-panels::page>
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-900 p-8 print:p-4" id="printArea">
        {{-- Header --}}
        <div class="text-center border-b-2 pb-4 mb-6">
            <h1 class="text-2xl font-bold dark:text-gray-100">{{ $hotel?->name ?? 'PAR Hotel' }}</h1>
            <p class="text-sm text-gray-500">{{ $hotel?->address ?? '' }}</p>
            <p class="text-sm text-gray-500">Tel: {{ $hotel?->phone ?? '' }} | Email: {{ $hotel?->email ?? '' }}</p>
        </div>

        <h2 class="text-xl font-bold text-center mb-6 dark:text-gray-200">CONFIRMATION LETTER</h2>

        {{-- Date --}}
        <div class="text-right mb-4 text-sm dark:text-gray-300">
            Date: {{ now()->format('d F Y') }}
        </div>

        {{-- Guest Info --}}
        <div class="mb-6">
            <p class="dark:text-gray-300"><strong>Dear {{ $reservation->guest?->full_name }},</strong></p>
            <p class="mt-2 text-sm dark:text-gray-400">We are pleased to confirm your reservation as follows:</p>
        </div>

        {{-- Reservation Details --}}
        <table class="w-full text-sm mb-6">
            <tbody>
                <tr>
                    <td class="py-1 font-medium w-1/3 dark:text-gray-300">Confirmation No.</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->reservation_no }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Guest Name</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->guest?->full_name }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Arrival Date</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->arrival_date?->format('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Departure Date</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->departure_date?->format('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Duration</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->nights }} night(s)</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Room Category</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->roomCategory?->name }}</td>
                </tr>
                @if($reservation->room)
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Room Number</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->room->room_number }}</td>
                </tr>
                @endif
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Room Rate</td>
                    <td class="py-1 dark:text-gray-400">: IDR {{ number_format((float) $reservation->room_rate, 0, ',', '.') }} per night</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Arrangement</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->arrangement?->description ?? 'Room Only' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">No. of Guests</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->adults }} adult(s){{ $reservation->children > 0 ? ", {$reservation->children} child(ren)" : '' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Status</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->status->label() }}</td>
                </tr>
                @if($reservation->deposit_amount > 0)
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Deposit Required</td>
                    <td class="py-1 dark:text-gray-400">: IDR {{ number_format((float) $reservation->deposit_amount, 0, ',', '.') }}</td>
                </tr>
                @if($reservation->deposit_limit_date)
                <tr>
                    <td class="py-1 font-medium dark:text-gray-300">Deposit Due Date</td>
                    <td class="py-1 dark:text-gray-400">: {{ $reservation->deposit_limit_date->format('d F Y') }}</td>
                </tr>
                @endif
                @endif
            </tbody>
        </table>

        {{-- Terms --}}
        <div class="text-sm mb-6 dark:text-gray-400">
            <p class="font-medium dark:text-gray-300">Terms & Conditions:</p>
            <ul class="list-disc ml-5 mt-1 space-y-1">
                <li>Check-in time: 14:00 | Check-out time: 12:00</li>
                <li>All rates are subject to 21% government tax and service charge</li>
                @if($reservation->status->value === 'six_pm')
                <li>This reservation will be held until 6:00 PM on the arrival date</li>
                @endif
                @if($reservation->deposit_amount > 0)
                <li>Please ensure deposit is received by the due date to guarantee your reservation</li>
                @endif
            </ul>
        </div>

        {{-- Footer --}}
        <div class="text-sm dark:text-gray-400">
            <p>We look forward to welcoming you.</p>
            <p class="mt-4">Warm regards,</p>
            <p class="mt-8 font-medium dark:text-gray-300">Reservation Department</p>
            <p>{{ $hotel?->name ?? 'PAR Hotel' }}</p>
        </div>
    </div>

    {{-- Print Button --}}
    <div class="text-center mt-4 print:hidden">
        <button onclick="window.print()"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            🖨 Print Confirmation Letter
        </button>
    </div>
</x-filament-panels::page>
