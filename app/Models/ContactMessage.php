<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContactMessage extends Model
{
    protected $fillable = [
        'public_id',
        'access_token',
        'user_id',
        'name',
        'email',
        'subject',
        'message',
        'status',
        'ticket_status',
        'ip_address',
        'read_at',
        'last_reply_at',
        'last_reply_by',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContactMessage $msg) {
            if (Schema::hasColumn('contact_messages', 'public_id') && empty($msg->public_id)) {
                $msg->public_id = static::generatePublicId();
            }
            if (Schema::hasColumn('contact_messages', 'access_token') && empty($msg->access_token)) {
                $msg->access_token = Str::random(48);
            }
            if (Schema::hasColumn('contact_messages', 'ticket_status') && empty($msg->ticket_status)) {
                $msg->ticket_status = 'open';
            }
        });
    }

    public static function generatePublicId(): string
    {
        do {
            $id = 'TKT-'.strtoupper(Str::random(8));
        } while (static::query()->where('public_id', $id)->exists());

        return $id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ContactMessageReply::class, 'contact_message_id')->orderBy('created_at');
    }

    public function publicReplies(): HasMany
    {
        return $this->replies()->where('is_internal', false);
    }

    public function markRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => $this->read_at ?? now(),
        ]);
    }

    public function isOpen(): bool
    {
        $s = $this->ticket_status ?? 'open';

        return ! in_array($s, ['closed', 'archived'], true);
    }

    public function publicUrl(): string
    {
        if (! $this->public_id) {
            return url('/contact');
        }

        $url = url('/support/'.$this->public_id);
        if ($this->access_token) {
            $url .= '?token='.$this->access_token;
        }

        return $url;
    }

    public function canBeViewedBy(?User $user, ?string $token = null): bool
    {
        if ($token && $this->access_token && hash_equals((string) $this->access_token, $token)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($this->user_id && (int) $this->user_id === (int) $user->id) {
            return true;
        }

        return strcasecmp((string) $this->email, (string) $user->email) === 0;
    }

    public function canBeRepliedBy(?User $user): bool
    {
        if (! $user || ! $this->isOpen()) {
            return false;
        }

        if ($this->user_id && (int) $this->user_id === (int) $user->id) {
            return true;
        }

        return strcasecmp((string) $this->email, (string) $user->email) === 0;
    }
}
