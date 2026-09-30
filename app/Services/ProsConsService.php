<?php

namespace App\Services;

use App\Models\AlternativeProsCon;
use App\Models\OpenSourceAlternative;
use App\Models\ProsConsVote;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProsConsService
{
    public function ready(): bool
    {
        try {
            return Schema::hasTable('alternative_pros_cons') && Schema::hasTable('pros_cons_votes');
        } catch (\Throwable) {
            return false;
        }
    }

    public function voterKey(?string $sessionId, ?string $ip): string
    {
        $base = ($sessionId ?: 'guest').'|'.($ip ?: '0.0.0.0');

        return hash('sha256', $base);
    }

    /**
     * @return array{pros: Collection, cons: Collection}
     */
    public function listsFor(OpenSourceAlternative $alt): array
    {
        if (! $this->ready()) {
            return ['pros' => collect(), 'cons' => collect()];
        }

        $items = AlternativeProsCon::query()
            ->where('open_source_alternative_id', $alt->id)
            ->where('is_approved', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();

        return [
            'pros' => $items->where('type', 'pro')->values(),
            'cons' => $items->where('type', 'con')->values(),
        ];
    }

    /**
     * @return array{ok: bool, message: string, item: ?AlternativeProsCon}
     */
    public function submit(
        OpenSourceAlternative $alt,
        string $type,
        string $body,
        ?string $authorName,
        ?string $authorEmail,
        ?string $ip,
        ?User $user = null
    ): array {
        if (! $this->ready()) {
            return ['ok' => false, 'message' => 'Pros/cons unavailable. Run migrations.', 'item' => null];
        }

        $type = strtolower(trim($type)) === 'con' ? 'con' : 'pro';
        $body = trim(Str::limit($body, 280, ''));

        if (mb_strlen($body) < 8) {
            return ['ok' => false, 'message' => 'Please write at least 8 characters.', 'item' => null];
        }

        $user = $user ?: Auth::user();

        // Auto-approve signed-in users with prior approved items, else pending
        $autoApprove = false;
        if ($user) {
            $prior = AlternativeProsCon::query()
                ->where('user_id', $user->id)
                ->where('is_approved', true)
                ->exists();
            $autoApprove = $prior;
        }

        $item = AlternativeProsCon::create([
            'open_source_alternative_id' => $alt->id,
            'type' => $type,
            'body' => $body,
            'votes_count' => 0,
            'is_approved' => $autoApprove,
            'is_featured' => false,
            'author_name' => $authorName ?: ($user?->name),
            'author_email' => $authorEmail ?: ($user?->email),
            'user_id' => $user?->id,
            'ip_address' => $ip ? Str::limit($ip, 45, '') : null,
        ]);

        return [
            'ok' => true,
            'message' => $autoApprove
                ? 'Published — thanks for contributing.'
                : 'Submitted for review. It appears after approval.',
            'item' => $item,
        ];
    }

    /**
     * @return array{ok: bool, votes: int, message: string, voted: bool}
     */
    public function upvote(AlternativeProsCon $item, string $voterKey, ?string $ip): array
    {
        if (! $this->ready() || ! $item->is_approved) {
            return ['ok' => false, 'votes' => (int) $item->votes_count, 'message' => 'Unavailable.', 'voted' => false];
        }

        if (ProsConsVote::query()
            ->where('alternative_pros_con_id', $item->id)
            ->where('voter_key', $voterKey)
            ->exists()) {
            return [
                'ok' => false,
                'votes' => (int) $item->fresh()->votes_count,
                'message' => 'You already upvoted this.',
                'voted' => true,
            ];
        }

        return DB::transaction(function () use ($item, $voterKey, $ip) {
            ProsConsVote::create([
                'alternative_pros_con_id' => $item->id,
                'voter_key' => $voterKey,
                'ip_address' => $ip ? Str::limit($ip, 45, '') : null,
            ]);
            $item->increment('votes_count');

            return [
                'ok' => true,
                'votes' => (int) $item->fresh()->votes_count,
                'message' => 'Upvoted.',
                'voted' => true,
            ];
        });
    }

    public function hasVoted(int $itemId, string $voterKey): bool
    {
        if (! $this->ready()) {
            return false;
        }

        return ProsConsVote::query()
            ->where('alternative_pros_con_id', $itemId)
            ->where('voter_key', $voterKey)
            ->exists();
    }
}
