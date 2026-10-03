<?php

namespace App\Providers;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Observers\OpenSourceAlternativeObserver;
use App\Observers\ProprietaryToolObserver;
use App\Support\IntegrationsSettings;
use App\Support\MailSettings;
use App\View\Composers\CmsNavComposer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.app', 'welcome'], CmsNavComposer::class);

        OpenSourceAlternative::observe(OpenSourceAlternativeObserver::class);
        ProprietaryTool::observe(ProprietaryToolObserver::class);

        try {
            MailSettings::apply();
        } catch (\Throwable) {
        }

        try {
            IntegrationsSettings::apply();
        } catch (\Throwable) {
        }

        /*
         | Session cookies on shared hosting (Hostinger, Cloudflare, etc.)
         |
         | Do NOT force session.secure=true from APP_URL alone. If PHP does not
         | see the request as HTTPS (missing X-Forwarded-Proto), a Secure cookie
         | is never stored/sent and every Livewire/Filament Save hits 419 Page Expired.
         |
         | Leave SESSION_SECURE_COOKIE unset/null → Laravel marks Secure only when
         | the current request is HTTPS (works with TrustProxies).
         */
        if ($this->app->environment('production')) {
            $https = str_starts_with(strtolower((string) config('app.url')), 'https://');

            $secureEnv = env('SESSION_SECURE_COOKIE');
            $secure = null;
            if ($secureEnv === true || $secureEnv === 'true' || $secureEnv === '1') {
                $secure = true;
            } elseif ($secureEnv === false || $secureEnv === 'false' || $secureEnv === '0') {
                $secure = false;
            }

            config([
                'session.http_only' => true,
                'session.same_site' => env('SESSION_SAME_SITE', 'lax'),
                'session.secure' => $secure,
                // Longer admin editing sessions (default 120 is tight for long forms)
                'session.lifetime' => (int) env('SESSION_LIFETIME', 480),
            ]);

            if ($https) {
                URL::forceScheme('https');
            }
        }

        $uploadsRoot = base_path('uploads');
        if (! is_dir($uploadsRoot)) {
            @mkdir($uploadsRoot, 0775, true);
        }
        foreach (['logos', 'collections', 'gallery', 'general', 'branding'] as $dir) {
            $path = $uploadsRoot.'/'.$dir;
            if (! is_dir($path)) {
                @mkdir($path, 0775, true);
            }
        }
    }
}
