<?php

namespace App\Filament\Resources\BillItems\Schemas;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item')
                    ->columns(2)
                    ->schema([
                        Select::make('bill_id')
                            ->relationship(
                                name: 'bill',
                                titleAttribute: 'vendor',
                                modifyQueryUsing: fn ($query) => $query->with('project')->latest('bill_date'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string => "{$record->vendor} · {$record->bill_date?->format('Y-m-d')} ({$record->project?->name})",
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
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
                            ->numeric()
                            ->required()
                            ->prefix('₹')
                            ->minValue(0),
                        Select::make('status')
                            ->options(BillItemStatus::class)
                            ->default(BillItemStatus::Active)
                            ->required()
                            ->live(),
                        Select::make('refunded_by')
                            ->options(RefundedBy::class)
                            ->visible(fn ($get): bool => $get('status') === BillItemStatus::Returned->value),
                        DatePicker::make('settled_at')
                            ->visible(fn ($get): bool => in_array($get('status'), [
                                BillItemStatus::Returned->value,
                                BillItemStatus::Waived->value,
                            ], true)),
                        TextInput::make('refunded_amount')
                            ->numeric()
                            ->prefix('₹')
                            ->visible(fn ($get): bool => $get('status') === BillItemStatus::Returned->value),
                        Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
