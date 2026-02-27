<?php

declare(strict_types=1);

namespace App\Filament\Fo\Resources\ReservationResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'Activity Log';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date/Time')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->default('System'),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'cancelled' => 'danger',
                        'status_changed' => 'warning',
                        'room_sharer_added' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('field_changed')
                    ->label('Field')
                    ->default('-'),
                Tables\Columns\TextColumn::make('old_value')
                    ->label('Old Value')
                    ->default('-')
                    ->limit(50),
                Tables\Columns\TextColumn::make('new_value')
                    ->label('New Value')
                    ->default('-')
                    ->limit(50),
            ])
            ->paginated([10, 25, 50]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
