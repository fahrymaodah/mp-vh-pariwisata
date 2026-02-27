<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Segment;
use Illuminate\Database\Seeder;

class SegmentSeeder extends Seeder
{
    public function run(): void
    {
        $segments = [
            ['code' => 'FIT', 'description' => 'Free Individual Traveler'],
            ['code' => 'GIT', 'description' => 'Group Inclusive Tour'],
            ['code' => 'COR', 'description' => 'Corporate'],
            ['code' => 'GOV', 'description' => 'Government'],
            ['code' => 'WHL', 'description' => 'Wholesale'],
            ['code' => 'OTA', 'description' => 'Online Travel Agent'],
            ['code' => 'COM', 'description' => 'Complimentary'],
            ['code' => 'HSE', 'description' => 'House Use'],
            ['code' => 'AIR', 'description' => 'Airline Crew'],
            ['code' => 'PKG', 'description' => 'Package'],
        ];

        foreach ($segments as $segment) {
            Segment::updateOrCreate(
                ['code' => $segment['code']],
                $segment,
            );
        }
    }
}
