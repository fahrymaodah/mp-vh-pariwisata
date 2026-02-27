<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\BillingService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PrintInvoicePage extends Page
{
    protected string $view = 'filament.fo.pages.print-invoice';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Printer;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'print-invoice/{record}/{format}';

    public ?Invoice $record = null;

    public string $format = 'regular';

    public function mount(int $record, string $format = 'regular'): void
    {
        $this->record = Invoice::with([
            'reservation.guest',
            'reservation.arrangement',
            'room',
            'items.article',
            'items.department',
            'payments.article',
            'guest',
        ])->findOrFail($record);

        $this->format = in_array($format, ['regular', 'oneline', 'summary']) ? $format : 'regular';

        // Mark as printed
        app(BillingService::class)->markPrinted($this->record);
    }

    public function getTitle(): string
    {
        $formatLabel = match ($this->format) {
            'regular' => 'Regular Final Folio',
            'oneline' => 'One Line Folio',
            'summary' => 'Summary By Article',
            default => 'Invoice',
        };

        return "{$formatLabel} — {$this->record->invoice_no}";
    }

    /**
     * Get active (non-cancelled) items.
     */
    public function getActiveItemsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->record->items()->where('is_cancelled', false)->orderBy('posting_date')->get();
    }

    /**
     * Get active payments.
     */
    public function getActivePaymentsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->record->payments()->where('is_cancelled', false)->get();
    }

    /**
     * Summary by article — group items by article_id.
     */
    public function getSummaryByArticleProperty(): \Illuminate\Support\Collection
    {
        return $this->record->items()
            ->where('is_cancelled', false)
            ->selectRaw('article_id, description, SUM(qty) as total_qty, SUM(amount) as total_amount')
            ->groupBy('article_id', 'description')
            ->orderBy('article_id')
            ->get();
    }

    /**
     * One-line folio — group by department.
     */
    public function getOneLineSummaryProperty(): \Illuminate\Support\Collection
    {
        return $this->record->items()
            ->where('is_cancelled', false)
            ->join('departments', 'invoice_items.department_id', '=', 'departments.id')
            ->selectRaw('departments.name as dept_name, SUM(invoice_items.amount) as total_amount')
            ->groupBy('departments.name')
            ->orderBy('departments.name')
            ->get();
    }

    public static function getRoutePath(\Filament\Panel|string|null $panel = null): string
    {
        return '/print-invoice/{record}/{format}';
    }
}
