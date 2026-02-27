<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ScenarioResource\Pages;
use App\Filament\Admin\Resources\ScenarioResource\RelationManagers;
use App\Models\Scenario;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ScenarioResource extends Resource
{
    protected static ?string $model = Scenario::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::AcademicCap;

    protected static string | UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Scenario Details')->schema([
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
                    Select::make('difficulty')
                        ->options([
                            'beginner' => 'Beginner',
                            'intermediate' => 'Intermediate',
                            'advanced' => 'Advanced',
                        ])
                        ->default('beginner')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),

            Section::make('Instructions & Objectives')->schema([
                RichEditor::make('instructions')
                    ->label('Instructions for Students')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline',
                        'bulletList', 'orderedList',
                        'heading',
                    ])
                    ->columnSpanFull(),
                Repeater::make('objectives')
                    ->label('Learning Objectives')
                    ->schema([
                        TextInput::make('objective')
                            ->label('Objective')
                            ->required(),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel('Add Objective')
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
                TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('assignments_count')
                    ->counts('assignments')
                    ->label('Assigned')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->options([
                        'fo' => 'Front Office',
                        'hk' => 'Housekeeping',
                        'sales' => 'Sales',
                        'telop' => 'TelOp',
                    ]),
                SelectFilter::make('difficulty')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScenarios::route('/'),
            'create' => Pages\CreateScenario::route('/create'),
            'edit' => Pages\EditScenario::route('/{record}/edit'),
        ];
    }
}
