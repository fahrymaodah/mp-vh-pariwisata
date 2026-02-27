<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Traits\HasReportExport;
use App\Models\Guest;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class RepeaterGuestList extends Page implements HasTable
{
    use InteractsWithTable;
    use HasReportExport;

    protected string $view = 'filament.fo.pages.repeater-guest-list';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowPath;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 22;

    protected static ?string $navigationLabel = 'Repeater Guest List';

    protected static ?string $title = 'Repeater Guest List';

    protected static ?string $slug = 'repeater-guest-list';

    public int $minStays = 2;

    public array $summaryData = [];

    public function mount(): void
    {
        $this->loadSummary();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                TextInput::make('minStays')
                    ->label('Minimum Stays')
                    ->numeric()
                    ->minValue(2)
                    ->default(2)
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadSummary()),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->columns([
                TextColumn::make('guest_no')
                    ->label('Guest No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (Guest $record) => trim(($record->title ? $record->title . ' ' : '') . $record->first_name . ' ' . $record->name)),
                TextColumn::make('nationality')
                    ->label('Nationality')
                    ->searchable(),
                TextColumn::make('stay_count')
                    ->label('Total Stays')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('total_nights')
                    ->label('Total Nights')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('last_visit')
                    ->label('Last Visit')
                    ->sortable()
                    ->date(),
                TextColumn::make('is_vip')
                    ->label('VIP')
                    ->formatStateUsing(fn ($state) => $state ? '⭐ VIP' : '-')
                    ->alignCenter(),
            ])
            ->defaultSort('stay_count', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $minStays = max(2, $this->minStays);

        return Guest::query()
            ->select(
                'guests.*',
                DB::raw('COUNT(reservations.id) as stay_count'),
                DB::raw('COALESCE(SUM(reservations.nights), 0) as total_nights'),
                DB::raw('COALESCE(SUM(reservations.room_rate * reservations.nights), 0) as total_revenue'),
                DB::raw('MAX(reservations.arrival_date) as last_visit'),
            )
            ->join('reservations', 'guests.id', '=', 'reservations.guest_id')
            ->whereIn('reservations.status', [
                ReservationStatus::CheckedIn,
                ReservationStatus::CheckedOut,
            ])
            ->groupBy('guests.id')
            ->havingRaw('COUNT(reservations.id) >= ?', [$minStays]);
    }

    public function loadSummary(): void
    {
        try {
            $minStays = max(2, $this->minStays);

            $repeaters = Guest::query()
                ->select('guests.id', DB::raw('COUNT(reservations.id) as stay_count'))
                ->join('reservations', 'guests.id', '=', 'reservations.guest_id')
                ->whereIn('reservations.status', [
                    ReservationStatus::CheckedIn,
                    ReservationStatus::CheckedOut,
                ])
                ->groupBy('guests.id')
                ->havingRaw('COUNT(reservations.id) >= ?', [$minStays])
                ->get();

            $totalRepeaters = $repeaters->count();
            $avgStays = $repeaters->avg('stay_count');
            $maxStays = $repeaters->max('stay_count');

            $totalGuests = Guest::count();
            $repeaterPct = $totalGuests > 0 ? round(($totalRepeaters / $totalGuests) * 100, 1) : 0;

            $this->summaryData = [
                'total_repeaters' => $totalRepeaters,
                'avg_stays' => round((float) ($avgStays ?? 0), 1),
                'max_stays' => (int) ($maxStays ?? 0),
                'repeater_pct' => $repeaterPct,
            ];
        } catch (\Throwable $e) {
            report($e);
            $this->summaryData = ['total_repeaters' => 0, 'avg_stays' => 0, 'max_stays' => 0, 'repeater_pct' => 0];
        }
    }

    protected function getReportTitle(): string
    {
        return 'Repeater Guest List';
    }

    protected function getExportData(): array
    {
        $rows = $this->getTableQuery()->get()->map(fn ($item) => [
            $item->guest_no,
            trim(($item->title ? $item->title . ' ' : '') . $item->first_name . ' ' . $item->name),
            $item->nationality ?? '-',
            (string) $item->stay_count,
            (string) $item->total_nights,
            'Rp ' . number_format((float) $item->total_revenue, 0, ',', '.'),
            $item->last_visit ?? '-',
            $item->is_vip ? 'VIP' : '-',
        ])->toArray();

        return [
            'headers' => ['Guest No', 'Name', 'Nationality', 'Stays', 'Nights', 'Revenue', 'Last Visit', 'VIP'],
            'rows' => $rows,
            'subtitle' => 'Minimum stays: ' . $this->minStays,
            'orientation' => 'landscape',
        ];
    }
}
