<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\SalesActivityResource\Pages;
use App\Models\Guest;
use App\Models\SalesActivity;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class SalesActivityResource extends Resource
{
    protected static ?string $model = SalesActivity::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::ClipboardDocumentList;
    protected static string | UnitEnum | null $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Sales Activities';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Activity Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('guest_id')
                            ->label('Customer')
                            ->relationship('guest', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Guest $record) => "{$record->guest_no} — {$record->name}")
                            ->searchable(['name', 'first_name', 'guest_no'])
                            ->preload()
                            ->required(),
                        TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(9),
                    ]),
                    Textarea::make('description')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    Grid::make(3)->schema([
                        TextInput::make('target_amount')
                            ->label('Target Amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0),
                        TextInput::make('competitor')
                            ->maxLength(255),
                        DatePicker::make('next_action_date')
                            ->label('Next Action Date'),
                    ]),
                    Grid::make(3)->schema([
                        TimePicker::make('next_action_time')
                            ->label('Next Action Time'),
                        Toggle::make('is_finished')
                            ->label('Finished'),
                        DatePicker::make('finish_date')
                            ->label('Finish Date'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guest.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('priority')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match(true) {
                        $state >= 7 => 'danger',
                        $state >= 4 => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('competitor')
                    ->toggleable(),
                TextColumn::make('next_action_date')
                    ->label('Next Action')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_finished')
                    ->label('Done')
                    ->boolean(),
                TextColumn::make('finish_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Sales')
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_finished')
                    ->label('Status')
                    ->trueLabel('Finished')
                    ->falseLabel('In Progress'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('next_action_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesActivities::route('/'),
            'create' => Pages\CreateSalesActivity::route('/create'),
            'edit' => Pages\EditSalesActivity::route('/{record}/edit'),
        ];
    }
}
