<?php

namespace App\Filament\Resources\Bills\Schemas;

use App\Filament\Forms\Components\BillAttachmentsUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bill details')
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('vehicle'),
                                TextInput::make('restoration_guy'),
                            ]),
                        TextInput::make('vendor')
                            ->label('Vendor / store')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('bill_number')
                            ->label('Bill / invoice #')
                            ->maxLength(255),
                        DatePicker::make('bill_date')
                            ->required()
                            ->default(now()),
                        TextInput::make('stated_total')
                            ->label('Printed bill total')
                            ->helperText('Optional. What the paper bill says, for cross-checking.')
                            ->numeric()
                            ->prefix('₹'),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        BillAttachmentsUpload::make(),
                    ]),
            ]);
    }
}
