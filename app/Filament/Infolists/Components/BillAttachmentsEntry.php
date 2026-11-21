<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BillAttachmentsEntry
{
    public static function make(string $name = 'attachments'): TextEntry
    {
        return TextEntry::make($name)
            ->label('Attachments')
            ->columnSpanFull()
            ->html()
            ->placeholder('No attachments')
            ->formatStateUsing(function (mixed $state): string {
                $paths = collect(is_array($state) ? $state : [])
                    ->filter(fn (mixed $path): bool => is_string($path) && filled($path))
                    ->values();

                if ($paths->isEmpty()) {
                    return '—';
                }

                return $paths
                    ->map(function (string $path): string {
                        $url = e(Storage::disk('public')->url($path));
                        $name = e(basename($path));
                        $isImage = Str::endsWith(Str::lower($path), ['.jpg', '.jpeg', '.png', '.gif', '.webp']);

                        $preview = $isImage
                            ? "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener\" class=\"mb-2 block\"><img src=\"{$url}\" alt=\"{$name}\" class=\"max-h-40 rounded-lg border border-gray-200 object-contain dark:border-white/10\" /></a>"
                            : '';

                        return <<<HTML
                            <div class="mb-3 rounded-lg border border-gray-200 p-3 dark:border-white/10">
                                {$preview}
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span class="font-medium">{$name}</span>
                                    <a href="{$url}" target="_blank" rel="noopener" class="text-amber-600 underline">View</a>
                                    <a href="{$url}" download class="text-amber-600 underline">Download</a>
                                </div>
                            </div>
                        HTML;
                    })
                    ->implode('');
            });
    }
}
