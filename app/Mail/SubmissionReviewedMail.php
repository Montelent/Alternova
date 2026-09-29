<?php

namespace App\Mail;

use App\Models\AlternativeSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AlternativeSubmission $submission)
    {
    }

    public function envelope(): Envelope
    {
        $status = $this->submission->status === 'approved' ? 'approved' : 'updated';

        return new Envelope(
            subject: '[Alternova] Your suggestion was '.$status.': '.$this->submission->alternative_name,
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody());
    }

    protected function htmlBody(): string
    {
        $s = $this->submission;
        $status = e($s->statusLabel());
        $name = e($s->alternative_name);
        $notes = $s->admin_notes ? '<p><strong>Note from the team:</strong> '.e($s->admin_notes).'</p>' : '';
        $track = $s->tracking_token
            ? '<p><a href="'.e(url('/submissions/'.$s->tracking_token)).'">Track status</a></p>'
            : '';

        $extra = '';
        if ($s->status === 'approved' && $s->created_alternative_id) {
            $extra = '<p>It is now listed in the Alternova directory. Thank you for contributing!</p>';
        }

        return "<p>Hi".($s->submitter_name ? ' '.e($s->submitter_name) : '').",</p>"
            ."<p>Your suggestion for <strong>{$name}</strong> is now: <strong>{$status}</strong>.</p>"
            .$notes.$extra.$track
            .'<p>— Alternova</p>';
    }
}
