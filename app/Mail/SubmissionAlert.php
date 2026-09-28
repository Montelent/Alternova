<?php

namespace App\Mail;

use App\Models\AlternativeSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AlternativeSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Submission] '.$this->submission->alternative_name.' (vs '.$this->submission->proprietary_name.')',
            replyTo: array_filter([$this->submission->submitter_email]),
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
        $s = $this->submission;
        $adminUrl = url('/admin/alternative-submissions/'.$s->id.'/edit');

        return '<!DOCTYPE html><html><body style="font-family:system-ui,sans-serif;line-height:1.5;color:#0f172a">'
            .'<h2 style="margin:0 0 12px">New alternative submission</h2>'
            .'<p><strong>Alternative:</strong> '.e($s->alternative_name).'</p>'
            .'<p><strong>Replaces:</strong> '.e($s->proprietary_name).'</p>'
            .'<p><strong>Repo:</strong> <a href="'.e($s->repo_url).'">'.e($s->repo_url).'</a></p>'
            .($s->website_url ? '<p><strong>Website:</strong> <a href="'.e($s->website_url).'">'.e($s->website_url).'</a></p>' : '')
            .($s->license_type ? '<p><strong>License:</strong> '.e($s->license_type).'</p>' : '')
            .($s->submitter_name || $s->submitter_email
                ? '<p><strong>Submitted by:</strong> '.e(trim(($s->submitter_name ?? '').' '.($s->submitter_email ?? ''))).'</p>'
                : '')
            .($s->description
                ? '<div style="margin:16px 0;padding:12px 16px;background:#f8fafc;border-radius:8px;white-space:pre-wrap">'.e($s->description).'</div>'
                : '')
            .'<p><a href="'.e($adminUrl).'">Review in admin</a></p>'
            .'<p style="color:#64748b;font-size:12px">Alternova · Submission alert</p>'
            .'</body></html>';
    }
}
