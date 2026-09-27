<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotInstalled
{
    /**
     * Redirect to the installer when the app has not been installed yet.
     * Skips install routes and assets.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Installer::isInstalled()) {
            return $next($request);
        }

        // Allow installer routes and static assets
        if ($request->is('install*') || $request->is('css/*') || $request->is('js/*') || $request->is('build/*')) {
            return $next($request);
        }

        return redirect()->route('install.index');
    }
}
