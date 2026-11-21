<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;

class BillAttachmentsUpload
{
    public static function make(string $name = 'attachments'): FileUpload
    {
        return AttachmentsUpload::make($name, 'bills/attachments')
            ->helperText('Upload bill photos or PDFs. On your phone, tap browse → Take Photo (or gallery). Multiple files OK.');
    }
}
