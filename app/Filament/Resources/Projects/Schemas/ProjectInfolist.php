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
                    ->columns(7)
                    ->schema([
                        TextEntry::make('cash_on_hand')
                            ->label('Cash on hand')
                            ->money('INR')
                            ->placeholder('—'),
                        TextEntry::make('cash_to_load')
                            ->label('Cash I will load')
                            ->money('INR')
                            ->placeholder('—')
                            ->helperText('Loan / cash you will add.'),
                        TextEntry::make('spent_by_me')
                            ->label('I already spent')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->spentByMeTotal())
                            ->helperText('Parts / labor paid by me that are not returned.'),
                        TextEntry::make('paid_to_guy_in_plan')
                            ->label('Paid to guy')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->paidToRestorationGuyTotal()),
                        TextEntry::make('budget_covered')
                            ->label('Total covered')
                            ->money('INR')
                            ->state(fn (Project $record): float => $record->budgetCoveredTotal())
                            ->helperText('Paid guy + spent parts + on hand + loan.'),
                        TextEntry::make('cash_still_needed')
                            ->label('Need to save')
                            ->money('INR')
                            ->state(fn (Project $record): ?float => $record->cashStillNeeded())
                            ->placeholder('Set agreed amount')
                            ->visible(fn (Project $record): bool => ($record->cashStillNeeded() ?? 0) > 0)
                            ->color('danger')
                            ->weight('bold')
                            ->helperText('Agreed − total covered.'),
                        TextEntry::make('cash_surplus')
                            ->label('Surplus')
                            ->money('INR')
                            ->state(fn (Project $record): ?float => $record->cashSurplus())
                            ->placeholder('Set agreed amount')
                            ->visible(fn (Project $record): bool => ($record->cashSurplus() ?? 0) > 0)
                            ->color('success')
                            ->weight('bold')
                            ->helperText('Total covered is more than agreed.'),
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
