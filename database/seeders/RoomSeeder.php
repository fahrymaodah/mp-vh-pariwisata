<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $categories = RoomCategory::all()->keyBy('code');

        // Floor 1: Standard rooms (101-110)
        $this->createRooms($categories['STD']->id, 1, 101, 110);

        // Floor 2: Superior rooms (201-210)
        $this->createRooms($categories['SUP']->id, 2, 201, 210);

        // Floor 3: Deluxe rooms (301-308)
        $this->createRooms($categories['DLX']->id, 3, 301, 308);

        // Floor 4: Junior Suite (401-404) + Suite (405-406)
        $this->createRooms($categories['JRS']->id, 4, 401, 404);
        $this->createRooms($categories['SUT']->id, 4, 405, 406);

        // Floor 5: Presidential Suite (501-502)
        $this->createRooms($categories['PRS']->id, 5, 501, 502);
    }

    private function createRooms(int $categoryId, int $floor, int $start, int $end): void
    {
        for ($i = $start; $i <= $end; $i++) {
            Room::updateOrCreate(
                ['room_number' => (string) $i],
                [
                    'room_category_id' => $categoryId,
                    'floor' => $floor,
                    'status' => RoomStatus::VacantClean,
                    'is_active' => true,
                    'is_smoking' => false,
                ],
            );
        }
    }
}
