<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources;

use App\Models\LostAndFound;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class LostAndFoundResource extends Resource
{
    protected static ?string $model = LostAndFound::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::MagnifyingGlass;

    protected static string | UnitEnum | null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Lost & Found';

    protected static ?string $modelLabel = 'Lost & Found';

    protected static ?string $pluralModelLabel = 'Lost & Found';

    protected static ?string $slug = 'lost-and-found';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Type')
                ->options([
                    'lost' => 'Lost',
                    'found' => 'Found',
                ])
                ->required()
                ->default('found'),
            Select::make('room_id')
                ->label('Room')
                ->relationship('room', 'room_number')
                ->searchable()
                ->preload()
                ->nullable(),
            Textarea::make('description')
                ->label('Description')
                ->required()
                ->rows(3),
            TextInput::make('found_by')
                ->label('Found By')
                ->required()
                ->maxLength(255),
            TextInput::make('submitted_to')
                ->label('Submitted To')
                ->maxLength(255),
            TextInput::make('claimed_by')
                ->label('Claimed By')
                ->maxLength(255),
            DatePicker::make('claimed_date')
                ->label('Claimed Date'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                LostAndFound::query()->with(['room', 'user'])
            )
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('created_at_time')
                    ->label('Time')
                    ->getStateUsing(fn (LostAndFound $record): string => $record->created_at->format('H:i')),
                TextColumn::make('room.room_number')
                    ->label('Rm No')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('user.name')
                    ->label('ID'),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('found_by')
                    ->label('Found By')
                    ->searchable(),
                TextColumn::make('submitted_to')
                    ->label('Submitted To')
                    ->toggleable(),
                TextColumn::make('claimed_by')
                    ->label('Claimed By')
                    ->toggleable(),
                TextColumn::make('claimed_date')
                    ->label('Claimed Date')
                    ->date('d M Y')
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'lost' ? 'danger' : 'success')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'lost' => 'Lost',
                        'found' => 'Found',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
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
            'index' => \App\Filament\Hk\Resources\LostAndFoundResource\Pages\ListLostAndFounds::route('/'),
            'create' => \App\Filament\Hk\Resources\LostAndFoundResource\Pages\CreateLostAndFound::route('/create'),
            'edit' => \App\Filament\Hk\Resources\LostAndFoundResource\Pages\EditLostAndFound::route('/{record}/edit'),
        ];
    }
}
