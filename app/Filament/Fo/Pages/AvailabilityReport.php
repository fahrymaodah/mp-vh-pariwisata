<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Models\RoomCategory;
use App\Models\SystemDate;
use App\Services\RoomAvailabilityService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AvailabilityReport extends Page
{
    protected static ?string $title = 'Room Availability';

    protected static ?string $navigationLabel = 'Availability';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CalendarDateRange;

    protected static string | UnitEnum | null $navigationGroup = 'Reservation';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.fo.pages.availability-report';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?int $categoryId = null;

    public array $summary = [];

    public array $dailyData = [];

    public function mount(): void
    {
        $today = SystemDate::today();
        $this->startDate = $today;
        $this->endDate = \Carbon\Carbon::parse($today)->addDays(13)->toDateString();
        $this->loadData();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate')
                ->label('From Date')
                ->required()
                ->live()
                ->afterStateUpdated(fn () => $this->loadData()),
            DatePicker::make('endDate')
                ->label('To Date')
                ->required()
                ->live()
                ->afterStateUpdated(fn () => $this->loadData()),
            Select::make('categoryId')
                ->label('Room Category')
                ->options(RoomCategory::pluck('name', 'id'))
                ->placeholder('All Categories')
                ->live()
                ->afterStateUpdated(fn () => $this->loadData()),
        ]);
    }

    public function loadData(): void
    {
        if (! $this->startDate || ! $this->endDate) {
            return;
        }

        $service = app(RoomAvailabilityService::class);

        // Overall summary
        $this->summary = $service->getAvailabilitySummary($this->startDate, $this->endDate)->toArray();

        // Daily breakdown
        if ($this->categoryId) {
            $this->dailyData = $service->getDailyAvailability(
                $this->categoryId,
                $this->startDate,
                $this->endDate,
            );
        } else {
            $this->dailyData = [];
            // Show daily for all categories
            $categories = RoomCategory::all();
            foreach ($categories as $category) {
                $this->dailyData[$category->code] = $service->getDailyAvailability(
                    $category->id,
                    $this->startDate,
                    $this->endDate,
                );
            }
        }
    }
}
