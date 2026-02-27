<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\GuestResource\RelationManagers;

use App\Models\Segment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class SegmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'segments';

    protected static ?string $title = 'Segment Codes';

    protected static ?string $recordTitleAttribute = 'description';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('recordId')
                    ->label('Segment')
                    ->options(Segment::active()->pluck('description', 'id'))
                    ->searchable()
                    ->required(),
                Toggle::make('is_main')
                    ->label('Main Segment')
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('pivot.is_main')
                    ->label('Main')
                    ->boolean(),
            ])
            ->filters([])
            ->headerActions([
                Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Toggle::make('is_main')
                            ->label('Main Segment')
                            ->inline(false),
                    ]),
            ])
            ->actions([
                Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
