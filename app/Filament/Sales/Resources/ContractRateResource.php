<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\ContractRateResource\Pages;
use App\Models\Arrangement;
use App\Models\ContractRate;
use App\Models\RoomCategory;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class ContractRateResource extends Resource
{
    protected static ?string $model = ContractRate::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::CurrencyDollar;
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Contract Rates';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Price Code')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('price_code')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('currency_code')
                            ->label('Currency')
                            ->default('IDR')
                            ->maxLength(10),
                    ]),
                ]),
            Section::make('Contract Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('room_category_id')
                            ->label('Room Category')
                            ->relationship('roomCategory', 'code')
                            ->getOptionLabelFromRecordUsing(fn (RoomCategory $record) => "{$record->code} — {$record->description}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('arrangement_id')
                            ->label('Arrangement')
                            ->relationship('arrangement', 'description')
                            ->searchable()
                            ->preload(),
                    ]),
                    Grid::make(3)->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required(),
                        Select::make('day_of_week')
                            ->label('Day of Week')
                            ->options([
                                0 => 'All Days',
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                                7 => 'Sunday',
                            ])
                            ->default(0),
                    ]),
                ]),
            Section::make('Rates')
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('adults')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        TextInput::make('room_rate')
                            ->label('Room Rate')
                            ->numeric()
                            ->prefix('IDR')
                            ->required(),
                        TextInput::make('child1_rate')
                            ->label('Child 1 Rate')
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0),
                        TextInput::make('child2_rate')
                            ->label('Child 2 Rate')
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('price_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roomCategory.code')
                    ->label('Room Cat.')
                    ->sortable(),
                TextColumn::make('arrangement.description')
                    ->label('Arrangement')
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->label('Start')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('End')
                    ->date()
                    ->sortable(),
                TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'All',
                        1 => 'Mon', 2 => 'Tue', 3 => 'Wed',
                        4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun',
                        default => '-',
                    })
                    ->toggleable(),
                TextColumn::make('adults')
                    ->toggleable(),
                TextColumn::make('room_rate')
                    ->label('Rate')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('child1_rate')
                    ->label('Child 1')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('child2_rate')
                    ->label('Child 2')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('room_category_id')
                    ->label('Room Category')
                    ->relationship('roomCategory', 'code'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('price_code');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContractRates::route('/'),
            'create' => Pages\CreateContractRate::route('/create'),
            'edit' => Pages\EditContractRate::route('/{record}/edit'),
        ];
    }
}
