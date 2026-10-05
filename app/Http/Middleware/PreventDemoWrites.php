<?php

namespace App\Http\Middleware;

use App\Support\DemoMode;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops Filament / Livewire Save-Create-Delete when DEMO_MODE=true.
 * Screens stay fully open; only mutating Livewire calls are rejected.
 */
class PreventDemoWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! DemoMode::enabled()) {
            return $next($request);
        }

        // Livewire endpoint used by Filament forms and table actions
        $isLivewire = $request->is('livewire/*')
            || str_contains($request->path(), 'livewire/update');

        if ($isLivewire && $request->isMethod('POST') && DemoMode::livewireRequestIsMutation($request)) {
            $message = DemoMode::message();

            try {
                Notification::make()
                    ->title($message)
                    ->body('You can explore every screen. Changes are not saved in the demo.')
                    ->warning()
                    ->persistent()
                    ->send();
            } catch (\Throwable) {
            }

            // Livewire-friendly validation error (shows on the form)
            return response()->json([
                'message' => $message,
                'errors' => [
                    'demo' => [$message],
                ],
            ], 422);
        }

        // Non-Livewire POST/PUT/PATCH/DELETE under /admin (settings forms, etc.)
        if ($request->is('admin/*')
            && ! $request->is('admin/login', 'admin/logout')
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && ! $isLivewire
        ) {
            $message = DemoMode::message();

            return back()
                ->withErrors(['demo' => $message])
                ->with('error', $message);
        }

        return $next($request);
    }
}
