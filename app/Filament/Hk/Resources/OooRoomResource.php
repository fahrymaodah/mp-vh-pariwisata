<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources;

use App\Enums\OooType;
use App\Models\Room;
use App\Models\RoomOutOfOrder;
use App\Models\SystemDate;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class OooRoomResource extends Resource
{
    protected static ?string $model = RoomOutOfOrder::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::WrenchScrewdriver;

    protected static string | UnitEnum | null $navigationGroup = 'Room Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'OOO / Off-Market';

    protected static ?string $modelLabel = 'OOO Room';

    protected static ?string $pluralModelLabel = 'OOO Rooms';

    protected static ?string $slug = 'ooo-rooms';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('room_id')
                ->label('Room')
                ->relationship('room', 'room_number')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('type')
                ->label('Type')
                ->options(OooType::class)
                ->required()
                ->default(OooType::OutOfOrder),
            Textarea::make('reason')
                ->label('Reason')
                ->required()
                ->rows(3),
            DatePicker::make('from_date')
                ->label('From Date')
                ->required()
                ->default(fn () => SystemDate::today()),
            DatePicker::make('until_date')
                ->label('Until Date')
                ->required()
                ->after('from_date'),
            TextInput::make('department')
                ->label('Department')
                ->maxLength(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                RoomOutOfOrder::query()->with(['room.category', 'user'])
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Rm No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (OooType $state): string => $state->label())
                    ->color(fn (OooType $state): string => $state->color()),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('from_date')
                    ->label('From')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('until_date')
                    ->label('Until')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('room.floor')
                    ->label('Floor')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('department')
                    ->label('Dept'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(OooType::class),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('from_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Hk\Resources\OooRoomResource\Pages\ListOooRooms::route('/'),
            'create' => \App\Filament\Hk\Resources\OooRoomResource\Pages\CreateOooRoom::route('/create'),
            'edit' => \App\Filament\Hk\Resources\OooRoomResource\Pages\EditOooRoom::route('/{record}/edit'),
        ];
    }
}
