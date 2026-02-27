<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\GuestLocator;
use App\Models\GuestMessage;
use App\Models\Reservation;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ResidentGuestList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.resident-guest-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::HomeModern;

    protected static string | UnitEnum | null $navigationGroup = 'In House';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Resident Guest';

    protected static ?string $title = 'Resident Guest List';

    protected static ?string $slug = 'resident-guest-list';

    // Filter state
    public ?string $filterType = 'all';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                \Filament\Forms\Components\Select::make('filterType')
                    ->label('Display')
                    ->options([
                        'all' => 'All In-House Guests',
                        'inhouse' => 'Regular In-House Only',
                        'room_sharer' => 'Room Sharers Only',
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
                $type = $this->filterType ?? 'all';

                $query = Reservation::query()
                    ->where('status', ReservationStatus::CheckedIn)
                    ->with(['guest', 'room', 'roomCategory', 'arrangement', 'messages', 'locators']);

                if ($type === 'room_sharer') {
                    $query->where('is_room_sharer', true);
                } elseif ($type === 'inhouse') {
                    $query->where('is_room_sharer', false);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('reservation_no')
                    ->label('Res #')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                // Indicators column (M/L/G/I/MB/A)
                TextColumn::make('indicators')
                    ->label('Indicators')
                    ->getStateUsing(function (Reservation $record): string {
                        $indicators = [];

                        // M = Message waiting (unread messages)
                        if ($record->messages()->where('is_read', false)->exists()) {
                            $indicators[] = 'M';
                        }

                        // L = Guest Locator active
                        if ($record->locators()->where('is_active', true)->exists()) {
                            $indicators[] = 'L';
                        }

                        // G = Group/Company guest
                        if ($record->group_name || $record->parent_reservation_id) {
                            $indicators[] = 'G';
                        }

                        // I = Inactive room (incognito)
                        if ($record->is_incognito) {
                            $indicators[] = 'I';
                        }

                        // MB = Master Bill
                        if ($record->is_master_bill) {
                            $indicators[] = 'MB';
                        }

                        // A = Allotment (company guest via segment)
                        if ($record->guest?->master_company_id) {
                            $indicators[] = 'A';
                        }

                        return implode(' ', $indicators);
                    })
                    ->badge()
                    ->color('warning')
                    ->separator(' '),

                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->sortable()
                    ->weight('bold')
                    ->color(function (Reservation $record): string {
                        if ($record->guest?->is_vip) {
                            return 'danger';
                        }
                        if ($record->is_incognito) {
                            return 'gray';
                        }
                        if ($record->is_complimentary) {
                            return 'info';
                        }

                        return 'primary';
                    }),

                TextColumn::make('roomCategory.code')
                    ->label('Cat')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('res_status_display')
                    ->label('ResStatus')
                    ->getStateUsing(function (Reservation $record): string {
                        if ($record->is_room_sharer) {
                            return 'RmSharer';
                        }

                        return 'Inhouse';
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'RmSharer' ? 'warning' : 'success'),

                TextColumn::make('arrangement.code')
                    ->label('Argt'),

                TextColumn::make('pax')
                    ->label('Pax')
                    ->getStateUsing(fn (Reservation $r) => $r->adults . '/' . ($r->children ?? 0)),

                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nights')
                    ->alignCenter(),

                TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('guest.nationality')
                    ->label('Nat.')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('message')
                    ->label('Message')
                    ->icon(Heroicon::Envelope)
                    ->color(fn (Reservation $record) => $record->messages()->where('is_read', false)->exists() ? 'warning' : 'gray')
                    ->form([
                        TextInput::make('message')
                            ->label('Guest Message')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Reservation $record, array $data) {
                        GuestMessage::create([
                            'reservation_id' => $record->id,
                            'guest_id' => $record->guest_id,
                            'room_id' => $record->room_id,
                            'message' => $data['message'],
                            'is_read' => false,
                            'created_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Message Created')
                            ->body("Message sent for {$record->guest?->full_name}")
                            ->success()
                            ->send();
                    }),
                Action::make('locator')
                    ->label('Locator')
                    ->icon(Heroicon::MapPin)
                    ->color(fn (Reservation $record) => $record->locators()->where('is_active', true)->exists() ? 'info' : 'gray')
                    ->form([
                        TextInput::make('location')
                            ->label('Guest Location')
                            ->required()
                            ->placeholder('e.g., Restaurant, Pool, Lobby')
                            ->maxLength(255),
                        TextInput::make('remarks')
                            ->maxLength(500),
                    ])
                    ->action(function (Reservation $record, array $data) {
                        // Deactivate previous locators
                        $record->locators()->update(['is_active' => false]);

                        GuestLocator::create([
                            'reservation_id' => $record->id,
                            'guest_id' => $record->guest_id,
                            'room_id' => $record->room_id,
                            'location' => $data['location'],
                            'remarks' => $data['remarks'] ?? null,
                            'is_active' => true,
                            'created_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Locator Updated')
                            ->body("Guest located at: {$data['location']}")
                            ->success()
                            ->send();
                    }),
                Action::make('toggle_incognito')
                    ->label(fn (Reservation $record) => $record->is_incognito ? 'Remove Incognito' : 'Set Incognito')
                    ->icon(Heroicon::EyeSlash)
                    ->color(fn (Reservation $record) => $record->is_incognito ? 'danger' : 'gray')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $record->update(['is_incognito' => ! $record->is_incognito]);

                        $record->logs()->create([
                            'user_id' => auth()->id(),
                            'action' => $record->is_incognito ? 'set_incognito' : 'remove_incognito',
                            'field_changed' => 'is_incognito',
                            'old_value' => $record->is_incognito ? '0' : '1',
                            'new_value' => $record->is_incognito ? '1' : '0',
                        ]);

                        Notification::make()
                            ->title($record->is_incognito ? 'Guest Set Incognito' : 'Incognito Removed')
                            ->success()
                            ->send();
                    }),
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->defaultSort('room.room_number')
            ->emptyStateHeading('No in-house guests')
            ->emptyStateDescription('No guests are currently checked in.')
            ->emptyStateIcon(Heroicon::HomeModern);
    }

    /**
     * Get summary statistics for the page header.
     */
    public function getInHouseStats(): array
    {
        $base = Reservation::where('status', ReservationStatus::CheckedIn);

        return [
            'total' => (clone $base)->count(),
            'regular' => (clone $base)->where('is_room_sharer', false)->count(),
            'room_sharer' => (clone $base)->where('is_room_sharer', true)->count(),
            'vip' => (clone $base)->whereHas('guest', fn ($q) => $q->where('is_vip', true))->count(),
            'incognito' => (clone $base)->where('is_incognito', true)->count(),
            'with_messages' => (clone $base)->whereHas('messages', fn ($q) => $q->where('is_read', false))->count(),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Reservation::where('status', ReservationStatus::CheckedIn)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
