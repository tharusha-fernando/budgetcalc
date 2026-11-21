<?php

namespace App\Filament\Resources\BillItems\Tables;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use App\Models\BillItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BillItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bill.project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bill.vendor')
                    ->label('Bill / vendor')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('quantity')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('unit_price')
                    ->money('INR'),
                TextColumn::make('line_total')
                    ->label('Line total')
                    ->money('INR')
                    ->state(fn (BillItem $record): float => (float) $record->line_total),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('refunded_by')
                    ->label('Refunded by')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('settled_at')
                    ->date()
                    ->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(BillItemStatus::class),
                SelectFilter::make('category')
                    ->options(BillItemCategory::class),
                SelectFilter::make('refunded_by')
                    ->options(RefundedBy::class),
                SelectFilter::make('project')
                    ->label('Project')
                    ->relationship('bill.project', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
