<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Models\Invoice;
use App\Models\Reservation;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RoomRevenueBreakdown extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.room-revenue-breakdown';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Banknotes;

    protected static string | UnitEnum | null $navigationGroup = 'In House';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Room Revenue';

    protected static ?string $title = 'Room Revenue Breakdown';

    protected static ?string $slug = 'room-revenue-breakdown';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->where('status', ReservationStatus::CheckedIn)
                    ->with(['guest', 'room', 'arrangement', 'invoices.items.article.department'])
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('RmNo')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->limit(25),
                TextColumn::make('arrangement.code')
                    ->label('Argt')
                    ->placeholder('—'),
                TextColumn::make('currency_code')
                    ->label('Curr'),
                TextColumn::make('room_rate')
                    ->label('Room Rate')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('pax_display')
                    ->label('Pax')
                    ->getStateUsing(fn (Reservation $r) => $r->adults + ($r->children ?? 0))
                    ->alignCenter(),

                // Revenue from lodging (room charges)
                TextColumn::make('lodging_revenue')
                    ->label('Lodging')
                    ->getStateUsing(function (Reservation $record): string {
                        $total = $this->getRevenueByDepartment($record, 'Hotel');

                        return number_format($total, 0, ',', '.');
                    })
                    ->prefix('IDR ')
                    ->alignEnd(),

                // Revenue from F&B
                TextColumn::make('fb_revenue')
                    ->label('F&B')
                    ->getStateUsing(function (Reservation $record): string {
                        $total = $this->getRevenueByDepartment($record, 'Restaurant');

                        return number_format($total, 0, ',', '.');
                    })
                    ->prefix('IDR ')
                    ->alignEnd(),

                // Other revenue
                TextColumn::make('other_revenue')
                    ->label('Other')
                    ->getStateUsing(function (Reservation $record): string {
                        $total = $this->getOtherRevenue($record);

                        return number_format($total, 0, ',', '.');
                    })
                    ->prefix('IDR ')
                    ->alignEnd(),

                // Fix Cost total
                TextColumn::make('fix_cost_total')
                    ->label('Fix Cost')
                    ->getStateUsing(function (Reservation $record): string {
                        $total = $record->fixCostArticles->sum(fn ($fc) => $fc->qty * $fc->price);

                        return number_format($total, 0, ',', '.');
                    })
                    ->prefix('IDR ')
                    ->alignEnd(),

                // Total rate (all invoice items)
                TextColumn::make('total_revenue')
                    ->label('Total')
                    ->getStateUsing(function (Reservation $record): string {
                        $total = 0;
                        foreach ($record->invoices as $invoice) {
                            $total += $invoice->items
                                ->where('is_cancelled', false)
                                ->sum('amount');
                        }

                        return number_format($total, 0, ',', '.');
                    })
                    ->prefix('IDR ')
                    ->alignEnd()
                    ->weight('bold'),

                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d/m')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('departure_date')
                    ->label('Depart')
                    ->date('d/m')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('invoice_no')
                    ->label('Bill#')
                    ->getStateUsing(fn (Reservation $r) => $r->invoices->first()?->invoice_no ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('room.room_number')
            ->emptyStateHeading('No in-house guests')
            ->emptyStateDescription('No room revenue to display — no guests are currently checked in.')
            ->emptyStateIcon(Heroicon::Banknotes);
    }

    /**
     * Get revenue by department name for a reservation.
     */
    private function getRevenueByDepartment(Reservation $reservation, string $departmentName): float
    {
        $total = 0;
        foreach ($reservation->invoices as $invoice) {
            $total += $invoice->items
                ->where('is_cancelled', false)
                ->filter(fn ($item) => str_contains(strtolower($item->department?->name ?? ''), strtolower($departmentName)))
                ->sum('amount');
        }

        return (float) $total;
    }

    /**
     * Get revenue from departments other than Hotel and Restaurant.
     */
    private function getOtherRevenue(Reservation $reservation): float
    {
        $total = 0;
        foreach ($reservation->invoices as $invoice) {
            $total += $invoice->items
                ->where('is_cancelled', false)
                ->filter(function ($item) {
                    $dept = strtolower($item->department?->name ?? '');

                    return ! str_contains($dept, 'hotel') && ! str_contains($dept, 'restaurant');
                })
                ->sum('amount');
        }

        return (float) $total;
    }
}
