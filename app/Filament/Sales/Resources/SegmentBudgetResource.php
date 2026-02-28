<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\SegmentBudgetResource\Pages;
use App\Models\SegmentBudget;
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
use App\Enums\UserRole;
use UnitEnum;

class SegmentBudgetResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(UserRole::salesManagerRoles()) ?? false;
    }

    protected static ?string $model = SegmentBudget::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartPie;
    protected static string | UnitEnum | null $navigationGroup = 'Budget';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Guest Segment Budget';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Guest Segment Budget')
                ->description('Monthly budget targets per market segment')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('segment_id')
                            ->label('Segment')
                            ->relationship('segment', 'description')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Month')
                            ->displayFormat('F Y')
                            ->required(),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('budget_rooms')
                            ->label('Budget Rooms')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('budget_persons')
                            ->label('Budget Persons')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('budget_lodging')
                            ->label('Budget Lodging')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('segment.description')
                    ->label('Segment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date('F Y')
                    ->sortable(),
                TextColumn::make('budget_rooms')
                    ->label('Rooms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('budget_persons')
                    ->label('Persons')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('budget_lodging')
                    ->label('Lodging')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('segment_id')
                    ->label('Segment')
                    ->relationship('segment', 'description'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSegmentBudgets::route('/'),
            'create' => Pages\CreateSegmentBudget::route('/create'),
            'edit' => Pages\EditSegmentBudget::route('/{record}/edit'),
        ];
    }
}
