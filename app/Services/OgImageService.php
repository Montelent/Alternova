<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Facades\File;

class OgImageService
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    public function alternativeUrl(OpenSourceAlternative $alt): string
    {
        if (! empty($alt->og_image_url)) {
            return $alt->og_image_url;
        }

        return url('/og/alternative/'.$alt->slug.'.png');
    }

    public function toolUrl(ProprietaryTool $tool): string
    {
        if (! empty($tool->og_image_url)) {
            return $tool->og_image_url;
        }

        return url('/og/tool/'.$tool->slug.'.png');
    }

    public function pathForAlternative(string $slug): string
    {
        return storage_path('app/og/alternative-'.$slug.'.png');
    }

    public function pathForTool(string $slug): string
    {
        return storage_path('app/og/tool-'.$slug.'.png');
    }

    public function ensureDir(): void
    {
        $dir = storage_path('app/og');
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    public function gdAvailable(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    /**
     * Build or refresh cached PNG for an alternative.
     */
    public function renderAlternative(OpenSourceAlternative $alt, bool $force = false): string
    {
        $this->ensureDir();
        $path = $this->pathForAlternative($alt->slug);

        if (! $force && is_file($path) && filemtime($path) >= $alt->updated_at?->getTimestamp()) {
            return $path;
        }

        if (! $this->gdAvailable()) {
            throw new \RuntimeException('GD extension is not available');
        }

        $prop = $alt->proprietaryTool?->name ?? 'proprietary software';
        $subtitle = 'Open-source alternative to '.$prop;
        $meta = [];
        if ($alt->license_type) {
            $meta[] = $alt->license_type;
        }
        if ($alt->overall_health_score > 0) {
            $meta[] = 'Health '.round($alt->overall_health_score);
        }
        if ($alt->repoMetric?->github_stars) {
            $meta[] = '★ '.number_format((int) $alt->repoMetric->github_stars);
        }

        $this->paint(
            $path,
            $alt->name,
            $subtitle,
            implode('  ·  ', $meta),
            method_exists($alt, 'hasActiveSponsorship') && $alt->hasActiveSponsorship()
                ? ($alt->sponsor_label ?: 'Sponsored')
                : null
        );

        return $path;
    }

    public function renderTool(ProprietaryTool $tool, bool $force = false): string
    {
        $this->ensureDir();
        $path = $this->pathForTool($tool->slug);

        if (! $force && is_file($path) && filemtime($path) >= $tool->updated_at?->getTimestamp()) {
            return $path;
        }

        if (! $this->gdAvailable()) {
            throw new \RuntimeException('GD extension is not available');
        }

        $count = $tool->publishedAlternatives()->count();
        $subtitle = $count > 0
            ? $count.' open-source alternative'.($count === 1 ? '' : 's').' on Alternova'
            : 'Open-source alternatives on Alternova';

        $this->paint(
            $path,
            $tool->name,
            $subtitle,
            'Proprietary software · Compare FOSS options',
            null
        );

        return $path;
    }

    protected function paint(string $path, string $title, string $subtitle, string $meta, ?string $badge): void
    {
        $w = self::WIDTH;
        $h = self::HEIGHT;
        $im = imagecreatetruecolor($w, $h);

        // Background gradient-ish: dark slate
        $bg = imagecolorallocate($im, 15, 23, 42); // slate-950
        imagefilledrectangle($im, 0, 0, $w, $h, $bg);

        // Accent bar top
        $accent = imagecolorallocate($im, 79, 70, 229); // indigo-600
        imagefilledrectangle($im, 0, 0, $w, 8, $accent);

        // Soft orb
        $orb = imagecolorallocatealpha($im, 99, 102, 241, 100);
        imagefilledellipse($im, 980, 120, 420, 420, $orb);

        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = imagecolorallocate($im, 148, 163, 184);
        $brand = imagecolorallocate($im, 165, 180, 252);
        $amber = imagecolorallocate($im, 252, 211, 77);

        $font = $this->fontPath();
        $useTtf = $font !== null && function_exists('imagettftext');

        // Brand mark
        if ($useTtf) {
            imagettftext($im, 22, 0, 72, 80, $brand, $font, 'Alternova');
        } else {
            imagestring($im, 5, 72, 50, 'Alternova', $brand);
        }

        if ($badge) {
            $badgeText = strtoupper(mb_substr($badge, 0, 24));
            if ($useTtf) {
                imagettftext($im, 16, 0, 72, 130, $amber, $font, $badgeText);
            } else {
                imagestring($im, 3, 72, 110, $badgeText, $amber);
            }
        }

        // Title (wrap roughly)
        $titleLines = $this->wrap($title, 28);
        $y = $badge ? 210 : 180;
        foreach ($titleLines as $i => $line) {
            if ($i >= 3) {
                break;
            }
            if ($useTtf) {
                imagettftext($im, 48, 0, 72, $y + ($i * 64), $white, $font, $line);
            } else {
                imagestring($im, 5, 72, $y - 40 + ($i * 28), $line, $white);
            }
        }

        $subY = $y + (min(count($titleLines), 3) * 64) + 20;
        $subLines = $this->wrap($subtitle, 48);
        foreach ($subLines as $i => $line) {
            if ($i >= 2) {
                break;
            }
            if ($useTtf) {
                imagettftext($im, 22, 0, 72, $subY + ($i * 34), $muted, $font, $line);
            } else {
                imagestring($im, 3, 72, $subY - 20 + ($i * 18), $line, $muted);
            }
        }

        if ($meta !== '') {
            if ($useTtf) {
                imagettftext($im, 18, 0, 72, 560, $brand, $font, $meta);
            } else {
                imagestring($im, 3, 72, 540, $meta, $brand);
            }
        }

        // Footer domain
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'alternova';
        if ($useTtf) {
            imagettftext($im, 16, 0, 72, 600, $muted, $font, $host);
        } else {
            imagestring($im, 2, 72, 580, $host, $muted);
        }

        imagepng($im, $path, 6);
        imagedestroy($im);
    }

    /**
     * @return list<string>
     */
    protected function wrap(string $text, int $maxChars): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            return [''];
        }

        $words = explode(' ', $text);
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $try = $current === '' ? $word : $current.' '.$word;
            if (mb_strlen($try) > $maxChars && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $try;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    protected function fontPath(): ?string
    {
        $candidates = [
            public_path('fonts/Inter-Bold.ttf'),
            public_path('fonts/DejaVuSans-Bold.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
