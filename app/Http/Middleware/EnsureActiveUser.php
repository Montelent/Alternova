<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        // API routes authenticate their own API key/user and must return JSON,
        // not a browser login redirect.
        if ($request->is('api/*')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && ! $user->isActive()) {
            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'This account is currently disabled. Contact the site administrator.',
            ]);
        }

        return $next($request);
    }
}
