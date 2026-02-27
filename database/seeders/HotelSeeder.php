<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        Hotel::updateOrCreate(
            ['name' => 'PAR Hotel'],
            [
                'address' => 'Jl. Raya Kuta No. 123',
                'city' => 'Denpasar',
                'country' => 'Indonesia',
                'phone' => '+62 361 123456',
                'fax' => '+62 361 123457',
                'email' => 'info@parhotel.test',
                'website' => 'https://parhotel.test',
                'checkout_time' => '12:00',
                'currency_code' => 'IDR',
                'tax_percentage' => 11.00,
                'service_percentage' => 10.00,
            ]
        );
    }
}
