<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class OverCreditLimitList extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.over-credit-limit-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ExclamationTriangle;

    protected static string | UnitEnum | null $navigationGroup = 'In House';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Over Credit Limit';

    protected static ?string $title = 'Over Credit Limit List';

    protected static ?string $slug = 'over-credit-limit-list';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->where('status', ReservationStatus::CheckedIn)
                    ->whereHas('guest', fn (Builder $q) => $q->where('credit_limit', '>', 0))
                    ->with(['guest', 'room', 'roomCategory', 'invoices'])
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Room No')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('reservation_no')
                    ->label('Res #')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->weight('bold'),
                TextColumn::make('roomCategory.code')
                    ->label('Cat'),
                TextColumn::make('guest.credit_limit')
                    ->label('Credit Limit')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('current_balance')
                    ->label('Current Balance')
                    ->getStateUsing(function (Reservation $record): float {
                        return (float) $record->invoices
                            ->where('status', InvoiceStatus::Open)
                            ->sum('balance');
                    })
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('over_amount')
                    ->label('Over Limit')
                    ->getStateUsing(function (Reservation $record): float {
                        $balance = (float) $record->invoices
                            ->where('status', InvoiceStatus::Open)
                            ->sum('balance');
                        $limit = (float) ($record->guest?->credit_limit ?? 0);
                        $over = $balance - $limit;

                        return max(0, $over);
                    })
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('status_indicator')
                    ->label('Status')
                    ->getStateUsing(function (Reservation $record): string {
                        $balance = (float) $record->invoices
                            ->where('status', InvoiceStatus::Open)
                            ->sum('balance');
                        $limit = (float) ($record->guest?->credit_limit ?? 0);

                        if ($balance > $limit) {
                            return 'OVER LIMIT';
                        }
                        if ($balance > $limit * 0.8) {
                            return 'WARNING';
                        }

                        return 'OK';
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'OVER LIMIT' => 'danger',
                        'WARNING' => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d/m/Y'),
                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d/m/Y'),
            ])
            ->defaultSort('room.room_number')
            ->emptyStateHeading('No credit limit issues')
            ->emptyStateDescription('No in-house guests have credit limits set, or all are within limits.')
            ->emptyStateIcon(Heroicon::ExclamationTriangle);
    }

    public static function getNavigationBadge(): ?string
    {
        // Count guests actually over their credit limit
        $overLimitCount = 0;

        $reservations = Reservation::where('status', ReservationStatus::CheckedIn)
            ->whereHas('guest', fn (Builder $q) => $q->where('credit_limit', '>', 0))
            ->with(['guest', 'invoices'])
            ->get();

        foreach ($reservations as $reservation) {
            $balance = (float) $reservation->invoices
                ->where('status', InvoiceStatus::Open)
                ->sum('balance');
            $limit = (float) ($reservation->guest?->credit_limit ?? 0);

            if ($balance > $limit) {
                $overLimitCount++;
            }
        }

        return $overLimitCount > 0 ? (string) $overLimitCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
