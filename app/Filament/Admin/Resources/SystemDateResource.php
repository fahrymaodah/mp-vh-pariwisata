<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SystemDateResource\Pages;
use App\Models\SystemDate;
use Filament\Forms\Components\DatePicker;
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

class SystemDateResource extends Resource
{
    protected static ?string $model = SystemDate::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::CalendarDays;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'System Date';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('System Date')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('current_date')
                                    ->required()
                                    ->label('Current Business Date'),
                                DatePicker::make('last_night_audit')
                                    ->disabled()
                                    ->label('Last Night Audit'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('current_date')
                    ->date()
                    ->sortable()
                    ->label('Business Date'),
                Tables\Columns\TextColumn::make('last_night_audit')
                    ->date()
                    ->label('Last Night Audit'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemDates::route('/'),
            'create' => Pages\CreateSystemDate::route('/create'),
            'edit' => Pages\EditSystemDate::route('/{record}/edit'),
        ];
    }
}
