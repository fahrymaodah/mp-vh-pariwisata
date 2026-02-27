<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\QuizResource\Pages;
use App\Filament\Admin\Resources\QuizResource\RelationManagers;
use App\Models\Quiz;
use BackedEnum;
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

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::QuestionMarkCircle;

    protected static string | UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 31;

    protected static ?string $pluralModelLabel = 'Quizzes';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quiz Details')->schema([
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
                    TextInput::make('passing_score')
                        ->numeric()
                        ->default(70)
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->required(),
                    TextInput::make('time_limit_minutes')
                        ->label('Time Limit (minutes)')
                        ->numeric()
                        ->nullable()
                        ->placeholder('No limit'),
                ]),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
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
                TextColumn::make('passing_score')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('time_limit_minutes')
                    ->label('Time Limit')
                    ->formatStateUsing(fn (?int $state): string => $state ? $state . ' min' : 'No limit')
                    ->placeholder('No limit'),
                TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions'),
                TextColumn::make('attempts_count')
                    ->counts('attempts')
                    ->label('Attempts'),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
