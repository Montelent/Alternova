<?php

namespace Tests\Unit;

use App\Models\ContactMessage;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class ContactMessageAuthorizationTest extends TestCase
{
    public function test_unverified_matching_email_cannot_access_a_guest_ticket(): void
    {
        $ticket = new ContactMessage([
            'email' => 'owner@example.com',
            'ticket_status' => 'open',
        ]);
        $user = new User([
            'id' => 42,
            'email' => 'owner@example.com',
            'email_verified_at' => null,
        ]);

        self::assertFalse($ticket->canBeViewedBy($user));
        self::assertFalse($ticket->canBeRepliedBy($user));
    }

    public function test_verified_matching_email_can_access_and_reply_to_guest_ticket(): void
    {
        $ticket = new ContactMessage([
            'email' => 'owner@example.com',
            'ticket_status' => 'open',
        ]);
        $user = new User([
            'id' => 42,
            'email' => 'OWNER@example.com',
            'email_verified_at' => new \DateTimeImmutable(),
        ]);

        self::assertTrue($ticket->canBeViewedBy($user));
        self::assertTrue($ticket->canBeRepliedBy($user));
    }

    public function test_ticket_bearer_token_remains_an_independent_access_credential(): void
    {
        $ticket = new ContactMessage([
            'email' => 'owner@example.com',
            'access_token' => 'a-long-random-ticket-token',
            'ticket_status' => 'open',
        ]);

        self::assertTrue($ticket->canBeViewedBy(null, 'a-long-random-ticket-token'));
    }

    public function test_account_linked_ticket_is_owned_by_its_user_id(): void
    {
        $ticket = new ContactMessage([
            'email' => 'old-address@example.com',
            'user_id' => 42,
            'ticket_status' => 'open',
        ]);
        $user = new User([
            'email' => 'new-address@example.com',
            'email_verified_at' => null,
        ]);
        $user->setAttribute('id', 42);

        self::assertTrue($ticket->canBeViewedBy($user));
        self::assertTrue($ticket->canBeRepliedBy($user));
    }

    public function test_verified_email_owner_cannot_reply_to_a_closed_ticket(): void
    {
        $ticket = new ContactMessage([
            'email' => 'owner@example.com',
            'ticket_status' => 'closed',
        ]);
        $user = new User([
            'id' => 42,
            'email' => 'owner@example.com',
            'email_verified_at' => new \DateTimeImmutable(),
        ]);

        self::assertTrue($ticket->canBeViewedBy($user));
        self::assertFalse($ticket->canBeRepliedBy($user));
    }
}
