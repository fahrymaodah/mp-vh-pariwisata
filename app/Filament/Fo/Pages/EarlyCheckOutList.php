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
use Filament\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EarlyCheckOutList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.early-check-out-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ExclamationTriangle;

    protected static string | UnitEnum | null $navigationGroup = 'Check-Out';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Early Check-Out';

    protected static ?string $title = 'Early Check-Out Guest List';

    protected static ?string $slug = 'early-check-out-list';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(7)->toDateString();
        $this->dateTo = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Grid::make(2)->schema([
                DatePicker::make('dateFrom')
                    ->label('From Date')
                    ->default(fn () => now()->subDays(7)->toDateString())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                DatePicker::make('dateTo')
                    ->label('To Date')
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
                $query = Reservation::query()
                    ->with(['guest', 'roomCategory', 'room'])
                    ->where('status', ReservationStatus::CheckedOut)
                    ->whereNotNull('checked_out_at')
                    // Early C/O = checked_out_at date is before departure_date
                    ->whereColumn(\Illuminate\Support\Facades\DB::raw('DATE(checked_out_at)'), '<', 'departure_date');

                if ($this->dateFrom) {
                    $query->whereDate('checked_out_at', '>=', $this->dateFrom);
                }

                if ($this->dateTo) {
                    $query->whereDate('checked_out_at', '<=', $this->dateTo);
                }

                return $query;
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
                    ->label('Original Departure')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('Actual C/O')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nights_lost')
                    ->label('Nights Lost')
                    ->getStateUsing(function (Reservation $record) {
                        if (! $record->checked_out_at) {
                            return 0;
                        }

                        $actualCo = \Carbon\Carbon::parse($record->checked_out_at)->startOfDay();
                        $planned = \Carbon\Carbon::parse($record->departure_date)->startOfDay();

                        return max(0, $planned->diffInDays($actualCo));
                    })
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR'),
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
            ->emptyStateHeading('No early check-outs')
            ->emptyStateDescription('No guests checked out before their planned departure date in this period.')
            ->emptyStateIcon(Heroicon::ExclamationTriangle);
    }

    public static function getNavigationBadge(): ?string
    {
        $today = SystemDate::today();
        $count = Reservation::where('status', ReservationStatus::CheckedOut)
            ->whereNotNull('checked_out_at')
            ->whereDate('checked_out_at', $today)
            ->whereColumn(\Illuminate\Support\Facades\DB::raw('DATE(checked_out_at)'), '<', 'departure_date')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
