<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Services\OgImageService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class OgImageController extends Controller
{
    public function alternative(string $slug, OgImageService $og): Response
    {
        $slug = preg_replace('/\.png$/i', '', $slug) ?? $slug;

        $alt = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        try {
            $path = $og->renderAlternative($alt);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Throwable) {
            return $this->svgFallback($alt->name, 'Open-source alternative');
        }
    }

    public function tool(string $slug, OgImageService $og): Response
    {
        $slug = preg_replace('/\.png$/i', '', $slug) ?? $slug;

        $tool = ProprietaryTool::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        try {
            $path = $og->renderTool($tool);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Throwable) {
            return $this->svgFallback($tool->name, 'Open-source alternatives');
        }
    }

    public function collection(string $slug, OgImageService $og): Response
    {
        $slug = preg_replace('/\.png$/i', '', $slug) ?? $slug;

        abort_unless(Schema::hasTable('collections'), 404);

        $collection = Collection::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        try {
            $path = $og->renderCollection($collection);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Throwable) {
            return $this->svgFallback($collection->name, 'Curated collection');
        }
    }

    public function compare(string $a, string $b, OgImageService $og): Response
    {
        $a = preg_replace('/\.png$/i', '', $a) ?? $a;
        $b = preg_replace('/\.png$/i', '', $b) ?? $b;

        $left = OpenSourceAlternative::query()
            ->with(['repoMetric'])
            ->where('slug', $a)
            ->where('is_published', true)
            ->firstOrFail();

        $right = OpenSourceAlternative::query()
            ->with(['repoMetric'])
            ->where('slug', $b)
            ->where('is_published', true)
            ->firstOrFail();

        try {
            $path = $og->renderCompare($left, $right);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Throwable) {
            return $this->svgCompareFallback($left->name, $right->name);
        }
    }

    protected function svgFallback(string $title, string $subtitle): Response
    {
        $title = htmlspecialchars(mb_substr($title, 0, 60), ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars(mb_substr($subtitle, 0, 80), ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
  <rect width="1200" height="630" fill="#0f172a"/>
  <rect width="1200" height="8" fill="#4f46e5"/>
  <text x="72" y="80" fill="#a5b4fc" font-family="system-ui,sans-serif" font-size="28">Alternova</text>
  <text x="72" y="220" fill="#ffffff" font-family="system-ui,sans-serif" font-size="52" font-weight="700">{$title}</text>
  <text x="72" y="290" fill="#94a3b8" font-family="system-ui,sans-serif" font-size="28">{$subtitle}</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    protected function svgCompareFallback(string $left, string $right): Response
    {
        $left = htmlspecialchars(mb_substr($left, 0, 28), ENT_QUOTES, 'UTF-8');
        $right = htmlspecialchars(mb_substr($right, 0, 28), ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
  <rect width="1200" height="630" fill="#0f172a"/>
  <rect width="1200" height="8" fill="#4f46e5"/>
  <text x="72" y="70" fill="#a5b4fc" font-family="system-ui,sans-serif" font-size="24">Alternova · Compare</text>
  <text x="120" y="300" fill="#ffffff" font-family="system-ui,sans-serif" font-size="40" font-weight="700">{$left}</text>
  <text x="560" y="300" fill="#a5b4fc" font-family="system-ui,sans-serif" font-size="36" font-weight="700">VS</text>
  <text x="680" y="300" fill="#ffffff" font-family="system-ui,sans-serif" font-size="40" font-weight="700">{$right}</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
