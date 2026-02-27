<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\RoomStatus;
use App\Filament\Admin\Resources\RoomResource\Pages;
use App\Models\Room;
use App\Models\RoomCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::HomeModern;

    protected static string | UnitEnum | null $navigationGroup = 'Room Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Room Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('room_number')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true),
                                Select::make('room_category_id')
                                    ->relationship('roomCategory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('floor')
                                    ->numeric()
                                    ->required(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->options(RoomStatus::class)
                                    ->default(RoomStatus::VacantClean),
                                Toggle::make('is_active')
                                    ->default(true),
                                Toggle::make('is_smoking')
                                    ->default(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('overlook')
                                    ->maxLength(100),
                                TextInput::make('connecting_room')
                                    ->maxLength(10),
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
                Tables\Columns\TextColumn::make('room_number')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('roomCategory.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_smoking')
                    ->boolean(),
                Tables\Columns\TextColumn::make('overlook'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('room_category_id')
                    ->relationship('roomCategory', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(RoomStatus::class),
                Tables\Filters\SelectFilter::make('floor')
                    ->options(fn () => Room::query()->distinct()->pluck('floor', 'floor')->toArray()),
            ])
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
