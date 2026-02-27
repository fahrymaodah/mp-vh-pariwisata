<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\GuestMessage;
use App\Models\Reservation;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GuestMessagePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.guest-message';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Envelope;

    protected static string | UnitEnum | null $navigationGroup = 'In House';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Guest Message';

    protected static ?string $title = 'Guest Message';

    protected static ?string $slug = 'guest-message';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                GuestMessage::query()
                    ->with(['reservation', 'guest', 'room', 'createdBy'])
                    ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('guest.full_name')
                    ->label('Guest')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->sortable(),
                TextColumn::make('message')
                    ->label('Message')
                    ->limit(60)
                    ->wrap(),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::ExclamationCircle)
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('read_at')
                    ->label('Read At')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('createdBy.name')
                    ->label('Created By'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('Read Status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread')
                    ->placeholder('All'),
            ])
            ->actions([
                Action::make('mark_read')
                    ->label('Mark Read')
                    ->icon(Heroicon::Check)
                    ->color('success')
                    ->visible(fn (GuestMessage $record) => ! $record->is_read)
                    ->action(function (GuestMessage $record) {
                        $record->update([
                            'is_read' => true,
                            'read_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Message Marked as Read')
                            ->success()
                            ->send();
                    }),
                Action::make('edit_message')
                    ->label('Modify')
                    ->icon(Heroicon::PencilSquare)
                    ->color('warning')
                    ->form([
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(3)
                            ->default(fn (GuestMessage $record) => $record->message),
                    ])
                    ->action(function (GuestMessage $record, array $data) {
                        $record->update(['message' => $data['message']]);

                        Notification::make()
                            ->title('Message Updated')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('Delete'),
            ])
            ->headerActions([
                Action::make('new_message')
                    ->label('New Message')
                    ->icon(Heroicon::Plus)
                    ->color('primary')
                    ->form([
                        \Filament\Forms\Components\Select::make('reservation_id')
                            ->label('Guest (Room)')
                            ->options(function () {
                                return Reservation::where('status', ReservationStatus::CheckedIn)
                                    ->with(['guest', 'room'])
                                    ->get()
                                    ->mapWithKeys(fn (Reservation $r) => [
                                        $r->id => "Room {$r->room?->room_number} — {$r->guest?->full_name}",
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (array $data) {
                        $reservation = Reservation::find($data['reservation_id']);

                        GuestMessage::create([
                            'reservation_id' => $reservation->id,
                            'guest_id' => $reservation->guest_id,
                            'room_id' => $reservation->room_id,
                            'message' => $data['message'],
                            'is_read' => false,
                            'created_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Message Created')
                            ->body("Message sent for {$reservation->guest?->full_name}")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No guest messages')
            ->emptyStateDescription('No messages for in-house guests.')
            ->emptyStateIcon(Heroicon::Envelope);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = GuestMessage::where('is_read', false)
            ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
