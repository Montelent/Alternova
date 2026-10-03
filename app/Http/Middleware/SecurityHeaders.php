<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser security headers for all responses.
 * Embed routes allow framing; OG images allow cross-origin for social crawlers.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Never expose PHP / framework fingerprint
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        $isEmbed = $request->is('embed', 'embed/*');
        $isOgImage = $request->is('og', 'og/*');
        $isSecure = $request->isSecure()
            || str_starts_with(strtolower((string) config('app.url')), 'https://');

        // Clickjacking — embeds must remain frameable by third-party sites
        if ($isEmbed) {
            $response->headers->remove('X-Frame-Options');
        } else {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Modern browsers ignore X-XSS-Protection; 0 avoids legacy filter issues
        $response->headers->set('X-XSS-Protection', '0');

        $response->headers->set(
            'Permissions-Policy',
            'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=(), interest-cohort=()'
        );

        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Social crawlers (WhatsApp, X, Facebook) fetch og:image cross-origin
        $response->headers->set(
            'Cross-Origin-Resource-Policy',
            ($isEmbed || $isOgImage) ? 'cross-origin' : 'same-site'
        );

        if ($isSecure) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Pragmatic CSP: allow self + TinyMCE CDN + data/blob images used by admin uploads
        $frameAncestors = $isEmbed ? '*' : "'self'";
        $csp = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "frame-ancestors {$frameAncestors}",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https://cdn.jsdelivr.net",
            "connect-src 'self'",
            "media-src 'self' data: blob:",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
        ];

        if ($isSecure) {
            $csp[] = 'upgrade-insecure-requests';
        }

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        if ($request->is('admin', 'admin/*', 'account', 'account/*', 'login', 'register', 'password/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
