<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\SystemDate;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class DepartureList extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Departure List';

    protected static ?string $navigationLabel = 'Departures';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowUpOnSquare;

    protected static string | UnitEnum | null $navigationGroup = 'Reservation';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.fo.pages.departure-list';

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('selectedDate')
                ->label('Departure Date')
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
                    ->with(['guest', 'roomCategory', 'room'])
                    ->where('departure_date', $this->selectedDate ?? SystemDate::today())
                    ->where('status', ReservationStatus::CheckedIn)
            )
            ->columns([
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation_no')
                    ->label('Res. No')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('guest', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")))
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat.'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d M'),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('room_rate')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('room.room_number')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    public static function getNavigationBadge(): ?string
    {
        $today = SystemDate::today();
        $count = Reservation::where('departure_date', $today)
            ->where('status', ReservationStatus::CheckedIn)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
