<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SystemDate;
use App\Services\CheckInService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CheckInPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.check-in';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowRightEndOnRectangle;

    protected static string | UnitEnum | null $navigationGroup = 'Check-In';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Individual Check-In';

    protected static ?string $title = 'Individual Check-In';

    protected static ?string $slug = 'check-in';

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Grid::make(2)->schema([
                DatePicker::make('selectedDate')
                    ->label('Arrival Date')
                    ->default(fn () => SystemDate::today())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                Select::make('statusFilter')
                    ->label('Status Filter')
                    ->options([
                        'all' => 'All Active',
                        'confirmed' => 'Confirmed',
                        'guaranteed' => 'Guaranteed',
                        'six_pm' => '6 PM Release',
                        'tentative' => 'Tentative',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = Reservation::query()
                    ->with(['guest', 'roomCategory', 'room'])
                    ->where('arrival_date', $this->selectedDate ?? SystemDate::today())
                    ->whereIn('status', [
                        ReservationStatus::Guaranteed,
                        ReservationStatus::SixPm,
                        ReservationStatus::OralConfirmed,
                        ReservationStatus::Tentative,
                        ReservationStatus::WaitingList,
                        ReservationStatus::Confirmed,
                    ]);

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('reservation_no')
                    ->label('Res. No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('guest.name')
                    ->label('Guest')
                    ->searchable(['name', 'first_name'])
                    ->sortable()
                    ->description(fn (Reservation $record) => $record->guest?->first_name)
                    ->weight('bold')
                    ->color(function (Reservation $record) {
                        if ($record->guest?->is_vip) {
                            return 'danger';
                        }
                        if ($record->is_incognito) {
                            return 'gray';
                        }

                        return null;
                    }),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color(fn (Reservation $record) => $record->room_id ? 'success' : 'danger')
                    ->default('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('adults')
                    ->label('Pax')
                    ->alignCenter()
                    ->formatStateUsing(fn (Reservation $record) => "{$record->adults}+{$record->children}"),
            ])
            ->actions([
                // Check-In Action
                Actions\Action::make('checkIn')
                    ->label('Check-In')
                    ->icon(Heroicon::ArrowRightEndOnRectangle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Reservation $record) => "Check-In: {$record->guest?->full_name}")
                    ->modalDescription(fn (Reservation $record) => 'Room: ' . ($record->room?->room_number ?? 'Not assigned') . ' | Category: ' . ($record->roomCategory?->code ?? '-') . ' | Rate: IDR ' . number_format((float) $record->room_rate, 0, ',', '.'))
                    ->form(function (Reservation $record): array {
                        $fields = [];

                        // If no room assigned, show room selection
                        if (! $record->room_id) {
                            $fields[] = Select::make('room_id')
                                ->label('Assign Room')
                                ->options(function () use ($record) {
                                    return Room::where('is_active', true)
                                        ->where('room_category_id', $record->room_category_id)
                                        ->whereIn('status', [
                                            RoomStatus::VacantClean,
                                            RoomStatus::VacantCleanUnchecked,
                                            RoomStatus::VacantDirty,
                                        ])
                                        ->get()
                                        ->mapWithKeys(fn (Room $room) => [
                                            $room->id => "{$room->room_number} — {$room->status->label()} (Floor {$room->floor})",
                                        ])
                                        ->toArray();
                                })
                                ->required()
                                ->searchable()
                                ->helperText('Room must be assigned before check-in');
                        }

                        return $fields;
                    })
                    ->action(function (Reservation $record, array $data) {
                        try {
                            $roomId = $data['room_id'] ?? $record->room_id;

                            if (! $record->room_id && isset($data['room_id'])) {
                                $record->update(['room_id' => $data['room_id']]);
                            }

                            app(CheckInService::class)->checkIn($record, $roomId);

                            Notification::make()
                                ->title('Guest Checked In')
                                ->body("Room {$record->fresh()->room->room_number} — {$record->guest?->full_name}")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Check-In Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Reservation $record) => $record->status !== ReservationStatus::CheckedIn),

                // Modify reservation before check-in
                Actions\Action::make('assignRoom')
                    ->label('Assign Room')
                    ->icon(Heroicon::Key)
                    ->color('warning')
                    ->form(function (Reservation $record): array {
                        return [
                            Select::make('room_id')
                                ->label('Room')
                                ->options(function () use ($record) {
                                    return Room::where('is_active', true)
                                        ->where('room_category_id', $record->room_category_id)
                                        ->whereIn('status', [
                                            RoomStatus::VacantClean,
                                            RoomStatus::VacantCleanUnchecked,
                                            RoomStatus::VacantDirty,
                                        ])
                                        ->get()
                                        ->mapWithKeys(fn (Room $room) => [
                                            $room->id => "{$room->room_number} — {$room->status->label()} (Floor {$room->floor})",
                                        ])
                                        ->toArray();
                                })
                                ->required()
                                ->searchable()
                                ->default($record->room_id),
                        ];
                    })
                    ->action(function (Reservation $record, array $data) {
                        $record->update(['room_id' => $data['room_id']]);

                        $room = Room::find($data['room_id']);

                        Notification::make()
                            ->title('Room Assigned')
                            ->body("Room {$room->room_number} assigned to {$record->reservation_no}")
                            ->success()
                            ->send();
                    }),

                // View reservation
                Actions\Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->defaultSort('reservation_no')
            ->striped()
            ->emptyStateHeading('No arrivals for this date')
            ->emptyStateDescription('Select a different date or check the Arrival Reservation List.')
            ->emptyStateIcon(Heroicon::CalendarDays)
            ->poll('15s');
    }

    public static function getNavigationBadge(): ?string
    {
        $today = SystemDate::today();
        $count = Reservation::where('arrival_date', $today)
            ->whereIn('status', [
                ReservationStatus::Guaranteed,
                ReservationStatus::SixPm,
                ReservationStatus::OralConfirmed,
                ReservationStatus::Confirmed,
                ReservationStatus::Tentative,
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
