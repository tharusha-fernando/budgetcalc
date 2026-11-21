<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;

class BillAttachmentsUpload
{
    public static function make(string $name = 'attachments'): FileUpload
    {
        return FileUpload::make($name)
            ->label('Attachments')
            ->helperText('Upload bill photos, PDFs, or scans. You can open or download them later.')
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->disk('public')
            ->directory('bills/attachments')
            ->visibility('public')
            ->acceptedFileTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'application/pdf',
            ])
            ->maxSize(10240)
            ->downloadable()
            ->openable()
            ->previewable()
            ->columnSpanFull();
    }
}
