<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class UserNotificationService
{
    public function ready(): bool
    {
        try {
            return Schema::hasTable('user_notifications');
        } catch (\Throwable) {
            return false;
        }
    }

    public function create(User $user, string $type, string $title, ?string $body = null, ?string $url = null, array $data = []): ?UserNotification
    {
        if (! $this->ready()) {
            return null;
        }

        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'data' => $data ?: null,
        ]);
    }

    /**
     * @return Collection<int, UserNotification>
     */
    public function listFor(User $user, int $limit = 50): Collection
    {
        if (! $this->ready()) {
            return collect();
        }

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function unreadCount(User $user): int
    {
        if (! $this->ready()) {
            return 0;
        }

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function markRead(int $id, User $user): void
    {
        if (! $this->ready()) {
            return;
        }

        $n = UserNotification::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        $n?->markRead();
    }

    public function markAllRead(User $user): void
    {
        if (! $this->ready()) {
            return;
        }

        UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
