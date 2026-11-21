<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'name',
    'vehicle',
    'restoration_guy',
    'agreed_amount',
    'cash_on_hand',
    'cash_to_load',
    'notes',
    'is_active',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'agreed_amount' => 'decimal:2',
            'cash_on_hand' => 'decimal:2',
            'cash_to_load' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function billItems(): HasManyThrough
    {
        return $this->hasManyThrough(BillItem::class, Bill::class);
    }

    public function restorationPayments(): HasMany
    {
        return $this->hasMany(RestorationPayment::class);
    }

    public function grossTotal(): float
    {
        return BillItem::totalFor($this->billItems());
    }

    public function returnedTotal(): float
    {
        return BillItem::totalFor($this->billItems()->returned());
    }

    public function waivedTotal(): float
    {
        return BillItem::totalFor($this->billItems()->waived());
    }

    public function chargeableTotal(): float
    {
        return BillItem::totalFor($this->billItems()->active());
    }

    public function refundedByRestorationGuyTotal(): float
    {
        return BillItem::totalFor($this->billItems()->refundedByRestorationGuy());
    }

    public function availableCash(): float
    {
        return (float) ($this->cash_on_hand ?? 0) + (float) ($this->cash_to_load ?? 0);
    }

    public function cashStillNeeded(): ?float
    {
        if ($this->agreed_amount === null) {
            return null;
        }

        return max(0.0, (float) $this->agreed_amount - $this->availableCash());
    }

    public function cashSurplus(): ?float
    {
        if ($this->agreed_amount === null) {
            return null;
        }

        return max(0.0, $this->availableCash() - (float) $this->agreed_amount);
    }

    public function paidToRestorationGuyTotal(): float
    {
        return (float) $this->restorationPayments()->sum('amount');
    }

    public function stillOwedToRestorationGuy(): ?float
    {
        if ($this->agreed_amount === null) {
            return null;
        }

        return max(0.0, (float) $this->agreed_amount - $this->paidToRestorationGuyTotal());
    }
}
