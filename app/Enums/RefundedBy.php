<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RefundedBy: string implements HasLabel
{
    case RestorationGuy = 'restoration_guy';
    case Store = 'store';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::RestorationGuy => 'Restoration guy',
            self::Store => 'Store / vendor',
            self::Other => 'Other',
        };
    }
}
