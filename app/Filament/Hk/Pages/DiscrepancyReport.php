<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DiscrepancyReport extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::hkSupervisorRoles()) ?? false;
    }

    protected string $view = 'filament.hk.pages.discrepancy-report';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ExclamationTriangle;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Discrepancy Report';

    protected static ?string $title = 'FO vs HK Discrepancy Report';

    protected static ?string $slug = 'discrepancy-report';

    /**
     * Returns rooms where FO status (based on reservation) doesn't match HK status (room.status).
     * FO says occupied (has checked-in reservation) but HK says vacant, or vice versa.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Room::query()
                    ->active()
                    ->with(['category', 'reservations' => fn ($q) => $q->where('status', ReservationStatus::CheckedIn)->with('guest')])
                    ->where(function (Builder $q) {
                        // FO occupied but HK vacant
                        $q->where(function (Builder $sub) {
                            $sub->whereHas('reservations', fn (Builder $rq) => $rq->where('status', ReservationStatus::CheckedIn))
                                ->whereIn('status', [
                                    RoomStatus::VacantClean,
                                    RoomStatus::VacantCleanUnchecked,
                                    RoomStatus::VacantDirty,
                                ]);
                        })
                        // HK occupied but FO no checked-in reservation
                        ->orWhere(function (Builder $sub) {
                            $sub->whereDoesntHave('reservations', fn (Builder $rq) => $rq->where('status', ReservationStatus::CheckedIn))
                                ->whereIn('status', [
                                    RoomStatus::OccupiedClean,
                                    RoomStatus::OccupiedDirty,
                                    RoomStatus::ExpectedDeparture,
                                ]);
                        });
                    })
                    ->orderBy('floor')
                    ->orderBy('room_number')
            )
            ->columns([
                TextColumn::make('room_number')
                    ->label('Rm No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('fo_status')
                    ->label('F/O Status')
                    ->getStateUsing(function (Room $record): string {
                        $hasCheckedIn = $record->reservations->where('status', ReservationStatus::CheckedIn)->isNotEmpty();
                        return $hasCheckedIn ? 'Occupied' : 'Vacant';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Occupied' ? 'primary' : 'success'),
                TextColumn::make('status')
                    ->label('H/K Status')
                    ->badge()
                    ->formatStateUsing(fn (RoomStatus $state): string => $state->label())
                    ->color(fn (RoomStatus $state): string => $state->color()),
                TextColumn::make('explanation')
                    ->label('Explanation')
                    ->getStateUsing(function (Room $record): string {
                        $hasCheckedIn = $record->reservations->where('status', ReservationStatus::CheckedIn)->isNotEmpty();
                        if ($hasCheckedIn && $record->status->isAvailable()) {
                            return 'FO says Occupied, HK says Vacant';
                        }
                        if (!$hasCheckedIn && $record->status->isOccupied()) {
                            return 'HK says Occupied, FO has no active reservation';
                        }
                        return 'Status mismatch';
                    })
                    ->color('danger'),
                TextColumn::make('last_change')
                    ->label('Time')
                    ->getStateUsing(function (Room $record): ?string {
                        $log = RoomStatusLog::where('room_id', $record->id)->latest()->first();
                        return $log?->created_at?->format('H:i');
                    }),
                TextColumn::make('last_user')
                    ->label('ID')
                    ->getStateUsing(function (Room $record): ?string {
                        $log = RoomStatusLog::where('room_id', $record->id)->latest()->with('user')->first();
                        return $log?->user?->name;
                    }),
                TextColumn::make('floor')
                    ->label('Floor')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('category.name')
                    ->label('Room Description'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No Discrepancies')
            ->emptyStateDescription('All room statuses match between Front Office and Housekeeping.');
    }

    public function getDiscrepancyCount(): int
    {
        return Room::active()
            ->where(function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->whereHas('reservations', fn (Builder $rq) => $rq->where('status', ReservationStatus::CheckedIn))
                        ->whereIn('status', [RoomStatus::VacantClean, RoomStatus::VacantCleanUnchecked, RoomStatus::VacantDirty]);
                })
                ->orWhere(function (Builder $sub) {
                    $sub->whereDoesntHave('reservations', fn (Builder $rq) => $rq->where('status', ReservationStatus::CheckedIn))
                        ->whereIn('status', [RoomStatus::OccupiedClean, RoomStatus::OccupiedDirty, RoomStatus::ExpectedDeparture]);
                });
            })
            ->count();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Room::active()
            ->where(function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->whereHas('reservations', fn (Builder $rq) => $rq->where('status', ReservationStatus::CheckedIn))
                        ->whereIn('status', [RoomStatus::VacantClean, RoomStatus::VacantCleanUnchecked, RoomStatus::VacantDirty]);
                })
                ->orWhere(function (Builder $sub) {
                    $sub->whereDoesntHave('reservations', fn (Builder $rq) => $rq->where('status', ReservationStatus::CheckedIn))
                        ->whereIn('status', [RoomStatus::OccupiedClean, RoomStatus::OccupiedDirty, RoomStatus::ExpectedDeparture]);
                });
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
