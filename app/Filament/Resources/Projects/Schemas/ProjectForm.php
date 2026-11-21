<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('vehicle')
                            ->label('Vehicle')
                            ->maxLength(255),
                        TextInput::make('restoration_guy')
                            ->label('Restoration guy')
                            ->maxLength(255),
                        TextInput::make('agreed_amount')
                            ->label('Agreed amount')
                            ->numeric()
                            ->prefix('₹')
                            ->helperText('Optional agreed settlement amount with the restoration guy.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Cash plan')
                    ->description('Compared against the agreed amount, so you know how much more cash to save.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cash_on_hand')
                            ->label('Cash on hand')
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0)
                            ->helperText('Cash you already have ready for this project.'),
                        TextInput::make('cash_to_load')
                            ->label('Cash I will load')
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0)
                            ->helperText('Extra cash you plan to load / put in.'),
                    ]),
            ]);
    }
}
