<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->attributes->get('api_key');

        if ($apiKey) {
            $limit = 600; // authenticated: 600/min
            $bucket = 'api-key:'.$apiKey->id;
        } else {
            $limit = 60; // anonymous: 60/min
            $bucket = 'api-ip:'.$request->ip();
        }

        if (RateLimiter::tooManyAttempts($bucket, $limit)) {
            $retry = RateLimiter::availableIn($bucket);

            return response()->json([
                'error' => 'Too many requests. Retry later.',
                'retry_after' => $retry,
                'authenticated' => (bool) $apiKey,
            ], 429)->withHeaders([
                'Retry-After' => (string) $retry,
                'X-RateLimit-Limit' => (string) $limit,
            ]);
        }

        RateLimiter::hit($bucket, 60);

        $response = $next($request);

        $remaining = max(0, $limit - RateLimiter::attempts($bucket));

        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }
}
