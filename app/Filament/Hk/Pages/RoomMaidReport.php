<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Room;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RoomMaidReport extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::hkSupervisorRoles()) ?? false;
    }

    protected string $view = 'filament.hk.pages.room-maid-report';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::UserGroup;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Room Maid Report';

    protected static ?string $title = 'Room Maid Report';

    protected static ?string $slug = 'room-maid-report';

    public ?int $fromFloor = null;

    public ?int $toFloor = null;

    public ?string $roomFilter = 'all';

    public function filtersForm(Schema $schema): Schema
    {
        $floors = Room::active()
            ->select('floor')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor', 'floor')
            ->mapWithKeys(fn ($f) => [$f => "Floor {$f}"])
            ->toArray();

        return $schema->components([
            Select::make('fromFloor')
                ->label('From Floor')
                ->options($floors)
                ->placeholder('All')
                ->live(),
            Select::make('toFloor')
                ->label('To Floor')
                ->options($floors)
                ->placeholder('All')
                ->live(),
            Select::make('roomFilter')
                ->label('Show')
                ->options([
                    'all' => 'All Rooms',
                    'odd' => 'Odd Rooms',
                    'even' => 'Even Rooms',
                ])
                ->default('all')
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
                    ->when($this->fromFloor, fn ($q) => $q->where('floor', '>=', $this->fromFloor))
                    ->when($this->toFloor, fn ($q) => $q->where('floor', '<=', $this->toFloor))
                    ->when($this->roomFilter === 'odd', fn ($q) => $q->whereRaw('CAST(room_number AS UNSIGNED) % 2 = 1'))
                    ->when($this->roomFilter === 'even', fn ($q) => $q->whereRaw('CAST(room_number AS UNSIGNED) % 2 = 0'))
                    ->orderBy('floor')
                    ->orderBy('room_number')
            )
            ->columns([
                TextColumn::make('floor')
                    ->label('Fl')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('room_number')
                    ->label('Rm No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('category.name')
                    ->label('Description'),
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
                            return '-';
                        }
                        if ($res->status === ReservationStatus::CheckedOut) {
                            return '*';
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
                        '*' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('guest_name')
                    ->label('Main Guest Name')
                    ->getStateUsing(fn (Room $record): string => $record->reservations->first()?->guest?->full_name ?? '-'),
                TextColumn::make('arrival')
                    ->label('Arrival')
                    ->getStateUsing(fn (Room $record): ?string => $record->reservations->first()?->arrival_date?->format('d M'))
                    ->alignCenter(),
                TextColumn::make('departure')
                    ->label('Departure')
                    ->getStateUsing(fn (Room $record): ?string => $record->reservations->first()?->departure_date?->format('d M'))
                    ->alignCenter(),
                TextColumn::make('nationality')
                    ->label('Nation')
                    ->getStateUsing(fn (Room $record): ?string => $record->reservations->first()?->guest?->nationality)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('credit_points')
                    ->label('Credit Pts')
                    ->getStateUsing(fn (Room $record): int => $record->category?->credit_points ?? 1)
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
            ])
            ->bulkActions([])
            ->defaultSort('room_number');
    }

    public function getTotalCreditPoints(): int
    {
        return Room::active()
            ->with('category')
            ->get()
            ->sum(fn (Room $r) => $r->category?->credit_points ?? 1);
    }
}
