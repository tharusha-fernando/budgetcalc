<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;

class AttachmentsUpload
{
    public static function make(string $name = 'attachments', string $directory = 'attachments'): FileUpload
    {
        return FileUpload::make($name)
            ->label('Attachments')
            ->helperText('Add photos or PDFs. On your phone, tap browse → Take Photo (or choose from gallery). Add as many as you need.')
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->panelLayout('grid')
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes([
                'image/*',
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/heic',
                'image/heif',
                'application/pdf',
            ])
            // Do not set HTML capture= — on iOS that blocks multiple picks.
            // accept=image/* makes the phone offer Take Photo alongside gallery.
            ->extraInputAttributes([
                'accept' => 'image/*,application/pdf',
            ])
            ->maxSize(10240)
            ->downloadable()
            ->openable()
            ->previewable()
            ->columnSpanFull();
    }
}
