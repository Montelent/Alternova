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

        $mailer = SiteSetting::get('mail_mailer', config('mail.default', 'log')) ?: 'log';

        $fromAddress = SiteSetting::get('mail_from_address', config('mail.from.address'));
        $fromName = SiteSetting::get('mail_from_name', config('mail.from.name'));

        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }
        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }

        $resendKey = trim((string) SiteSetting::get('mail_resend_key', config('services.resend.key', '')));

        /*
         | Resend without the PHP SDK (common on shared hosting if composer
         | did not install resend/resend-php). Use Resend's official SMTP
         | endpoint so delivery still works with only an API key.
         */
        if ($mailer === 'resend') {
            $hasSdk = class_exists(\Resend\Factory::class)
                || class_exists(\Resend::class)
                || class_exists(\Resend\Resend::class)
                || class_exists(\Resend\Laravel\ResendServiceProvider::class);

            if ($hasSdk && $resendKey !== '') {
                Config::set('mail.default', 'resend');
                Config::set('services.resend.key', $resendKey);
                Config::set('mail.mailers.resend.key', $resendKey);
                if (class_exists(\Resend\Laravel\ResendServiceProvider::class)) {
                    Config::set('resend.api_key', $resendKey);
                }
            } else {
                // SMTP fallback — no Resend PHP class required
                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp.host', 'smtp.resend.com');
                Config::set('mail.mailers.smtp.port', 587);
                Config::set('mail.mailers.smtp.username', 'resend');
                Config::set('mail.mailers.smtp.password', $resendKey !== '' ? $resendKey : null);
                Config::set('mail.mailers.smtp.encryption', 'tls');
                Config::set('mail.mailers.smtp.scheme', null);
                if ($resendKey !== '') {
                    Config::set('services.resend.key', $resendKey);
                }
            }
        } elseif ($mailer === 'smtp') {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', SiteSetting::get('mail_smtp_host', config('mail.mailers.smtp.host')));
            Config::set('mail.mailers.smtp.port', (int) SiteSetting::get('mail_smtp_port', config('mail.mailers.smtp.port', 587)));
            Config::set('mail.mailers.smtp.username', SiteSetting::get('mail_smtp_username', config('mail.mailers.smtp.username')));
            $password = SiteSetting::get('mail_smtp_password');
            if ($password !== null && $password !== '') {
                Config::set('mail.mailers.smtp.password', $password);
            }
            Config::set('mail.mailers.smtp.encryption', SiteSetting::get('mail_smtp_encryption', config('mail.mailers.smtp.encryption')) ?: null);
        } else {
            Config::set('mail.default', $mailer);
        }

        Config::set('alternova.mail.admin_email', SiteSetting::get('mail_admin_email', $fromAddress));
        Config::set('alternova.mail.alert_contact', SiteSetting::getBool('mail_alert_contact', true));
        Config::set('alternova.mail.alert_submission', SiteSetting::getBool('mail_alert_submission', true));
        Config::set('alternova.mail.contact_autoreply', SiteSetting::getBool('mail_contact_autoreply', false));
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
            'comment' => SiteSetting::getBool('mail_alert_comment', true),
            default => false,
        };
    }
}
