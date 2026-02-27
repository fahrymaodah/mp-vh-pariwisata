<?php

declare(strict_types=1);

namespace App\Filament\Fo\Pages;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SpecialGuestLists extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.fo.pages.special-guest-lists';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Star;

    protected static string | UnitEnum | null $navigationGroup = 'In House';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Special Lists';

    protected static ?string $title = 'Special Guest Lists';

    protected static ?string $slug = 'special-guest-lists';

    // Filter state
    public ?string $listType = 'walk_in';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                \Filament\Forms\Components\Select::make('listType')
                    ->label('List Type')
                    ->options([
                        'walk_in' => 'Walk-In Guest',
                        'foreign' => 'Foreign Guest',
                        'compliment' => 'Compliment Guest (All)',
                        'compliment_exclude' => 'Compliment (Excl. Bonus Night)',
                        'abf' => 'ABF List (Breakfast)',
                    ])
                    ->default('walk_in')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $type = $this->listType ?? 'walk_in';

                $query = Reservation::query()
                    ->where('status', ReservationStatus::CheckedIn)
                    ->with(['guest', 'room', 'roomCategory', 'arrangement']);

                return match ($type) {
                    'walk_in' => $query->where('source', 'walk_in'),
                    'foreign' => $query->whereHas('guest', fn (Builder $q) => $q->where('nationality', '!=', 'ID')
                        ->whereNotNull('nationality')
                        ->where('nationality', '!=', '')),
                    'compliment' => $query->where('is_complimentary', true),
                    'compliment_exclude' => $query->where('is_complimentary', true)
                        ->where(function (Builder $q) {
                            $q->whereNull('comments')
                                ->orWhere('comments', 'not like', '%bonus night%');
                        }),
                    'abf' => $query->whereHas('arrangement', fn (Builder $q) => $q->where('code', 'like', '%ABF%')
                        ->orWhere('code', 'like', '%BB%')
                        ->orWhere('description', 'like', '%breakfast%')),
                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('reservation_no')
                    ->label('Res #')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('guest.full_name')
                    ->label('Guest Name')
                    ->searchable(['guests.name', 'guests.first_name'])
                    ->weight('bold'),
                TextColumn::make('roomCategory.code')
                    ->label('Cat')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('arrangement.code')
                    ->label('Argt')
                    ->placeholder('—'),
                TextColumn::make('guest.nationality')
                    ->label('Nationality')
                    ->placeholder('—'),
                TextColumn::make('pax_display')
                    ->label('Pax')
                    ->getStateUsing(fn (Reservation $r) => $r->adults . '/' . ($r->children ?? 0)),
                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d/m/Y'),
                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d/m/Y'),
                TextColumn::make('nights')
                    ->alignCenter(),
                TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('comments')
                    ->label('Remark')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_complimentary')
                    ->label('Comp')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('room.room_number')
            ->emptyStateHeading('No guests found')
            ->emptyStateDescription('No guests matching the selected list criteria.')
            ->emptyStateIcon(Heroicon::Star);
    }
}
