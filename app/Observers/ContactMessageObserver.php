<?php

namespace App\Observers;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageAlert;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Support\MailSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMessageObserver
{
    public function created(ContactMessage $message): void
    {
        try {
            MailSettings::apply();

            if (MailSettings::alertsEnabled('contact')) {
                $to = MailSettings::adminEmail();
                if ($to) {
                    Mail::to($to)->send(new ContactMessageAlert($message));
                }
            }

            if (SiteSetting::getBool('mail_contact_autoreply', false) && $message->email) {
                Mail::to($message->email)->send(new ContactAutoReply($message));
            }
        } catch (\Throwable $e) {
            Log::warning('Contact email failed: '.$e->getMessage());
        }
    }
}
