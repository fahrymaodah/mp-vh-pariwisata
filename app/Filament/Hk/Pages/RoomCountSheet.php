<?php

declare(strict_types=1);

namespace App\Filament\Hk\Pages;

use App\Enums\RoomStatus;
use App\Models\Room;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class RoomCountSheet extends Page
{
    protected string $view = 'filament.hk.pages.room-count-sheet';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::TableCells;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Room Count Sheet';

    protected static ?string $title = 'Room Count Sheet';

    protected static ?string $slug = 'room-count-sheet';

    /**
     * Returns array of floors and per-status counts.
     * Format: [
     *   'floors' => [1, 2, 3...],
     *   'statuses' => [RoomStatus::VacantClean, ...],
     *   'data' => [floor => [status_value => count]],
     *   'summary' => [status_value => total_count],
     *   'floor_totals' => [floor => total],
     *   'grand_total' => int,
     * ]
     */
    public function getCountData(): array
    {
        $rooms = Room::active()->get();

        $floors = $rooms->pluck('floor')->unique()->sort()->values()->toArray();
        $statuses = RoomStatus::cases();

        $data = [];
        $summary = [];
        $floorTotals = [];

        foreach ($floors as $floor) {
            $data[$floor] = [];
            $floorTotals[$floor] = 0;
            foreach ($statuses as $status) {
                $count = $rooms->where('floor', $floor)->where('status', $status)->count();
                $data[$floor][$status->value] = $count;
                $floorTotals[$floor] += $count;
                $summary[$status->value] = ($summary[$status->value] ?? 0) + $count;
            }
        }

        return [
            'floors' => $floors,
            'statuses' => $statuses,
            'data' => $data,
            'summary' => $summary,
            'floor_totals' => $floorTotals,
            'grand_total' => array_sum($floorTotals),
        ];
    }
}
