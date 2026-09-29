<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'key_prefix',
        'key_hash',
        'last_used_at',
        'request_count',
        'revoked_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
        'request_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    /**
     * @return array{model: self, plain: string}
     */
    public static function issue(User $user, string $name): array
    {
        $plain = 'alt_'.Str::random(40);
        $prefix = substr($plain, 0, 12);

        $model = static::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'key_prefix' => $prefix,
            'key_hash' => hash('sha256', $plain),
        ]);

        return ['model' => $model, 'plain' => $plain];
    }

    public static function findByPlainKey(string $plain): ?self
    {
        $plain = trim($plain);
        if ($plain === '') {
            return null;
        }

        return static::query()
            ->whereNull('revoked_at')
            ->where('key_hash', hash('sha256', $plain))
            ->first();
    }

    public function markUsed(): void
    {
        $this->forceFill([
            'last_used_at' => now(),
            'request_count' => $this->request_count + 1,
        ])->save();
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }
}
