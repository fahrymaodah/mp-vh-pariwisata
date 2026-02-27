<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
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

class HistoryList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.history-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Clock;

    protected static string | UnitEnum | null $navigationGroup = 'Check-Out';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'History List';

    protected static ?string $title = 'History List';

    protected static ?string $slug = 'history-list';

    // Filter properties
    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?string $searchName = null;

    public ?string $searchRoom = null;

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(30)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Grid::make(4)->schema([
                DatePicker::make('dateFrom')
                    ->label('From Date')
                    ->default(fn () => now()->subDays(30)->toDateString())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                DatePicker::make('dateTo')
                    ->label('To Date')
                    ->default(fn () => now()->toDateString())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                TextInput::make('searchName')
                    ->label('Guest Name')
                    ->placeholder('Search by name...')
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn () => $this->resetTable()),
                TextInput::make('searchRoom')
                    ->label('Room Number')
                    ->placeholder('Room #')
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = Reservation::query()
                    ->with(['guest', 'roomCategory', 'room', 'invoices'])
                    ->where('status', ReservationStatus::CheckedOut);

                // Date range filter on checked_out_at
                if ($this->dateFrom) {
                    $query->whereDate('checked_out_at', '>=', $this->dateFrom);
                }

                if ($this->dateTo) {
                    $query->whereDate('checked_out_at', '<=', $this->dateTo);
                }

                // Guest name search
                if ($this->searchName) {
                    $query->whereHas('guest', function (Builder $q) {
                        $q->where('name', 'like', "%{$this->searchName}%")
                            ->orWhere('first_name', 'like', "%{$this->searchName}%");
                    });
                }

                // Room number search
                if ($this->searchRoom) {
                    $query->whereHas('room', function (Builder $q) {
                        $q->where('room_number', 'like', "%{$this->searchRoom}%");
                    });
                }

                return $query;
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
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_bill')
                    ->label('Total Bill')
                    ->getStateUsing(fn (Reservation $record) => $record->invoices->sum('total_sales'))
                    ->money('IDR'),
            ])
            ->actions([
                // Display Bill / View closed invoice
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

                // View reservation detail
                Action::make('view')
                    ->label('Detail')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Reservation $record) => route('filament.fo.resources.reservations.view', $record)),
            ])
            ->defaultSort('checked_out_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100])
            ->emptyStateHeading('No check-out history found')
            ->emptyStateDescription('Adjust your search filters to find checked-out guests.')
            ->emptyStateIcon(Heroicon::Clock);
    }
}
