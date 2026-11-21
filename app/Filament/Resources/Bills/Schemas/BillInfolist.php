<?php

namespace App\Filament\Resources\Bills\Schemas;

use App\Filament\Infolists\Components\BillAttachmentsEntry;
use App\Models\Bill;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bill')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('project.name')
                            ->label('Project'),
                        TextEntry::make('vendor')
                            ->label('Vendor / store'),
                        TextEntry::make('bill_number')
                            ->label('Bill / invoice #')
                            ->placeholder('—'),
                        TextEntry::make('bill_date')
                            ->date(),
                        TextEntry::make('stated_total')
                            ->label('Printed bill total')
                            ->money('INR')
                            ->placeholder('—'),
                        TextEntry::make('notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Attachments')
                    ->schema([
                        BillAttachmentsEntry::make(),
                    ]),
                Section::make('Totals')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('gross_total')
                            ->label('Items total')
                            ->money('INR')
                            ->state(fn (Bill $record): float => $record->grossTotal()),
                        TextEntry::make('returned_total')
                            ->label('Returned')
                            ->money('INR')
                            ->state(fn (Bill $record): float => $record->returnedTotal()),
                        TextEntry::make('waived_total')
                            ->label('I pay')
                            ->money('INR')
                            ->state(fn (Bill $record): float => $record->waivedTotal()),
                        TextEntry::make('chargeable_total')
                            ->label('Charge guy')
                            ->money('INR')
                            ->state(fn (Bill $record): float => $record->chargeableTotal()),
                    ]),
            ]);
    }
}
