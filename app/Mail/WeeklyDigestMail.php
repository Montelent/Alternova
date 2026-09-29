<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\OpenSourceAlternative>  $newAlternatives
     * @param  Collection<int, \App\Models\OpenSourceAlternative>  $updatedAlternatives
     */
    public function __construct(
        public Collection $newAlternatives,
        public Collection $updatedAlternatives,
        public string $unsubscribeUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        $count = $this->newAlternatives->count() + $this->updatedAlternatives->count();

        return new Envelope(
            subject: 'Alternova weekly digest — '.$count.' open-source update'.($count === 1 ? '' : 's'),
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody());
    }

    protected function htmlBody(): string
    {
        $parts = [];
        $parts[] = '<div style="font-family:system-ui,sans-serif;max-width:560px;margin:0 auto;color:#0f172a">';
        $parts[] = '<h1 style="font-size:20px">This week on Alternova</h1>';
        $parts[] = '<p style="color:#475569">Fresh open-source alternatives and notable updates.</p>';

        if ($this->newAlternatives->isNotEmpty()) {
            $parts[] = '<h2 style="font-size:16px;margin-top:24px">New alternatives</h2><ul>';
            foreach ($this->newAlternatives as $alt) {
                $url = e(url('/alternatives/'.$alt->slug));
                $name = e($alt->name);
                $prop = e($alt->proprietaryTool?->name ?? 'proprietary tools');
                $parts[] = "<li style=\"margin:8px 0\"><a href=\"{$url}\">{$name}</a> — alternative to {$prop}</li>";
            }
            $parts[] = '</ul>';
        }

        if ($this->updatedAlternatives->isNotEmpty()) {
            $parts[] = '<h2 style="font-size:16px;margin-top:24px">Updated listings</h2><ul>';
            foreach ($this->updatedAlternatives as $alt) {
                $url = e(url('/alternatives/'.$alt->slug));
                $name = e($alt->name);
                $parts[] = "<li style=\"margin:8px 0\"><a href=\"{$url}\">{$name}</a></li>";
            }
            $parts[] = '</ul>';
        }

        if ($this->newAlternatives->isEmpty() && $this->updatedAlternatives->isEmpty()) {
            $parts[] = '<p>No new published alternatives this week — browse the <a href="'.e(url('/alternatives')).'">full catalog</a>.</p>';
        }

        $parts[] = '<p style="margin-top:28px;font-size:12px;color:#94a3b8">'
            .'<a href="'.e(url('/whats-new')).'">What\'s new</a> · '
            .'<a href="'.e($this->unsubscribeUrl).'">Unsubscribe</a></p>';
        $parts[] = '</div>';

        return implode('', $parts);
    }
}
