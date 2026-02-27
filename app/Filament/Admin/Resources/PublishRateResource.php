<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PublishRateResource\Pages;
use App\Models\PublishRate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class PublishRateResource extends Resource
{
    protected static ?string $model = PublishRate::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string | UnitEnum | null $navigationGroup = 'Rate Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rate Configuration')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('room_category_id')
                                    ->relationship('roomCategory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('arrangement_id')
                                    ->relationship('arrangement', 'description')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('day_of_week')
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
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->required(),
                                DatePicker::make('end_date')
                                    ->required(),
                            ]),
                    ]),
                Section::make('Rates')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('rate_single')
                                    ->numeric()
                                    ->required()
                                    ->prefix('IDR'),
                                TextInput::make('rate_double')
                                    ->numeric()
                                    ->required()
                                    ->prefix('IDR'),
                                TextInput::make('rate_triple')
                                    ->numeric()
                                    ->prefix('IDR'),
                                TextInput::make('rate_quad')
                                    ->numeric()
                                    ->prefix('IDR'),
                            ]),
                    ]),
                Section::make('Extra Charges')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('extra_child1')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR')
                                    ->label('Extra Child 1'),
                                TextInput::make('extra_child2')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR')
                                    ->label('Extra Child 2'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('roomCategory.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('arrangement.description')
                    ->sortable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate_single')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate_double')
                    ->money('IDR'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('room_category_id')
                    ->relationship('roomCategory', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('arrangement_id')
                    ->relationship('arrangement', 'description')
                    ->label('Arrangement'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublishRates::route('/'),
            'create' => Pages\CreatePublishRate::route('/create'),
            'edit' => Pages\EditPublishRate::route('/{record}/edit'),
        ];
    }
}
