<?php

namespace App\Filament\Forms;

use App\Services\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageField
{
    /**
     * Upload into {app}/uploads/{directory} — served at /uploads/...
     * Auto-converts heavy JPEG/PNG to WebP when GD supports it.
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
            ->maxSize(8192)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
            ->disk('uploads')
            ->directory($directory)
            ->visibility('public')
            ->downloadable()
            ->openable()
            ->previewable()
            ->imagePreviewHeight('120')
            ->helperText('Saved to /uploads/'.$directory.'/. JPEG/PNG auto-optimized to WebP when possible. Max 8MB.')
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $component) use ($directory) {
                $disk = 'uploads';
                $filename = $file->hashName();
                $path = trim($directory, '/').'/'.$filename;
                $file->storeAs(trim($directory, '/'), $filename, $disk);

                return app(ImageOptimizer::class)->optimizeStored($disk, $path);
            })
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
            ->maxSize(8192)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->disk('uploads')
            ->directory($directory)
            ->visibility('public')
            ->downloadable()
            ->openable()
            ->previewable()
            ->helperText("Saved to /uploads/{$directory}/. Auto WebP for large JPEG/PNG. Up to {$max} images.")
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) use ($directory) {
                $disk = 'uploads';
                $filename = $file->hashName();
                $path = trim($directory, '/').'/'.$filename;
                $file->storeAs(trim($directory, '/'), $filename, $disk);

                return app(ImageOptimizer::class)->optimizeStored($disk, $path);
            })
            ->columnSpanFull();
    }
}
