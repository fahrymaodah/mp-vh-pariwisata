<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
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

class FoInvoicePage extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::cashierRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.fo-invoice';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CreditCard;

    protected static string | UnitEnum | null $navigationGroup = 'FO Cashier';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'F/O Invoice';

    protected static ?string $title = 'F/O Invoice — In House Guest Bills';

    protected static ?string $slug = 'fo-invoice';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed, InvoiceStatus::Reopened])
                    ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
                    ->with(['reservation.guest', 'room', 'reservation.arrangement'])
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Rm No')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('invoice_no')
                    ->label('Bill No')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('reservation.guest.full_name')
                    ->label('Guest Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->weight('bold'),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'warning' : 'success')),
                TextColumn::make('reservation.room_rate')
                    ->label('Rm Rate')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('reservation.departure_date')
                    ->label('Depart')
                    ->date('d/m/Y'),
                TextColumn::make('reservation.reservation_no')
                    ->label('Res No')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (InvoiceStatus $state) => $state->color()),
            ])
            ->defaultSort('room.room_number')
            ->recordUrl(fn (Invoice $record) => InvoiceDetailPage::getUrl(['record' => $record->id]))
            ->emptyStateHeading('No open invoices')
            ->emptyStateDescription('No open bills for in-house guests.')
            ->emptyStateIcon(Heroicon::CreditCard);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Invoice::whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed, InvoiceStatus::Reopened])
            ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
