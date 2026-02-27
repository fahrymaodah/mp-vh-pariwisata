<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\NightAuditStatus;
use App\Enums\ReservationStatus;
use App\Models\NightAudit;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class NightAuditReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.night-audit-reports';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartBar;

    protected static string | UnitEnum | null $navigationGroup = 'Night Audit';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Audit Reports';

    protected static ?string $title = 'Night Audit Reports';

    protected static ?string $slug = 'night-audit-reports';

    public ?string $selectedDate = null;

    public string $activeReport = 'audit_history';

    public function mount(): void
    {
        $this->selectedDate = SystemDate::today();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Grid::make(2)->schema([
                DatePicker::make('selectedDate')
                    ->label('Report Date')
                    ->default(fn () => SystemDate::today())
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ]),
        ]);
    }

    public function setReport(string $report): void
    {
        $this->activeReport = $report;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return match ($this->activeReport) {
            'audit_history' => $this->auditHistoryTable($table),
            'in_house' => $this->inHouseGuestTable($table),
            'arrivals' => $this->arrivalsTable($table),
            'departures' => $this->departuresTable($table),
            'occupancy' => $this->occupancyTable($table),
            default => $this->auditHistoryTable($table),
        };
    }

    private function auditHistoryTable(Table $table): Table
    {
        return $table
            ->query(
                NightAudit::query()->latest('audit_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('audit_date')
                    ->label('Audit Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (NightAuditStatus $state) => $state->color()),
                Tables\Columns\TextColumn::make('total_rooms_occupied')
                    ->label('Occupied')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_rooms_available')
                    ->label('Available')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('occupancy_rate')
                    ->label('Occ. %')
                    ->suffix('%')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('H:i'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('H:i'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Auditor'),
            ])
            ->defaultSort('audit_date', 'desc')
            ->striped()
            ->paginated([25, 50]);
    }

    private function inHouseGuestTable(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->with(['guest', 'roomCategory', 'room'])
                    ->where('status', ReservationStatus::CheckedIn)
            )
            ->columns([
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat.'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d M'),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M'),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('adults')
                    ->label('Pax')
                    ->formatStateUsing(fn (Reservation $r) => "{$r->adults}+{$r->children}")
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR'),
            ])
            ->defaultSort('room.room_number')
            ->striped();
    }

    private function arrivalsTable(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->with(['guest', 'roomCategory', 'room'])
                    ->where('arrival_date', $this->selectedDate ?? SystemDate::today())
            )
            ->columns([
                Tables\Columns\TextColumn::make('reservation_no')
                    ->label('Res. No')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label('Guest')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat.'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('nights')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('room_rate')
                    ->money('IDR'),
            ])
            ->defaultSort('reservation_no')
            ->striped();
    }

    private function departuresTable(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->with(['guest', 'roomCategory', 'room'])
                    ->where('departure_date', $this->selectedDate ?? SystemDate::today())
                    ->whereIn('status', [ReservationStatus::CheckedIn, ReservationStatus::CheckedOut])
            )
            ->columns([
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label('Guest')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('roomCategory.code')
                    ->label('Cat.'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d M'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('C/O Time')
                    ->dateTime('H:i')
                    ->default('—'),
            ])
            ->defaultSort('room.room_number')
            ->striped();
    }

    private function occupancyTable(Table $table): Table
    {
        return $table
            ->query(
                NightAudit::query()
                    ->where('status', NightAuditStatus::Completed)
                    ->latest('audit_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('audit_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_rooms_occupied')
                    ->label('Occupied')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_rooms_available')
                    ->label('Available')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('occupancy_rate')
                    ->label('Occ. Rate')
                    ->suffix('%')
                    ->alignCenter()
                    ->color(fn (float $state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('double_occupancy')
                    ->label('Double Occ.')
                    ->getStateUsing(function (NightAudit $record) {
                        // Count reservations with 2+ adults on that date
                        return Reservation::where('status', ReservationStatus::CheckedIn)
                            ->where('arrival_date', '<=', $record->audit_date)
                            ->where('departure_date', '>', $record->audit_date)
                            ->where('adults', '>=', 2)
                            ->count();
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('IDR'),
            ])
            ->defaultSort('audit_date', 'desc')
            ->striped()
            ->paginated([25, 50]);
    }
}
