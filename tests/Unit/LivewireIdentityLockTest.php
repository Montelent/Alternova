<?php

namespace Tests\Unit;

use App\Livewire\CommentSection;
use App\Livewire\ProsConsSection;
use App\Livewire\ReportIssue;
use App\Livewire\SupportTicketShow;
use Livewire\Attributes\Locked;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class LivewireIdentityLockTest extends TestCase
{
    public function test_public_livewire_components_lock_server_resolved_record_identity(): void
    {
        foreach ([
            [CommentSection::class, 'alternativeId'],
            [ProsConsSection::class, 'alternativeId'],
            [ReportIssue::class, 'alternativeId'],
            [SupportTicketShow::class, 'ticket'],
        ] as [$component, $property]) {
            $reflection = new ReflectionProperty($component, $property);

            self::assertNotEmpty(
                $reflection->getAttributes(Locked::class),
                $component.'::$'.$property.' must not be client-mutable.'
            );
        }
    }
}
