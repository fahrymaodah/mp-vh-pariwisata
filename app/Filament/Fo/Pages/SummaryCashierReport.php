<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Filament\Traits\HasReportExport;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\SystemDate;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class SummaryCashierReport extends Page implements HasTable
{
    use InteractsWithTable;
    use HasReportExport;

    protected string $view = 'filament.fo.pages.summary-cashier-report';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Calculator;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 24;

    protected static ?string $navigationLabel = 'Summary Cashier';

    protected static ?string $title = 'Summary Cashier Report';

    protected static ?string $slug = 'summary-cashier-report';

    public ?string $reportDate = null;

    public array $summaryData = [];

    public function mount(): void
    {
        $this->reportDate = SystemDate::today();
        $this->loadSummary();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                DatePicker::make('reportDate')
                    ->label('Report Date')
                    ->default(SystemDate::today())
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadSummary()),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        $date = $this->reportDate ?? SystemDate::today();

        return $table
            ->query(
                Payment::query()
                    ->with(['invoice.reservation.guest', 'invoice.room', 'article', 'user'])
                    ->whereDate('created_at', $date)
                    ->where('is_cancelled', false)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('invoice.room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),
                TextColumn::make('invoice.invoice_no')
                    ->label('Bill No')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('invoice.reservation.guest.name')
                    ->label('Guest')
                    ->searchable(),
                TextColumn::make('method')
                    ->label('Method')
                    ->badge(),
                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('IDR')),
                TextColumn::make('user.name')
                    ->label('Cashier'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No payments today')
            ->emptyStateDescription('No payment transactions recorded for this date.')
            ->emptyStateIcon(Heroicon::Calculator);
    }

    public function loadSummary(): void
    {
        try {
            $date = Carbon::parse($this->reportDate ?? SystemDate::today());

        // Total charges posted today
        $totalCharges = InvoiceItem::where('is_cancelled', false)
            ->whereDate('posting_date', $date)
            ->sum('amount');

        // Total payments received today
        $totalPayments = Payment::where('is_cancelled', false)
            ->whereDate('created_at', $date)
            ->sum('amount');

        // Payment method breakdown
        $paymentMethods = Payment::where('is_cancelled', false)
            ->whereDate('created_at', $date)
            ->select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('method')
            ->get()
            ->map(fn ($item) => [
                'method' => $item->method?->value ?? $item->method ?? 'Unknown',
                'total' => (float) $item->total,
                'count' => $item->count,
            ])
            ->toArray();

        // Cashier breakdown
        $cashierBreakdown = Payment::where('is_cancelled', false)
            ->whereDate('created_at', $date)
            ->select('user_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->with('user')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->user?->name ?? 'System',
                'total' => (float) $item->total,
                'count' => $item->count,
            ])
            ->toArray();

        // Active open invoices count
        $openInvoices = Invoice::whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Reopened])
            ->count();

        // Closed bills today
        $closedToday = Invoice::where('status', InvoiceStatus::Closed)
            ->whereDate('closed_at', $date)
            ->count();

        $this->summaryData = [
            'total_charges' => (float) $totalCharges,
            'total_payments' => (float) $totalPayments,
            'net_difference' => (float) $totalCharges - (float) $totalPayments,
            'payment_methods' => $paymentMethods,
            'cashier_breakdown' => $cashierBreakdown,
            'open_invoices' => $openInvoices,
            'closed_today' => $closedToday,
        ];
        } catch (\Throwable $e) {
            report($e);
            $this->summaryData = ['total_charges' => 0, 'total_payments' => 0, 'net_difference' => 0, 'payment_methods' => [], 'cashier_breakdown' => [], 'open_invoices' => 0, 'closed_today' => 0];
        }
    }

    public function getReportTitle(): string
    {
        return 'Summary Cashier Report';
    }

    public function getExportData(): array
    {
        $date = Carbon::parse($this->reportDate ?? SystemDate::today());
        $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');

        $headers = ['Time', 'Room', 'Bill No', 'Guest', 'Method', 'Reference', 'Amount', 'Cashier'];
        $rows = [];

        $payments = Payment::with(['invoice.reservation.guest', 'invoice.room', 'user'])
            ->whereDate('created_at', $date)
            ->where('is_cancelled', false)
            ->orderByDesc('created_at')
            ->get();

        foreach ($payments as $payment) {
            $rows[] = [
                $payment->created_at?->format('H:i') ?? '',
                $payment->invoice?->room?->room_number ?? '—',
                $payment->invoice?->invoice_no ?? '',
                $payment->invoice?->reservation?->guest?->name ?? '',
                $payment->method?->value ?? $payment->method ?? '',
                $payment->reference_no ?? '—',
                $fmt($payment->amount),
                $payment->user?->name ?? 'System',
            ];
        }

        $summary = [
            ['Total Charges', $fmt($this->summaryData['total_charges'] ?? 0)],
            ['Total Payments', $fmt($this->summaryData['total_payments'] ?? 0)],
            ['Net Difference', $fmt($this->summaryData['net_difference'] ?? 0)],
            ['Open Invoices', (string) ($this->summaryData['open_invoices'] ?? 0)],
            ['Closed Today', (string) ($this->summaryData['closed_today'] ?? 0)],
        ];

        return [
            'headers' => $headers,
            'rows' => $rows,
            'subtitle' => 'Date: ' . $date->format('d M Y'),
            'summary' => $summary,
            'orientation' => 'landscape',
        ];
    }
}
