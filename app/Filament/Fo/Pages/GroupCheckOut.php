<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\SystemDate;
use App\Services\CheckOutService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GroupCheckOut extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.group-check-out';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::UserGroup;

    protected static string | UnitEnum | null $navigationGroup = 'Check-Out';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Group Check-Out';

    protected static ?string $title = 'Group Check-Out';

    protected static ?string $slug = 'group-check-out';

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Grid::make(2)->schema([
                DatePicker::make('selectedDate')
                    ->label('Departure Date')
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

                // Show parent group reservations with checked-in members departing on selected date
                return Reservation::query()
                    ->whereNotNull('group_name')
                    ->where('group_name', '!=', '')
                    ->whereDoesntHave('parentReservation')
                    ->where(function (Builder $q) use ($date) {
                        // Parent or children have departure on this date and are checked in
                        $q->where(function (Builder $q2) use ($date) {
                            $q2->where('departure_date', $date)
                                ->where('status', ReservationStatus::CheckedIn);
                        })->orWhereHas('childReservations', function (Builder $q2) use ($date) {
                            $q2->where('departure_date', $date)
                                ->where('status', ReservationStatus::CheckedIn);
                        });
                    });
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
                TextColumn::make('checked_in_count')
                    ->label('Checked In')
                    ->getStateUsing(fn (Reservation $r) => $r->childReservations()->where('status', ReservationStatus::CheckedIn)->count())
                    ->badge()
                    ->color('warning'),
                TextColumn::make('checked_out_count')
                    ->label('Checked Out')
                    ->getStateUsing(fn (Reservation $r) => $r->childReservations()->where('status', ReservationStatus::CheckedOut)->count())
                    ->badge()
                    ->color('success'),
                TextColumn::make('pending_co_count')
                    ->label('Departing Today')
                    ->getStateUsing(function (Reservation $r) {
                        $date = $this->selectedDate ?? SystemDate::today();

                        return $r->childReservations()
                            ->where('departure_date', $date)
                            ->where('status', ReservationStatus::CheckedIn)
                            ->count();
                    })
                    ->badge()
                    ->color('danger'),
                TextColumn::make('roomCategory.name')
                    ->label('Category'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state) => $state->color()),
            ])
            ->actions([
                // Auto Check-Out All
                Action::make('auto_check_out')
                    ->label('Auto Check-Out All')
                    ->icon(Heroicon::Bolt)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Auto Check-Out All Members')
                    ->modalDescription(fn (Reservation $record) => "Automatically check-out all checked-in members of group '{$record->group_name}'? All bills must be settled.")
                    ->action(function (Reservation $record) {
                        $result = app(CheckOutService::class)->groupAutoCheckOut($record);

                        $message = "Success: {$result['success']}, Failed: {$result['failed']}";

                        if ($result['errors']) {
                            $message .= "\n" . implode("\n", $result['errors']);
                        }

                        $notification = Notification::make()
                            ->title('Group Auto Check-Out Complete')
                            ->body($message);

                        if ($result['failed'] > 0) {
                            $notification->warning();
                        } else {
                            $notification->success();
                        }

                        $notification->send();
                    }),

                // View Members (Manual Check-Out via detail page)
                Action::make('view_members')
                    ->label('View Members')
                    ->icon(Heroicon::UserGroup)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->emptyStateHeading('No group departures')
            ->emptyStateDescription('No group reservations with checked-in members departing on this date.')
            ->emptyStateIcon(Heroicon::UserGroup);
    }

    public static function getNavigationBadge(): ?string
    {
        $today = SystemDate::today();

        $count = Reservation::query()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->whereDoesntHave('parentReservation')
            ->whereHas('childReservations', function (Builder $q) use ($today) {
                $q->where('departure_date', $today)
                    ->where('status', ReservationStatus::CheckedIn);
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
