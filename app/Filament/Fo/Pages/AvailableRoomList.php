<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AvailableRoomList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.available-room-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::BuildingOffice;

    protected static string | UnitEnum | null $navigationGroup = 'Check-In';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Available Rooms';

    protected static ?string $title = 'Available Room List';

    public ?string $selectedDate = null;

    public ?string $selectedDepartDate = null;

    public ?string $selectedCategory = null;

    public ?string $selectedStatus = null;

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Grid::make(4)->schema([
                DatePicker::make('selectedDate')
                    ->label('Arrival Date')
                    ->default(fn () => SystemDate::today())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                DatePicker::make('selectedDepartDate')
                    ->label('Departure Date')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                Select::make('selectedCategory')
                    ->label('Room Category')
                    ->options(RoomCategory::pluck('name', 'id')->toArray())
                    ->placeholder('All Categories')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                Select::make('selectedStatus')
                    ->label('Room Status')
                    ->options(collect(RoomStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->toArray())
                    ->placeholder('All Available')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = Room::query()
                    ->where('is_active', true);

                if ($this->selectedCategory) {
                    $query->where('room_category_id', $this->selectedCategory);
                }

                if ($this->selectedStatus) {
                    $query->where('status', $this->selectedStatus);
                } else {
                    // Default: show available rooms
                    $query->whereIn('status', [
                        RoomStatus::VacantClean,
                        RoomStatus::VacantCleanUnchecked,
                        RoomStatus::VacantDirty,
                    ]);
                }

                // If arrival & departure dates provided, exclude rooms with conflicting reservations
                if ($this->selectedDate && $this->selectedDepartDate) {
                    $arrival = $this->selectedDate;
                    $departure = $this->selectedDepartDate;

                    $query->whereDoesntHave('reservations', function ($q) use ($arrival, $departure) {
                        $q->where('arrival_date', '<', $departure)
                            ->where('departure_date', '>', $arrival)
                            ->whereIn('status', [
                                \App\Enums\ReservationStatus::Guaranteed,
                                \App\Enums\ReservationStatus::SixPm,
                                \App\Enums\ReservationStatus::OralConfirmed,
                                \App\Enums\ReservationStatus::Tentative,
                                \App\Enums\ReservationStatus::Confirmed,
                                \App\Enums\ReservationStatus::CheckedIn,
                            ]);
                    });
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('room_number')
                    ->label('Room No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('category.code')
                    ->label('Category')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Cat. Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('floor')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.bed_setup')
                    ->label('Bed Setup'),
                Tables\Columns\TextColumn::make('overlook')
                    ->label('Overlook'),
                Tables\Columns\TextColumn::make('connecting_room')
                    ->label('Connecting'),
                Tables\Columns\IconColumn::make('is_smoking')
                    ->label('Smoking')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('category.base_rate')
                    ->label('Base Rate')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('room_number')
            ->striped()
            ->paginated([25, 50, 100])
            ->poll('30s');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Room::where('is_active', true)
            ->whereIn('status', [
                RoomStatus::VacantClean,
                RoomStatus::VacantCleanUnchecked,
                RoomStatus::VacantDirty,
            ])
            ->count();

        return (string) $count;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
