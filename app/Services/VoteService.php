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

        return DB::transaction(function () use ($alt, $voterKey, $ip) {
            AlternativeVote::create([
                'open_source_alternative_id' => $alt->id,
                'voter_key' => $voterKey,
                'ip_address' => $ip ? Str::limit($ip, 45, '') : null,
            ]);

            $alt->increment('votes_count');

            return [
                'ok' => true,
                'votes' => (int) $alt->fresh()->votes_count,
                'message' => 'Thanks — your vote was recorded.',
                'voted' => true,
            ];
        });
    }
}
