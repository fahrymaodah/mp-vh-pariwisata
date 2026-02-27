<x-filament-panels::page>
    {{ $this->filtersForm }}

    <div class="mt-4">
        {{ $this->table }}
    </div>

    @php $notes = $this->getContactNotes(); @endphp

    @if (!empty($notes))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            {{-- Recent Activities --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Recent Activities</h3>
                @forelse ($notes['activities'] as $activity)
                    <div class="border-b border-gray-100 dark:border-gray-800 py-2 last:border-b-0">
                        <div class="text-xs text-gray-500">{{ $activity->created_at?->format('d M Y') }}</div>
                        <div class="text-sm text-gray-800 dark:text-gray-200">{{ Str::limit($activity->description, 80) }}</div>
                        <div class="flex gap-2 mt-1">
                            <span class="text-xs {{ $activity->is_finished ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $activity->is_finished ? 'Finished' : 'In Progress' }}
                            </span>
                            @if ($activity->target_amount > 0)
                                <span class="text-xs text-gray-500">Target: IDR {{ number_format($activity->target_amount) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No activities found.</p>
                @endforelse
            </div>

            {{-- Recent Opportunities --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Recent Opportunities</h3>
                @forelse ($notes['opportunities'] as $opp)
                    <div class="border-b border-gray-100 dark:border-gray-800 py-2 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $opp->prospect_name }}</div>
                                @if ($opp->contact_name)
                                    <div class="text-xs text-gray-500">Contact: {{ $opp->contact_name }}</div>
                                @endif
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $opp->status === 'open' ? 'bg-blue-100 text-blue-700' : ($opp->status === 'close' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($opp->status) }}
                            </span>
                        </div>
                        <div class="flex gap-3 mt-1 text-xs text-gray-500">
                            @if ($opp->target_amount > 0)
                                <span>IDR {{ number_format($opp->target_amount) }}</span>
                            @endif
                            @if ($opp->probability)
                                <span>{{ $opp->probability }}%</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No opportunities found.</p>
                @endforelse
            </div>
        </div>
    @endif
</x-filament-panels::page>
