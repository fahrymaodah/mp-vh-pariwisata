<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ArticleType;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Article;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Services\BillingService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class InvoiceDetailPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.invoice-detail';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::DocumentText;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'fo-invoice/{record}';

    public ?Invoice $record = null;

    public function mount(int $record): void
    {
        $this->record = Invoice::with(['reservation.guest', 'reservation.arrangement', 'room'])->findOrFail($record);
    }

    public function getTitle(): string
    {
        $room = $this->record->room?->room_number ?? 'N/A';
        $guest = $this->record->reservation?->guest?->full_name ?? $this->record->guest?->full_name ?? 'N/A';

        return "Invoice {$this->record->invoice_no} — Rm {$room} — {$guest}";
    }

    // ── Header Actions: Post Article, Post Payment, New Bill, Print, Transfer ──

    protected function getHeaderActions(): array
    {
        return [
            // Post Sales Article (3 methods combined in one modal)
            Actions\Action::make('post_article')
                ->label('Post Article')
                ->icon(Heroicon::Plus)
                ->color('success')
                ->form([
                    Select::make('article_id')
                        ->label('Sales Article')
                        ->options(
                            Article::query()
                                ->active()
                                ->sales()
                                ->get()
                                ->mapWithKeys(fn (Article $a) => [$a->id => "[{$a->article_no}] {$a->name} — Rp " . number_format((float) $a->default_price, 0, ',', '.')])
                        )
                        ->searchable()
                        ->required(),
                    TextInput::make('qty')
                        ->label('Quantity')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),
                    TextInput::make('rate')
                        ->label('Rate (leave empty for default)')
                        ->numeric()
                        ->prefix('Rp'),
                ])
                ->action(function (array $data) {
                    $article = Article::findOrFail($data['article_id']);
                    $rate = ! empty($data['rate']) ? (float) $data['rate'] : null;

                    app(BillingService::class)->postArticle(
                        $this->record,
                        $article,
                        (int) $data['qty'],
                        $rate,
                    );

                    $this->record->refresh();

                    Notification::make()->title('Article posted successfully')->success()->send();
                })
                ->visible(fn () => in_array($this->record->status, [InvoiceStatus::Open, InvoiceStatus::Reopened])),

            // Post Payment
            Actions\Action::make('post_payment')
                ->label('Payment')
                ->icon(Heroicon::Banknotes)
                ->color('primary')
                ->form([
                    Select::make('method')
                        ->label('Payment Method')
                        ->options(PaymentMethod::class)
                        ->required(),
                    Select::make('article_id')
                        ->label('Payment Article')
                        ->options(
                            Article::query()
                                ->active()
                                ->payment()
                                ->get()
                                ->mapWithKeys(fn (Article $a) => [$a->id => "[{$a->article_no}] {$a->name}"])
                        )
                        ->searchable()
                        ->required(),
                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    TextInput::make('reference_no')
                        ->label('Reference No / Voucher No')
                        ->maxLength(100),
                ])
                ->action(function (array $data) {
                    $article = Article::findOrFail($data['article_id']);

                    app(BillingService::class)->postPayment(
                        $this->record,
                        $article,
                        PaymentMethod::from($data['method']),
                        (float) $data['amount'],
                        $data['reference_no'] ?? null,
                    );

                    $this->record->refresh();

                    Notification::make()->title('Payment posted successfully')->success()->send();
                })
                ->visible(fn () => in_array($this->record->status, [InvoiceStatus::Open, InvoiceStatus::Reopened])),

            // New Bill (Split)
            Actions\Action::make('new_bill')
                ->label('New Bill')
                ->icon(Heroicon::DocumentPlus)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Create New Bill')
                ->modalDescription('Create a new bill split for this guest? Items can be transferred between bills after creation.')
                ->action(function () {
                    $newInvoice = app(BillingService::class)->createNewBill($this->record->reservation);

                    Notification::make()
                        ->title("New bill {$newInvoice->invoice_no} created")
                        ->success()
                        ->send();

                    return redirect(InvoiceDetailPage::getUrl(['record' => $newInvoice->id]));
                })
                ->visible(fn () => $this->record->reservation_id !== null),

            // Bill Transfer
            Actions\Action::make('bill_transfer')
                ->label('Bill Transfer')
                ->icon(Heroicon::ArrowsRightLeft)
                ->color('warning')
                ->url(fn () => BillTransferPage::getUrl(['record' => $this->record->id]))
                ->visible(fn () => in_array($this->record->status, [InvoiceStatus::Open, InvoiceStatus::Reopened])),

            // Print
            Actions\ActionGroup::make([
                Actions\Action::make('print_regular')
                    ->label('Regular Final Folio')
                    ->icon(Heroicon::Printer)
                    ->url(fn () => PrintInvoicePage::getUrl(['record' => $this->record->id, 'format' => 'regular']), shouldOpenInNewTab: true)
                    ->action(function () {
                        app(BillingService::class)->markPrinted($this->record);
                        $this->record->refresh();
                    }),
                Actions\Action::make('print_oneline')
                    ->label('Print One Line Folio')
                    ->icon(Heroicon::DocumentMinus)
                    ->url(fn () => PrintInvoicePage::getUrl(['record' => $this->record->id, 'format' => 'oneline']), shouldOpenInNewTab: true)
                    ->action(function () {
                        app(BillingService::class)->markPrinted($this->record);
                        $this->record->refresh();
                    }),
                Actions\Action::make('print_summary')
                    ->label('Summary By Article')
                    ->icon(Heroicon::DocumentChartBar)
                    ->url(fn () => PrintInvoicePage::getUrl(['record' => $this->record->id, 'format' => 'summary']), shouldOpenInNewTab: true)
                    ->action(function () {
                        app(BillingService::class)->markPrinted($this->record);
                        $this->record->refresh();
                    }),
            ])
                ->label('Print')
                ->icon(Heroicon::Printer)
                ->color('info'),

            // Close Bill
            Actions\Action::make('close_bill')
                ->label('Close Bill')
                ->icon(Heroicon::LockClosed)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Close this bill?')
                ->modalDescription('Bill must be printed and balanced (zero balance) to close.')
                ->action(function () {
                    try {
                        app(BillingService::class)->closeInvoice($this->record);
                        $this->record->refresh();

                        Notification::make()->title('Bill closed successfully')->success()->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('Cannot close bill')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => in_array($this->record->status, [InvoiceStatus::Printed, InvoiceStatus::Reopened])),

            // Back
            Actions\Action::make('back')
                ->label('Back')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->url(FoInvoicePage::getUrl()),
        ];
    }

    // ── Table: Invoice Items + Payments combined ──

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
                    ->label('Art')
                    ->sortable(),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter(),
                TextColumn::make('description')
                    ->label('Description')
                    ->wrap(),
                TextColumn::make('rate')
                    ->label('Rate')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn (InvoiceItem $r) => $r->is_cancelled ? 'gray' : ($r->amount < 0 ? 'danger' : 'success')),
                TextColumn::make('department.name')
                    ->label('Dept')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('posting_date')
                    ->label('Date')
                    ->date('d/m/Y'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_cancelled')
                    ->label('Cxl')
                    ->boolean()
                    ->trueIcon(Heroicon::XCircle)
                    ->falseIcon(Heroicon::CheckCircle)
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->actions([
                // Cancel Item
                TableAction::make('cancel_item')
                    ->label('Cancel')
                    ->icon(Heroicon::XMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel this posting?')
                    ->form([
                        Textarea::make('cancel_reason')
                            ->label('Cancel Reason (mandatory)')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (InvoiceItem $record, array $data) {
                        app(BillingService::class)->cancelItem($record, $data['cancel_reason']);
                        $this->record->refresh();

                        Notification::make()->title('Item cancelled')->warning()->send();
                    })
                    ->visible(fn (InvoiceItem $record) => ! $record->is_cancelled
                        && in_array($this->record->status, [InvoiceStatus::Open, InvoiceStatus::Reopened])),
            ])
            ->defaultSort('created_at', 'asc')
            ->emptyStateHeading('No postings yet')
            ->emptyStateDescription('Use the "Post Article" or "Payment" button above to add items.')
            ->emptyStateIcon(Heroicon::DocumentText);
    }

    /**
     * Get total payments for display in the view.
     */
    public function getPaymentsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->record->payments()
            ->with(['article'])
            ->orderBy('created_at')
            ->get();
    }

    public static function getRoutePath(\Filament\Panel|string|null $panel = null): string
    {
        return '/fo-invoice/{record}';
    }
}
