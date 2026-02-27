<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoomCategoryResource\Pages;
use App\Models\RoomCategory;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class RoomCategoryResource extends Resource
{
    protected static ?string $model = RoomCategory::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::RectangleGroup;

    protected static string | UnitEnum | null $navigationGroup = 'Room Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('base_rate')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR'),
                                TextInput::make('max_occupancy')
                                    ->numeric()
                                    ->default(2)
                                    ->minValue(1),
                                TextInput::make('credit_points')
                                    ->numeric()
                                    ->default(1),
                                TextInput::make('bed_setup')
                                    ->maxLength(100),
                            ]),
                        Textarea::make('description')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_rate')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_occupancy')
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit_points'),
                Tables\Columns\TextColumn::make('bed_setup'),
                Tables\Columns\TextColumn::make('rooms_count')
                    ->counts('rooms')
                    ->label('Rooms'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoomCategories::route('/'),
            'create' => Pages\CreateRoomCategory::route('/create'),
            'edit' => Pages\EditRoomCategory::route('/{record}/edit'),
        ];
    }
}
