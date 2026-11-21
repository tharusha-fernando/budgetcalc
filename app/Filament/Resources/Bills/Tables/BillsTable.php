<?php

namespace App\Filament\Resources\Bills\Tables;

use App\Models\Bill;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vendor')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bill_number')
                    ->label('Bill #')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('bill_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('stated_total')
                    ->label('Printed total')
                    ->money('INR')
                    ->placeholder('—'),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                TextColumn::make('attachments')
                    ->label('Files')
                    ->formatStateUsing(fn (?array $state): string => (string) count($state ?? []))
                    ->alignEnd(),
                TextColumn::make('chargeable_total')
                    ->label('Chargeable')
                    ->money('INR')
                    ->state(fn (Bill $record): float => $record->chargeableTotal())
                    ->sortable(false),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('bill_date', 'desc')
            ->filters([
                SelectFilter::make('project')
                    ->relationship('project', 'name'),
            ])
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
