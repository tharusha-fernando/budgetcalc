<?php

namespace App\Models;

use Database\Factories\BillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_id',
    'vendor',
    'bill_number',
    'bill_date',
    'stated_total',
    'notes',
    'attachments',
])]
class Bill extends Model
{
    /** @use HasFactory<BillFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'bill_date' => 'date',
            'stated_total' => 'decimal:2',
            'attachments' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function grossTotal(): float
    {
        return BillItem::totalFor($this->items());
    }

    public function returnedTotal(): float
    {
        return BillItem::totalFor($this->items()->returned());
    }

    public function waivedTotal(): float
    {
        return BillItem::totalFor($this->items()->waived());
    }

    public function chargeableTotal(): float
    {
        return BillItem::totalFor($this->items()->active());
    }
}
