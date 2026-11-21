<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use App\Filament\Resources\Bills\BillResource;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Project;
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
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

class BillItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'billItems';

    protected static ?string $title = 'Items';

    protected static ?string $recordTitleAttribute = 'description';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        if (! $ownerRecord instanceof Project) {
            return null;
        }

        $count = $ownerRecord->billItems()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bill_id')
                    ->label('Bill')
                    ->options(fn (): array => $this->billOptions())
                    ->required()
                    ->searchable()
                    ->preload(),
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('bill'))
            ->columns([
                TextColumn::make('bill.vendor')
                    ->label('Bill')
                    ->description(fn (BillItem $record): ?string => $record->bill?->bill_number
                        ? '#'.$record->bill->bill_number
                        : null)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bill.bill_date')
                    ->label('Bill date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
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
                    ->alignEnd()
                    ->summarize(
                        Summarizer::make()
                            ->label('Total')
                            ->using(function (QueryBuilder $query): string {
                                $total = (float) (clone $query)
                                    ->reorder()
                                    ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as aggregate')
                                    ->value('aggregate');

                                return '₹'.number_format($total, 2);
                            }),
                    ),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('refunded_by')
                    ->label('Refunded by')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('bill_id')
                    ->label('Bill')
                    ->options(fn (): array => $this->billOptions())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(BillItemStatus::class),
                SelectFilter::make('category')
                    ->options(BillItemCategory::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add item')
                    ->using(function (array $data): BillItem {
                        $bill = $this->findProjectBill((int) ($data['bill_id'] ?? 0));

                        abort_unless($bill instanceof Bill, 404);

                        return $bill->items()->create(collect($data)->except('bill_id')->all());
                    }),
            ])
            ->recordActions([
                Action::make('openBill')
                    ->label('Open bill')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn (BillItem $record): string => BillResource::getUrl('view', ['record' => $record->bill_id])),
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

    /**
     * @return array<int|string, string>
     */
    protected function billOptions(): array
    {
        $project = $this->getOwnerRecord();

        if (! $project instanceof Project) {
            return [];
        }

        return $project->bills()
            ->orderByDesc('bill_date')
            ->orderBy('vendor')
            ->get()
            ->mapWithKeys(function (Bill $bill): array {
                $label = $bill->vendor;

                if ($bill->bill_number) {
                    $label .= ' · #'.$bill->bill_number;
                }

                if ($bill->bill_date) {
                    $label .= ' · '.$bill->bill_date->format('d M Y');
                }

                return [$bill->id => $label];
            })
            ->all();
    }

    protected function findProjectBill(int $billId): ?Bill
    {
        $project = $this->getOwnerRecord();

        if (! $project instanceof Project || $billId < 1) {
            return null;
        }

        return $project->bills()->whereKey($billId)->first();
    }
}
