<?php

declare(strict_types=1);

namespace App\Filament\Telop\Resources;

use App\Filament\Telop\Resources\GuestMessageResource\Pages;
use App\Models\GuestMessage;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class GuestMessageResource extends Resource
{
    protected static ?string $model = GuestMessage::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::Envelope;
    protected static string | UnitEnum | null $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Guest Messages';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Guest Message')
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
                    Textarea::make('message')
                        ->required()
                        ->rows(4)
                        ->maxLength(65535),
                    Grid::make(2)->schema([
                        Toggle::make('is_read')
                            ->label('Read')
                            ->default(false),
                    ]),
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
                TextColumn::make('message')
                    ->limit(50)
                    ->tooltip(fn (GuestMessage $record): string => $record->message),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::ExclamationCircle)
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date/Time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('Read Status'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('markRead')
                    ->label('Mark Read')
                    ->icon(Heroicon::Check)
                    ->color('success')
                    ->visible(fn (GuestMessage $record): bool => ! $record->is_read)
                    ->action(fn (GuestMessage $record) => $record->update([
                        'is_read' => true,
                        'read_at' => now(),
                    ])),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestMessages::route('/'),
            'create' => Pages\CreateGuestMessage::route('/create'),
            'edit' => Pages\EditGuestMessage::route('/{record}/edit'),
        ];
    }
}
