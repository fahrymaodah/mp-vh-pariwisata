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
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DepartedGuestList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.departed-guest-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowUpOnSquare;

    protected static string | UnitEnum | null $navigationGroup = 'Check-Out';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Departed Guests';

    protected static ?string $title = 'Departed Guest List';

    protected static ?string $slug = 'departed-guest-list';

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
                    ->where('status', ReservationStatus::CheckedOut)
                    ->whereDate('checked_out_at', $this->selectedDate ?? SystemDate::today());
            })
            ->columns([
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation_no')
                    ->label('Res. No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat.')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('C/O Time')
                    ->dateTime('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('payment_info')
                    ->label('Payment')
                    ->getStateUsing(function (Reservation $record) {
                        $total = $record->invoices->sum('total_payment');

                        return 'IDR ' . number_format((float) $total, 0, ',', '.');
                    }),
            ])
            ->actions([
                Action::make('viewBill')
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
                Action::make('view')
                    ->label('Detail')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->defaultSort('checked_out_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100])
            ->emptyStateHeading('No departed guests')
            ->emptyStateDescription('No guests checked out on this date.')
            ->emptyStateIcon(Heroicon::ArrowUpOnSquare);
    }

    public static function getNavigationBadge(): ?string
    {
        $today = SystemDate::today();
        $count = Reservation::where('status', ReservationStatus::CheckedOut)
            ->whereDate('checked_out_at', $today)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
