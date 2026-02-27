<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\QuizResource\RelationManagers;

use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('question')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True / False',
                    ])
                    ->default('multiple_choice')
                    ->required()
                    ->reactive(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Repeater::make('options')
                    ->schema([
                        TextInput::make('option')
                            ->label('Answer Option')
                            ->required(),
                    ])
                    ->defaultItems(4)
                    ->addActionLabel('Add Option')
                    ->columnSpanFull()
                    ->visible(fn (callable $get): bool => $get('type') === 'multiple_choice'),
                TextInput::make('correct_answer')
                    ->label('Correct Answer')
                    ->helperText('For multiple choice: enter the exact answer text. For true/false: enter "true" or "false".')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('explanation')
                    ->label('Explanation (shown after answering)')
                    ->rows(2)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),
                TextColumn::make('question')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'primary',
                        'true_false' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        default => $state,
                    }),
                TextColumn::make('correct_answer')
                    ->limit(30),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Add Question'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
