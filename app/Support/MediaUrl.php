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

        // Already absolute
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        // Leading /public or /storage variants
        if (str_starts_with($path, '/uploads/') || str_starts_with($path, 'uploads/')) {
            return asset(ltrim($path, '/'));
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return asset(ltrim($path, '/'));
        }

        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
        } catch (\Throwable) {
        }

        // Fallback: treat as relative under uploads
        try {
            return Storage::disk('uploads')->url($path);
        } catch (\Throwable) {
            return asset('uploads/'.ltrim($path, '/'));
        }
    }
}
