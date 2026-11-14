<?php

namespace App\Support;

use App\Models\SiteSetting;
use App\Support\PublicHttpUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Site branding for any install / domain (script buyers).
 */
class WhiteLabelSettings
{
    public static function defaults(): array
    {
        $name = config('app.name', 'Alternova');

        return [
            'brand_site_name' => $name,
            'brand_tagline' => 'Open-source alternatives and brandable domain ideas.',
            'brand_admin_name' => $name.' Admin',
            'brand_primary' => '#4f46e5',
            'brand_letter' => strtoupper(substr($name, 0, 1)) ?: 'A',
            'brand_footer_text' => '',
            'brand_support_email' => '',
            'brand_logo_path' => '',
            'brand_logo_url' => '',
            'brand_favicon_path' => '',
            'brand_favicon_url' => '',
            'admin_path' => 'admin',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $defaults = self::defaults();

        return SiteSetting::get($key, $defaults[$key] ?? $default);
    }

    public static function siteName(): string
    {
        $n = trim((string) self::get('brand_site_name', config('app.name', 'Alternova')));

        return $n !== '' ? $n : (string) config('app.name', 'Alternova');
    }

    public static function adminName(): string
    {
        $n = trim((string) self::get('brand_admin_name', ''));

        return $n !== '' ? $n : self::siteName().' Admin';
    }

    public static function tagline(): string
    {
        return (string) self::get('brand_tagline', self::defaults()['brand_tagline']);
    }

    public static function letter(): string
    {
        $l = trim((string) self::get('brand_letter', ''));
        if ($l === '') {
            $l = strtoupper(substr(self::siteName(), 0, 1)) ?: 'A';
        }

        return strtoupper(substr($l, 0, 1));
    }

    public static function primary(): string
    {
        $hex = trim((string) self::get('brand_primary', '#4f46e5'));
        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            return '#4f46e5';
        }

        return strtolower($hex);
    }

    public static function footerText(): string
    {
        return trim((string) self::get('brand_footer_text', ''));
    }

    public static function supportEmail(): string
    {
        return trim((string) self::get('brand_support_email', ''));
    }

    public static function logoUrl(): ?string
    {
        $path = trim((string) self::get('brand_logo_path', ''));
        if ($path !== '') {
            // Logos live under public/uploads (Hostinger); /storage/... often 403
            return MediaUrl::make($path, 'uploads');
        }
        $url = trim((string) self::get('brand_logo_url', ''));

        return $url !== '' ? $url : null;
    }

    public static function faviconUrl(): ?string
    {
        $path = trim((string) self::get('brand_favicon_path', ''));
        if ($path !== '') {
            return MediaUrl::make($path, 'uploads');
        }
        $url = trim((string) self::get('brand_favicon_url', ''));

        return $url !== '' ? $url : null;
    }

    public static function importRemoteImage(string $url, string $kind = 'logo'): ?string
    {
        $url = trim($url);
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL) || ! PublicHttpUrl::isSafe($url)) {
            return null;
        }

        try {
            $response = Http::timeout(8)
                ->connectTimeout(4)
                ->withOptions([
                    'allow_redirects' => PublicHttpUrl::safeRedirectOptions(),
                    'stream' => true,
                ])
                ->get($url);

            $stream = $response->toPsrResponse()->getBody();
            if (! $response->successful()) {
                $stream->close();

                return null;
            }

            // Branding imports are raster-only and bounded to prevent SSRF
            // response bodies from exhausting PHP memory or writing active SVG.
            $body = $stream->read((8 * 1024 * 1024) + 1);
            $stream->close();
            if (strlen($body) > 8 * 1024 * 1024) {
                return null;
            }

            $info = @getimagesizefromstring($body);
            $mime = is_array($info) ? strtolower((string) ($info['mime'] ?? '')) : '';
            $extensions = [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/x-icon' => 'ico',
                'image/vnd.microsoft.icon' => 'ico',
            ];

            if (! is_array($info)
                || ! isset($extensions[$mime])
                || ($info[0] ?? 0) < 1
                || ($info[1] ?? 0) < 1
                || $info[0] > 5000
                || $info[1] > 5000
                || ($info[0] * $info[1]) > 25000000
            ) {
                return null;
            }

            $name = 'branding/'.Str::slug($kind).'-'.Str::random(8).'.'.$extensions[$mime];
            Storage::disk('uploads')->put($name, $body);

            return $name;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{50: string, 100: string, 500: string, 600: string, 700: string} */
    public static function palette(): array
    {
        $hex = self::primary();

        return [
            '50' => self::mix($hex, '#ffffff', 0.92),
            '100' => self::mix($hex, '#ffffff', 0.85),
            '500' => self::mix($hex, '#ffffff', 0.15),
            '600' => $hex,
            '700' => self::mix($hex, '#000000', 0.18),
        ];
    }

    protected static function mix(string $hex, string $with, float $ratio): string
    {
        $a = self::hexToRgb($hex);
        $b = self::hexToRgb($with);
        $r = (int) round($a[0] * (1 - $ratio) + $b[0] * $ratio);
        $g = (int) round($a[1] * (1 - $ratio) + $b[1] * $ratio);
        $bl = (int) round($a[2] * (1 - $ratio) + $b[2] * $ratio);

        return sprintf('#%02x%02x%02x', $r, $g, $bl);
    }

    /** @return array{0: int, 1: int, 2: int} */
    protected static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
