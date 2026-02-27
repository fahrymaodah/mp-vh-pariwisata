<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Arrangement;
use App\Models\PublishRate;
use App\Models\RoomCategory;
use Illuminate\Database\Seeder;

class PublishRateSeeder extends Seeder
{
    public function run(): void
    {
        $categories = RoomCategory::all()->keyBy('code');
        $roArrangement = Arrangement::where('code', 'RO')->first();
        $rbArrangement = Arrangement::where('code', 'RB')->first();

        $startDate = now()->startOfYear()->toDateString();
        $endDate = now()->endOfYear()->toDateString();

        // Publish rates per category for RO and RB
        $rates = [
            'STD' => ['single' => 500000, 'double' => 550000, 'triple' => null, 'quad' => null],
            'SUP' => ['single' => 750000, 'double' => 800000, 'triple' => null, 'quad' => null],
            'DLX' => ['single' => 1000000, 'double' => 1100000, 'triple' => null, 'quad' => null],
            'JRS' => ['single' => 1500000, 'double' => 1600000, 'triple' => 1700000, 'quad' => null],
            'SUT' => ['single' => 2500000, 'double' => 2700000, 'triple' => 2900000, 'quad' => 3100000],
            'PRS' => ['single' => 5000000, 'double' => 5500000, 'triple' => 6000000, 'quad' => 6500000],
        ];

        foreach ($rates as $catCode => $rate) {
            $category = $categories[$catCode];

            // RO rate
            if ($roArrangement) {
                PublishRate::updateOrCreate(
                    [
                        'room_category_id' => $category->id,
                        'arrangement_id' => $roArrangement->id,
                        'day_of_week' => 0,
                        'start_date' => $startDate,
                    ],
                    [
                        'end_date' => $endDate,
                        'rate_single' => $rate['single'],
                        'rate_double' => $rate['double'],
                        'rate_triple' => $rate['triple'],
                        'rate_quad' => $rate['quad'],
                        'extra_child1' => 100000,
                        'extra_child2' => 75000,
                    ]
                );
            }

            // RB rate (+150k per person for breakfast)
            if ($rbArrangement) {
                PublishRate::updateOrCreate(
                    [
                        'room_category_id' => $category->id,
                        'arrangement_id' => $rbArrangement->id,
                        'day_of_week' => 0,
                        'start_date' => $startDate,
                    ],
                    [
                        'end_date' => $endDate,
                        'rate_single' => $rate['single'] + 150000,
                        'rate_double' => $rate['double'] + 300000,
                        'rate_triple' => $rate['triple'] ? $rate['triple'] + 450000 : null,
                        'rate_quad' => $rate['quad'] ? $rate['quad'] + 600000 : null,
                        'extra_child1' => 100000,
                        'extra_child2' => 75000,
                    ]
                );
            }
        }
    }
}
