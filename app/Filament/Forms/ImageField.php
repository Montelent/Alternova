<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;

class ImageField
{
    /**
     * Upload into {app}/uploads/{directory} — served at /uploads/...
     * Uses the dedicated "uploads" disk (base_path, not public/).
     */
    public static function make(
        string $name,
        string $label = 'Image',
        string $directory = 'general',
        bool $withUrlFallback = true,
        string $urlField = 'image_url',
    ): array {
        $upload = FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageEditor()
            ->imageEditorAspectRatios([
                null,
                '1:1',
                '16:9',
                '4:3',
            ])
            ->maxSize(5120)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
            ->disk('uploads')
            ->directory($directory)
            ->visibility('public')
            ->downloadable()
            ->openable()
            ->previewable()
            ->imagePreviewHeight('120')
            ->helperText('Saved to /uploads/'.$directory.'/ (site root). Max 5MB.')
            ->columnSpanFull();

        if (! $withUrlFallback) {
            return [$upload];
        }

        return [
            Group::make([
                $upload,
                TextInput::make($urlField)
                    ->label($label.' URL (optional)')
                    ->url()
                    ->maxLength(500)
                    ->helperText('Or paste an external image URL if you prefer not to upload.')
                    ->columnSpanFull(),
            ])->columnSpanFull(),
        ];
    }

    public static function multiple(
        string $name,
        string $label = 'Images',
        string $directory = 'gallery',
        int $max = 12,
    ): FileUpload {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->multiple()
            ->reorderable()
            ->maxFiles($max)
            ->imageEditor()
            ->maxSize(5120)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->disk('uploads')
            ->directory($directory)
            ->visibility('public')
            ->downloadable()
            ->openable()
            ->previewable()
            ->helperText("Saved to /uploads/{$directory}/. Up to {$max} images, max 5MB each.")
            ->columnSpanFull();
    }
}
