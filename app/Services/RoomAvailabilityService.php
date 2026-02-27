<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Reservation;
use App\Models\SystemDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RoomAvailabilityService
{
    /**
     * Get available rooms for a date range and optional category.
     */
    public function getAvailableRooms(
        string $arrivalDate,
        string $departureDate,
        ?int $categoryId = null,
        ?int $excludeReservationId = null,
    ): Collection {
        $query = Room::query()
            ->where('is_active', true)
            ->whereDoesntHave('reservations', function ($q) use ($arrivalDate, $departureDate, $excludeReservationId) {
                $q->where(function ($q2) use ($arrivalDate, $departureDate) {
                    $q2->where('arrival_date', '<', $departureDate)
                        ->where('departure_date', '>', $arrivalDate);
                })
                ->whereIn('status', [
                    ReservationStatus::Guaranteed,
                    ReservationStatus::SixPm,
                    ReservationStatus::OralConfirmed,
                    ReservationStatus::Tentative,
                    ReservationStatus::WaitingList,
                    ReservationStatus::Confirmed,
                    ReservationStatus::CheckedIn,
                ]);

                if ($excludeReservationId) {
                    $q->where('reservations.id', '!=', $excludeReservationId);
                }
            });

        if ($categoryId) {
            $query->where('room_category_id', $categoryId);
        }

        return $query->with('category')->orderBy('room_number')->get();
    }

    /**
     * Check if a specific room is available for a date range.
     */
    public function isRoomAvailable(
        int $roomId,
        string $arrivalDate,
        string $departureDate,
        ?int $excludeReservationId = null,
    ): bool {
        $conflict = Reservation::query()
            ->where('room_id', $roomId)
            ->where('arrival_date', '<', $departureDate)
            ->where('departure_date', '>', $arrivalDate)
            ->whereIn('status', [
                ReservationStatus::Guaranteed,
                ReservationStatus::SixPm,
                ReservationStatus::OralConfirmed,
                ReservationStatus::Tentative,
                ReservationStatus::WaitingList,
                ReservationStatus::Confirmed,
                ReservationStatus::CheckedIn,
            ]);

        if ($excludeReservationId) {
            $conflict->where('id', '!=', $excludeReservationId);
        }

        return ! $conflict->exists();
    }

    /**
     * Get availability summary per category for a date range.
     * Returns: [category_id => ['total' => X, 'reserved' => Y, 'available' => Z]]
     */
    public function getAvailabilitySummary(
        string $startDate,
        string $endDate,
    ): Collection {
        $categories = RoomCategory::with('rooms')->get();

        return $categories->map(function (RoomCategory $category) use ($startDate, $endDate) {
            $totalRooms = $category->rooms()->where('is_active', true)->count();

            $reservedRooms = Reservation::query()
                ->where('room_category_id', $category->id)
                ->where('arrival_date', '<', $endDate)
                ->where('departure_date', '>', $startDate)
                ->whereIn('status', [
                    ReservationStatus::Guaranteed,
                    ReservationStatus::SixPm,
                    ReservationStatus::OralConfirmed,
                    ReservationStatus::Tentative,
                    ReservationStatus::WaitingList,
                    ReservationStatus::Confirmed,
                    ReservationStatus::CheckedIn,
                ])
                ->where('is_room_sharer', false)
                ->count();

            return [
                'category_id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'total' => $totalRooms,
                'reserved' => $reservedRooms,
                'available' => max(0, $totalRooms - $reservedRooms),
                'base_rate' => $category->base_rate,
            ];
        });
    }

    /**
     * Get daily availability for a category over a date range.
     * Returns array keyed by date with availability counts.
     */
    public function getDailyAvailability(
        int $categoryId,
        string $startDate,
        string $endDate,
    ): array {
        $totalRooms = Room::where('room_category_id', $categoryId)
            ->where('is_active', true)
            ->count();

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();

            $reserved = Reservation::query()
                ->where('room_category_id', $categoryId)
                ->where('arrival_date', '<=', $dateStr)
                ->where('departure_date', '>', $dateStr)
                ->whereIn('status', [
                    ReservationStatus::Guaranteed,
                    ReservationStatus::SixPm,
                    ReservationStatus::OralConfirmed,
                    ReservationStatus::Tentative,
                    ReservationStatus::WaitingList,
                    ReservationStatus::Confirmed,
                    ReservationStatus::CheckedIn,
                ])
                ->where('is_room_sharer', false)
                ->count();

            $dates[$dateStr] = [
                'total' => $totalRooms,
                'reserved' => $reserved,
                'available' => max(0, $totalRooms - $reserved),
                'occupancy_pct' => $totalRooms > 0 ? round(($reserved / $totalRooms) * 100, 1) : 0,
            ];

            $current->addDay();
        }

        return $dates;
    }

    /**
     * Get today's arrival list (ARL).
     */
    public function getArrivalReservationList(?string $date = null): Collection
    {
        $date = $date ?? SystemDate::today();

        return Reservation::with(['guest', 'roomCategory', 'room', 'arrangement', 'segment'])
            ->where('arrival_date', $date)
            ->whereNotIn('status', [
                ReservationStatus::Cancelled,
                ReservationStatus::NoShow,
                ReservationStatus::CheckedOut,
            ])
            ->orderBy('guest_id')
            ->get();
    }

    /**
     * Get today's departure list.
     */
    public function getDepartureList(?string $date = null): Collection
    {
        $date = $date ?? SystemDate::today();

        return Reservation::with(['guest', 'room', 'roomCategory'])
            ->where('departure_date', $date)
            ->where('status', ReservationStatus::CheckedIn)
            ->orderBy('room_id')
            ->get();
    }
}
