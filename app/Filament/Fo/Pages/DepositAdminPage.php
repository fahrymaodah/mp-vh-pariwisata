<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Article;
use App\Models\Reservation;
use App\Services\BillingService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class DepositAdminPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.deposit-admin';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Banknotes;

    protected static string | UnitEnum | null $navigationGroup = 'FO Cashier';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Deposit';

    protected static ?string $title = 'Deposit Administration';

    protected static ?string $slug = 'deposit-admin';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->where('deposit_amount', '>', 0)
                    ->whereIn('status', [
                        ReservationStatus::Guaranteed,
                        ReservationStatus::Confirmed,
                        ReservationStatus::CheckedIn,
                        ReservationStatus::SixPm,
                        ReservationStatus::OralConfirmed,
                    ])
                    ->with(['guest', 'room', 'deposits'])
            )
            ->columns([
                TextColumn::make('reservation_no')
                    ->label('Res No')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('guest.full_name')
                    ->label('Reservation Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->weight('bold'),
                TextColumn::make('group_name')
                    ->label('Group')
                    ->placeholder('—'),
                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('deposit_amount')
                    ->label('Deposit')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('deposit_paid')
                    ->label('Deposit Paid')
                    ->money('IDR')
                    ->alignEnd()
                    ->color(fn (Reservation $r) => (float) $r->deposit_paid >= (float) $r->deposit_amount ? 'success' : 'warning'),
                TextColumn::make('deposit2_paid')
                    ->label('Deposit2 Paid')
                    ->money('IDR')
                    ->alignEnd()
                    ->placeholder('—'),
                TextColumn::make('deposit_balance')
                    ->label('Balance')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'warning' : 'success')),
                TextColumn::make('deposit_limit_date')
                    ->label('Due Date')
                    ->date('d/m/Y')
                    ->color(fn (Reservation $r) => $r->deposit_limit_date && $r->deposit_limit_date->isPast() ? 'danger' : null),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state) => $state->color()),
            ])
            ->defaultSort('arrival_date')
            ->actions([
                TableAction::make('pay_deposit')
                    ->label('Pay')
                    ->icon(Heroicon::Banknotes)
                    ->color('success')
                    ->form([
                        TextInput::make('amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->helperText('Enter the deposit amount to be paid.'),
                        Select::make('payment_method')
                            ->label('Payment Article')
                            ->options(
                                Article::query()
                                    ->active()
                                    ->payment()
                                    ->get()
                                    ->mapWithKeys(fn (Article $a) => [$a->name => "[{$a->article_no}] {$a->name}"])
                            )
                            ->searchable()
                            ->required(),
                        TextInput::make('voucher_no')
                            ->label('Voucher No')
                            ->maxLength(50)
                            ->helperText('Enter voucher number if using voucher payment.'),
                    ])
                    ->action(function (Reservation $record, array $data) {
                        app(BillingService::class)->recordDeposit(
                            $record,
                            (float) $data['amount'],
                            $data['payment_method'],
                            $data['voucher_no'] ?? null,
                        );

                        Notification::make()
                            ->title("Deposit payment recorded for {$record->reservation_no}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Reservation $record) => (float) $record->deposit_balance > 0),
                TableAction::make('history')
                    ->label('History')
                    ->icon(Heroicon::Clock)
                    ->color('gray')
                    ->modalHeading('Deposit Payment History')
                    ->modalContent(fn (Reservation $record) => view('filament.fo.pages.partials.deposit-history', [
                        'deposits' => $record->deposits()->with('user')->orderBy('payment_date')->get(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->emptyStateHeading('No deposit reservations')
            ->emptyStateDescription('No reservations with deposit requirements found.')
            ->emptyStateIcon(Heroicon::Banknotes);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Reservation::where('deposit_amount', '>', 0)
            ->where('deposit_balance', '>', 0)
            ->whereIn('status', [
                ReservationStatus::Guaranteed,
                ReservationStatus::Confirmed,
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
