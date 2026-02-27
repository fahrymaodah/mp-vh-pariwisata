<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\SystemDate;
use App\Services\CheckInService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class GroupCheckIn extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.group-check-in';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::UserGroup;

    protected static string | UnitEnum | null $navigationGroup = 'Check-In';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Group Check-In';

    protected static ?string $title = 'Group Check-In';

    protected static ?string $slug = 'group-check-in';

    // Filter state
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Grid::make(2)->schema([
                \Filament\Forms\Components\DatePicker::make('selectedDate')
                    ->label('Arrival Date')
                    ->default(fn () => SystemDate::today())
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

                // Show parent reservations that have group_name and arriving on selected date
                return Reservation::query()
                    ->whereNotNull('group_name')
                    ->where('group_name', '!=', '')
                    ->where('arrival_date', $date)
                    ->whereDoesntHave('parentReservation')
                    ->whereNotIn('status', [
                        ReservationStatus::Cancelled,
                        ReservationStatus::NoShow,
                    ]);
            })
            ->columns([
                TextColumn::make('reservation_no')
                    ->label('Reservation #')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('group_name')
                    ->label('Group / Company')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('guest.full_name')
                    ->label('Contact Person')
                    ->searchable(),
                TextColumn::make('member_count')
                    ->label('Members')
                    ->getStateUsing(fn (Reservation $r) => $r->childReservations()->count())
                    ->badge()
                    ->color('info'),
                TextColumn::make('checked_in_count')
                    ->label('Checked In')
                    ->getStateUsing(fn (Reservation $r) => $r->childReservations()->where('status', ReservationStatus::CheckedIn)->count())
                    ->badge()
                    ->color('success'),
                TextColumn::make('pending_count')
                    ->label('Pending')
                    ->getStateUsing(function (Reservation $r) {
                        return $r->childReservations()
                            ->whereNotIn('status', [
                                ReservationStatus::CheckedIn,
                                ReservationStatus::Cancelled,
                                ReservationStatus::NoShow,
                                ReservationStatus::CheckedOut,
                            ])
                            ->count();
                    })
                    ->badge()
                    ->color('warning'),
                TextColumn::make('roomCategory.name')
                    ->label('Category'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state) => $state->color()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ReservationStatus::cases())
                        ->filter(fn ($s) => $s->isActive())
                        ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                        ->toArray()),
            ])
            ->actions([
                Action::make('auto_check_in')
                    ->label('Auto Check-In All')
                    ->icon(Heroicon::Bolt)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Auto Check-In All Members')
                    ->modalDescription(fn (Reservation $record) => "Automatically check-in all pending members of group '{$record->group_name}'? Each member must have a room assigned.")
                    ->action(function (Reservation $record) {
                        $result = app(CheckInService::class)->groupAutoCheckIn($record);

                        $message = "Success: {$result['success']}, Failed: {$result['failed']}";

                        if ($result['errors']) {
                            $message .= "\n" . implode("\n", $result['errors']);
                        }

                        $notification = Notification::make()
                            ->title('Group Auto Check-In Complete')
                            ->body($message);

                        if ($result['failed'] > 0) {
                            $notification->warning();
                        } else {
                            $notification->success();
                        }

                        $notification->send();
                    }),
                Action::make('view_members')
                    ->label('View Members')
                    ->icon(Heroicon::UserGroup)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->emptyStateHeading('No group reservations arriving')
            ->emptyStateDescription('No group reservations found for the selected date.')
            ->emptyStateIcon(Heroicon::UserGroup);
    }

    /**
     * Get group arriving count for nav badge.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = Reservation::query()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->where('arrival_date', SystemDate::today())
            ->whereDoesntHave('parentReservation')
            ->whereNotIn('status', [
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
                ReservationStatus::CheckedOut,
                ReservationStatus::CheckedIn,
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
