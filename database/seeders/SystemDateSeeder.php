<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemDate;
use Illuminate\Database\Seeder;

class SystemDateSeeder extends Seeder
{
    public function run(): void
    {
        SystemDate::updateOrCreate(
            ['id' => 1],
            [
                'current_date' => now()->toDateString(),
                'last_night_audit' => null,
            ]
        );
    }
}
