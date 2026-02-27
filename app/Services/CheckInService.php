<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SystemDate;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    // ── Individual Check-In ──────────────────────────

    /**
     * Process check-in for a single reservation.
     *
     * @throws \RuntimeException
     */
    public function checkIn(Reservation $reservation, ?int $roomId = null): Reservation
    {
        $roomId = $roomId ?? $reservation->room_id;

        $this->validateCheckIn($reservation, $roomId);

        $room = Room::findOrFail($roomId);

        return DB::transaction(function () use ($reservation, $room) {
            // 1. Update reservation status
            $reservation->update([
                'status' => ReservationStatus::CheckedIn,
                'room_id' => $room->id,
                'checked_in_at' => now(),
            ]);

            // 2. Update room status → Occupied Dirty
            $oldStatus = $room->status;
            $room->update([
                'status' => RoomStatus::OccupiedDirty,
            ]);

            // 3. Log room status change
            $room->statusLogs()->create([
                'old_status' => $oldStatus->value,
                'new_status' => RoomStatus::OccupiedDirty->value,
                'changed_by' => auth()->id(),
            ]);

            // 4. Create invoice
            $this->createInvoice($reservation, $room);

            // 5. Log reservation action
            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'checked_in',
                'field_changed' => 'status',
                'old_value' => 'active',
                'new_value' => ReservationStatus::CheckedIn->value,
            ]);

            return $reservation->fresh();
        });
    }

    // ── Quick Check-In (Walk-In) ─────────────────────

    /**
     * Create reservation and check-in simultaneously (walk-in guest).
     *
     * @throws \RuntimeException
     */
    public function quickCheckIn(array $reservationData, ?float $depositAmount = null): Reservation
    {
        $roomId = $reservationData['room_id'] ?? null;

        if (! $roomId) {
            throw new \RuntimeException('Room must be assigned for Quick Check-In.');
        }

        $room = Room::findOrFail($roomId);

        if (! $room->status->isAvailable()) {
            throw new \RuntimeException("Room {$room->room_number} is not available (status: {$room->status->label()}).");
        }

        return DB::transaction(function () use ($reservationData, $room, $depositAmount) {
            // 1. Create reservation
            $reservationData['status'] = ReservationStatus::CheckedIn;
            $reservationData['checked_in_at'] = now();
            $reservationData['source'] = $reservationData['source'] ?? 'walk_in';
            $reservationData['arrival_date'] = $reservationData['arrival_date'] ?? SystemDate::today();
            $reservationData['created_by'] = auth()->id();

            $reservation = Reservation::create($reservationData);

            // 2. Update room status
            $oldStatus = $room->status;
            $room->update(['status' => RoomStatus::OccupiedDirty]);

            $room->statusLogs()->create([
                'old_status' => $oldStatus->value,
                'new_status' => RoomStatus::OccupiedDirty->value,
                'changed_by' => auth()->id(),
            ]);

            // 3. Create invoice
            $this->createInvoice($reservation, $room);

            // 4. Process deposit if provided
            if ($depositAmount && $depositAmount > 0) {
                Deposit::create([
                    'reservation_id' => $reservation->id,
                    'amount' => $depositAmount,
                    'payment_method' => 'cash',
                    'payment_date' => now()->toDateString(),
                    'user_id' => auth()->id(),
                ]);

                $reservation->update([
                    'deposit_paid' => $depositAmount,
                    'deposit_balance' => ($reservation->deposit_amount ?? 0) - $depositAmount,
                ]);
            }

            // 5. Log
            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'quick_check_in',
                'field_changed' => 'status',
                'old_value' => null,
                'new_value' => ReservationStatus::CheckedIn->value,
            ]);

            return $reservation->fresh();
        });
    }

    // ── Group Check-In (Automatic) ───────────────────

    /**
     * Check-in all members of a group reservation at once.
     *
     * @return array{success: int, failed: int, errors: array}
     */
    public function groupAutoCheckIn(Reservation $parentReservation): array
    {
        $children = $parentReservation->childReservations()
            ->whereIn('status', [
                ReservationStatus::Guaranteed,
                ReservationStatus::SixPm,
                ReservationStatus::OralConfirmed,
                ReservationStatus::Confirmed,
                ReservationStatus::Tentative,
            ])
            ->get();

        $result = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($children as $child) {
            try {
                $this->checkIn($child);
                $result['success']++;
            } catch (\RuntimeException $e) {
                $result['failed']++;
                $result['errors'][] = "Res #{$child->reservation_no}: {$e->getMessage()}";
            }
        }

        // Also check-in parent if it has a room assigned
        if ($parentReservation->room_id && $parentReservation->status !== ReservationStatus::CheckedIn) {
            try {
                $this->checkIn($parentReservation);
                $result['success']++;
            } catch (\RuntimeException $e) {
                $result['failed']++;
                $result['errors'][] = "Parent #{$parentReservation->reservation_no}: {$e->getMessage()}";
            }
        }

        return $result;
    }

    // ── Reactivate Reservation ───────────────────────

    /**
     * Reactivate a cancelled or no-show reservation.
     *
     * @throws \RuntimeException
     */
    public function reactivateReservation(Reservation $reservation): Reservation
    {
        if (! in_array($reservation->status, [ReservationStatus::Cancelled, ReservationStatus::NoShow])) {
            throw new \RuntimeException('Only cancelled or no-show reservations can be reactivated.');
        }

        return DB::transaction(function () use ($reservation) {
            $oldStatus = $reservation->status;

            $reservation->update([
                'status' => ReservationStatus::Confirmed,
                'cancelled_at' => null,
                'cancel_reason' => null,
                'cancelled_by' => null,
            ]);

            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'reactivated',
                'field_changed' => 'status',
                'old_value' => $oldStatus->value,
                'new_value' => ReservationStatus::Confirmed->value,
            ]);

            return $reservation->fresh();
        });
    }

    // ── Re-Check-In (Accidental C/O) ────────────────

    /**
     * Re-check-in a guest that was accidentally checked out.
     * Conditions: room must still be available, no new transactions on bill.
     *
     * @throws \RuntimeException
     */
    public function reCheckIn(Reservation $reservation): Reservation
    {
        if ($reservation->status !== ReservationStatus::CheckedOut) {
            throw new \RuntimeException('Only checked-out reservations can be re-checked-in.');
        }

        $room = $reservation->room;

        if (! $room) {
            throw new \RuntimeException('Reservation has no room assigned.');
        }

        if (! $room->status->isAvailable()) {
            throw new \RuntimeException("Room {$room->room_number} is no longer available (status: {$room->status->label()}).");
        }

        // Check no new transactions on the bill after checkout
        $invoice = $reservation->invoices()
            ->where('status', InvoiceStatus::Closed)
            ->latest()
            ->first();

        if ($invoice && $invoice->items()->where('created_at', '>', $reservation->checked_out_at)->exists()) {
            throw new \RuntimeException('Cannot re-check-in: new transactions have been posted on the bill after check-out.');
        }

        return DB::transaction(function () use ($reservation, $room, $invoice) {
            // 1. Revert reservation status
            $reservation->update([
                'status' => ReservationStatus::CheckedIn,
                'checked_out_at' => null,
            ]);

            // 2. Revert room status
            $oldStatus = $room->status;
            $room->update(['status' => RoomStatus::OccupiedDirty]);

            $room->statusLogs()->create([
                'old_status' => $oldStatus->value,
                'new_status' => RoomStatus::OccupiedDirty->value,
                'changed_by' => auth()->id(),
            ]);

            // 3. Reopen invoice if exists
            if ($invoice) {
                $invoice->update([
                    'status' => InvoiceStatus::Reopened,
                    'closed_at' => null,
                ]);
            }

            // 4. Log
            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 're_checked_in',
                'field_changed' => 'status',
                'old_value' => ReservationStatus::CheckedOut->value,
                'new_value' => ReservationStatus::CheckedIn->value,
            ]);

            return $reservation->fresh();
        });
    }

    // ── Private Helpers ──────────────────────────────

    /**
     * Validate that a reservation can be checked in.
     *
     * @throws \RuntimeException
     */
    private function validateCheckIn(Reservation $reservation, ?int $roomId): void
    {
        // Must have active status
        $allowedStatuses = [
            ReservationStatus::Guaranteed,
            ReservationStatus::SixPm,
            ReservationStatus::OralConfirmed,
            ReservationStatus::Tentative,
            ReservationStatus::WaitingList,
            ReservationStatus::Confirmed,
        ];

        if (! in_array($reservation->status, $allowedStatuses)) {
            throw new \RuntimeException("Cannot check-in reservation with status: {$reservation->status->label()}");
        }

        if (! $roomId) {
            throw new \RuntimeException('Room must be assigned before check-in.');
        }

        $room = Room::find($roomId);
        if (! $room) {
            throw new \RuntimeException('Room not found.');
        }

        if (! $room->status->isAvailable()) {
            throw new \RuntimeException("Room {$room->room_number} is not available (status: {$room->status->label()}).");
        }
    }

    /**
     * Create an invoice for the checked-in reservation.
     */
    private function createInvoice(Reservation $reservation, Room $room): Invoice
    {
        return Invoice::create([
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $room->id,
            'type' => InvoiceType::Guest,
            'status' => InvoiceStatus::Open,
            'bill_address' => $reservation->guest?->full_address ?? '',
            'total_sales' => 0,
            'total_payment' => 0,
            'balance' => 0,
            'created_by' => auth()->id(),
        ]);
    }
}
