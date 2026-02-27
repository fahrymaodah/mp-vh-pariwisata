<x-filament-panels::page>
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-900 p-8 print:p-4" id="printArea">
        {{-- Header --}}
        <div class="text-center border-b-2 pb-4 mb-4">
            <h1 class="text-2xl font-bold dark:text-gray-100">{{ $hotel?->name ?? 'PAR Hotel' }}</h1>
            <p class="text-sm text-gray-500">{{ $hotel?->address ?? '' }}</p>
            <h2 class="text-lg font-bold mt-2 dark:text-gray-200">REGISTRATION FORM</h2>
        </div>

        {{-- Pre-filled Info --}}
        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
            <div>
                <div class="border-b dark:border-gray-700 py-1">
                    <span class="font-medium dark:text-gray-300">Reservation No:</span>
                    <span class="dark:text-gray-400">{{ $reservation->reservation_no }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-1">
                    <span class="font-medium dark:text-gray-300">Room No:</span>
                    <span class="dark:text-gray-400">{{ $reservation->room?->room_number ?? '________' }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-1">
                    <span class="font-medium dark:text-gray-300">Room Category:</span>
                    <span class="dark:text-gray-400">{{ $reservation->roomCategory?->name }}</span>
                </div>
            </div>
            <div>
                <div class="border-b dark:border-gray-700 py-1">
                    <span class="font-medium dark:text-gray-300">Arrival:</span>
                    <span class="dark:text-gray-400">{{ $reservation->arrival_date?->format('d/m/Y') }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-1">
                    <span class="font-medium dark:text-gray-300">Departure:</span>
                    <span class="dark:text-gray-400">{{ $reservation->departure_date?->format('d/m/Y') }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-1">
                    <span class="font-medium dark:text-gray-300">Rate:</span>
                    <span class="dark:text-gray-400">IDR {{ number_format((float) $reservation->room_rate, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Guest Details Section --}}
        <div class="text-sm space-y-3 mb-6">
            <h3 class="font-bold border-b dark:border-gray-700 pb-1 dark:text-gray-200">GUEST DETAILS</h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">Full Name:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->full_name }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">Nationality:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->nationality ?? '________________' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">ID/Passport No:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->id_number ?? '________________' }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">Date of Birth:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->date_of_birth?->format('d/m/Y') ?? '________________' }}</span>
                </div>
            </div>

            <div class="border-b dark:border-gray-700 py-2">
                <span class="font-medium dark:text-gray-300">Address:</span>
                <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->address ?? '________________________________________________________________' }}</span>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">City:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->city ?? '________' }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">Phone:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->phone ?? '________' }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">Email:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->guest?->email ?? '________' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">No. of Adults:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->adults }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">No. of Children:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->children }}</span>
                </div>
            </div>

            @if($reservation->flight_no)
            <div class="grid grid-cols-3 gap-4">
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">Flight No:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->flight_no }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">ETA:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->eta ?? '-' }}</span>
                </div>
                <div class="border-b dark:border-gray-700 py-2">
                    <span class="font-medium dark:text-gray-300">ETD:</span>
                    <span class="ml-1 dark:text-gray-400">{{ $reservation->etd ?? '-' }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Payment Method Section --}}
        <div class="text-sm mb-6">
            <h3 class="font-bold border-b dark:border-gray-700 pb-1 dark:text-gray-200">PAYMENT METHOD</h3>
            <div class="grid grid-cols-4 gap-2 mt-2">
                <label class="flex items-center gap-1 dark:text-gray-400">
                    <span class="w-4 h-4 border border-gray-400 inline-block"></span> Cash
                </label>
                <label class="flex items-center gap-1 dark:text-gray-400">
                    <span class="w-4 h-4 border border-gray-400 inline-block"></span> Credit Card
                </label>
                <label class="flex items-center gap-1 dark:text-gray-400">
                    <span class="w-4 h-4 border border-gray-400 inline-block"></span> Bank Transfer
                </label>
                <label class="flex items-center gap-1 dark:text-gray-400">
                    <span class="w-4 h-4 border border-gray-400 inline-block"></span> City Ledger
                </label>
            </div>
            <div class="border-b dark:border-gray-700 py-2 mt-2">
                <span class="font-medium dark:text-gray-300">CC No:</span>
                <span class="dark:text-gray-400">____________________________ Exp: ______/______</span>
            </div>
        </div>

        {{-- Signature Section --}}
        <div class="text-sm mb-4">
            <h3 class="font-bold border-b dark:border-gray-700 pb-1 dark:text-gray-200">AGREEMENT</h3>
            <p class="text-xs mt-2 dark:text-gray-400">I acknowledge that I am personally liable for the payment of my account. In the event of a credit arrangement, I acknowledge my liability if the third party fails to pay.</p>

            <div class="grid grid-cols-2 gap-8 mt-8">
                <div class="text-center">
                    <div class="border-b dark:border-gray-700 mb-1 h-12"></div>
                    <p class="dark:text-gray-400">Guest Signature</p>
                    <p class="text-xs dark:text-gray-500">Date: ____/____/________</p>
                </div>
                <div class="text-center">
                    <div class="border-b dark:border-gray-700 mb-1 h-12"></div>
                    <p class="dark:text-gray-400">Receptionist</p>
                    <p class="text-xs dark:text-gray-500">Date: {{ now()->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Print Button --}}
    <div class="text-center mt-4 print:hidden">
        <button onclick="window.print()"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            🖨 Print Registration Form
        </button>
    </div>
</x-filament-panels::page>
