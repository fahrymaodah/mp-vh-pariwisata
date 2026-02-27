<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\NightAuditStatus;
use App\Enums\PostingType;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Arrangement;
use App\Models\ArrangementLine;
use App\Models\FixCostArticle;
use App\Models\Invoice;
use App\Models\NightAudit;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SystemDate;
use Illuminate\Support\Facades\DB;

class NightAuditService
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {}

    // ── Checklist Validation ─────────────────────────

    /**
     * Run all pre-audit checklist validations.
     *
     * @return array<int, array{step: int, label: string, passed: bool, detail: string}>
     */
    public function runChecklist(): array
    {
        $today = SystemDate::today();

        return [
            $this->checkFoTransactions($today),
            $this->checkRestaurantBills(),
            $this->checkPayments($today),
            $this->checkRoomRevenueBreakdown($today),
            $this->checkTotalRevenue($today),
            $this->checkSegmentCode(),
            $this->checkNationalityCountry(),
            $this->checkOpenedMasterBills(),
            $this->checkInHouseGuest($today),
            $this->checkSummaryCashierReport($today),
        ];
    }

    // ── Midnight Program ─────────────────────────────

    /**
     * Execute the full Night Audit midnight program.
     *
     * @throws \RuntimeException
     */
    public function runMidnightProgram(): NightAudit
    {
        $today = SystemDate::today();
        $systemDate = SystemDate::current();

        // Validate: audit not already run for today
        if (NightAudit::where('audit_date', $today)->where('status', NightAuditStatus::Completed)->exists()) {
            throw new \RuntimeException("Night Audit already completed for {$today}.");
        }

        // Create or get the audit record
        $audit = NightAudit::updateOrCreate(
            ['audit_date' => $today],
            [
                'status' => NightAuditStatus::InProgress,
                'started_at' => now(),
                'user_id' => auth()->id(),
            ]
        );

        try {
            return DB::transaction(function () use ($audit, $today, $systemDate) {
                // 1. Post room charges to all in-house guests
                $postingResult = $this->postRoomCharges($today);

                // 2. Post fix-cost articles
                $fixCostResult = $this->postFixCostArticles($today);

                // 3. Mark no-shows
                $noShowCount = $this->markNoShows($today);

                // 4. Mark 6PM cancellations
                $cancelCount = $this->markSixPmCancellations($today);

                // 5. Update expected departure room status
                $this->updateExpectedDepartures($today);

                // 6. Calculate statistics
                $stats = $this->calculateStatistics();

                // 7. Advance system date
                $nextDate = \Carbon\Carbon::parse($today)->addDay()->toDateString();
                $systemDate->update([
                    'current_date' => $nextDate,
                    'last_night_audit' => $today,
                ]);

                // 8. Update audit record
                $audit->update([
                    'status' => NightAuditStatus::Completed,
                    'completed_at' => now(),
                    'checklist' => $this->runChecklist(),
                    'total_revenue' => $stats['total_revenue'],
                    'total_rooms_occupied' => $stats['rooms_occupied'],
                    'total_rooms_available' => $stats['rooms_available'],
                    'occupancy_rate' => $stats['occupancy_rate'],
                    'notes' => implode("\n", [
                        "Room charges posted: {$postingResult['posted']} reservations",
                        "Fix-cost articles posted: {$fixCostResult['posted']} items",
                        "No-shows marked: {$noShowCount}",
                        "6PM cancellations: {$cancelCount}",
                        "System date advanced to: {$nextDate}",
                    ]),
                ]);

                return $audit->fresh();
            });
        } catch (\Throwable $e) {
            $audit->update([
                'status' => NightAuditStatus::Failed,
                'completed_at' => now(),
                'notes' => "Failed: {$e->getMessage()}",
            ]);

            throw new \RuntimeException("Night Audit failed: {$e->getMessage()}", 0, $e);
        }
    }

    // ── Room Charge Posting ──────────────────────────

    /**
     * Post room charges (lodging article from arrangement) to all checked-in guests.
     *
     * @return array{posted: int, skipped: int, errors: array}
     */
    public function postRoomCharges(string $date): array
    {
        $result = ['posted' => 0, 'skipped' => 0, 'errors' => []];

        $reservations = Reservation::where('status', ReservationStatus::CheckedIn)
            ->with(['arrangement.lodgingArticle', 'arrangement.lines.article', 'room', 'invoices'])
            ->get();

        foreach ($reservations as $reservation) {
            try {
                // Skip complimentary / house use (zero rate)
                if ($reservation->is_complimentary || (float) $reservation->room_rate === 0.0) {
                    $result['skipped']++;

                    continue;
                }

                // Post lodging (room charge) from arrangement
                $arrangement = $reservation->arrangement;

                if ($arrangement && $arrangement->lodgingArticle) {
                    $this->billingService->quickPost(
                        $reservation,
                        $arrangement->lodgingArticle,
                        1,
                        (float) $reservation->room_rate
                    );
                }

                // Post arrangement lines (daily posting type)
                if ($arrangement) {
                    $dailyLines = $arrangement->lines()
                        ->where('posting_type', PostingType::Daily)
                        ->where('included_in_room_rate', false)
                        ->with('article')
                        ->get();

                    foreach ($dailyLines as $line) {
                        if ($line->article) {
                            $qty = $line->qty_always_one ? 1 : ($reservation->adults + $reservation->children);
                            $this->billingService->quickPost(
                                $reservation,
                                $line->article,
                                $qty,
                                (float) $line->amount
                            );
                        }
                    }
                }

                $result['posted']++;
            } catch (\Throwable $e) {
                $result['errors'][] = "Res #{$reservation->reservation_no}: {$e->getMessage()}";
            }
        }

        return $result;
    }

    /**
     * Post fix-cost articles (additional charges per reservation).
     *
     * @return array{posted: int, errors: array}
     */
    public function postFixCostArticles(string $date): array
    {
        $result = ['posted' => 0, 'errors' => []];

        $fixCosts = FixCostArticle::whereHas('reservation', function ($q) {
            $q->where('status', ReservationStatus::CheckedIn);
        })
            ->where('posting_type', PostingType::Daily)
            ->with(['reservation', 'article'])
            ->get();

        foreach ($fixCosts as $fixCost) {
            try {
                if ($fixCost->article && $fixCost->reservation) {
                    $this->billingService->quickPost(
                        $fixCost->reservation,
                        $fixCost->article,
                        $fixCost->qty,
                        (float) $fixCost->price
                    );
                    $result['posted']++;
                }
            } catch (\Throwable $e) {
                $result['errors'][] = "FixCost #{$fixCost->id}: {$e->getMessage()}";
            }
        }

        return $result;
    }

    // ── No-Show & Cancellation ───────────────────────

    /**
     * Mark reservations as no-show if arrival date = today but not checked in.
     */
    public function markNoShows(string $date): int
    {
        $activeStatuses = [
            ReservationStatus::Guaranteed,
            ReservationStatus::Confirmed,
            ReservationStatus::OralConfirmed,
            ReservationStatus::Tentative,
            ReservationStatus::WaitingList,
        ];

        $reservations = Reservation::where('arrival_date', $date)
            ->whereIn('status', $activeStatuses)
            ->get();

        $count = 0;

        foreach ($reservations as $reservation) {
            $reservation->update([
                'status' => ReservationStatus::NoShow,
            ]);

            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'no_show_auto',
                'field_changed' => 'status',
                'old_value' => $reservation->getOriginal('status'),
                'new_value' => ReservationStatus::NoShow->value,
            ]);

            // Release room if assigned
            if ($reservation->room_id) {
                $room = $reservation->room;
                if ($room && $room->status->isOccupied()) {
                    $oldStatus = $room->status;
                    $room->update(['status' => RoomStatus::VacantDirty]);
                    $room->statusLogs()->create([
                        'old_status' => $oldStatus->value,
                        'new_status' => RoomStatus::VacantDirty->value,
                        'changed_by' => auth()->id(),
                    ]);
                }
            }

            $count++;
        }

        return $count;
    }

    /**
     * Mark 6PM release reservations as cancelled after 6 PM.
     */
    public function markSixPmCancellations(string $date): int
    {
        $reservations = Reservation::where('arrival_date', $date)
            ->where('status', ReservationStatus::SixPm)
            ->get();

        $count = 0;

        foreach ($reservations as $reservation) {
            $reservation->update([
                'status' => ReservationStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => '6PM auto-release by Night Audit',
            ]);

            $reservation->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'six_pm_cancel_auto',
                'field_changed' => 'status',
                'old_value' => ReservationStatus::SixPm->value,
                'new_value' => ReservationStatus::Cancelled->value,
            ]);

            $count++;
        }

        return $count;
    }

    // ── Room Status Updates ──────────────────────────

    /**
     * Update rooms of guests departing tomorrow to Expected Departure status.
     */
    public function updateExpectedDepartures(string $today): int
    {
        $tomorrow = \Carbon\Carbon::parse($today)->addDay()->toDateString();

        $departingReservations = Reservation::where('departure_date', $tomorrow)
            ->where('status', ReservationStatus::CheckedIn)
            ->with('room')
            ->get();

        $count = 0;

        foreach ($departingReservations as $reservation) {
            $room = $reservation->room;
            if ($room && $room->status !== RoomStatus::ExpectedDeparture) {
                $oldStatus = $room->status;
                $room->update(['status' => RoomStatus::ExpectedDeparture]);
                $room->statusLogs()->create([
                    'old_status' => $oldStatus->value,
                    'new_status' => RoomStatus::ExpectedDeparture->value,
                    'changed_by' => auth()->id(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    // ── Statistics ────────────────────────────────────

    /**
     * Calculate occupancy statistics for the current date.
     *
     * @return array{total_revenue: float, rooms_occupied: int, rooms_available: int, occupancy_rate: float}
     */
    public function calculateStatistics(): array
    {
        $totalRooms = Room::where('is_active', true)->count();
        $oooRooms = Room::where('status', RoomStatus::OutOfOrder)->count();
        $availableRooms = $totalRooms - $oooRooms;

        $occupiedRooms = Room::where('is_active', true)
            ->whereIn('status', [
                RoomStatus::OccupiedClean,
                RoomStatus::OccupiedDirty,
                RoomStatus::ExpectedDeparture,
            ])
            ->count();

        $todayRevenue = \App\Models\InvoiceItem::whereDate('posting_date', SystemDate::today())
            ->where('is_cancelled', false)
            ->sum('amount');

        $occupancyRate = $availableRooms > 0
            ? round(($occupiedRooms / $availableRooms) * 100, 2)
            : 0;

        return [
            'total_revenue' => (float) $todayRevenue,
            'rooms_occupied' => $occupiedRooms,
            'rooms_available' => $availableRooms,
            'occupancy_rate' => $occupancyRate,
        ];
    }

    // ── Private Checklist Steps ──────────────────────

    private function checkFoTransactions(string $date): array
    {
        $count = \App\Models\InvoiceItem::whereDate('posting_date', $date)
            ->where('is_cancelled', false)
            ->count();

        return [
            'step' => 1,
            'label' => 'Check FO Transactions',
            'passed' => true,
            'detail' => "{$count} transaction(s) posted today.",
        ];
    }

    private function checkRestaurantBills(): array
    {
        $openOutletBills = Invoice::where('status', InvoiceStatus::Open)
            ->whereHas('items', function ($q) {
                $q->whereHas('article', function ($q2) {
                    $q2->whereHas('department', function ($q3) {
                        $q3->whereIn('code', ['FB', 'REST', 'BAR']);
                    });
                });
            })
            ->count();

        return [
            'step' => 2,
            'label' => 'Check Restaurant / Outlet Bills',
            'passed' => $openOutletBills === 0,
            'detail' => $openOutletBills > 0
                ? "{$openOutletBills} open outlet bill(s) remain."
                : 'All outlet bills are settled.',
        ];
    }

    private function checkPayments(string $date): array
    {
        $totalPayments = \App\Models\Payment::whereDate('payment_date', $date)
            ->where('is_cancelled', false)
            ->sum('amount');

        return [
            'step' => 3,
            'label' => 'Check Payments',
            'passed' => true,
            'detail' => 'Total payments today: IDR ' . number_format((float) $totalPayments, 0, ',', '.'),
        ];
    }

    private function checkRoomRevenueBreakdown(string $date): array
    {
        $checkedIn = Reservation::where('status', ReservationStatus::CheckedIn)->count();
        $totalRate = Reservation::where('status', ReservationStatus::CheckedIn)->sum('room_rate');

        return [
            'step' => 4,
            'label' => 'Check Room Revenue Breakdown',
            'passed' => true,
            'detail' => "{$checkedIn} in-house guests, total rate: IDR " . number_format((float) $totalRate, 0, ',', '.'),
        ];
    }

    private function checkTotalRevenue(string $date): array
    {
        $revenue = \App\Models\InvoiceItem::whereDate('posting_date', $date)
            ->where('is_cancelled', false)
            ->sum('amount');

        return [
            'step' => 5,
            'label' => 'Check Total Revenue',
            'passed' => true,
            'detail' => 'Total revenue today: IDR ' . number_format((float) $revenue, 0, ',', '.') . ' (Room Charge not yet posted)',
        ];
    }

    private function checkSegmentCode(): array
    {
        $walkIns = Reservation::where('status', ReservationStatus::CheckedIn)
            ->where('source', 'walk_in')
            ->count();
        $compRooms = Reservation::where('status', ReservationStatus::CheckedIn)
            ->where('is_complimentary', true)
            ->count();

        return [
            'step' => 6,
            'label' => 'Check Segment Code',
            'passed' => true,
            'detail' => "Walk-Ins: {$walkIns}, Complimentary: {$compRooms}",
        ];
    }

    private function checkNationalityCountry(): array
    {
        $missingNationality = Reservation::where('status', ReservationStatus::CheckedIn)
            ->whereHas('guest', function ($q) {
                $q->whereNull('nationality')->orWhere('nationality', '');
            })
            ->count();

        return [
            'step' => 7,
            'label' => 'Check Nationality & Country',
            'passed' => $missingNationality === 0,
            'detail' => $missingNationality > 0
                ? "{$missingNationality} guest(s) missing nationality data."
                : 'All guest nationality data complete.',
        ];
    }

    private function checkOpenedMasterBills(): array
    {
        $openMasterBills = Invoice::where('type', \App\Enums\InvoiceType::MasterBill)
            ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Printed])
            ->count();

        return [
            'step' => 8,
            'label' => 'Check Opened Master Bills',
            'passed' => $openMasterBills === 0,
            'detail' => $openMasterBills > 0
                ? "{$openMasterBills} open master bill(s) — should be closed."
                : 'No open master bills.',
        ];
    }

    private function checkInHouseGuest(string $date): array
    {
        $pendingArrivals = Reservation::where('arrival_date', $date)
            ->whereIn('status', [
                ReservationStatus::Guaranteed,
                ReservationStatus::Confirmed,
                ReservationStatus::OralConfirmed,
            ])
            ->count();

        $pendingDepartures = Reservation::where('departure_date', $date)
            ->where('status', ReservationStatus::CheckedIn)
            ->count();

        $passed = $pendingDepartures === 0;

        return [
            'step' => 9,
            'label' => 'Check In House Guest',
            'passed' => $passed,
            'detail' => "Pending arrivals (→ no-show): {$pendingArrivals}, Pending departures: {$pendingDepartures}",
        ];
    }

    private function checkSummaryCashierReport(string $date): array
    {
        $unsettledInvoices = Invoice::whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Reopened])
            ->where('balance', '!=', 0)
            ->count();

        return [
            'step' => 10,
            'label' => 'Check Summary Cashier Report',
            'passed' => $unsettledInvoices === 0,
            'detail' => $unsettledInvoices > 0
                ? "{$unsettledInvoices} unsettled invoice(s) with outstanding balance."
                : 'All invoices balanced.',
        ];
    }
}
