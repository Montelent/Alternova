<?php

namespace App\Mail;

use App\Models\AlternativeSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AlternativeSubmission $submission)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Alternova] New alternative suggestion: '.$this->submission->alternative_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlBody(),
        );
    }

    protected function htmlBody(): string
    {
        $s = $this->submission;
        $admin = rtrim(config('app.url'), '/').'/admin';

        return '<p>A visitor submitted a new open-source alternative for review.</p>'
            .'<ul>'
            .'<li><strong>Alternative:</strong> '.e($s->alternative_name).'</li>'
            .'<li><strong>Replaces:</strong> '.e($s->proprietary_name).'</li>'
            .'<li><strong>Repo:</strong> <a href="'.e($s->repo_url).'">'.e($s->repo_url).'</a></li>'
            .'<li><strong>From:</strong> '.e($s->submitter_name ?: 'Anonymous')
            .' '.($s->submitter_email ? '('.e($s->submitter_email).')' : '').'</li>'
            .'</ul>'
            .'<p><a href="'.e($admin).'">Open admin panel</a></p>';
    }
}
