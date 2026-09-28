<?php

namespace App\Providers;

use App\Models\AlternativeSubmission;
use App\Models\ContactMessage;
use App\Observers\AlternativeSubmissionObserver;
use App\Observers\ContactMessageObserver;
use App\Support\MailSettings;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->usePublicPath(base_path('public'));
    }

    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Apply DB mail settings after boot (safe if table missing)
        try {
            MailSettings::apply();
        } catch (\Throwable) {
            // installer / missing DB
        }

        ContactMessage::observe(ContactMessageObserver::class);
        AlternativeSubmission::observe(AlternativeSubmissionObserver::class);
    }
}
