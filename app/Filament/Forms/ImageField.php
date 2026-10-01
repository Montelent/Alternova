<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;

class ImageField
{
    /**
     * Direct file upload into public/uploads (no storage symlink required).
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
            ->helperText('Upload from device (max 5MB). Saved under /uploads/'.$directory.'/')
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
            ->helperText("Upload up to {$max} images (max 5MB each). Stored in /uploads/{$directory}/")
            ->columnSpanFull();
    }
}
