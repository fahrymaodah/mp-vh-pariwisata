<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Filament\Traits\HasReportExport;
use App\Enums\UserRole;
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
use Illuminate\Support\Facades\DB;
use UnitEnum;

class FoTurnoverReport extends Page
{
    use HasReportExport;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::cashierRoles()) ?? false;
    }

    protected string $view = 'filament.fo.pages.fo-turnover-report';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Banknotes;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 23;

    protected static ?string $navigationLabel = 'FO Turnover';

    protected static ?string $title = 'Front Office Turnover Report';

    protected static ?string $slug = 'fo-turnover-report';

    public ?string $selectedDate = null;

    public array $dailyData = [];

    public array $mtdData = [];

    public array $ytdData = [];

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
        $this->loadReport();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                DatePicker::make('selectedDate')
                    ->label('Report Date')
                    ->default(SystemDate::today())
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadReport()),
            ]),
        ]);
    }

    public function loadReport(): void
    {
        try {
            $date = Carbon::parse($this->selectedDate ?? SystemDate::today());

            $this->dailyData = $this->getRevenueData($date, $date);
            $this->mtdData = $this->getRevenueData($date->copy()->startOfMonth(), $date);
            $this->ytdData = $this->getRevenueData($date->copy()->startOfYear(), $date);
        } catch (\Throwable $e) {
            report($e);
            $empty = ['total_charges' => 0, 'room_revenue' => 0, 'tax_amount' => 0, 'service_amount' => 0, 'payments_received' => 0, 'net_outstanding' => 0, 'department_breakdown' => [], 'start' => '', 'end' => ''];
            $this->dailyData = $this->mtdData = $this->ytdData = $empty;
        }
    }

    protected function getRevenueData(Carbon $start, Carbon $end): array
    {
        // Room revenue (from invoice items where article is room type)
        $roomRevenue = InvoiceItem::where('is_cancelled', false)
            ->whereBetween('posting_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $taxAmount = InvoiceItem::where('is_cancelled', false)
            ->whereBetween('posting_date', [$start->toDateString(), $end->toDateString()])
            ->sum('tax_amount');

        $serviceAmount = InvoiceItem::where('is_cancelled', false)
            ->whereBetween('posting_date', [$start->toDateString(), $end->toDateString()])
            ->sum('service_amount');

        $totalCharges = (float) $roomRevenue + (float) $taxAmount + (float) $serviceAmount;

        // Payments received
        $paymentsReceived = Payment::where('is_cancelled', false)
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->sum('amount');

        // Department breakdown
        $departmentBreakdown = InvoiceItem::query()
            ->select(
                'departments.name as department_name',
                DB::raw('SUM(invoice_items.amount) as total_amount'),
                DB::raw('SUM(invoice_items.tax_amount) as total_tax'),
                DB::raw('SUM(invoice_items.service_amount) as total_service'),
                DB::raw('COUNT(invoice_items.id) as transaction_count'),
            )
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('departments', 'invoice_items.department_id', '=', 'departments.id')
            ->where('invoice_items.is_cancelled', false)
            ->whereBetween('invoice_items.posting_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('departments.name')
            ->orderByDesc('total_amount')
            ->get()
            ->toArray();

        return [
            'total_charges' => $totalCharges,
            'room_revenue' => (float) $roomRevenue,
            'tax_amount' => (float) $taxAmount,
            'service_amount' => (float) $serviceAmount,
            'payments_received' => (float) $paymentsReceived,
            'net_outstanding' => $totalCharges - (float) $paymentsReceived,
            'department_breakdown' => $departmentBreakdown,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    public function getReportTitle(): string
    {
        return 'FO Turnover Report';
    }

    public function getExportData(): array
    {
        $headers = ['Period', 'Department', 'Charges', 'Tax', 'Service', 'Total', 'Trx Count'];
        $rows = [];
        $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');

        foreach (['Daily' => $this->dailyData, 'MTD' => $this->mtdData, 'YTD' => $this->ytdData] as $label => $data) {
            // Summary row
            $rows[] = [
                $label,
                '— TOTAL —',
                $fmt($data['room_revenue']),
                $fmt($data['tax_amount']),
                $fmt($data['service_amount']),
                $fmt($data['total_charges']),
                '',
            ];

            // Department breakdown
            foreach ($data['department_breakdown'] as $dept) {
                $rows[] = [
                    $label,
                    $dept['department_name'] ?? 'Uncategorized',
                    $fmt($dept['total_amount']),
                    $fmt($dept['total_tax']),
                    $fmt($dept['total_service']),
                    $fmt((float) $dept['total_amount'] + (float) $dept['total_tax'] + (float) $dept['total_service']),
                    (string) $dept['transaction_count'],
                ];
            }
        }

        $date = Carbon::parse($this->selectedDate ?? SystemDate::today());

        return [
            'headers' => $headers,
            'rows' => $rows,
            'subtitle' => 'Date: ' . $date->format('d M Y'),
            'orientation' => 'landscape',
        ];
    }
}
