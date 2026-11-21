<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BillItemStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Returned = 'returned';
    case Waived = 'waived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Charge guy',
            self::Returned => 'Returned',
            self::Waived => 'I pay',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Returned => 'warning',
            self::Waived => 'gray',
        };
    }
}
