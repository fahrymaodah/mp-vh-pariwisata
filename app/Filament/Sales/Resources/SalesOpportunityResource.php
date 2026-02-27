<?php

declare(strict_types=1);

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\SalesOpportunityResource\Pages;
use App\Models\Guest;
use App\Models\SalesActivity;
use App\Models\SalesOpportunity;
use App\Models\SalesProduct;
use App\Models\SalesReason;
use App\Models\SalesReferralSource;
use App\Models\SalesStage;
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
use UnitEnum;

class SalesOpportunityResource extends Resource
{
    protected static ?string $model = SalesOpportunity::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::RocketLaunch;
    protected static string | UnitEnum | null $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Opportunities';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Opportunity Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('sales_activity_id')
                            ->label('Sales Activity')
                            ->relationship('salesActivity', 'description')
                            ->getOptionLabelFromRecordUsing(fn (SalesActivity $record) => "#{$record->id} — " . str()->limit($record->description, 50))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('guest_id')
                            ->label('Company / Guest')
                            ->relationship('guest', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Guest $record) => "{$record->guest_no} — {$record->name}")
                            ->searchable(['name', 'first_name', 'guest_no'])
                            ->preload()
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('prospect_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('contact_name')
                            ->label('Contact Person')
                            ->maxLength(255),
                    ]),
                ]),
            Section::make('Pipeline')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('stage_id')
                            ->label('Stage')
                            ->relationship('stage', 'name')
                            ->preload(),
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->preload(),
                        Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'close' => 'Close',
                                'inactive' => 'Inactive',
                            ])
                            ->default('open')
                            ->required(),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('target_amount')
                            ->label('Target Amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0),
                        TextInput::make('probability')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),
                        DatePicker::make('finish_date')
                            ->label('Finish Date'),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('reason_id')
                            ->label('Close Reason')
                            ->relationship('reason', 'name')
                            ->preload(),
                        Select::make('source_id')
                            ->label('Referral Source')
                            ->relationship('source', 'name')
                            ->preload(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prospect_name')
                    ->label('Prospect')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guest.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_name')
                    ->label('Contact')
                    ->toggleable(),
                TextColumn::make('stage.name')
                    ->label('Stage')
                    ->badge()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'close' => 'success',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('probability')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('finish_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Sales')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'close' => 'Close',
                        'inactive' => 'Inactive',
                    ]),
                SelectFilter::make('stage_id')
                    ->label('Stage')
                    ->relationship('stage', 'name'),
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name'),
            ])
            ->actions([
                Actions\ViewAction::make(),
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
            'index' => Pages\ListSalesOpportunities::route('/'),
            'create' => Pages\CreateSalesOpportunity::route('/create'),
            'edit' => Pages\EditSalesOpportunity::route('/{record}/edit'),
        ];
    }
}
