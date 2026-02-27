<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ArticleType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Models\Article;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\SystemDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingService
{
    // ── Article Posting ──────────────────────────────

    /**
     * Post a sales article to an invoice.
     */
    public function postArticle(
        Invoice $invoice,
        Article $article,
        int $qty = 1,
        ?float $rate = null,
    ): InvoiceItem {
        return DB::transaction(function () use ($invoice, $article, $qty, $rate) {
            $unitRate = $rate ?? (float) $article->default_price;
            $amount = $unitRate * $qty;

            // Calculate tax & service if applicable
            $taxAmount = $article->tax_inclusive ? 0 : round($amount * 0.11, 2);
            $serviceAmount = $article->tax_inclusive ? 0 : round($amount * 0.10, 2);

            $item = InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'article_id' => $article->id,
                'department_id' => $article->department_id,
                'description' => $article->name,
                'qty' => $qty,
                'rate' => $unitRate,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'service_amount' => $serviceAmount,
                'posting_date' => SystemDate::today(),
                'user_id' => Auth::id(),
            ]);

            $this->recalculateInvoice($invoice);

            return $item;
        });
    }

    /**
     * Post a payment to an invoice.
     */
    public function postPayment(
        Invoice $invoice,
        Article $article,
        PaymentMethod $method,
        float $amount,
        ?string $referenceNo = null,
        string $currencyCode = 'IDR',
        float $exchangeRate = 1.0,
    ): Payment {
        return DB::transaction(function () use ($invoice, $article, $method, $amount, $referenceNo, $currencyCode, $exchangeRate) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'article_id' => $article->id,
                'method' => $method,
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'exchange_rate' => $exchangeRate,
                'reference_no' => $referenceNo,
                'user_id' => Auth::id(),
            ]);

            $this->recalculateInvoice($invoice);

            return $payment;
        });
    }

    // ── Cancellation ─────────────────────────────────

    /**
     * Cancel a sales item with mandatory reason.
     */
    public function cancelItem(InvoiceItem $item, string $reason): InvoiceItem
    {
        return DB::transaction(function () use ($item, $reason) {
            $item->update([
                'is_cancelled' => true,
                'cancel_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
            ]);

            $this->recalculateInvoice($item->invoice);

            return $item->fresh();
        });
    }

    /**
     * Cancel a payment with mandatory reason.
     */
    public function cancelPayment(Payment $payment, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment->update([
                'is_cancelled' => true,
                'cancel_reason' => $reason,
            ]);

            $this->recalculateInvoice($payment->invoice);

            return $payment->fresh();
        });
    }

    // ── Bill Transfer / Splitting ────────────────────

    /**
     * Create a new bill split for the same reservation.
     */
    public function createNewBill(Reservation $reservation): Invoice
    {
        return Invoice::create([
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $reservation->room_id,
            'type' => InvoiceType::Guest,
            'status' => InvoiceStatus::Open,
            'bill_address' => $reservation->guest?->full_name,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Transfer items from one invoice to another.
     *
     * @param  array<int>  $itemIds
     */
    public function transferItems(array $itemIds, Invoice $fromInvoice, Invoice $toInvoice): int
    {
        return DB::transaction(function () use ($itemIds, $fromInvoice, $toInvoice) {
            $count = InvoiceItem::whereIn('id', $itemIds)
                ->where('invoice_id', $fromInvoice->id)
                ->where('is_cancelled', false)
                ->update([
                    'invoice_id' => $toInvoice->id,
                    'transferred_from_invoice_id' => $fromInvoice->id,
                    'transferred_to_invoice_id' => $toInvoice->id,
                ]);

            $this->recalculateInvoice($fromInvoice);
            $this->recalculateInvoice($toInvoice);

            return $count;
        });
    }

    // ── Print / Close / Reopen ───────────────────────

    /**
     * Mark invoice as printed.
     */
    public function markPrinted(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status' => InvoiceStatus::Printed,
            'printed_at' => now(),
        ]);

        return $invoice->fresh();
    }

    /**
     * Close a balanced invoice.
     */
    public function closeInvoice(Invoice $invoice): Invoice
    {
        if ((float) $invoice->balance !== 0.0) {
            throw new \RuntimeException('Cannot close invoice with outstanding balance.');
        }

        $invoice->update([
            'status' => InvoiceStatus::Closed,
            'closed_at' => now(),
        ]);

        return $invoice->fresh();
    }

    /**
     * Reopen a closed invoice.
     */
    public function reopenInvoice(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status' => InvoiceStatus::Reopened,
            'closed_at' => null,
        ]);

        return $invoice->fresh();
    }

    // ── NSG (Non-Stay Guest) ─────────────────────────

    /**
     * Create an NSG invoice.
     */
    public function createNsgInvoice(int $guestId, int $departmentId): Invoice
    {
        return Invoice::create([
            'reservation_id' => null,
            'guest_id' => $guestId,
            'room_id' => null,
            'type' => InvoiceType::NonStayGuest,
            'status' => InvoiceStatus::Open,
            'created_by' => Auth::id(),
        ]);
    }

    // ── Deposit ──────────────────────────────────────

    /**
     * Record a deposit payment for a reservation.
     */
    public function recordDeposit(
        Reservation $reservation,
        float $amount,
        string $paymentMethod,
        ?string $voucherNo = null,
    ): Deposit {
        return DB::transaction(function () use ($reservation, $amount, $paymentMethod, $voucherNo) {
            $deposit = Deposit::create([
                'reservation_id' => $reservation->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'voucher_no' => $voucherNo,
                'payment_date' => SystemDate::today(),
                'user_id' => Auth::id(),
            ]);

            // Update reservation deposit totals
            $totalPaid = $reservation->deposits()->sum('amount');
            $reservation->update([
                'deposit_paid' => $totalPaid,
                'deposit_balance' => (float) $reservation->deposit_amount - $totalPaid,
            ]);

            return $deposit;
        });
    }

    // ── Quick Posting ────────────────────────────────

    /**
     * Quick-post an article to a guest's open invoice.
     */
    public function quickPost(Reservation $reservation, Article $article, int $qty = 1, ?float $rate = null): InvoiceItem
    {
        $invoice = $reservation->invoices()
            ->where('status', InvoiceStatus::Open)
            ->first();

        if (! $invoice) {
            $invoice = $this->createNewBill($reservation);
        }

        return $this->postArticle($invoice, $article, $qty, $rate);
    }

    // ── Recalculate ──────────────────────────────────

    /**
     * Recalculate invoice totals from items and payments.
     */
    public function recalculateInvoice(Invoice $invoice): void
    {
        $totalSales = $invoice->items()
            ->where('is_cancelled', false)
            ->sum('amount');

        $totalPayment = $invoice->payments()
            ->where('is_cancelled', false)
            ->sum('amount');

        $invoice->update([
            'total_sales' => $totalSales,
            'total_payment' => $totalPayment,
            'balance' => $totalSales - $totalPayment,
        ]);
    }
}
