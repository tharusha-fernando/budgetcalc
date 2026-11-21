<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Project;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('vehicle')
                            ->placeholder('—'),
                        TextEntry::make('restoration_guy')
                            ->label('Restoration guy')
                            ->placeholder('—'),
                        TextEntry::make('agreed_amount')
                            ->money('INR')
                            ->placeholder('—'),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Cash plan')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('cash_on_hand')
                            ->label('Cash on hand')
                            ->money('INR')
                            ->placeholder('—'),
                        TextEntry::make('cash_to_load')
                            ->label('Cash I will load')
                            ->money('INR')
                            ->placeholder('—'),
                        TextEntry::make('available_cash')
                            ->label('Available cash')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->availableCash()),
                        TextEntry::make('cash_still_needed')
                            ->label('Need to save')
                            ->money('INR')
                            ->state(fn (Project $record): ?float => $record->cashStillNeeded())
                            ->placeholder('Set agreed amount')
                            ->color(fn (Project $record): string => ($record->cashStillNeeded() ?? 0) > 0 ? 'danger' : 'success')
                            ->helperText(function (Project $record): ?string {
                                $surplus = $record->cashSurplus();

                                if ($surplus === null) {
                                    return null;
                                }

                                if ($surplus > 0) {
                                    return 'Surplus ₹'.number_format($surplus, 2).' vs agreed amount';
                                }

                                return 'Compared to agreed amount.';
                            }),
                    ]),
                Section::make('Payments to restoration guy')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('paid_to_guy')
                            ->label('Paid to guy')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->paidToRestorationGuyTotal()),
                        TextEntry::make('agreed_for_payments')
                            ->label('Agreed amount')
                            ->money('INR')
                            ->state(fn (Project $record): ?float => $record->agreed_amount !== null
                                ? (float) $record->agreed_amount
                                : null)
                            ->placeholder('—'),
                        TextEntry::make('still_owed_to_guy')
                            ->label('Still to pay him')
                            ->money('INR')
                            ->state(fn (Project $record): ?float => $record->stillOwedToRestorationGuy())
                            ->placeholder('Set agreed amount')
                            ->color(fn (Project $record): string => ($record->stillOwedToRestorationGuy() ?? 0) > 0 ? 'warning' : 'success'),
                    ]),
                Section::make('Expense summary')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('gross_total')
                            ->label('Gross spent')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->grossTotal()),
                        TextEntry::make('returned_total')
                            ->label('Returned')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->returnedTotal()),
                        TextEntry::make('waived_total')
                            ->label('I pay (not claimed)')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->waivedTotal()),
                        TextEntry::make('guy_refunded')
                            ->label('Paid back by guy')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->refundedByRestorationGuyTotal()),
                        TextEntry::make('chargeable_total')
                            ->label('Charge restoration guy')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->chargeableTotal()),
                    ]),
            ]);
    }
}
