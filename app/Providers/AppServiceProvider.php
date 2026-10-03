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

        // Harden cookies + force HTTPS URL generation in production
        if ($this->app->environment('production')) {
            $https = str_starts_with(strtolower((string) config('app.url')), 'https://');

            config([
                'session.http_only' => true,
                'session.same_site' => 'lax',
                'session.secure' => $https || (bool) config('session.secure'),
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
