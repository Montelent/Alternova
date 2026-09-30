<?php

namespace App\Services;

use App\Mail\CommentReplyMail;
use App\Models\AlternativeComment;
use App\Models\OpenSourceAlternative;
use App\Models\User;
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
        if (! $parent) {
            return;
        }

        // Don't notify if replying to yourself (same email)
        if ($parent->author_email && $reply->author_email
            && strtolower((string) $parent->author_email) === strtolower((string) $reply->author_email)) {
            return;
        }

        $alt = OpenSourceAlternative::query()->find($reply->open_source_alternative_id);
        if (! $alt) {
            return;
        }

        $url = url('/alternatives/'.$alt->slug);

        // In-app notification for logged-in parent author
        if ($parent->user_id) {
            $parentUser = User::query()->find($parent->user_id);
            if ($parentUser && (int) $parentUser->id !== (int) ($reply->user_id ?? 0)) {
                try {
                    app(UserNotificationService::class)->create(
                        $parentUser,
                        'comment_reply',
                        'New reply on '.$alt->name,
                        $reply->author_name.': '.str($reply->body)->limit(120),
                        $url,
                        ['comment_id' => $reply->id, 'alternative_id' => $alt->id]
                    );
                } catch (\Throwable $e) {
                    Log::warning('In-app comment notify failed: '.$e->getMessage());
                }
            }
        }

        if (! $parent->author_email) {
            return;
        }

        try {
            Mail::to($parent->author_email)->send(new CommentReplyMail($parent, $reply, $alt));
        } catch (\Throwable $e) {
            Log::warning('Comment reply email failed: '.$e->getMessage());
        }
    }
}
