<?php

namespace App\Services;

use App\Models\AlternativeVote;
use App\Models\OpenSourceAlternative;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoteService
{
    public function voterKey(?string $sessionId, ?string $ip): string
    {
        $base = ($sessionId ?: 'guest').'|'.($ip ?: '0.0.0.0');

        return hash('sha256', $base);
    }

    public function hasVoted(OpenSourceAlternative $alt, string $voterKey): bool
    {
        return AlternativeVote::query()
            ->where('open_source_alternative_id', $alt->id)
            ->where('voter_key', $voterKey)
            ->exists();
    }

    /**
     * Toggle vote on/off for this voter.
     *
     * @return array{voted: bool, count: int, ok: bool, message: string}
     */
    public function toggle(OpenSourceAlternative $alt, string $voterKey, ?string $ip = null): array
    {
        return DB::transaction(function () use ($alt, $voterKey, $ip) {
            $existing = AlternativeVote::query()
                ->where('open_source_alternative_id', $alt->id)
                ->where('voter_key', $voterKey)
                ->first();

            if ($existing) {
                $existing->delete();

                if ((int) $alt->votes_count > 0) {
                    $alt->decrement('votes_count');
                }

                $count = (int) $alt->fresh()->votes_count;

                return [
                    'ok' => true,
                    'voted' => false,
                    'count' => $count,
                    'message' => 'Vote removed.',
                ];
            }

            AlternativeVote::create([
                'open_source_alternative_id' => $alt->id,
                'voter_key' => $voterKey,
                'ip_address' => $ip ? Str::limit($ip, 45, '') : null,
            ]);

            $alt->increment('votes_count');

            $count = (int) $alt->fresh()->votes_count;

            return [
                'ok' => true,
                'voted' => true,
                'count' => $count,
                'message' => 'Thanks — your vote was recorded.',
            ];
        });
    }

    /**
     * One-way vote (cannot un-vote). Kept for API / older callers.
     *
     * @return array{ok: bool, votes: int, message: string, voted: bool}
     */
    public function vote(OpenSourceAlternative $alt, string $voterKey, ?string $ip): array
    {
        if ($this->hasVoted($alt, $voterKey)) {
            return [
                'ok' => false,
                'votes' => (int) $alt->fresh()->votes_count,
                'message' => 'You already voted for this alternative.',
                'voted' => true,
            ];
        }

        $result = $this->toggle($alt, $voterKey, $ip);

        return [
            'ok' => $result['ok'],
            'votes' => $result['count'],
            'message' => $result['message'],
            'voted' => $result['voted'],
        ];
    }
}
