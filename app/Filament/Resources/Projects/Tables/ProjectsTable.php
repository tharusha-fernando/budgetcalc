<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('restoration_guy')
                    ->label('Restoration guy')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('agreed_amount')
                    ->money('INR')
                    ->placeholder('—'),
                TextColumn::make('cash_still_needed')
                    ->label('Need to save')
                    ->money('INR')
                    ->state(fn (Project $record): ?float => $record->cashStillNeeded())
                    ->placeholder('—')
                    ->color(fn (Project $record): string => ($record->cashStillNeeded() ?? 0) > 0 ? 'danger' : 'success'),
                TextColumn::make('chargeable_total')
                    ->label('Chargeable')
                    ->money('INR')
                    ->state(fn (Project $record): float => $record->chargeableTotal()),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
