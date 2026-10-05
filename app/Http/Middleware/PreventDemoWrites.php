<?php

namespace App\Http\Middleware;

use App\Support\DemoMode;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops Filament / Livewire Save-Create-Delete when DEMO_MODE=true.
 * Screens stay open; mutating calls get a normal validation error + toast,
 * not a raw JSON page.
 */
class PreventDemoWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! DemoMode::enabled()) {
            return $next($request);
        }

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

            // Livewire turns this into a normal form/error response (not a JSON dump page).
            throw ValidationException::withMessages([
                'demo' => $message,
            ]);
        }

        if ($request->is('admin/*')
            && ! $request->is('admin/login', 'admin/logout')
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && ! $isLivewire
        ) {
            $message = DemoMode::message();

            try {
                Notification::make()
                    ->title($message)
                    ->warning()
                    ->persistent()
                    ->send();
            } catch (\Throwable) {
            }

            return back()
                ->withErrors(['demo' => $message])
                ->with('error', $message);
        }

        return $next($request);
    }
}
