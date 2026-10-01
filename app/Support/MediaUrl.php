<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    /**
     * Resolve a stored path or absolute URL to a browser-usable URL.
     */
    public static function make(?string $path, string $disk = 'uploads'): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        // Strip accidental public/ prefix from older saves
        $path = preg_replace('#^public/#', '', $path) ?? $path;
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            return url($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return url($path);
        }

        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return url('uploads/'.$path);
        }
    }
}
