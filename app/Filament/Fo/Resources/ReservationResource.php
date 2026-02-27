<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources;

use App\Enums\GuestType;
use App\Enums\ReservationStatus;
use App\Filament\Fo\Resources\ReservationResource\Pages;
use App\Filament\Fo\Resources\ReservationResource\RelationManagers;
use App\Models\Arrangement;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Segment;
use App\Models\SystemDate;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CalendarDays;

    protected static string | UnitEnum | null $navigationGroup = 'Reservation';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Reservations';

    protected static ?string $recordTitleAttribute = 'reservation_no';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Reservation')
                    ->tabs([
                        Tab::make('Reservation Details')
                            ->icon(Heroicon::CalendarDays)
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('reservation_no')
                                            ->label('Res. No')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder('Auto-generated'),
                                        Select::make('guest_id')
                                            ->label('Guest')
                                            ->relationship('guest', 'name')
                                            ->getOptionLabelFromRecordUsing(fn (Guest $record) => "{$record->guest_no} — {$record->full_name}")
                                            ->searchable(['name', 'first_name', 'guest_no'])
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $guest = Guest::find($state);
                                                    if ($guest) {
                                                        $set('segment_id', $guest->main_segment_id);
                                                    }
                                                }
                                            })
                                            ->createOptionForm([
                                                Grid::make(2)->schema([
                                                    Select::make('type')
                                                        ->options(GuestType::class)
                                                        ->default(GuestType::Individual)
                                                        ->required(),
                                                    TextInput::make('name')
                                                        ->required()
                                                        ->maxLength(255),
                                                    TextInput::make('first_name')
                                                        ->maxLength(255),
                                                    TextInput::make('phone')
                                                        ->tel()
                                                        ->maxLength(50),
                                                ]),
                                            ]),
                                        Select::make('status')
                                            ->options(ReservationStatus::class)
                                            ->default(ReservationStatus::Confirmed)
                                            ->required(),
                                    ]),

                                Section::make('Stay Details')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                DatePicker::make('arrival_date')
                                                    ->label('Arrival')
                                                    ->required()
                                                    ->default(fn () => SystemDate::today())
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => static::calculateNights($state, $get('departure_date'), $set)),
                                                TextInput::make('nights')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                        if ($state && $get('arrival_date')) {
                                                            $departure = \Carbon\Carbon::parse($get('arrival_date'))->addDays((int) $state);
                                                            $set('departure_date', $departure->format('Y-m-d'));
                                                        }
                                                    }),
                                                DatePicker::make('departure_date')
                                                    ->label('Departure')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => static::calculateNights($get('arrival_date'), $state, $set)),
                                                TextInput::make('room_qty')
                                                    ->label('Qty')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1),
                                            ]),
                                        Grid::make(4)
                                            ->schema([
                                                TextInput::make('adults')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->required(),
                                                TextInput::make('children')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0),
                                                TextInput::make('children_ages')
                                                    ->label('Children Ages')
                                                    ->placeholder('e.g. 5, 8, 12')
                                                    ->maxLength(100),
                                                Toggle::make('is_complimentary')
                                                    ->label('Complimentary')
                                                    ->inline(false),
                                            ]),
                                    ]),

                                Section::make('Room & Rate')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('room_category_id')
                                                    ->label('Category')
                                                    ->relationship('roomCategory', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        if ($state) {
                                                            $category = RoomCategory::find($state);
                                                            if ($category && !$get('is_fix_rate')) {
                                                                $set('room_rate', $category->base_rate);
                                                            }
                                                            // Reset room selection on category change
                                                            $set('room_id', null);
                                                        }
                                                    }),
                                                Select::make('room_id')
                                                    ->label('Room No')
                                                    ->options(function (callable $get): array {
                                                        $categoryId = $get('room_category_id');
                                                        if (!$categoryId) {
                                                            return [];
                                                        }

                                                        return Room::where('room_category_id', $categoryId)
                                                            ->where('is_active', true)
                                                            ->pluck('room_number', 'id')
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->placeholder('Select room'),
                                                Select::make('arrangement_id')
                                                    ->label('Arrangement')
                                                    ->relationship('arrangement', 'description', fn (Builder $query) => $query->where('is_active', true))
                                                    ->getOptionLabelFromRecordUsing(fn (Arrangement $record) => "{$record->code} — {$record->description}")
                                                    ->searchable()
                                                    ->preload(),
                                                TextInput::make('room_rate')
                                                    ->label('Room Rate')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->required(),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('currency_code')
                                                    ->default('IDR')
                                                    ->maxLength(10),
                                                Toggle::make('is_fix_rate')
                                                    ->label('Fix Rate')
                                                    ->helperText('Contract rate from Company/TA')
                                                    ->inline(false),
                                                TextInput::make('bill_instruction')
                                                    ->label('Bill Instruction')
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Main Reservation')
                            ->icon(Heroicon::DocumentText)
                            ->schema([
                                Section::make('Reservation Info')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('segment_id')
                                                    ->label('Segment')
                                                    ->relationship('segment', 'description', fn (Builder $query) => $query->where('is_active', true))
                                                    ->getOptionLabelFromRecordUsing(fn (Segment $record) => "{$record->code} — {$record->description}")
                                                    ->searchable()
                                                    ->preload(),
                                                TextInput::make('reserved_by')
                                                    ->maxLength(255),
                                                Select::make('source')
                                                    ->options([
                                                        'phone' => 'Phone',
                                                        'email' => 'Email',
                                                        'fax' => 'Fax',
                                                        'walk_in' => 'Walk-In',
                                                        'website' => 'Website',
                                                        'ota' => 'OTA',
                                                        'letter' => 'Letter',
                                                    ]),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('group_name')
                                                    ->label('Group Name')
                                                    ->maxLength(255),
                                                TextInput::make('ta_commission')
                                                    ->label('T/A Commission')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->default(0),
                                                TextInput::make('letter_no')
                                                    ->label('Letter No')
                                                    ->maxLength(50),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('purpose')
                                                    ->maxLength(255),
                                                Textarea::make('comments')
                                                    ->rows(2),
                                            ]),
                                    ]),

                                Section::make('Flight & Transport')
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('flight_no')
                                                    ->label('Flight No')
                                                    ->maxLength(20),
                                                TimePicker::make('eta')
                                                    ->label('ETA')
                                                    ->seconds(false),
                                                TimePicker::make('etd')
                                                    ->label('ETD')
                                                    ->seconds(false),
                                                Toggle::make('is_pickup')
                                                    ->label('Pickup')
                                                    ->inline(false),
                                                Toggle::make('is_dropoff')
                                                    ->label('Drop-off')
                                                    ->inline(false),
                                            ]),
                                    ]),

                                Section::make('Deposit')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                DatePicker::make('deposit_limit_date')
                                                    ->label('Limit Date'),
                                                TextInput::make('deposit_amount')
                                                    ->label('Amount')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->default(0),
                                                TextInput::make('deposit_paid')
                                                    ->label('Paid')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->default(0)
                                                    ->disabled()
                                                    ->dehydrated(),
                                                TextInput::make('deposit_balance')
                                                    ->label('Balance')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->default(0)
                                                    ->disabled()
                                                    ->dehydrated(),
                                            ]),
                                    ]),

                                Section::make('Master Bill')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('is_master_bill')
                                                    ->label('Master Bill Active')
                                                    ->inline(false)
                                                    ->live(),
                                                TextInput::make('master_bill_receiver')
                                                    ->label('Bill Receiver')
                                                    ->maxLength(255)
                                                    ->visible(fn (callable $get) => (bool) $get('is_master_bill')),
                                            ]),
                                    ]),

                                Section::make('Special Flags')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('is_incognito')
                                                    ->label('Incognito')
                                                    ->helperText('Guest will be hidden from search')
                                                    ->inline(false),
                                                Toggle::make('is_day_use')
                                                    ->label('Day Use')
                                                    ->inline(false),
                                                Toggle::make('is_room_sharer')
                                                    ->label('Room Sharer')
                                                    ->inline(false)
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->helperText('Managed via Room Sharer action'),
                                            ]),
                                    ]),

                                Hidden::make('created_by')
                                    ->default(fn () => auth()->id()),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation_no')
                    ->label('Res. No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('guest.name')
                    ->label('Guest Name')
                    ->searchable(['name', 'first_name'])
                    ->sortable()
                    ->description(fn (Reservation $record) => $record->guest?->guest_no),
                Tables\Columns\TextColumn::make('guest.type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Reservation $record) => $record->arrival_date?->isToday() ? 'success' : null),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_master_bill')
                    ->label('MB')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
            ])
            ->defaultSort('arrival_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ReservationStatus::class),
                Tables\Filters\SelectFilter::make('room_category_id')
                    ->label('Category')
                    ->relationship('roomCategory', 'name'),
                Tables\Filters\SelectFilter::make('segment_id')
                    ->label('Segment')
                    ->relationship('segment', 'description'),
                Tables\Filters\Filter::make('arrival_today')
                    ->label('Arriving Today')
                    ->query(fn (Builder $query) => $query->where('arrival_date', SystemDate::today())),
                Tables\Filters\Filter::make('departure_today')
                    ->label('Departing Today')
                    ->query(fn (Builder $query) => $query->where('departure_date', SystemDate::today())),
                Tables\Filters\TernaryFilter::make('is_master_bill')
                    ->label('Master Bill'),
                Tables\Filters\TernaryFilter::make('is_incognito')
                    ->label('Incognito'),
                Tables\Filters\Filter::make('arrival_date_range')
                    ->form([
                        DatePicker::make('arrival_from')->label('Arrival From'),
                        DatePicker::make('arrival_until')->label('Arrival Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['arrival_from'], fn (Builder $q, $date) => $q->where('arrival_date', '>=', $date))
                            ->when($data['arrival_until'], fn (Builder $q, $date) => $q->where('arrival_date', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Reservation')
                    ->modalDescription(fn (Reservation $record) => "Cancel reservation {$record->reservation_no} for {$record->guest?->full_name}?")
                    ->form([
                        Textarea::make('cancel_reason')
                            ->label('Cancellation Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Reservation $record, array $data) {
                        $record->update([
                            'status' => ReservationStatus::Cancelled,
                            'cancelled_at' => now(),
                            'cancel_reason' => $data['cancel_reason'],
                            'cancelled_by' => auth()->id(),
                        ]);

                        $record->logs()->create([
                            'user_id' => auth()->id(),
                            'action' => 'cancelled',
                            'field_changed' => 'status',
                            'old_value' => $record->getOriginal('status')?->value ?? $record->getOriginal('status'),
                            'new_value' => ReservationStatus::Cancelled->value,
                        ]);
                    })
                    ->visible(fn (Reservation $record) => $record->status->isActive() && $record->status !== ReservationStatus::CheckedIn),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FixCostArticlesRelationManager::class,
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $today = SystemDate::today();
        $count = Reservation::where('arrival_date', $today)
            ->whereIn('status', [
                ReservationStatus::Confirmed,
                ReservationStatus::Guaranteed,
                ReservationStatus::SixPm,
                ReservationStatus::OralConfirmed,
                ReservationStatus::Tentative,
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    /**
     * Calculate nights from arrival and departure dates.
     */
    protected static function calculateNights(?string $arrival, ?string $departure, callable $set): void
    {
        if ($arrival && $departure) {
            $nights = \Carbon\Carbon::parse($arrival)->diffInDays(\Carbon\Carbon::parse($departure));
            if ($nights > 0) {
                $set('nights', $nights);
            }
        }
    }
}
