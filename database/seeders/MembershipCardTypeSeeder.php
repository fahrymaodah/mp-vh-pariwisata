<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MembershipCardType;
use Illuminate\Database\Seeder;

class MembershipCardTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Silver', 'description' => 'Silver membership — basic benefits', 'discount_percentage' => 5.00],
            ['name' => 'Gold', 'description' => 'Gold membership — enhanced benefits including F&B discount', 'discount_percentage' => 10.00],
            ['name' => 'Platinum', 'description' => 'Platinum membership — premium benefits including room upgrade priority', 'discount_percentage' => 15.00],
            ['name' => 'Diamond', 'description' => 'Diamond membership — top-tier benefits with maximum discount', 'discount_percentage' => 20.00],
        ];

        foreach ($types as $type) {
            MembershipCardType::updateOrCreate(
                ['name' => $type['name']],
                $type,
            );
        }
    }
}
