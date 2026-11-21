<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BillItemCategory: string implements HasLabel
{
    case Parts = 'parts';
    case Labor = 'labor';
    case Materials = 'materials';
    case Tools = 'tools';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Parts => 'Parts',
            self::Labor => 'Labor / Workers',
            self::Materials => 'Materials',
            self::Tools => 'Tools',
            self::Other => 'Other',
        };
    }
}
