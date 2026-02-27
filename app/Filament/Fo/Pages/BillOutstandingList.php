<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Models\Invoice;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BillOutstandingList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.bill-outstanding-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::DocumentText;

    protected static string | UnitEnum | null $navigationGroup = 'In House';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Bill Outstanding';

    protected static ?string $title = 'Bill Outstanding List By Room No';

    protected static ?string $slug = 'bill-outstanding-list';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('status', InvoiceStatus::Open)
                    ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
                    ->with(['reservation.guest', 'room', 'reservation.roomCategory', 'reservation.arrangement'])
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Room No')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('invoice_no')
                    ->label('Bill #')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('reservation.guest.full_name')
                    ->label('Guest Name')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('reservation.guest', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%"))),
                TextColumn::make('reservation.roomCategory.code')
                    ->label('Cat'),
                TextColumn::make('reservation.arrangement.code')
                    ->label('Argt')
                    ->placeholder('—'),
                TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('total_payment')
                    ->label('Total Payment')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('reservation.arrival_date')
                    ->label('Arrival')
                    ->date('d/m/Y'),
                TextColumn::make('reservation.departure_date')
                    ->label('Departure')
                    ->date('d/m/Y'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (InvoiceStatus $state) => $state->color()),
            ])
            ->defaultSort('room.room_number')
            ->emptyStateHeading('No outstanding bills')
            ->emptyStateDescription('All in-house guest bills are settled.')
            ->emptyStateIcon(Heroicon::DocumentText);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Invoice::where('status', InvoiceStatus::Open)
            ->where('balance', '>', 0)
            ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
