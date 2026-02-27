<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DepartmentType;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'FO', 'name' => 'Front Office', 'type' => DepartmentType::Hotel],
            ['code' => 'HK', 'name' => 'Housekeeping', 'type' => DepartmentType::Hotel],
            ['code' => 'FB', 'name' => 'Food & Beverage', 'type' => DepartmentType::Restaurant],
            ['code' => 'LDY', 'name' => 'Laundry', 'type' => DepartmentType::Hotel],
            ['code' => 'TEL', 'name' => 'Telephone', 'type' => DepartmentType::Hotel],
            ['code' => 'SPA', 'name' => 'Spa & Wellness', 'type' => DepartmentType::Hotel],
            ['code' => 'BQT', 'name' => 'Banquet', 'type' => DepartmentType::Hotel],
            ['code' => 'BAR', 'name' => 'Bar', 'type' => DepartmentType::Restaurant],
            ['code' => 'MBC', 'name' => 'Minibar', 'type' => DepartmentType::Hotel],
            ['code' => 'DRG', 'name' => 'Drugstore', 'type' => DepartmentType::Drugstore],
            ['code' => 'PMT', 'name' => 'Payment', 'type' => DepartmentType::Hotel],
            ['code' => 'MSC', 'name' => 'Miscellaneous', 'type' => DepartmentType::Other],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept,
            );
        }
    }
}
