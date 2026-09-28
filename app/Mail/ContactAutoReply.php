<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your message — '.config('app.name', 'Alternova'),
        );
    }

    public function content(): Content
    {
        $name = e($this->contactMessage->name);
        $app = e(config('app.name', 'Alternova'));

        $html = '<!DOCTYPE html><html><body style="font-family:system-ui,sans-serif;line-height:1.5;color:#0f172a">'
            ."<p>Hi {$name},</p>"
            ."<p>Thanks for contacting <strong>{$app}</strong>. We received your message and will get back to you if a reply is needed.</p>"
            .'<p style="color:#64748b;font-size:13px">This is an automated confirmation — you do not need to reply.</p>'
            .'</body></html>';

        return new Content(htmlString: $html);
    }
}
