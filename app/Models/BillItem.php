<?php

namespace App\Models;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use Database\Factories\BillItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

#[Fillable([
    'bill_id',
    'description',
    'category',
    'quantity',
    'unit_price',
    'status',
    'refunded_by',
    'settled_at',
    'refunded_amount',
    'notes',
])]
class BillItem extends Model
{
    /** @use HasFactory<BillItemFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'quantity' => 1,
        'category' => 'parts',
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'category' => BillItemCategory::class,
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'status' => BillItemStatus::class,
            'refunded_by' => RefundedBy::class,
            'settled_at' => 'date',
            'refunded_amount' => 'decimal:2',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(
            fn (): string => number_format((float) $this->quantity * (float) $this->unit_price, 2, '.', ''),
        );
    }

    public function effectiveRefundAmount(): float
    {
        if ($this->status !== BillItemStatus::Returned) {
            return 0.0;
        }

        if ($this->refunded_amount !== null) {
            return (float) $this->refunded_amount;
        }

        return (float) $this->line_total;
    }

    /**
     * @param  Builder<BillItem>  $query
     * @return Builder<BillItem>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', BillItemStatus::Active);
    }

    /**
     * @param  Builder<BillItem>  $query
     * @return Builder<BillItem>
     */
    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('status', BillItemStatus::Returned);
    }

    /**
     * @param  Builder<BillItem>  $query
     * @return Builder<BillItem>
     */
    public function scopeWaived(Builder $query): Builder
    {
        return $query->where('status', BillItemStatus::Waived);
    }

    /**
     * @param  Builder<BillItem>  $query
     * @return Builder<BillItem>
     */
    public function scopeRefundedByRestorationGuy(Builder $query): Builder
    {
        return $query
            ->returned()
            ->where('refunded_by', RefundedBy::RestorationGuy);
    }

    /**
     * @param  Builder<BillItem>|\Illuminate\Database\Eloquent\Relations\Relation<*, BillItem, *>  $query
     */
    public static function totalFor(Builder|Relation $query): float
    {
        $builder = $query instanceof Builder ? $query->clone() : $query->getQuery()->clone();

        return (float) $builder
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as aggregate')
            ->value('aggregate');
    }

    public function markReturned(?RefundedBy $refundedBy = null, ?float $refundedAmount = null, ?string $notes = null): void
    {
        $this->update([
            'status' => BillItemStatus::Returned,
            'refunded_by' => $refundedBy,
            'refunded_amount' => $refundedAmount,
            'settled_at' => now()->toDateString(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    public function markWaived(?string $notes = null): void
    {
        $this->update([
            'status' => BillItemStatus::Waived,
            'refunded_by' => null,
            'refunded_amount' => null,
            'settled_at' => now()->toDateString(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    public function restoreActive(): void
    {
        $this->update([
            'status' => BillItemStatus::Active,
            'refunded_by' => null,
            'refunded_amount' => null,
            'settled_at' => null,
        ]);
    }
}
