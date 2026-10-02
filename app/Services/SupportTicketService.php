<?php

namespace App\Services;

use App\Mail\SupportTicketMail;
use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SupportTicketService
{
    public function ready(): bool
    {
        try {
            return Schema::hasTable('contact_messages');
        } catch (\Throwable) {
            return false;
        }
    }

    public function repliesReady(): bool
    {
        try {
            return Schema::hasTable('contact_message_replies');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array{name:string,email:string,subject?:string,message:string,ip_address?:string}  $data
     */
    public function createTicket(array $data, ?User $user = null): ContactMessage
    {
        $user = $user ?: Auth::user();

        $payload = [
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => 'unread',
            'ip_address' => $data['ip_address'] ?? request()->ip(),
        ];

        if (Schema::hasColumn('contact_messages', 'user_id')) {
            $payload['user_id'] = $user?->id;
        }
        if (Schema::hasColumn('contact_messages', 'ticket_status')) {
            $payload['ticket_status'] = 'open';
        }

        $ticket = ContactMessage::create($payload);

        $this->notifyUser($ticket, 'created', $data['message'], config('app.name', 'Support'));

        try {
            $admins = User::query()
                ->when(Schema::hasColumn('users', 'role'), fn ($q) => $q->whereIn('role', ['admin', 'editor']))
                ->limit(20)
                ->get();

            if ($admins->isEmpty()) {
                $admins = User::query()->orderBy('id')->limit(3)->get();
            }

            foreach ($admins as $admin) {
                if (! $admin->email || strcasecmp((string) $admin->email, $ticket->email) === 0) {
                    continue;
                }
                try {
                    Mail::to($admin->email)->send(new SupportTicketMail(
                        $ticket,
                        'user_reply',
                        $data['message'],
                        $ticket->name
                    ));
                } catch (\Throwable $e) {
                    Log::debug('Admin ticket mail failed: '.$e->getMessage());
                }
                try {
                    app(UserNotificationService::class)->create(
                        $admin,
                        'support_ticket',
                        'New support ticket '.($ticket->public_id ?: '#'.$ticket->id),
                        Str::limit($ticket->subject ?: $data['message'], 120),
                        url('/admin/contact-messages/'.$ticket->id),
                        ['ticket_id' => $ticket->id]
                    );
                } catch (\Throwable) {
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Admin notify failed: '.$e->getMessage());
        }

        return $ticket;
    }

    public function replyAsStaff(ContactMessage $ticket, string $body, ?User $staff = null, bool $internal = false): ?ContactMessageReply
    {
        if (! $this->repliesReady()) {
            return null;
        }

        $staff = $staff ?: Auth::user();
        $body = trim($body);
        if ($body === '') {
            return null;
        }

        $reply = ContactMessageReply::create([
            'contact_message_id' => $ticket->id,
            'user_id' => $staff?->id,
            'author_type' => 'staff',
            'author_name' => $staff?->name ?? 'Support',
            'author_email' => $staff?->email,
            'body' => $body,
            'is_internal' => $internal,
        ]);

        $updates = [
            'status' => 'read',
            'last_reply_at' => now(),
            'last_reply_by' => 'staff',
            'read_at' => $ticket->read_at ?? now(),
        ];
        if (Schema::hasColumn('contact_messages', 'ticket_status') && ! $internal) {
            $updates['ticket_status'] = 'awaiting_user';
        }
        $ticket->forceFill($updates)->save();

        if (! $internal) {
            $this->notifyUser($ticket, 'staff_reply', $body, $staff?->name ?? 'Support');
        }

        return $reply;
    }

    public function replyAsUser(ContactMessage $ticket, string $body, User $user): ?ContactMessageReply
    {
        if (! $this->repliesReady() || ! $ticket->canBeRepliedBy($user)) {
            return null;
        }

        $body = trim($body);
        if ($body === '') {
            return null;
        }

        $reply = ContactMessageReply::create([
            'contact_message_id' => $ticket->id,
            'user_id' => $user->id,
            'author_type' => 'user',
            'author_name' => $user->name,
            'author_email' => $user->email,
            'body' => $body,
            'is_internal' => false,
        ]);

        $updates = [
            'status' => 'unread',
            'last_reply_at' => now(),
            'last_reply_by' => 'user',
        ];
        if (Schema::hasColumn('contact_messages', 'user_id') && ! $ticket->user_id) {
            $updates['user_id'] = $user->id;
        }
        if (Schema::hasColumn('contact_messages', 'ticket_status')) {
            $updates['ticket_status'] = 'awaiting_staff';
        }
        $ticket->forceFill($updates)->save();

        $this->notifyAdminsOfUserReply($ticket, $body, $user);
        $this->notifyUser($ticket, 'user_reply', $body, $user->name);

        return $reply;
    }

    public function close(ContactMessage $ticket): void
    {
        $updates = ['status' => 'archived'];
        if (Schema::hasColumn('contact_messages', 'ticket_status')) {
            $updates['ticket_status'] = 'closed';
        }
        $ticket->forceFill($updates)->save();

        $this->notifyUser($ticket, 'closed', 'This ticket was marked closed. Open a new contact message if you need more help.');
    }

    protected function notifyUser(ContactMessage $ticket, string $event, string $body, string $actor = 'Support'): void
    {
        if ($ticket->email) {
            try {
                Mail::to($ticket->email)->send(new SupportTicketMail($ticket, $event, $body, $actor));
            } catch (\Throwable $e) {
                Log::warning('Ticket email failed: '.$e->getMessage());
            }
        }

        $user = $ticket->user;
        if (! $user && $ticket->email) {
            $user = User::query()->where('email', $ticket->email)->first();
        }

        if ($user) {
            try {
                $title = match ($event) {
                    'created' => 'Ticket '.($ticket->public_id ?: '#'.$ticket->id).' created',
                    'staff_reply' => 'Support replied on '.($ticket->public_id ?: '#'.$ticket->id),
                    'user_reply' => 'Your reply was posted on '.($ticket->public_id ?: '#'.$ticket->id),
                    'closed' => 'Ticket '.($ticket->public_id ?: '#'.$ticket->id).' closed',
                    default => 'Ticket update',
                };
                app(UserNotificationService::class)->create(
                    $user,
                    'support_ticket',
                    $title,
                    Str::limit($body, 160),
                    $ticket->publicUrl(),
                    ['ticket_id' => $ticket->id, 'event' => $event]
                );
            } catch (\Throwable $e) {
                Log::debug('Ticket in-app notify failed: '.$e->getMessage());
            }
        }
    }

    protected function notifyAdminsOfUserReply(ContactMessage $ticket, string $body, User $user): void
    {
        try {
            $admins = User::query()
                ->when(Schema::hasColumn('users', 'role'), fn ($q) => $q->whereIn('role', ['admin', 'editor']))
                ->limit(15)
                ->get();

            if ($admins->isEmpty()) {
                $admins = User::query()->orderBy('id')->limit(3)->get();
            }

            foreach ($admins as $admin) {
                if ((int) $admin->id === (int) $user->id) {
                    continue;
                }
                try {
                    if ($admin->email) {
                        Mail::to($admin->email)->send(new SupportTicketMail(
                            $ticket,
                            'user_reply',
                            $body,
                            $user->name
                        ));
                    }
                } catch (\Throwable) {
                }
                try {
                    app(UserNotificationService::class)->create(
                        $admin,
                        'support_ticket',
                        'User replied on '.($ticket->public_id ?: '#'.$ticket->id),
                        Str::limit($body, 120),
                        url('/admin/contact-messages/'.$ticket->id),
                        ['ticket_id' => $ticket->id]
                    );
                } catch (\Throwable) {
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Admin user-reply notify: '.$e->getMessage());
        }
    }
}
