<?php

namespace Tests\Unit;

use App\Services\DomainCheckService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DomainCheckServiceTest extends TestCase
{
    public function test_only_valid_public_fully_qualified_domains_are_accepted(): void
    {
        $service = new DomainCheckService();
        $method = new ReflectionMethod(DomainCheckService::class, 'isValidPublicDomain');
        $method->setAccessible(true);

        foreach (['example.com', 'sub.example.co', 'xn--caf-dma.com'] as $domain) {
            self::assertTrue($method->invoke($service, $domain), $domain);
        }

        foreach ([
            'localhost',
            'metadata.google.internal',
            'host.local',
            'example.test',
            'example..com',
            '-bad.example.com',
            'bad-.example.com',
            'example.com/path',
            str_repeat('a', 254).'.com',
        ] as $domain) {
            self::assertFalse($method->invoke($service, $domain), $domain);
        }
    }
}
