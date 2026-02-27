<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\GuestResource\Pages;

use App\Enums\GuestType;
use App\Filament\Fo\Resources\GuestResource;
use App\Models\Guest;
use App\Models\Reservation;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewGuest extends ViewRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // Merge Guest Card File action
            Actions\Action::make('mergeGuest')
                ->label('Merge GCF')
                ->icon(\Filament\Support\Icons\Heroicon::ArrowsRightLeft)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Merge Guest Card File')
                ->modalDescription('Select a guest to merge THIS guest INTO. All history and data will be transferred to the target guest. This guest will be deleted.')
                ->form([
                    \Filament\Forms\Components\Select::make('target_guest_id')
                        ->label('Merge Into (Target Guest)')
                        ->options(fn () => Guest::where('id', '!=', $this->record->id)
                            ->where('type', $this->record->type)
                            ->limit(100)
                            ->get()
                            ->mapWithKeys(fn (Guest $g) => [$g->id => "{$g->guest_no} — {$g->fullName}"]))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $targetId = $data['target_guest_id'];
                    $sourceId = $this->record->id;

                    // Transfer reservations
                    Reservation::where('guest_id', $sourceId)->update(['guest_id' => $targetId]);

                    // Transfer contacts
                    \App\Models\GuestContact::where('guest_id', $sourceId)->update(['guest_id' => $targetId]);

                    // Transfer memberships
                    \App\Models\GuestMembership::where('guest_id', $sourceId)->update(['guest_id' => $targetId]);

                    // Transfer segments (avoid duplicates)
                    $existingSegments = \Illuminate\Support\Facades\DB::table('guest_segment')
                        ->where('guest_id', $targetId)
                        ->pluck('segment_id')
                        ->toArray();

                    \Illuminate\Support\Facades\DB::table('guest_segment')
                        ->where('guest_id', $sourceId)
                        ->whereNotIn('segment_id', $existingSegments)
                        ->update(['guest_id' => $targetId]);

                    \Illuminate\Support\Facades\DB::table('guest_segment')
                        ->where('guest_id', $sourceId)
                        ->delete();

                    // Transfer invoices
                    \App\Models\Invoice::where('guest_id', $sourceId)->update(['guest_id' => $targetId]);

                    // Delete source guest
                    $this->record->delete();

                    \Filament\Notifications\Notification::make()
                        ->title('Guest merged successfully')
                        ->body("Guest #{$this->record->guest_no} has been merged into the target guest.")
                        ->success()
                        ->send();

                    $this->redirect(GuestResource::getUrl('view', ['record' => $targetId]));
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Guest Statistics')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('statistics.turnover')
                                    ->label('Total Turnover')
                                    ->state(function (Guest $record): string {
                                        $total = $record->invoices()
                                            ->selectRaw('COALESCE(SUM(total_amount), 0) as total')
                                            ->value('total');
                                        return 'IDR ' . number_format((float) $total, 0, ',', '.');
                                    }),
                                TextEntry::make('statistics.room_nights')
                                    ->label('Room Nights')
                                    ->state(function (Guest $record): int {
                                        return (int) $record->reservations()
                                            ->whereIn('status', ['checked_in', 'checked_out'])
                                            ->sum('nights');
                                    }),
                                TextEntry::make('statistics.stay_count')
                                    ->label('Stay Count')
                                    ->state(function (Guest $record): int {
                                        return $record->reservations()
                                            ->whereIn('status', ['checked_in', 'checked_out'])
                                            ->count();
                                    }),
                                TextEntry::make('statistics.no_shows')
                                    ->label('No-Shows')
                                    ->state(function (Guest $record): int {
                                        return $record->reservations()
                                            ->where('status', 'no_show')
                                            ->count();
                                    }),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('statistics.first_reservation')
                                    ->label('First Reservation')
                                    ->state(function (Guest $record): string {
                                        $first = $record->reservations()->oldest('arrival_date')->value('arrival_date');
                                        return $first ? \Carbon\Carbon::parse($first)->format('d M Y') : '-';
                                    }),
                                TextEntry::make('statistics.last_reservation')
                                    ->label('Last Reservation')
                                    ->state(function (Guest $record): string {
                                        $last = $record->reservations()->latest('arrival_date')->value('arrival_date');
                                        return $last ? \Carbon\Carbon::parse($last)->format('d M Y') : '-';
                                    }),
                                TextEntry::make('statistics.cancellations')
                                    ->label('Cancellations')
                                    ->state(function (Guest $record): int {
                                        return $record->reservations()
                                            ->where('status', 'cancelled')
                                            ->count();
                                    }),
                            ]),
                    ]),

                Section::make('Stay History')
                    ->schema([
                        RepeatableEntry::make('reservations')
                            ->schema([
                                TextEntry::make('reservation_no')->label('Res No'),
                                TextEntry::make('arrival_date')->label('Arrival')->date(),
                                TextEntry::make('departure_date')->label('Depart')->date(),
                                TextEntry::make('room.room_number')->label('Room'),
                                TextEntry::make('roomCategory.name')->label('Category'),
                                TextEntry::make('arrangement.code')->label('Argt'),
                                TextEntry::make('room_rate')->label('Rate')->money('IDR'),
                                TextEntry::make('status')->label('Status')->badge(),
                            ])
                            ->columns(8)
                            ->defaultItems(0),
                    ])
                    ->collapsible(),
            ]);
    }
}
