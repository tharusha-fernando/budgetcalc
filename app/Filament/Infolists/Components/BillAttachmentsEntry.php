<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

class BillAttachmentsEntry
{
    public static function make(string $name = 'attachments'): TextEntry
    {
        return AttachmentsEntry::make($name);
    }
}
