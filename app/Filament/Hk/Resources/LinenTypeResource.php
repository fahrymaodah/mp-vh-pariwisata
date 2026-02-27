<?php

declare(strict_types=1);

namespace App\Filament\Hk\Resources;

use App\Models\LinenType;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LinenTypeResource extends Resource
{
    protected static ?string $model = LinenType::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::RectangleStack;

    protected static string | UnitEnum | null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Linen Management';

    protected static ?string $modelLabel = 'Linen Type';

    protected static ?string $pluralModelLabel = 'Linen Types';

    protected static ?string $slug = 'linen-types';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Linen Name')
                ->required()
                ->maxLength(100),
            Textarea::make('description')
                ->label('Description')
                ->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                LinenType::query()->withCount('transactions')
            )
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Linen Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                TextColumn::make('total_incoming')
                    ->label('Total In')
                    ->getStateUsing(fn (LinenType $record): int => (int) $record->transactions()->where('type', 'incoming')->sum('qty'))
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                TextColumn::make('total_outgoing')
                    ->label('Total Out')
                    ->getStateUsing(fn (LinenType $record): int => (int) $record->transactions()->where('type', 'outgoing')->sum('qty'))
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->getStateUsing(function (LinenType $record): int {
                        $incoming = (int) $record->transactions()->where('type', 'incoming')->sum('qty');
                        $outgoing = (int) $record->transactions()->where('type', 'outgoing')->sum('qty');
                        return $incoming - $outgoing;
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
                TextColumn::make('transactions_count')
                    ->label('Transactions')
                    ->alignCenter(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Hk\Resources\LinenTypeResource\Pages\ListLinenTypes::route('/'),
            'create' => \App\Filament\Hk\Resources\LinenTypeResource\Pages\CreateLinenType::route('/create'),
            'edit' => \App\Filament\Hk\Resources\LinenTypeResource\Pages\EditLinenType::route('/{record}/edit'),
        ];
    }
}
