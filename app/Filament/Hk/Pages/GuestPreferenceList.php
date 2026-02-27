<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Enums\ReservationStatus;
use App\Models\GuestPreference;
use App\Models\Reservation;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class GuestPreferenceList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.hk.pages.guest-preference-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Heart;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Guest Preferences';

    protected static ?string $title = 'Guest Preference List';

    protected static ?string $slug = 'guest-preferences';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                GuestPreference::query()
                    ->with(['reservation.guest', 'room', 'user'])
                    ->whereHas('reservation', fn ($q) => $q->where('status', ReservationStatus::CheckedIn))
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('reservation.guest.full_name')
                    ->label('Guest Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->sortable(),
                TextColumn::make('preference_type')
                    ->label('Type')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('Recorded By'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                TableAction::make('addPreference')
                    ->label('New Preference')
                    ->icon(Heroicon::Plus)
                    ->color('primary')
                    ->form([
                        Select::make('reservation_id')
                            ->label('In-House Guest')
                            ->options(
                                Reservation::where('status', ReservationStatus::CheckedIn)
                                    ->with('guest')
                                    ->get()
                                    ->mapWithKeys(fn ($r) => [$r->id => "Rm {$r->room_id} — {$r->guest?->full_name}"])
                                    ->toArray()
                            )
                            ->searchable()
                            ->required(),
                        TextInput::make('preference_type')
                            ->label('Preference Type')
                            ->placeholder('e.g., Pillow, Mini Bar, Extra Towel')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data): void {
                        $reservation = Reservation::with('guest')->find($data['reservation_id']);

                        GuestPreference::create([
                            'reservation_id' => $reservation->id,
                            'guest_id' => $reservation->guest_id,
                            'room_id' => $reservation->room_id,
                            'preference_type' => $data['preference_type'],
                            'description' => $data['description'],
                            'user_id' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title("Preference recorded for {$reservation->guest?->full_name}")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Guest Preferences')
            ->emptyStateDescription('Record guest preferences for in-house guests using the New Preference button.');
    }
}
