<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        $subject = $this->contactMessage->subject
            ? '[Contact] '.$this->contactMessage->subject
            : '[Contact] New message from '.$this->contactMessage->name;

        return new Envelope(
            subject: $subject,
            replyTo: [$this->contactMessage->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    protected function buildHtml(): string
    {
        $m = $this->contactMessage;
        $adminUrl = url('/admin/contact-messages/'.$m->id);

        return '<!DOCTYPE html><html><body style="font-family:system-ui,sans-serif;line-height:1.5;color:#0f172a">'
            .'<h2 style="margin:0 0 12px">New contact message</h2>'
            .'<p><strong>From:</strong> '.e($m->name).' &lt;'.e($m->email).'&gt;</p>'
            .($m->subject ? '<p><strong>Subject:</strong> '.e($m->subject).'</p>' : '')
            .'<p><strong>Received:</strong> '.e(optional($m->created_at)->toDayDateTimeString() ?? '').'</p>'
            .'<div style="margin:16px 0;padding:12px 16px;background:#f8fafc;border-radius:8px;white-space:pre-wrap">'
            .e($m->message)
            .'</div>'
            .'<p><a href="'.e($adminUrl).'">Open in admin</a></p>'
            .'<p style="color:#64748b;font-size:12px">Alternova · Contact alert</p>'
            .'</body></html>';
    }
}
