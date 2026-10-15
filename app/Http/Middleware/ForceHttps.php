<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated Prefer ForcePreferredUrl (host + HTTPS from APP_URL).
 * Kept as a thin alias so older stacks still work.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        return app(ForcePreferredUrl::class)->handle($request, $next);
    }
}
