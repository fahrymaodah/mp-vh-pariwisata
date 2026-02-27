<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ArrivalReservationList extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Arrival Reservation List';

    protected static ?string $navigationLabel = 'ARL (Arrivals)';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowDownOnSquare;

    protected static string | UnitEnum | null $navigationGroup = 'Reservation';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.fo.pages.arrival-reservation-list';

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('selectedDate')
                ->label('Arrival Date')
                ->default(SystemDate::today())
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->with(['guest', 'roomCategory', 'room', 'arrangement', 'segment'])
                    ->where('arrival_date', $this->selectedDate ?? SystemDate::today())
                    ->whereNotIn('status', [
                        ReservationStatus::Cancelled,
                        ReservationStatus::NoShow,
                        ReservationStatus::CheckedOut,
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('reservation_no')
                    ->label('Res. No')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color(function (Reservation $record): string {
                        // ARL Color Coding (3.13)
                        if ($record->guest?->is_vip) {
                            return 'danger'; // VIP = Red
                        }
                        if ($record->is_incognito) {
                            return 'gray'; // Incognito = Gray
                        }
                        if ($record->is_room_sharer) {
                            return 'info'; // Room Sharer = Blue
                        }
                        if ($record->is_day_use) {
                            return 'warning'; // Day Use = Yellow/Orange
                        }
                        if ($record->nights >= 7) {
                            return 'purple'; // Long Stay (7+ nights) = Purple
                        }
                        return 'primary';
                    })
                    ->description(function (Reservation $record): ?string {
                        $tags = [];
                        if ($record->guest?->is_vip) {
                            $tags[] = '⭐ VIP';
                        }
                        if ($record->is_incognito) {
                            $tags[] = '🔒 Incognito';
                        }
                        if ($record->is_room_sharer) {
                            $tags[] = '👥 Room Sharer';
                        }
                        if ($record->is_day_use) {
                            $tags[] = '☀ Day Use';
                        }
                        if ($record->nights >= 7) {
                            $tags[] = "📅 Long Stay ({$record->nights}n)";
                        }
                        if ($record->is_master_bill) {
                            $tags[] = '💰 Master Bill';
                        }
                        if ($record->is_complimentary) {
                            $tags[] = '🎁 Complimentary';
                        }
                        return count($tags) > 0 ? implode(' | ', $tags) : null;
                    }),

                Tables\Columns\TextColumn::make('guest.type')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat.'),

                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('warning')
                    ->default('—'),

                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M'),

                Tables\Columns\TextColumn::make('arrangement.code')
                    ->label('Arr.')
                    ->default('-'),

                Tables\Columns\TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('segment.code')
                    ->label('Seg.')
                    ->default('-'),

                Tables\Columns\TextColumn::make('reserved_by')
                    ->label('Reserved By')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('comments')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('reservation_no')
            ->striped()
            ->paginated([25, 50, 100])
            ->poll('30s');
    }

    public static function getNavigationBadge(): ?string
    {
        $today = SystemDate::today();

        $count = Reservation::where('arrival_date', $today)
            ->whereNotIn('status', [
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
                ReservationStatus::CheckedOut,
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
