<?php

namespace App\Filament\Resources\Bills\RelationManagers;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use App\Models\BillItem;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Bill items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('category')
                    ->options(BillItemCategory::class)
                    ->default(BillItemCategory::Parts)
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(0.01),
                TextInput::make('unit_price')
                    ->label('Unit price')
                    ->numeric()
                    ->required()
                    ->prefix('₹')
                    ->minValue(0),
                Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('quantity')
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd(),
                TextColumn::make('unit_price')
                    ->money('INR')
                    ->alignEnd(),
                TextColumn::make('line_total')
                    ->label('Line total')
                    ->money('INR')
                    ->state(fn (BillItem $record): float => (float) $record->line_total)
                    ->alignEnd(),
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
            ->defaultSort('id')
            ->filters([
                SelectFilter::make('status')
                    ->options(BillItemStatus::class),
                SelectFilter::make('category')
                    ->options(BillItemCategory::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add item'),
            ])
            ->recordActions([
                Action::make('markReturned')
                    ->label('Mark returned')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (BillItem $record): bool => $record->status === BillItemStatus::Active)
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure?')
                    ->modalDescription('Are you sure you want to mark this item as returned? Confirm money came back, then enter who returned it.')
                    ->modalSubmitActionLabel('Yes, mark returned')
                    ->schema([
                        Select::make('refunded_by')
                            ->label('Who returned the money?')
                            ->options(RefundedBy::class)
                            ->required()
                            ->default(RefundedBy::RestorationGuy),
                        TextInput::make('refunded_amount')
                            ->label('Amount returned')
                            ->numeric()
                            ->prefix('₹')
                            ->helperText('Leave blank to use the full line total.'),
                        DatePicker::make('settled_at')
                            ->label('Returned on')
                            ->default(now())
                            ->required(),
                        Textarea::make('notes')
                            ->rows(2),
                    ])
                    ->action(function (BillItem $record, array $data): void {
                        $record->update([
                            'status' => BillItemStatus::Returned,
                            'refunded_by' => $data['refunded_by'],
                            'refunded_amount' => $data['refunded_amount'] ?: null,
                            'settled_at' => $data['settled_at'],
                            'notes' => $data['notes'] ?: $record->notes,
                        ]);
                    }),
                Action::make('markWaived')
                    ->label('I pay')
                    ->icon('heroicon-o-hand-raised')
                    ->color('gray')
                    ->visible(fn (BillItem $record): bool => $record->status !== BillItemStatus::Waived)
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure?')
                    ->modalDescription('Are you sure you want to mark this as I pay? You will cover this cost yourself and it will not be claimed from the restoration guy.')
                    ->modalSubmitActionLabel('Yes, I pay')
                    ->action(function (BillItem $record): void {
                        $record->markWaived('Paid by me — not claimed from restoration guy.');
                    }),
                Action::make('restoreActive')
                    ->label('Charge guy')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (BillItem $record): bool => $record->status !== BillItemStatus::Active)
                    ->requiresConfirmation()
                    ->modalHeading('Charge the restoration guy?')
                    ->modalDescription('This item will count toward the amount claimed from the restoration guy.')
                    ->modalSubmitActionLabel('Yes, charge guy')
                    ->action(fn (BillItem $record) => $record->restoreActive()),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete this item?')
                    ->modalDescription('This permanently removes the item from the bill.')
                    ->modalSubmitActionLabel('Yes, delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
