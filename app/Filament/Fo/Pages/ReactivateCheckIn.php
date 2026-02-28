<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\SystemDate;
use App\Services\CheckInService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReactivateCheckIn extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::cashierRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.reactivate-check-in';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowPath;

    protected static string | UnitEnum | null $navigationGroup = 'Check-In';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Reactivate / Re-Check-In';

    protected static ?string $title = 'Reactivate & Re-Check-In';

    protected static ?string $slug = 'reactivate-check-in';

    // Filter state
    public ?string $selectedDate = null;

    public ?string $filterType = 'all';

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                \Filament\Forms\Components\DatePicker::make('selectedDate')
                    ->label('Date')
                    ->default(fn () => SystemDate::today())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                \Filament\Forms\Components\Select::make('filterType')
                    ->label('Show')
                    ->options([
                        'all' => 'All (Cancelled + Checked-Out)',
                        'cancelled' => 'Cancelled / No-Show Only',
                        'checked_out' => 'Checked-Out Only',
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
                $date = $this->selectedDate ?? SystemDate::today();
                $type = $this->filterType ?? 'all';

                $query = Reservation::query()
                    ->where('arrival_date', $date);

                if ($type === 'cancelled') {
                    $query->whereIn('status', [
                        ReservationStatus::Cancelled,
                        ReservationStatus::NoShow,
                    ]);
                } elseif ($type === 'checked_out') {
                    $query->where('status', ReservationStatus::CheckedOut);
                } else {
                    $query->whereIn('status', [
                        ReservationStatus::Cancelled,
                        ReservationStatus::NoShow,
                        ReservationStatus::CheckedOut,
                    ]);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('reservation_no')
                    ->label('Reservation #')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('guest', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%"))),
                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->default('—'),
                TextColumn::make('roomCategory.name')
                    ->label('Category'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state) => $state->color()),
                TextColumn::make('nights')
                    ->alignCenter(),
                TextColumn::make('arrival_date')
                    ->date('d M Y')
                    ->label('Arrival'),
                TextColumn::make('departure_date')
                    ->date('d M Y')
                    ->label('Departure'),
                TextColumn::make('cancel_reason')
                    ->label('Cancel Reason')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('checked_out_at')
                    ->label('Checked Out At')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon(Heroicon::ArrowPath)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reactivate Reservation')
                    ->modalDescription(fn (Reservation $record) => "Reactivate reservation #{$record->reservation_no} for {$record->guest?->full_name}? Status will change to Confirmed.")
                    ->visible(fn (Reservation $record) => in_array($record->status, [
                        ReservationStatus::Cancelled,
                        ReservationStatus::NoShow,
                    ]))
                    ->action(function (Reservation $record) {
                        try {
                            app(CheckInService::class)->reactivateReservation($record);

                            Notification::make()
                                ->title('Reservation Reactivated')
                                ->body("Reservation #{$record->reservation_no} has been reactivated to Confirmed status.")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Reactivate Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('re_check_in')
                    ->label('Re-Check-In')
                    ->icon(Heroicon::ArrowUturnLeft)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Re-Check-In Guest')
                    ->modalDescription(fn (Reservation $record) => "Re-check-in {$record->guest?->full_name} to Room {$record->room?->room_number}? This will reverse the check-out and reopen the invoice.")
                    ->visible(fn (Reservation $record) => $record->status === ReservationStatus::CheckedOut)
                    ->action(function (Reservation $record) {
                        try {
                            app(CheckInService::class)->reCheckIn($record);

                            Notification::make()
                                ->title('Re-Check-In Successful')
                                ->body("Guest has been re-checked-in to Room {$record->room?->room_number}.")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Re-Check-In Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->emptyStateHeading('No reservations found')
            ->emptyStateDescription('No cancelled, no-show, or checked-out reservations for the selected date.')
            ->emptyStateIcon(Heroicon::ArrowPath);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Reservation::query()
            ->where('arrival_date', SystemDate::today())
            ->whereIn('status', [
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
