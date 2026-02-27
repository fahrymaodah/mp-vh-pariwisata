<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use App\Services\BillingService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class ClosedBillsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.closed-bills';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::LockClosed;

    protected static string | UnitEnum | null $navigationGroup = 'FO Cashier';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Closed Bills';

    protected static ?string $title = 'Closed Guest Bills';

    protected static ?string $slug = 'closed-bills';

    public ?string $billType = null;

    public ?string $billInquiry = null;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('billType')
                ->label('Type of Bills')
                ->options([
                    'guest' => 'Hotel Guest Bills',
                    'nsg' => 'Non-Stay Guest Bills',
                    'master' => 'Master Bills',
                ])
                ->default('guest')
                ->live(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('status', InvoiceStatus::Closed)
                    ->when($this->billType === 'guest', fn ($q) => $q->where('type', InvoiceType::Guest))
                    ->when($this->billType === 'nsg', fn ($q) => $q->where('type', InvoiceType::NonStayGuest))
                    ->when($this->billType === 'master', fn ($q) => $q->where('type', InvoiceType::MasterBill))
                    ->with(['reservation.guest', 'room', 'guest', 'department'])
            )
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Bill No')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('room.room_number')
                    ->label('Rm No')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),
                TextColumn::make('guestName')
                    ->label('Guest Name')
                    ->getStateUsing(fn (Invoice $r) => $r->reservation?->guest?->full_name ?? $r->guest?->full_name ?? '—')
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('reservation.guest', fn ($g) => $g->where('name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%"))
                                ->orWhereHas('guest', fn ($g) => $g->where('name', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%"));
                        });
                    })
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (InvoiceType $state) => $state->label()),
                TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('total_payment')
                    ->label('Payment')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold'),
                TextColumn::make('closed_at')
                    ->label('Closed At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('closed_at', 'desc')
            ->actions([
                TableAction::make('reopen')
                    ->label('Reopen')
                    ->icon(Heroicon::LockOpen)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reopen Closed Bill')
                    ->modalDescription('Reopening this bill will allow further postings and modifications. Are you sure?')
                    ->action(function (Invoice $record) {
                        app(BillingService::class)->reopenInvoice($record);

                        Notification::make()
                            ->title("Bill {$record->invoice_no} reopened")
                            ->success()
                            ->send();
                    }),
                TableAction::make('view')
                    ->label('View')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Invoice $record) => InvoiceDetailPage::getUrl(['record' => $record->id])),
            ])
            ->emptyStateHeading('No closed bills')
            ->emptyStateDescription('No closed bills found for the selected type.')
            ->emptyStateIcon(Heroicon::LockClosed);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Invoice::where('status', InvoiceStatus::Closed)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
