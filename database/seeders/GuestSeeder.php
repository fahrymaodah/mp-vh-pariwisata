<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\GuestType;
use App\Models\Guest;
use App\Models\GuestContact;
use App\Models\GuestMembership;
use App\Models\MembershipCardType;
use App\Models\Segment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuestSeeder extends Seeder
{
    public function run(): void
    {
        // ── Individual Guests ─────────────────────────
        $individuals = [
            [
                'type' => GuestType::Individual,
                'guest_no' => 'I000001',
                'name' => 'Smith',
                'first_name' => 'John',
                'title' => 'Mr',
                'sex' => Gender::Male,
                'address' => '123 Main Street',
                'city' => 'Sydney',
                'zip' => '2000',
                'country' => 'AU',
                'nationality' => 'AU',
                'birth_date' => '1985-03-15',
                'birth_place' => 'Sydney',
                'id_card_no' => 'PA12345678',
                'phone' => '+61-2-9876-5432',
                'email' => 'john.smith@email.com',
                'credit_limit' => 5000000,
                'is_vip' => true,
                'source_booking' => 'Website',
            ],
            [
                'type' => GuestType::Individual,
                'guest_no' => 'I000002',
                'name' => 'Tanaka',
                'first_name' => 'Yuki',
                'title' => 'Mrs',
                'sex' => Gender::Female,
                'address' => '456 Sakura Avenue',
                'city' => 'Tokyo',
                'zip' => '100-0001',
                'country' => 'JP',
                'nationality' => 'JP',
                'birth_date' => '1990-07-22',
                'birth_place' => 'Osaka',
                'id_card_no' => 'JP98765432',
                'phone' => '+81-3-1234-5678',
                'email' => 'yuki.tanaka@email.jp',
                'credit_limit' => 3000000,
                'is_vip' => false,
                'source_booking' => 'Travel Agent',
            ],
            [
                'type' => GuestType::Individual,
                'guest_no' => 'I000003',
                'name' => 'Wijaya',
                'first_name' => 'Putu',
                'title' => 'Mr',
                'sex' => Gender::Male,
                'address' => 'Jl. Sunset Road No. 88',
                'city' => 'Denpasar',
                'zip' => '80361',
                'country' => 'ID',
                'nationality' => 'ID',
                'birth_date' => '1988-12-01',
                'birth_place' => 'Denpasar',
                'id_card_no' => '5171012345678901',
                'phone' => '+62-361-123456',
                'email' => 'putu.wijaya@email.co.id',
                'credit_limit' => 2000000,
                'is_vip' => false,
                'source_booking' => 'Walk-In',
            ],
            [
                'type' => GuestType::Individual,
                'guest_no' => 'I000004',
                'name' => 'Mueller',
                'first_name' => 'Hans',
                'title' => 'Dr',
                'sex' => Gender::Male,
                'address' => 'Berliner Strasse 42',
                'city' => 'Berlin',
                'zip' => '10117',
                'country' => 'DE',
                'nationality' => 'DE',
                'birth_date' => '1975-06-10',
                'birth_place' => 'Munich',
                'id_card_no' => 'DE55667788',
                'phone' => '+49-30-1234567',
                'email' => 'hans.mueller@email.de',
                'credit_limit' => 8000000,
                'is_vip' => true,
                'source_booking' => 'Corporate',
            ],
            [
                'type' => GuestType::Individual,
                'guest_no' => 'I000005',
                'name' => 'Chen',
                'first_name' => 'Wei',
                'title' => 'Mr',
                'sex' => Gender::Male,
                'address' => '789 Dragon Road',
                'city' => 'Shanghai',
                'zip' => '200000',
                'country' => 'CN',
                'nationality' => 'CN',
                'birth_date' => '1992-01-28',
                'birth_place' => 'Beijing',
                'id_card_no' => 'CN112233445566',
                'phone' => '+86-21-8765-4321',
                'email' => 'wei.chen@email.cn',
                'credit_limit' => 4000000,
                'is_vip' => false,
                'source_booking' => 'OTA',
            ],
        ];

        // ── Company Guests ────────────────────────────
        $companies = [
            [
                'type' => GuestType::Company,
                'guest_no' => 'C000001',
                'name' => 'Garuda Indonesia Airlines',
                'company_title' => 'PT',
                'address' => 'Jl. Kebon Sirih No. 44',
                'city' => 'Jakarta',
                'zip' => '10110',
                'country' => 'ID',
                'phone' => '+62-21-2351-9999',
                'fax' => '+62-21-2351-9998',
                'email' => 'corporate@garuda-indonesia.com',
                'credit_limit' => 50000000,
                'discount' => 15,
                'payment_terms' => 'City Ledger 30 Days',
                'price_code' => 'COR-GA',
                'expired_date' => '2027-03-31',
                'source_booking' => 'Sales Call',
            ],
            [
                'type' => GuestType::Company,
                'guest_no' => 'C000002',
                'name' => 'Bank Mandiri',
                'company_title' => 'PT',
                'address' => 'Jl. Jend. Gatot Subroto Kav. 36-38',
                'city' => 'Jakarta',
                'zip' => '12190',
                'country' => 'ID',
                'phone' => '+62-21-5299-7777',
                'email' => 'travel@bankmandiri.co.id',
                'credit_limit' => 100000000,
                'discount' => 20,
                'payment_terms' => 'City Ledger 45 Days',
                'price_code' => 'COR-BM',
                'expired_date' => '2026-12-31',
                'source_booking' => 'Sales Call',
            ],
            [
                'type' => GuestType::Company,
                'guest_no' => 'C000003',
                'name' => 'Google Indonesia',
                'company_title' => 'PT',
                'address' => 'Pacific Century Place, SCBD',
                'city' => 'Jakarta',
                'zip' => '12950',
                'country' => 'ID',
                'phone' => '+62-21-2939-3900',
                'email' => 'travel@google.co.id',
                'credit_limit' => 200000000,
                'discount' => 10,
                'payment_terms' => 'City Ledger 30 Days',
                'price_code' => 'COR-GL',
                'expired_date' => '2027-06-30',
                'source_booking' => 'Corporate RFP',
            ],
        ];

        // ── Travel Agent Guests ───────────────────────
        $travelAgents = [
            [
                'type' => GuestType::TravelAgent,
                'guest_no' => 'T000001',
                'name' => 'Panorama Destination',
                'company_title' => 'PT',
                'address' => 'Jl. Tomang Raya No. 63',
                'city' => 'Jakarta',
                'zip' => '11440',
                'country' => 'ID',
                'phone' => '+62-21-563-8888',
                'email' => 'reservations@panorama.id',
                'credit_limit' => 30000000,
                'discount' => 25,
                'payment_terms' => 'City Ledger 14 Days',
                'price_code' => 'TA-PAN',
                'expired_date' => '2027-01-31',
                'source_booking' => 'Contract',
            ],
            [
                'type' => GuestType::TravelAgent,
                'guest_no' => 'T000002',
                'name' => 'JTB Indonesia',
                'company_title' => 'PT',
                'address' => 'Jl. HR Rasuna Said Kav. C-17',
                'city' => 'Jakarta',
                'zip' => '12940',
                'country' => 'ID',
                'phone' => '+62-21-520-0500',
                'email' => 'booking@jtb.co.id',
                'credit_limit' => 40000000,
                'discount' => 20,
                'payment_terms' => 'City Ledger 30 Days',
                'price_code' => 'TA-JTB',
                'expired_date' => '2026-09-30',
                'source_booking' => 'Contract',
            ],
        ];

        $segments = Segment::active()->pluck('id', 'code')->toArray();

        // Create individual guests
        foreach ($individuals as $data) {
            $guest = Guest::updateOrCreate(
                ['email' => $data['email']],
                $data,
            );

            // Assign segments
            $segmentCode = match (true) {
                $guest->is_vip => 'FIT',
                $guest->source_booking === 'Walk-In' => 'FIT',
                $guest->source_booking === 'OTA' => 'OTA',
                $guest->source_booking === 'Corporate' => 'COR',
                default => 'FIT',
            };

            if (isset($segments[$segmentCode])) {
                $guest->segments()->syncWithoutDetaching([
                    $segments[$segmentCode] => ['is_main' => true],
                ]);
                $guest->update(['main_segment_id' => $segments[$segmentCode]]);
            }
        }

        // Create companies
        $salesUser = \App\Models\User::where('email', 'sales@parhotel.test')->first();

        foreach ($companies as $data) {
            $data['sales_user_id'] = $salesUser?->id;
            $guest = Guest::updateOrCreate(
                ['email' => $data['email']],
                $data,
            );

            if (isset($segments['COR'])) {
                $guest->segments()->syncWithoutDetaching([
                    $segments['COR'] => ['is_main' => true],
                ]);
                $guest->update(['main_segment_id' => $segments['COR']]);
            }
        }

        // Create travel agents
        foreach ($travelAgents as $data) {
            $data['sales_user_id'] = $salesUser?->id;
            $guest = Guest::updateOrCreate(
                ['email' => $data['email']],
                $data,
            );

            if (isset($segments['WHL'])) {
                $guest->segments()->syncWithoutDetaching([
                    $segments['WHL'] => ['is_main' => true],
                ]);
                $guest->update(['main_segment_id' => $segments['WHL']]);
            }
        }

        // ── Add contacts for Company & TA ──────────────
        $garuda = Guest::where('name', 'Garuda Indonesia Airlines')->first();
        if ($garuda) {
            GuestContact::updateOrCreate(
                ['guest_id' => $garuda->id, 'email' => 'dewi@garuda-indonesia.com'],
                [
                    'name' => 'Sari',
                    'first_name' => 'Dewi',
                    'title' => 'Mrs',
                    'department' => 'Corporate Travel',
                    'function' => 'Travel Manager',
                    'extension' => '1234',
                    'email' => 'dewi@garuda-indonesia.com',
                    'is_main' => true,
                ],
            );
            GuestContact::updateOrCreate(
                ['guest_id' => $garuda->id, 'email' => 'budi@garuda-indonesia.com'],
                [
                    'name' => 'Pratama',
                    'first_name' => 'Budi',
                    'title' => 'Mr',
                    'department' => 'Corporate Travel',
                    'function' => 'Travel Coordinator',
                    'extension' => '1235',
                    'email' => 'budi@garuda-indonesia.com',
                    'is_main' => false,
                ],
            );
        }

        $bankMandiri = Guest::where('name', 'Bank Mandiri')->first();
        if ($bankMandiri) {
            GuestContact::updateOrCreate(
                ['guest_id' => $bankMandiri->id, 'email' => 'rina@bankmandiri.co.id'],
                [
                    'name' => 'Kusuma',
                    'first_name' => 'Rina',
                    'title' => 'Ms',
                    'department' => 'General Affairs',
                    'function' => 'Admin Manager',
                    'extension' => '5001',
                    'email' => 'rina@bankmandiri.co.id',
                    'is_main' => true,
                ],
            );
        }

        $panorama = Guest::where('name', 'Panorama Destination')->first();
        if ($panorama) {
            GuestContact::updateOrCreate(
                ['guest_id' => $panorama->id, 'email' => 'agung@panorama.id'],
                [
                    'name' => 'Nugraha',
                    'first_name' => 'Agung',
                    'title' => 'Mr',
                    'department' => 'Reservations',
                    'function' => 'Reservation Manager',
                    'extension' => '200',
                    'email' => 'agung@panorama.id',
                    'is_main' => true,
                ],
            );
        }

        // ── Add membership to VIP guest ────────────────
        $goldType = MembershipCardType::where('name', 'Gold')->first();
        $johnSmith = Guest::where('email', 'john.smith@email.com')->first();

        if ($goldType && $johnSmith) {
            GuestMembership::updateOrCreate(
                ['card_number' => 'GOLD-2026-0001'],
                [
                    'guest_id' => $johnSmith->id,
                    'membership_card_type_id' => $goldType->id,
                    'valid_from' => '2026-01-01',
                    'valid_until' => '2027-12-31',
                    'is_active' => true,
                ],
            );
        }

        $platinumType = MembershipCardType::where('name', 'Platinum')->first();
        $drMueller = Guest::where('email', 'hans.mueller@email.de')->first();

        if ($platinumType && $drMueller) {
            GuestMembership::updateOrCreate(
                ['card_number' => 'PLAT-2026-0001'],
                [
                    'guest_id' => $drMueller->id,
                    'membership_card_type_id' => $platinumType->id,
                    'valid_from' => '2026-01-01',
                    'valid_until' => '2027-06-30',
                    'is_active' => true,
                ],
            );
        }
    }
}
