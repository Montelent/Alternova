<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;

class MailSettings
{
    public static function apply(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $mailer = SiteSetting::get('mail_mailer', config('mail.default', 'log'));
        if (! $mailer) {
            $mailer = 'log';
        }

        Config::set('mail.default', $mailer);

        $fromAddress = SiteSetting::get('mail_from_address', config('mail.from.address'));
        $fromName = SiteSetting::get('mail_from_name', config('mail.from.name'));

        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }
        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }

        // SMTP
        Config::set('mail.mailers.smtp.host', SiteSetting::get('mail_smtp_host', config('mail.mailers.smtp.host')));
        Config::set('mail.mailers.smtp.port', (int) SiteSetting::get('mail_smtp_port', config('mail.mailers.smtp.port', 587)));
        Config::set('mail.mailers.smtp.username', SiteSetting::get('mail_smtp_username', config('mail.mailers.smtp.username')));
        $password = SiteSetting::get('mail_smtp_password');
        if ($password !== null && $password !== '') {
            Config::set('mail.mailers.smtp.password', $password);
        }
        Config::set('mail.mailers.smtp.encryption', SiteSetting::get('mail_smtp_encryption', config('mail.mailers.smtp.encryption')) ?: null);
        Config::set('mail.mailers.smtp.scheme', SiteSetting::get('mail_smtp_scheme', config('mail.mailers.smtp.scheme')));

        // Resend
        $resendKey = SiteSetting::get('mail_resend_key', config('services.resend.key'));
        if ($resendKey) {
            Config::set('services.resend.key', $resendKey);
            Config::set('mail.mailers.resend.key', $resendKey);
        }

        // Alert preferences
        Config::set('alternova.mail.admin_email', SiteSetting::get('mail_admin_email', $fromAddress));
        Config::set('alternova.mail.alert_contact', SiteSetting::getBool('mail_alert_contact', true));
        Config::set('alternova.mail.alert_submission', SiteSetting::getBool('mail_alert_submission', true));
    }

    public static function adminEmail(): ?string
    {
        $email = config('alternova.mail.admin_email') ?: SiteSetting::get('mail_admin_email');

        return $email ? trim((string) $email) : null;
    }

    public static function alertsEnabled(string $type): bool
    {
        return match ($type) {
            'contact' => (bool) config('alternova.mail.alert_contact', true),
            'submission' => (bool) config('alternova.mail.alert_submission', true),
            default => false,
        };
    }
}
