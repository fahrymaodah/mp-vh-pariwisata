<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RoomingList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.hk.pages.rooming-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ListBullet;

    protected static string | UnitEnum | null $navigationGroup = 'Room Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Rooming List';

    protected static ?string $title = 'Rooming List';

    protected static ?string $slug = 'rooming-list';

    public ?string $displayFilter = null;

    public ?int $floorFilter = null;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('displayFilter')
                ->label('Display')
                ->options([
                    '' => 'All',
                    'arrival' => 'Arrival',
                    'departure' => 'Departure',
                    'inhouse' => 'In-House',
                    'uncleaned' => 'Uncleaned',
                ])
                ->live(),
            Select::make('floorFilter')
                ->label('Floor')
                ->options(
                    Room::active()
                        ->select('floor')
                        ->distinct()
                        ->orderBy('floor')
                        ->pluck('floor', 'floor')
                        ->mapWithKeys(fn ($f) => [$f => "Floor {$f}"])
                        ->toArray()
                )
                ->placeholder('All Floors')
                ->live(),
        ]);
    }

    public function table(Table $table): Table
    {
        $today = SystemDate::today();

        return $table
            ->query(
                Room::query()
                    ->active()
                    ->with([
                        'category',
                        'reservations' => fn ($q) => $q->where('status', ReservationStatus::CheckedIn)->with('guest'),
                    ])
                    ->when($this->floorFilter, fn ($q) => $q->where('floor', $this->floorFilter))
                    ->when($this->displayFilter === 'arrival', fn ($q) => $q->whereHas('reservations', fn (Builder $rq) => $rq->where('arrival_date', $today)->where('status', ReservationStatus::CheckedIn)))
                    ->when($this->displayFilter === 'departure', fn ($q) => $q->whereHas('reservations', fn (Builder $rq) => $rq->where('departure_date', $today)->where('status', ReservationStatus::CheckedIn)))
                    ->when($this->displayFilter === 'inhouse', fn ($q) => $q->occupied())
                    ->when($this->displayFilter === 'uncleaned', fn ($q) => $q->where('status', RoomStatus::VacantDirty))
                    ->orderBy('floor')
                    ->orderBy('room_number')
            )
            ->columns([
                TextColumn::make('floor')
                    ->label('Floor')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('room_number')
                    ->label('Rm No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('category.code')
                    ->label('Cat'),
                TextColumn::make('status')
                    ->label('Room Status')
                    ->badge()
                    ->formatStateUsing(fn (RoomStatus $state): string => $state->label())
                    ->color(fn (RoomStatus $state): string => $state->color()),
                TextColumn::make('guest_status')
                    ->label('Guest')
                    ->getStateUsing(function (Room $record) use ($today): string {
                        $res = $record->reservations->first();
                        if (!$res) {
                            return $record->status === RoomStatus::OutOfOrder ? 'I' : '-';
                        }
                        if ($res->arrival_date->equalTo($today)) {
                            return 'A';
                        }
                        if ($res->departure_date->equalTo($today)) {
                            return 'D';
                        }
                        return 'R';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'D' => 'warning',
                        'R' => 'info',
                        'I' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('guest_name')
                    ->label('Main Guest Name')
                    ->getStateUsing(fn (Room $record): string => $record->reservations->first()?->guest?->full_name ?? '-')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('reservations.guest', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%"))),
                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->getStateUsing(fn (Room $record): ?string => $record->reservations->first()?->arrival_date?->format('d M'))
                    ->alignCenter(),
                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->getStateUsing(fn (Room $record): ?string => $record->reservations->first()?->departure_date?->format('d M'))
                    ->alignCenter(),
                TextColumn::make('reservation_comment')
                    ->label('Comment')
                    ->getStateUsing(fn (Room $record): ?string => $record->reservations->first()?->comments)
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->bulkActions([])
            ->defaultSort('room_number');
    }
}
