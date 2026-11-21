<?php

namespace App\Filament\Pages;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\PaymentMethod;
use App\Enums\RefundedBy;
use App\Filament\Forms\Components\BillAttachmentsUpload;
use App\Filament\Resources\Bills\BillResource;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Project;
use App\Models\RestorationPayment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class ExpenseReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Expenses';

    protected static ?string $navigationLabel = 'Clearance';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Clearance';

    protected string $view = 'filament.pages.expense-report';

    public ?int $project_id = null;

    public function mount(): void
    {
        $this->project_id = Project::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->value('id');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('addBill')
                ->label('Add bill')
                ->model(Bill::class)
                ->visible(fn (): bool => filled($this->project_id))
                ->schema([
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
                        ->numeric()
                        ->prefix('₹'),
                    Textarea::make('notes')
                        ->rows(2),
                    BillAttachmentsUpload::make(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['project_id'] = $this->project_id;

                    return $data;
                })
                ->successNotificationTitle('Bill added'),
            CreateAction::make('payGuy')
                ->label('Pay guy')
                ->model(RestorationPayment::class)
                ->color('warning')
                ->visible(fn (): bool => filled($this->project_id))
                ->modalHeading('Record payment to restoration guy')
                ->schema([
                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->prefix('₹')
                        ->minValue(0.01),
                    Select::make('method')
                        ->options(PaymentMethod::class)
                        ->default(PaymentMethod::Cash)
                        ->required(),
                    DatePicker::make('paid_on')
                        ->label('Paid on')
                        ->required()
                        ->default(now()),
                    Textarea::make('notes')
                        ->rows(2),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['project_id'] = $this->project_id;

                    return $data;
                })
                ->successNotificationTitle('Payment recorded'),
        ];
    }

    public function addItemAction(): Action
    {
        return Action::make('addItem')
            ->label('Add item')
            ->icon(Heroicon::OutlinedPlus)
            ->modalHeading('Add item to bill')
            ->schema([
                TextInput::make('description')
                    ->required()
                    ->maxLength(255),
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
                Select::make('status')
                    ->label('Who pays?')
                    ->options([
                        BillItemStatus::Active->value => 'Charge restoration guy',
                        BillItemStatus::Waived->value => 'I pay (do not claim)',
                    ])
                    ->default(BillItemStatus::Active->value)
                    ->required()
                    ->helperText('Choose whether this item should be claimed from the restoration guy.'),
                Textarea::make('notes')
                    ->rows(2),
            ])
            ->action(function (array $data, array $arguments): void {
                $bill = Bill::query()
                    ->whereKey($arguments['billId'] ?? null)
                    ->where('project_id', $this->project_id)
                    ->first();

                if (! $bill) {
                    Notification::make()
                        ->title('Bill not found')
                        ->danger()
                        ->send();

                    return;
                }

                $status = BillItemStatus::from($data['status']);

                BillItem::query()->create([
                    'bill_id' => $bill->id,
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'quantity' => $data['quantity'],
                    'unit_price' => $data['unit_price'],
                    'status' => $status,
                    'settled_at' => $status === BillItemStatus::Waived ? now()->toDateString() : null,
                    'notes' => $data['notes'] ?? null,
                ]);

                Notification::make()
                    ->title('Item added')
                    ->success()
                    ->send();
            });
    }

    public function getProjectProperty(): ?Project
    {
        if (! $this->project_id) {
            return null;
        }

        return Project::query()->find($this->project_id);
    }

    /**
     * @return Collection<int, Bill>
     */
    public function getBillsProperty(): Collection
    {
        if (! $this->project_id) {
            return collect();
        }

        return Bill::query()
            ->with(['items' => fn ($query) => $query->orderBy('id')])
            ->where('project_id', $this->project_id)
            ->orderBy('bill_date')
            ->get();
    }

    /**
     * @return array{gross: float, returned: float, waived: float, chargeable: float, guy_refunds: float, store_refunds: float, agreed: float|null, difference: float|null, paid_to_guy: float, still_owed_to_guy: float|null}
     */
    public function getSummaryProperty(): array
    {
        $project = $this->project;

        if (! $project) {
            return [
                'gross' => 0.0,
                'returned' => 0.0,
                'waived' => 0.0,
                'chargeable' => 0.0,
                'guy_refunds' => 0.0,
                'store_refunds' => 0.0,
                'agreed' => null,
                'difference' => null,
                'paid_to_guy' => 0.0,
                'still_owed_to_guy' => null,
            ];
        }

        $chargeable = $project->chargeableTotal();
        $agreed = $project->agreed_amount !== null ? (float) $project->agreed_amount : null;

        return [
            'gross' => $project->grossTotal(),
            'returned' => $project->returnedTotal(),
            'waived' => $project->waivedTotal(),
            'chargeable' => $chargeable,
            'guy_refunds' => $project->refundedByRestorationGuyTotal(),
            'store_refunds' => BillItem::totalFor(
                $project->billItems()
                    ->returned()
                    ->where('refunded_by', RefundedBy::Store),
            ),
            'agreed' => $agreed,
            'difference' => $agreed === null ? null : $chargeable - $agreed,
            'paid_to_guy' => $project->paidToRestorationGuyTotal(),
            'still_owed_to_guy' => $project->stillOwedToRestorationGuy(),
        ];
    }

    public function billUrl(Bill $bill): string
    {
        return BillResource::getUrl('view', ['record' => $bill]);
    }

    public function markIPayAction(): Action
    {
        return Action::make('markIPay')
            ->label('I pay')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Are you sure?')
            ->modalDescription('Are you sure you want to mark this as I pay? You will cover this cost yourself and it will not be claimed from the restoration guy.')
            ->modalSubmitActionLabel('Yes, I pay')
            ->action(function (array $arguments): void {
                $item = $this->findProjectItem((int) ($arguments['itemId'] ?? 0));

                if (! $item) {
                    return;
                }

                $item->markWaived('Paid by me — not claimed from restoration guy.');

                Notification::make()
                    ->title('Marked as I pay')
                    ->body($item->description.' will not be claimed from the restoration guy.')
                    ->success()
                    ->send();
            });
    }

    public function markChargeGuyAction(): Action
    {
        return Action::make('markChargeGuy')
            ->label('Charge guy')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Charge the restoration guy?')
            ->modalDescription('This item will count toward the amount claimed from the restoration guy.')
            ->modalSubmitActionLabel('Yes, charge guy')
            ->action(function (array $arguments): void {
                $item = $this->findProjectItem((int) ($arguments['itemId'] ?? 0));

                if (! $item) {
                    return;
                }

                $item->restoreActive();

                Notification::make()
                    ->title('Marked as charge guy')
                    ->body($item->description.' counts toward the restoration guy settlement.')
                    ->success()
                    ->send();
            });
    }

    public function markReturnedAction(): Action
    {
        return Action::make('markReturned')
            ->label('Mark returned')
            ->color('warning')
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
            ->action(function (array $data, array $arguments): void {
                $item = $this->findProjectItem((int) ($arguments['itemId'] ?? 0));

                if (! $item) {
                    return;
                }

                $item->update([
                    'status' => BillItemStatus::Returned,
                    'refunded_by' => $data['refunded_by'],
                    'refunded_amount' => $data['refunded_amount'] ?: null,
                    'settled_at' => $data['settled_at'],
                    'notes' => $data['notes'] ?: $item->notes,
                ]);

                Notification::make()
                    ->title('Marked as returned')
                    ->body($item->description)
                    ->success()
                    ->send();
            });
    }

    public function deleteItemAction(): Action
    {
        return Action::make('deleteItem')
            ->label('Delete')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete this item?')
            ->modalDescription('This permanently removes the item from the bill.')
            ->modalSubmitActionLabel('Yes, delete')
            ->action(function (array $arguments): void {
                $item = $this->findProjectItem((int) ($arguments['itemId'] ?? 0));

                if (! $item) {
                    return;
                }

                $description = $item->description;
                $item->delete();

                Notification::make()
                    ->title('Item deleted')
                    ->body($description)
                    ->success()
                    ->send();
            });
    }

    protected function findProjectItem(int $itemId): ?BillItem
    {
        if (! $this->project_id) {
            return null;
        }

        return BillItem::query()
            ->whereKey($itemId)
            ->whereHas('bill', fn ($query) => $query->where('project_id', $this->project_id))
            ->first();
    }
}
