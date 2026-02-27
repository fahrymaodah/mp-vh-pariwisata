<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TutorialResource\Pages;
use App\Models\Tutorial;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TutorialResource extends Resource
{
    protected static ?string $model = Tutorial::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::BookOpen;

    protected static string | UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 31;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tutorial Details')->schema([
                Grid::make(2)->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Select::make('module')
                        ->options([
                            'fo' => 'Front Office',
                            'hk' => 'Housekeeping',
                            'sales' => 'Sales & Marketing',
                            'telop' => 'Telephone Operator',
                        ])
                        ->required(),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('target_page')
                        ->label('Target Page URL')
                        ->placeholder('e.g. /fo/reservations')
                        ->helperText('The page this tutorial is contextually linked to.')
                        ->nullable(),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('description')
                        ->maxLength(500)
                        ->nullable(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),
            ]),

            Section::make('Tutorial Steps')->schema([
                Repeater::make('steps')
                    ->schema([
                        TextInput::make('title')
                            ->label('Step Title')
                            ->required(),
                        TextInput::make('content')
                            ->label('Step Content')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('element')
                            ->label('Target Element (CSS Selector)')
                            ->placeholder('e.g. #create-button, .nav-item')
                            ->helperText('Optional: highlight a specific element on the page.')
                            ->nullable(),
                        Select::make('placement')
                            ->options([
                                'top' => 'Top',
                                'bottom' => 'Bottom',
                                'left' => 'Left',
                                'right' => 'Right',
                                'center' => 'Center',
                            ])
                            ->default('bottom'),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Add Step')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('module')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fo' => 'primary',
                        'hk' => 'success',
                        'sales' => 'warning',
                        'telop' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fo' => 'Front Office',
                        'hk' => 'Housekeeping',
                        'sales' => 'Sales',
                        'telop' => 'TelOp',
                        default => $state,
                    }),
                TextColumn::make('target_page')
                    ->placeholder('—')
                    ->limit(30),
                TextColumn::make('steps')
                    ->label('Steps')
                    ->formatStateUsing(fn (?array $state): string => $state ? count($state) . ' steps' : '0 steps'),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->options([
                        'fo' => 'Front Office',
                        'hk' => 'Housekeeping',
                        'sales' => 'Sales',
                        'telop' => 'TelOp',
                    ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutorials::route('/'),
            'create' => Pages\CreateTutorial::route('/create'),
            'edit' => Pages\EditTutorial::route('/{record}/edit'),
        ];
    }
}
