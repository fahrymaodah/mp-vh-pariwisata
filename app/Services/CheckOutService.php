<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Invoice;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class CheckOutService
{
    // ── Individual Check-Out ─────────────────────────

    /**
     * Process check-out for a single reservation.
     * Prerequisites: all invoices must be closed (printed + balanced).
     *
     * @throws \RuntimeException
     */
    public function checkOut(Reservation $reservation): Reservation
    {
        $this->validateCheckOut($reservation);

        return DB::transaction(function () use ($reservation) {
            $room = $reservation->room;

            // 1. Update reservation status
            $reservation->update([
                'status' => ReservationStatus::CheckedOut,
                'checked_out_at' => now(),
            ]);

            // 2. Update room status → Vacant Dirty
            if ($room) {
                $oldStatus = $room->status;
                $room->update([
                    'status' => RoomStatus::VacantDirty,
                ]);

                // 3. Log room status change
                $room->statusLogs()->create([
                    'old_status' => $oldStatus->value,
                    'new_status' => RoomStatus::VacantDirty->value,
                    'changed_by' => auth()->id(),
                ]);
            }

            // 4. Close any remaining open/printed invoices that have zero balance
            $reservation->invoices()
                ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed, InvoiceStatus::Reopened])
                ->where('balance', 0)
                ->each(function (Invoice $invoice) {
                    $invoice->update([
                        'status' => InvoiceStatus::Closed,
                        'closed_at' => now(),
                    ]);
                });

            // 5. Log reservation action
            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'checked_out',
                'field_changed' => 'status',
                'old_value' => ReservationStatus::CheckedIn->value,
                'new_value' => ReservationStatus::CheckedOut->value,
            ]);

            return $reservation->fresh();
        });
    }

    // ── Group Check-Out (Automatic) ──────────────────

    /**
     * Check-out all checked-in members of a group reservation at once.
     *
     * @return array{success: int, failed: int, errors: array}
     */
    public function groupAutoCheckOut(Reservation $parentReservation): array
    {
        $children = $parentReservation->childReservations()
            ->where('status', ReservationStatus::CheckedIn)
            ->get();

        $result = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($children as $child) {
            try {
                $this->checkOut($child);
                $result['success']++;
            } catch (\RuntimeException $e) {
                $result['failed']++;
                $result['errors'][] = "Res #{$child->reservation_no}: {$e->getMessage()}";
            }
        }

        // Also check-out parent if it's checked in
        if ($parentReservation->status === ReservationStatus::CheckedIn) {
            try {
                $this->checkOut($parentReservation);
                $result['success']++;
            } catch (\RuntimeException $e) {
                $result['failed']++;
                $result['errors'][] = "Parent #{$parentReservation->reservation_no}: {$e->getMessage()}";
            }
        }

        return $result;
    }

    // ── Validation ───────────────────────────────────

    /**
     * Validate that a reservation can be checked out.
     * All invoices must be printed & balanced (closed) or have zero balance.
     *
     * @throws \RuntimeException
     */
    public function validateCheckOut(Reservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::CheckedIn) {
            throw new \RuntimeException("Cannot check-out reservation with status: {$reservation->status->label()}");
        }

        if (! $reservation->room) {
            throw new \RuntimeException('Reservation has no room assigned.');
        }

        // Check all invoices are settled (closed or zero balance)
        $unsettledInvoices = $reservation->invoices()
            ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed, InvoiceStatus::Reopened])
            ->where('balance', '!=', 0)
            ->count();

        if ($unsettledInvoices > 0) {
            throw new \RuntimeException(
                "Cannot check-out: {$unsettledInvoices} invoice(s) have outstanding balance. " .
                'All bills must be settled before check-out.'
            );
        }
    }
}
