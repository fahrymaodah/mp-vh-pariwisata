<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\SystemDate;
use App\Services\CheckOutService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CheckOutPage extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::cashierRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.check-out';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowLeftStartOnRectangle;

    protected static string | UnitEnum | null $navigationGroup = 'Check-Out';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Individual Check-Out';

    protected static ?string $title = 'Individual Check-Out';

    protected static ?string $slug = 'check-out';

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
                return Reservation::query()
                    ->with(['guest', 'roomCategory', 'room', 'invoices'])
                    ->where('departure_date', $this->selectedDate ?? SystemDate::today())
                    ->where('status', ReservationStatus::CheckedIn);
            })
            ->columns([
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation_no')
                    ->label('Res. No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('guest', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")))
                    ->weight('bold')
                    ->color(function (Reservation $record) {
                        if ($record->guest?->is_vip) {
                            return 'danger';
                        }

                        return null;
                    }),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat.')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('invoice_status')
                    ->label('Bill')
                    ->getStateUsing(function (Reservation $record) {
                        $invoices = $record->invoices;
                        if ($invoices->isEmpty()) {
                            return 'No Bill';
                        }

                        $unsettled = $invoices->filter(fn ($inv) => (float) $inv->balance !== 0.0);
                        if ($unsettled->isNotEmpty()) {
                            return 'Outstanding';
                        }

                        $allClosed = $invoices->every(fn ($inv) => $inv->status->value === 'closed');

                        return $allClosed ? 'Settled' : 'Ready';
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Settled', 'Ready' => 'success',
                        'Outstanding' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                // Check-Out Action
                Actions\Action::make('checkOut')
                    ->label('Check-Out')
                    ->icon(Heroicon::ArrowLeftStartOnRectangle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Reservation $record) => "Check-Out: {$record->guest?->full_name}")
                    ->modalDescription(function (Reservation $record) {
                        $room = $record->room?->room_number ?? '-';
                        $balance = $record->invoices->sum('balance');
                        $balanceFormatted = 'IDR ' . number_format((float) $balance, 0, ',', '.');

                        return "Room: {$room} | Outstanding: {$balanceFormatted}";
                    })
                    ->action(function (Reservation $record) {
                        try {
                            app(CheckOutService::class)->checkOut($record);

                            Notification::make()
                                ->title('Guest Checked Out')
                                ->body("Room {$record->fresh()->room->room_number} — {$record->guest?->full_name}")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Check-Out Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // View Bill
                Actions\Action::make('viewBill')
                    ->label('View Bill')
                    ->icon(Heroicon::DocumentText)
                    ->color('info')
                    ->url(function (Reservation $record) {
                        $invoice = $record->invoices()->latest()->first();
                        if ($invoice) {
                            return InvoiceDetailPage::getUrl(['record' => $invoice->id]);
                        }

                        return null;
                    })
                    ->visible(fn (Reservation $record) => $record->invoices()->exists()),

                // View reservation
                Actions\Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->defaultSort('room.room_number')
            ->striped()
            ->emptyStateHeading('No departures for this date')
            ->emptyStateDescription('No checked-in guests scheduled to depart on this date.')
            ->emptyStateIcon(Heroicon::ArrowLeftStartOnRectangle)
            ->poll('15s');
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
        return 'danger';
    }
}
