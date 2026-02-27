<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RoomCategory;
use Illuminate\Database\Seeder;

class RoomCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'STD', 'name' => 'Standard', 'base_rate' => 500000, 'max_occupancy' => 2, 'credit_points' => 1, 'bed_setup' => 'Twin/Double'],
            ['code' => 'SUP', 'name' => 'Superior', 'base_rate' => 750000, 'max_occupancy' => 2, 'credit_points' => 1, 'bed_setup' => 'Twin/Double'],
            ['code' => 'DLX', 'name' => 'Deluxe', 'base_rate' => 1000000, 'max_occupancy' => 2, 'credit_points' => 2, 'bed_setup' => 'King'],
            ['code' => 'JRS', 'name' => 'Junior Suite', 'base_rate' => 1500000, 'max_occupancy' => 3, 'credit_points' => 3, 'bed_setup' => 'King + Sofa'],
            ['code' => 'SUT', 'name' => 'Suite', 'base_rate' => 2500000, 'max_occupancy' => 4, 'credit_points' => 4, 'bed_setup' => 'King + Living Room'],
            ['code' => 'PRS', 'name' => 'Presidential Suite', 'base_rate' => 5000000, 'max_occupancy' => 4, 'credit_points' => 5, 'bed_setup' => 'King + Living + Dining'],
        ];

        foreach ($categories as $category) {
            RoomCategory::updateOrCreate(
                ['code' => $category['code']],
                $category,
            );
        }
    }
}
