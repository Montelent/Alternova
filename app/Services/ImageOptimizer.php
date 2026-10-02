<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Convert uploaded raster images to optimized WebP (GD).
 * SVG/GIF animated left as-is.
 */
class ImageOptimizer
{
    public function optimizeStored(string $disk, string $path, int $quality = 82, int $maxWidth = 1920): string
    {
        try {
            $storage = Storage::disk($disk);
            if (! $storage->exists($path)) {
                return $path;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['svg', 'gif', 'webp'], true)) {
                // Already webp or vector/animation — skip re-encode for gif/svg
                if ($ext === 'webp') {
                    return $path;
                }
                if (in_array($ext, ['svg', 'gif'], true)) {
                    return $path;
                }
            }

            if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
                return $path;
            }

            $absolute = $storage->path($path);
            $binary = @file_get_contents($absolute);
            if ($binary === false || $binary === '') {
                return $path;
            }

            // Skip tiny files
            if (strlen($binary) < 8 * 1024) {
                return $path;
            }

            $img = @imagecreatefromstring($binary);
            if ($img === false) {
                return $path;
            }

            $w = imagesx($img);
            $h = imagesy($img);
            if ($w > $maxWidth) {
                $nw = $maxWidth;
                $nh = (int) max(1, round($h * ($maxWidth / $w)));
                $resized = imagecreatetruecolor($nw, $nh);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagedestroy($img);
                $img = $resized;
            }

            imagealphablending($img, true);
            imagesavealpha($img, true);

            $dir = trim(str_replace('\\', '/', dirname($path)), '.');
            $base = pathinfo($path, PATHINFO_FILENAME);
            $newRel = ($dir !== '' ? $dir.'/' : '').$base.'.webp';

            $tmp = sys_get_temp_dir().'/altn_'.Str::random(12).'.webp';
            $ok = imagewebp($img, $tmp, $quality);
            imagedestroy($img);

            if (! $ok || ! is_file($tmp)) {
                @unlink($tmp);

                return $path;
            }

            $storage->put($newRel, file_get_contents($tmp));
            @unlink($tmp);

            // Remove original if different path
            if ($newRel !== $path) {
                $storage->delete($path);
            }

            return $newRel;
        } catch (\Throwable $e) {
            Log::debug('Image optimize failed: '.$e->getMessage());

            return $path;
        }
    }
}
