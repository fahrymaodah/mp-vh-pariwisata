<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources;

use App\Filament\Telop\Resources\GuestLocatorResource\Pages;
use App\Models\GuestLocator;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class GuestLocatorResource extends Resource
{
    protected static ?string $model = GuestLocator::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::MapPin;
    protected static string | UnitEnum | null $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Guest Locator';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Guest Locator')
                ->description('Track guest current location for caller inquiries')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('reservation_id')
                            ->label('Reservation')
                            ->relationship('reservation', 'reservation_no')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('guest_id')
                            ->label('Guest')
                            ->relationship('guest', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('room_id')
                            ->label('Room')
                            ->relationship('room', 'room_number')
                            ->searchable()
                            ->preload(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('location')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Restaurant, Pool, Meeting Room 2'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
                    Textarea::make('remarks')
                        ->rows(3)
                        ->maxLength(65535),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation.reservation_no')
                    ->label('Res No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guest.name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->icon(Heroicon::MapPin),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('remarks')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn (GuestLocator $record): bool => $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (GuestLocator $record) => $record->update(['is_active' => false])),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestLocators::route('/'),
            'create' => Pages\CreateGuestLocator::route('/create'),
            'edit' => Pages\EditGuestLocator::route('/{record}/edit'),
        ];
    }
}
