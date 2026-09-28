<?php

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class NewsletterSubscribe extends Component
{
    public string $email = '';

    public string $message = '';

    public bool $success = false;

    public string $source = 'footer';

    public function subscribe(): void
    {
        $this->validate([
            'email' => 'required|email|max:190',
        ]);

        try {
            if (! Schema::hasTable('newsletter_subscribers')) {
                $this->success = false;
                $this->message = 'Newsletter is not ready yet. Please try again after the site finishes updating.';

                return;
            }
        } catch (\Throwable) {
            $this->success = false;
            $this->message = 'Newsletter is temporarily unavailable.';

            return;
        }

        $key = 'newsletter:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->success = false;
            $this->message = 'Too many attempts. Please try again later.';

            return;
        }
        RateLimiter::hit($key, 3600);

        $email = strtolower(trim($this->email));

        $existing = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($existing && $existing->status === 'active') {
            $this->success = true;
            $this->message = 'You are already subscribed. Thanks!';
            $this->email = '';

            return;
        }

        if ($existing) {
            $existing->update([
                'status' => 'active',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'source' => $this->source,
                'ip_address' => request()->ip(),
            ]);
        } else {
            NewsletterSubscriber::create([
                'email' => $email,
                'status' => 'active',
                'source' => $this->source,
                'ip_address' => request()->ip(),
                'subscribed_at' => now(),
            ]);
        }

        $this->success = true;
        $this->message = 'Subscribed — we will only email occasional product updates.';
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.newsletter-subscribe');
    }
}
