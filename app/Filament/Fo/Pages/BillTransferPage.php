<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\BillingService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class BillTransferPage extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::cashierRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.bill-transfer';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'bill-transfer/{record}';

    public ?Invoice $record = null;

    /** @var array<int> */
    public array $selectedItems = [];

    public function mount(int $record): void
    {
        $this->record = Invoice::with(['reservation.guest', 'reservation.invoices', 'room'])->findOrFail($record);
    }

    public function getTitle(): string
    {
        return "Bill Transfer — {$this->record->invoice_no}";
    }

    protected function getHeaderActions(): array
    {
        $siblingInvoices = $this->record->reservation
            ? $this->record->reservation->invoices()
                ->where('id', '!=', $this->record->id)
                ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Reopened])
                ->pluck('invoice_no', 'id')
                ->toArray()
            : [];

        return [
            // Transfer selected items
            Actions\Action::make('transfer_items')
                ->label('Transfer Selected Items')
                ->icon(Heroicon::ArrowRight)
                ->color('warning')
                ->form([
                    Select::make('target_invoice_id')
                        ->label('Transfer to Invoice')
                        ->options($siblingInvoices)
                        ->required()
                        ->helperText('Select the target bill to move items to.'),
                    CheckboxList::make('item_ids')
                        ->label('Items to Transfer')
                        ->options(
                            $this->record->items()
                                ->where('is_cancelled', false)
                                ->get()
                                ->mapWithKeys(fn (InvoiceItem $item) => [
                                    $item->id => "[{$item->article?->article_no}] {$item->description} — Rp " . number_format((float) $item->amount, 0, ',', '.'),
                                ])
                                ->toArray()
                        )
                        ->required()
                        ->columns(1),
                ])
                ->action(function (array $data) {
                    $targetInvoice = Invoice::findOrFail($data['target_invoice_id']);

                    $count = app(BillingService::class)->transferItems(
                        $data['item_ids'],
                        $this->record,
                        $targetInvoice,
                    );

                    $this->record->refresh();

                    Notification::make()
                        ->title("{$count} item(s) transferred to {$targetInvoice->invoice_no}")
                        ->success()
                        ->send();
                })
                ->visible(fn () => ! empty($siblingInvoices)),

            // Create new bill for transfer
            Actions\Action::make('create_new_bill')
                ->label('New Bill')
                ->icon(Heroicon::DocumentPlus)
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $newInvoice = app(BillingService::class)->createNewBill($this->record->reservation);

                    Notification::make()
                        ->title("New bill {$newInvoice->invoice_no} created for transfer")
                        ->success()
                        ->send();

                    return redirect(BillTransferPage::getUrl(['record' => $this->record->id]));
                })
                ->visible(fn () => $this->record->reservation_id !== null),

            // Back to invoice
            Actions\Action::make('back')
                ->label('Back to Invoice')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->url(InvoiceDetailPage::getUrl(['record' => $this->record->id])),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InvoiceItem::query()
                    ->where('invoice_id', $this->record->id)
                    ->with(['article', 'department'])
            )
            ->columns([
                TextColumn::make('article.article_no')
                    ->label('Art'),
                TextColumn::make('description')
                    ->label('Description'),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold'),
                TextColumn::make('room.room_number')
                    ->label('Rm No')
                    ->getStateUsing(fn (InvoiceItem $r) => $this->record->room?->room_number),
                TextColumn::make('posting_date')
                    ->label('Date')
                    ->date('d/m/Y'),
                IconColumn::make('is_cancelled')
                    ->label('Cxl')
                    ->boolean()
                    ->trueIcon(Heroicon::XCircle)
                    ->trueColor('danger')
                    ->falseIcon(Heroicon::CheckCircle)
                    ->falseColor('success'),
                TextColumn::make('transferred_from_invoice_id')
                    ->label('From')
                    ->getStateUsing(fn (InvoiceItem $r) => $r->transferred_from_invoice_id
                        ? Invoice::find($r->transferred_from_invoice_id)?->invoice_no
                        : '—')
                    ->color('warning'),
            ])
            ->defaultSort('created_at', 'asc')
            ->emptyStateHeading('No items')
            ->emptyStateIcon(Heroicon::ArrowsRightLeft);
    }

    /**
     * Get sibling invoices for display.
     */
    public function getSiblingInvoicesProperty(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->record->reservation_id) {
            return collect();
        }

        return $this->record->reservation->invoices()
            ->where('id', '!=', $this->record->id)
            ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Reopened, InvoiceStatus::Printed])
            ->with(['room'])
            ->get();
    }

    public static function getRoutePath(\Filament\Panel|string|null $panel = null): string
    {
        return '/bill-transfer/{record}';
    }
}
