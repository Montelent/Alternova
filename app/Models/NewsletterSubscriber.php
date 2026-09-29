<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'status',
        'source',
        'ip_address',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_token',
        'last_digest_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_digest_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function ensureUnsubscribeToken(): string
    {
        if (! empty($this->unsubscribe_token)) {
            return $this->unsubscribe_token;
        }

        $token = Str::random(48);
        try {
            $this->forceFill(['unsubscribe_token' => $token])->save();
        } catch (\Throwable) {
            // column may not exist yet
        }

        return $token;
    }

    public function unsubscribeUrl(): string
    {
        $token = $this->ensureUnsubscribeToken();

        if ($token) {
            return route('newsletter.unsubscribe', ['token' => $token]);
        }

        return route('newsletter.unsubscribe', ['email' => $this->email]);
    }
}
