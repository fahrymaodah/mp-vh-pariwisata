<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ReservationStatus;
use App\Models\Arrangement;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Segment;
use App\Models\SystemDate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::parse(SystemDate::today());

        // Fetch references
        $guests = Guest::all();
        $categories = RoomCategory::all();
        $rooms = Room::where('is_active', true)->get();
        $arrangements = Arrangement::all();
        $segments = Segment::all();
        $admin = User::first();

        $ro = $arrangements->firstWhere('code', 'RO');
        $rb = $arrangements->firstWhere('code', 'RB');

        $fitSeg = $segments->firstWhere('code', 'FIT');
        $corSeg = $segments->firstWhere('code', 'COR');
        $otaSeg = $segments->firstWhere('code', 'OTA');
        $gitSeg = $segments->firstWhere('code', 'GIT');

        $std = $categories->firstWhere('code', 'STD');
        $sup = $categories->firstWhere('code', 'SUP');
        $dlx = $categories->firstWhere('code', 'DLX');
        $jrs = $categories->firstWhere('code', 'JRS');
        $sut = $categories->firstWhere('code', 'SUT');

        $stdRooms = $rooms->where('room_category_id', $std?->id)->values();
        $supRooms = $rooms->where('room_category_id', $sup?->id)->values();
        $dlxRooms = $rooms->where('room_category_id', $dlx?->id)->values();
        $jrsRooms = $rooms->where('room_category_id', $jrs?->id)->values();
        $sutRooms = $rooms->where('room_category_id', $sut?->id)->values();

        $reservations = [
            // 1. Today's arrivals — Guaranteed
            [
                'reservation_no' => 'R00000001',
                'guest_id' => $guests->firstWhere('guest_no', 'I000001')?->id,
                'status' => ReservationStatus::Guaranteed,
                'arrival_date' => $today->toDateString(),
                'departure_date' => $today->copy()->addDays(3)->toDateString(),
                'nights' => 3,
                'adults' => 2,
                'children' => 0,
                'room_category_id' => $dlx?->id,
                'room_id' => $dlxRooms->get(0)?->id,
                'room_qty' => 1,
                'arrangement_id' => $rb?->id,
                'room_rate' => 1000000,
                'currency_code' => 'IDR',
                'segment_id' => $fitSeg?->id,
                'reserved_by' => 'Mr. Budi Santoso',
                'source' => 'phone',
                'created_by' => $admin?->id,
            ],
            // 2. Today's arrival — 6PM Release
            [
                'reservation_no' => 'R00000002',
                'guest_id' => $guests->firstWhere('guest_no', 'I000002')?->id,
                'status' => ReservationStatus::SixPm,
                'arrival_date' => $today->toDateString(),
                'departure_date' => $today->copy()->addDays(2)->toDateString(),
                'nights' => 2,
                'adults' => 1,
                'children' => 0,
                'room_category_id' => $std?->id,
                'room_id' => $stdRooms->get(0)?->id,
                'room_qty' => 1,
                'arrangement_id' => $ro?->id,
                'room_rate' => 500000,
                'currency_code' => 'IDR',
                'segment_id' => $otaSeg?->id,
                'reserved_by' => 'Booking.com',
                'source' => 'ota',
                'created_by' => $admin?->id,
            ],
            // 3. Today's arrival — VIP guest with Incognito
            [
                'reservation_no' => 'R00000003',
                'guest_id' => $guests->firstWhere('guest_no', 'I000003')?->id,
                'status' => ReservationStatus::Confirmed,
                'arrival_date' => $today->toDateString(),
                'departure_date' => $today->copy()->addDays(5)->toDateString(),
                'nights' => 5,
                'adults' => 2,
                'children' => 1,
                'room_category_id' => $sut?->id,
                'room_id' => $sutRooms->get(0)?->id,
                'room_qty' => 1,
                'arrangement_id' => $rb?->id,
                'room_rate' => 2500000,
                'currency_code' => 'IDR',
                'segment_id' => $corSeg?->id,
                'is_incognito' => true,
                'reserved_by' => 'PA Office',
                'source' => 'email',
                'comments' => 'VIP — special welcome amenities',
                'deposit_amount' => 5000000,
                'deposit_paid' => 2500000,
                'deposit_balance' => 2500000,
                'created_by' => $admin?->id,
            ],
            // 4. Already checked in (arrived yesterday)
            [
                'reservation_no' => 'R00000004',
                'guest_id' => $guests->firstWhere('guest_no', 'I000004')?->id,
                'status' => ReservationStatus::CheckedIn,
                'arrival_date' => $today->copy()->subDay()->toDateString(),
                'departure_date' => $today->copy()->addDays(2)->toDateString(),
                'nights' => 3,
                'adults' => 2,
                'children' => 0,
                'room_category_id' => $sup?->id,
                'room_id' => $supRooms->get(0)?->id,
                'room_qty' => 1,
                'arrangement_id' => $rb?->id,
                'room_rate' => 750000,
                'currency_code' => 'IDR',
                'segment_id' => $fitSeg?->id,
                'checked_in_at' => $today->copy()->subDay()->setHour(14),
                'created_by' => $admin?->id,
            ],
            // 5. Departing today
            [
                'reservation_no' => 'R00000005',
                'guest_id' => $guests->firstWhere('guest_no', 'I000005')?->id,
                'status' => ReservationStatus::CheckedIn,
                'arrival_date' => $today->copy()->subDays(2)->toDateString(),
                'departure_date' => $today->toDateString(),
                'nights' => 2,
                'adults' => 1,
                'children' => 0,
                'room_category_id' => $std?->id,
                'room_id' => $stdRooms->get(1)?->id,
                'room_qty' => 1,
                'arrangement_id' => $ro?->id,
                'room_rate' => 500000,
                'currency_code' => 'IDR',
                'segment_id' => $fitSeg?->id,
                'checked_in_at' => $today->copy()->subDays(2)->setHour(15),
                'created_by' => $admin?->id,
            ],
            // 6. Company group reservation (parent)
            [
                'reservation_no' => 'R00000006',
                'guest_id' => $guests->firstWhere('guest_no', 'C000001')?->id,
                'status' => ReservationStatus::Guaranteed,
                'arrival_date' => $today->copy()->addDays(3)->toDateString(),
                'departure_date' => $today->copy()->addDays(6)->toDateString(),
                'nights' => 3,
                'adults' => 2,
                'children' => 0,
                'room_category_id' => $sup?->id,
                'room_id' => null,
                'room_qty' => 5,
                'arrangement_id' => $rb?->id,
                'room_rate' => 700000,
                'currency_code' => 'IDR',
                'segment_id' => $corSeg?->id,
                'group_name' => 'PT Maju Bersama Annual Meeting',
                'is_master_bill' => true,
                'master_bill_receiver' => 'PT Maju Bersama',
                'reserved_by' => 'Ms. Siti (HRD)',
                'source' => 'email',
                'created_by' => $admin?->id,
            ],
            // 7. Travel Agent reservation
            [
                'reservation_no' => 'R00000007',
                'guest_id' => $guests->firstWhere('guest_no', 'T000001')?->id,
                'status' => ReservationStatus::Confirmed,
                'arrival_date' => $today->copy()->addDays(7)->toDateString(),
                'departure_date' => $today->copy()->addDays(10)->toDateString(),
                'nights' => 3,
                'adults' => 2,
                'children' => 0,
                'room_category_id' => $dlx?->id,
                'room_id' => $dlxRooms->get(1)?->id,
                'room_qty' => 1,
                'arrangement_id' => $rb?->id,
                'room_rate' => 900000,
                'currency_code' => 'IDR',
                'segment_id' => $otaSeg?->id,
                'ta_commission' => 90000,
                'reserved_by' => 'Traveloka',
                'source' => 'ota',
                'created_by' => $admin?->id,
            ],
            // 8. Tentative reservation
            [
                'reservation_no' => 'R00000008',
                'guest_id' => $guests->firstWhere('guest_no', 'I000001')?->id,
                'status' => ReservationStatus::Tentative,
                'arrival_date' => $today->copy()->addDays(14)->toDateString(),
                'departure_date' => $today->copy()->addDays(16)->toDateString(),
                'nights' => 2,
                'adults' => 2,
                'children' => 0,
                'room_category_id' => $jrs?->id,
                'room_id' => null,
                'room_qty' => 1,
                'arrangement_id' => $rb?->id,
                'room_rate' => 1500000,
                'currency_code' => 'IDR',
                'segment_id' => $fitSeg?->id,
                'deposit_limit_date' => $today->copy()->addDays(7)->toDateString(),
                'deposit_amount' => 1500000,
                'reserved_by' => 'Mr. Budi Santoso',
                'source' => 'phone',
                'created_by' => $admin?->id,
            ],
            // 9. Cancelled reservation
            [
                'reservation_no' => 'R00000009',
                'guest_id' => $guests->firstWhere('guest_no', 'I000002')?->id,
                'status' => ReservationStatus::Cancelled,
                'arrival_date' => $today->copy()->addDay()->toDateString(),
                'departure_date' => $today->copy()->addDays(3)->toDateString(),
                'nights' => 2,
                'adults' => 1,
                'children' => 0,
                'room_category_id' => $std?->id,
                'room_id' => null,
                'room_qty' => 1,
                'arrangement_id' => $ro?->id,
                'room_rate' => 500000,
                'currency_code' => 'IDR',
                'segment_id' => $otaSeg?->id,
                'cancelled_at' => $today->copy()->subDay(),
                'cancel_reason' => 'Changed travel plans',
                'cancelled_by' => $admin?->id,
                'created_by' => $admin?->id,
            ],
            // 10. Day-use reservation (today)
            [
                'reservation_no' => 'R00000010',
                'guest_id' => $guests->firstWhere('guest_no', 'I000004')?->id,
                'status' => ReservationStatus::Confirmed,
                'arrival_date' => $today->toDateString(),
                'departure_date' => $today->toDateString(),
                'nights' => 0,
                'adults' => 2,
                'children' => 0,
                'room_category_id' => $std?->id,
                'room_id' => $stdRooms->get(2)?->id,
                'room_qty' => 1,
                'arrangement_id' => $ro?->id,
                'room_rate' => 300000,
                'currency_code' => 'IDR',
                'segment_id' => $fitSeg?->id,
                'is_day_use' => true,
                'source' => 'walk_in',
                'created_by' => $admin?->id,
            ],
            // 11. Long stay guest (future)
            [
                'reservation_no' => 'R00000011',
                'guest_id' => $guests->firstWhere('guest_no', 'C000002')?->id,
                'status' => ReservationStatus::Guaranteed,
                'arrival_date' => $today->copy()->addDays(2)->toDateString(),
                'departure_date' => $today->copy()->addDays(32)->toDateString(),
                'nights' => 30,
                'adults' => 1,
                'children' => 0,
                'room_category_id' => $sup?->id,
                'room_id' => $supRooms->get(1)?->id,
                'room_qty' => 1,
                'arrangement_id' => $rb?->id,
                'room_rate' => 600000,
                'currency_code' => 'IDR',
                'is_fix_rate' => true,
                'segment_id' => $corSeg?->id,
                'group_name' => 'PT Sentosa Jaya',
                'reserved_by' => 'HR Dept',
                'source' => 'email',
                'created_by' => $admin?->id,
            ],
            // 12. Waiting list
            [
                'reservation_no' => 'R00000012',
                'guest_id' => $guests->firstWhere('guest_no', 'I000003')?->id,
                'status' => ReservationStatus::WaitingList,
                'arrival_date' => $today->copy()->addDays(5)->toDateString(),
                'departure_date' => $today->copy()->addDays(7)->toDateString(),
                'nights' => 2,
                'adults' => 2,
                'children' => 2,
                'room_category_id' => $sut?->id,
                'room_id' => null,
                'room_qty' => 1,
                'arrangement_id' => $rb?->id,
                'room_rate' => 2500000,
                'currency_code' => 'IDR',
                'segment_id' => $fitSeg?->id,
                'reserved_by' => 'Website',
                'source' => 'website',
                'flight_no' => 'GA 123',
                'eta' => '10:30',
                'is_pickup' => true,
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($reservations as $data) {
            Reservation::create($data);
        }

        // Create room sharer for reservation 4 (checked-in)
        $parentRes = Reservation::where('reservation_no', 'R00000004')->first();
        if ($parentRes) {
            Reservation::create([
                'reservation_no' => 'R00000013',
                'guest_id' => $guests->firstWhere('guest_no', 'I000001')?->id,
                'status' => ReservationStatus::CheckedIn,
                'arrival_date' => $parentRes->arrival_date,
                'departure_date' => $parentRes->departure_date,
                'nights' => $parentRes->nights,
                'adults' => 1,
                'children' => 0,
                'room_category_id' => $parentRes->room_category_id,
                'room_id' => $parentRes->room_id,
                'room_qty' => 0,
                'arrangement_id' => $parentRes->arrangement_id,
                'room_rate' => 0,
                'is_room_sharer' => true,
                'parent_reservation_id' => $parentRes->id,
                'checked_in_at' => $parentRes->checked_in_at,
                'created_by' => $admin?->id,
            ]);
        }

        // Create reservation logs for the created reservations
        foreach (Reservation::all() as $res) {
            $res->logs()->create([
                'user_id' => $admin?->id,
                'action' => 'created',
                'field_changed' => null,
                'old_value' => null,
                'new_value' => "Reservation {$res->reservation_no} created (seeder)",
            ]);
        }
    }
}
