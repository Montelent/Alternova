<?php

namespace App\Services;

use App\Mail\CommentReplyMail;
use App\Models\AlternativeComment;
use App\Models\OpenSourceAlternative;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CommentNotificationService
{
    public function notifyReplyIfNeeded(AlternativeComment $reply): void
    {
        if (! $reply->parent_id || ! $reply->is_approved || $reply->is_hidden) {
            return;
        }

        $parent = AlternativeComment::query()->find($reply->parent_id);
        if (! $parent || ! $parent->author_email) {
            return;
        }

        // Don't notify if replying to yourself (same email)
        if (strtolower((string) $parent->author_email) === strtolower((string) $reply->author_email)) {
            return;
        }

        $alt = OpenSourceAlternative::query()->find($reply->open_source_alternative_id);
        if (! $alt) {
            return;
        }

        try {
            Mail::to($parent->author_email)->send(new CommentReplyMail($parent, $reply, $alt));
        } catch (\Throwable $e) {
            Log::warning('Comment reply email failed: '.$e->getMessage());
        }
    }
}
