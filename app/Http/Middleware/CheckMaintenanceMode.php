<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Always allow installer, admin, and health
        if ($request->is('install', 'install/*', 'admin', 'admin/*', 'livewire/*', 'up')) {
            return $next($request);
        }

        try {
            if (SiteSetting::getBool('maintenance_mode', false)) {
                $message = SiteSetting::get(
                    'maintenance_message',
                    'Alternova is temporarily offline for maintenance. Please check back soon.'
                );

                return response()->view('maintenance', [
                    'message' => $message,
                ], 503);
            }
        } catch (\Throwable) {
            // DB not ready
        }

        return $next($request);
    }
}
