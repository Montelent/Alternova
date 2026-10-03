<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Services\OgImageService;
use App\Services\SeoManager;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class OgImageController extends Controller
{
    public function alternative(string $slug, OgImageService $og): Response|RedirectResponse
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
                'Cross-Origin-Resource-Policy' => 'cross-origin',
            ]);
        } catch (\Throwable) {
            // WhatsApp/X/Facebook do not show SVG — redirect to a real PNG/JPEG
            return $this->redirectToPhotoFallback(
                $og->absoluteImageUrl($alt->logo_url ?? null)
                    ?? $this->firstGallery($alt)
                    ?? $og->absoluteImageUrl(app(SeoManager::class)->defaultOgImage())
            );
        }
    }

    public function tool(string $slug, OgImageService $og): Response|RedirectResponse
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
                'Cross-Origin-Resource-Policy' => 'cross-origin',
            ]);
        } catch (\Throwable) {
            return $this->redirectToPhotoFallback(
                $og->absoluteImageUrl($tool->logo_url ?? null)
                    ?? $og->absoluteImageUrl(app(SeoManager::class)->defaultOgImage())
            );
        }
    }

    public function collection(string $slug, OgImageService $og): Response|RedirectResponse
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
                'Cross-Origin-Resource-Policy' => 'cross-origin',
            ]);
        } catch (\Throwable) {
            return $this->redirectToPhotoFallback(
                $og->absoluteImageUrl($collection->cover_image_url ?? null)
                    ?? $og->absoluteImageUrl(app(SeoManager::class)->defaultOgImage())
            );
        }
    }

    public function compare(Request $request, OgImageService $og): Response|RedirectResponse
    {
        $a = (string) ($request->query('a') ?: $request->route('a') ?: '');
        $b = (string) ($request->query('b') ?: $request->route('b') ?: '');
        $a = preg_replace('/\.png$/i', '', $a) ?? $a;
        $b = preg_replace('/\.png$/i', '', $b) ?? $b;

        abort_if($a === '' || $b === '', 404);

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
                'Cross-Origin-Resource-Policy' => 'cross-origin',
            ]);
        } catch (\Throwable) {
            return $this->redirectToPhotoFallback(
                $og->absoluteImageUrl(app(SeoManager::class)->defaultOgImage())
            );
        }
    }

    protected function firstGallery(OpenSourceAlternative $alt): ?string
    {
        $og = app(OgImageService::class);

        if (method_exists($alt, 'galleryImageUrls')) {
            foreach ($alt->galleryImageUrls() as $url) {
                if ($abs = $og->absoluteImageUrl($url)) {
                    return $abs;
                }
            }
        }

        foreach ((array) ($alt->gallery_paths ?? []) as $path) {
            if (is_string($path) && ($u = MediaUrl::make($path, 'uploads'))) {
                if ($abs = $og->absoluteImageUrl($u)) {
                    return $abs;
                }
            }
        }

        return null;
    }

    /**
     * Never serve SVG to social crawlers — they ignore it and show no image.
     */
    protected function redirectToPhotoFallback(?string $url): Response|RedirectResponse
    {
        if ($url) {
            return redirect()->away($url, 302);
        }

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300',
            'Cross-Origin-Resource-Policy' => 'cross-origin',
        ]);
    }
}
