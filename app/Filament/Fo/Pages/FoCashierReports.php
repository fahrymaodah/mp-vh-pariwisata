<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

class FoCashierReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.fo-cashier-reports';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartBarSquare;

    protected static string | UnitEnum | null $navigationGroup = 'FO Cashier';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'FO Reports';

    protected static ?string $title = 'FO Cashier Reports';

    protected static ?string $slug = 'fo-cashier-reports';

    public string $reportType = 'booking_journal_article';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public ?int $fromDept = null;

    public ?int $toDept = null;

    public ?int $userId = null;

    public function mount(): void
    {
        $this->fromDate = now()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('reportType')
                ->label('Report Type')
                ->options([
                    'booking_journal_article' => 'Booking Journal by Article',
                    'booking_journal_user' => 'Booking Journal by User',
                    'payment_journal_user' => 'Payment Journal by User',
                    'cancellation_journal' => 'Cancellation Journal',
                    'turnover_by_dept' => 'FO Turnover Report by Department',
                    'summary_cashier' => 'Summary Cashier Report',
                    'guest_account_balance' => 'Guest Account Balance',
                ])
                ->default('booking_journal_article')
                ->live()
                ->columnSpan(2),
            DatePicker::make('fromDate')
                ->label('From Date')
                ->default(now()),
            DatePicker::make('toDate')
                ->label('To Date')
                ->default(now()),
            Select::make('fromDept')
                ->label('From Department')
                ->options(Department::active()->pluck('name', 'id'))
                ->placeholder('All departments'),
            Select::make('toDept')
                ->label('To Department')
                ->options(Department::active()->pluck('name', 'id'))
                ->placeholder('All departments'),
            Select::make('userId')
                ->label('User')
                ->options(User::pluck('name', 'id'))
                ->placeholder('All users')
                ->visible(fn (callable $get) => in_array($get('reportType'), ['booking_journal_user', 'payment_journal_user'])),
        ]);
    }

    public function table(Table $table): Table
    {
        return match ($this->reportType) {
            'booking_journal_article' => $this->bookingJournalByArticle($table),
            'booking_journal_user' => $this->bookingJournalByUser($table),
            'payment_journal_user' => $this->paymentJournalByUser($table),
            'cancellation_journal' => $this->cancellationJournal($table),
            'turnover_by_dept' => $this->turnoverByDept($table),
            'summary_cashier' => $this->summaryCashierReport($table),
            'guest_account_balance' => $this->guestAccountBalance($table),
            default => $this->bookingJournalByArticle($table),
        };
    }

    // ── Booking Journal by Article ───────────────────

    private function bookingJournalByArticle(Table $table): Table
    {
        return $table
            ->query(
                InvoiceItem::query()
                    ->with(['article', 'department', 'invoice.room', 'invoice.reservation.guest', 'user'])
                    ->when($this->fromDate, fn (Builder $q) => $q->where('posting_date', '>=', $this->fromDate))
                    ->when($this->toDate, fn (Builder $q) => $q->where('posting_date', '<=', $this->toDate))
                    ->when($this->fromDept, fn (Builder $q) => $q->where('department_id', '>=', $this->fromDept))
                    ->when($this->toDept, fn (Builder $q) => $q->where('department_id', '<=', $this->toDept))
            )
            ->columns([
                TextColumn::make('posting_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('invoice.room.room_number')
                    ->label('RmNo')
                    ->placeholder('—'),
                TextColumn::make('invoice.invoice_no')
                    ->label('Bill-No')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('article.article_no')
                    ->label('ArtNo')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description'),
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('invoice.reservation.guest.full_name')
                    ->label('Guest Name')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('posting_date')
            ->emptyStateHeading('No transactions')
            ->emptyStateDescription('No booking journal entries found for the given filters.')
            ->emptyStateIcon(Heroicon::DocumentText);
    }

    // ── Booking Journal by User ──────────────────────

    private function bookingJournalByUser(Table $table): Table
    {
        return $table
            ->query(
                InvoiceItem::query()
                    ->with(['article', 'department', 'invoice.room', 'invoice.reservation.guest', 'user'])
                    ->when($this->userId, fn (Builder $q) => $q->where('user_id', $this->userId))
                    ->when($this->fromDate, fn (Builder $q) => $q->where('posting_date', '>=', $this->fromDate))
                    ->when($this->toDate, fn (Builder $q) => $q->where('posting_date', '<=', $this->toDate))
            )
            ->columns([
                TextColumn::make('posting_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('invoice.room.room_number')
                    ->label('RmNo')
                    ->placeholder('—'),
                TextColumn::make('invoice.invoice_no')
                    ->label('Bill-No')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('article.article_no')
                    ->label('ArtNo'),
                TextColumn::make('description')
                    ->label('Description'),
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('invoice.reservation.guest.full_name')
                    ->label('Guest Name')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->time('H:i'),
                TextColumn::make('user.name')
                    ->label('ID'),
            ])
            ->defaultSort('posting_date')
            ->emptyStateHeading('No user transactions')
            ->emptyStateIcon(Heroicon::DocumentText);
    }

    // ── Payment Journal by User ──────────────────────

    private function paymentJournalByUser(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->with(['article', 'invoice.room', 'invoice.reservation.guest', 'user'])
                    ->when($this->userId, fn (Builder $q) => $q->where('user_id', $this->userId))
                    ->when($this->fromDate, fn (Builder $q) => $q->where('created_at', '>=', $this->fromDate))
                    ->when($this->toDate, fn (Builder $q) => $q->where('created_at', '<=', $this->toDate . ' 23:59:59'))
                    ->where('is_cancelled', false)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('invoice.room.room_number')
                    ->label('RmNo')
                    ->placeholder('—'),
                TextColumn::make('invoice.invoice_no')
                    ->label('Bill-No')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('article.article_no')
                    ->label('ArtNo'),
                TextColumn::make('article.name')
                    ->label('Description'),
                TextColumn::make('method')
                    ->label('Method')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Local Amount')
                    ->money('IDR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('foreignAmount')
                    ->label('F-Amount')
                    ->getStateUsing(fn (Payment $r) => $r->currency_code !== 'IDR'
                        ? number_format((float) $r->amount / max((float) $r->exchange_rate, 0.01), 2)
                        : '—')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->time('H:i'),
                TextColumn::make('user.name')
                    ->label('ID'),
            ])
            ->defaultSort('created_at')
            ->emptyStateHeading('No payment transactions')
            ->emptyStateIcon(Heroicon::Banknotes);
    }

    // ── Cancellation Journal ─────────────────────────

    private function cancellationJournal(Table $table): Table
    {
        return $table
            ->query(
                InvoiceItem::query()
                    ->where('is_cancelled', true)
                    ->with(['article', 'department', 'invoice.room', 'user'])
                    ->when($this->fromDate, fn (Builder $q) => $q->where('cancelled_at', '>=', $this->fromDate))
                    ->when($this->toDate, fn (Builder $q) => $q->where('cancelled_at', '<=', $this->toDate . ' 23:59:59'))
                    ->when($this->fromDept, fn (Builder $q) => $q->where('department_id', '>=', $this->fromDept))
                    ->when($this->toDept, fn (Builder $q) => $q->where('department_id', '<=', $this->toDept))
            )
            ->columns([
                TextColumn::make('cancelled_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('invoice.room.room_number')
                    ->label('RmNo')
                    ->placeholder('—'),
                TextColumn::make('invoice.invoice_no')
                    ->label('Bill-No')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('article.article_no')
                    ->label('ArtNo'),
                TextColumn::make('description')
                    ->label('Description'),
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('cancel_reason')
                    ->label('Cancel Reason')
                    ->wrap()
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label('ID'),
            ])
            ->defaultSort('cancelled_at', 'desc')
            ->emptyStateHeading('No cancellations')
            ->emptyStateDescription('No cancelled transactions found for the given filters.')
            ->emptyStateIcon(Heroicon::XCircle);
    }

    // ── FO Turnover Report by Department ─────────────

    private function turnoverByDept(Table $table): Table
    {
        return $table
            ->query(
                InvoiceItem::query()
                    ->where('is_cancelled', false)
                    ->with(['article', 'department'])
                    ->when($this->fromDate, fn (Builder $q) => $q->where('posting_date', '>=', $this->fromDate))
                    ->when($this->toDate, fn (Builder $q) => $q->where('posting_date', '<=', $this->toDate))
                    ->when($this->fromDept, fn (Builder $q) => $q->where('department_id', '>=', $this->fromDept))
                    ->when($this->toDept, fn (Builder $q) => $q->where('department_id', '<=', $this->toDept))
            )
            ->columns([
                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('article.article_no')
                    ->label('ArtNo')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description'),
                TextColumn::make('amount')
                    ->label('Day Nett')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('service_amount')
                    ->label('Service')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('tax_amount')
                    ->label('Gov Tax')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('dayGross')
                    ->label('Day Gross')
                    ->getStateUsing(fn (InvoiceItem $r) => (float) $r->amount + (float) $r->service_amount + (float) $r->tax_amount)
                    ->money('IDR')
                    ->alignEnd(),
            ])
            ->defaultSort('department.name')
            ->emptyStateHeading('No turnover data')
            ->emptyStateIcon(Heroicon::ChartBar);
    }

    // ── Summary Cashier Report ───────────────────────

    private function summaryCashierReport(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed, InvoiceStatus::Reopened])
                    ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
                    ->with(['reservation.guest', 'room'])
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('RmNo')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('invoice_no')
                    ->label('Bill-No')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('reservation.guest.full_name')
                    ->label('Guest Name')
                    ->searchable(),
                TextColumn::make('total_sales')
                    ->label('Revenue')
                    ->money('IDR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('total_payment')
                    ->label('Payment')
                    ->money('IDR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('balance')
                    ->label('Outstanding')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (InvoiceStatus $state) => $state->color()),
            ])
            ->defaultSort('room.room_number')
            ->emptyStateHeading('No active invoices')
            ->emptyStateIcon(Heroicon::ChartBarSquare);
    }

    // ── Guest Account Balance ────────────────────────

    private function guestAccountBalance(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed, InvoiceStatus::Reopened])
                    ->whereHas('reservation', fn (Builder $q) => $q->where('status', ReservationStatus::CheckedIn))
                    ->with(['reservation.guest', 'room'])
            )
            ->columns([
                TextColumn::make('reservation.guest.full_name')
                    ->label('Name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('room.room_number')
                    ->label('RmNo')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('invoice_no')
                    ->label('Bill-No')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('total_sales')
                    ->label('Debit')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('total_payment')
                    ->label('Credit')
                    ->money('IDR')
                    ->alignEnd(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'warning' : 'success'))
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('reservation.departure_date')
                    ->label('Departure')
                    ->date('d/m/Y'),
            ])
            ->defaultSort('room.room_number')
            ->emptyStateHeading('No guest balances')
            ->emptyStateIcon(Heroicon::CurrencyDollar);
    }
}
