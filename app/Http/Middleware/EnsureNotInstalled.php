<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstalled
{
    /**
     * Allow access to the installer only when the app is NOT yet installed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Installer::isInstalled()) {
            abort(403, 'Application is already installed. Installer is locked.');
        }

        return $next($request);
    }
}
