<?php

namespace Tests\Unit;

use App\Support\PublicHttpUrl;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class PublicHttpUrlTest extends TestCase
{
    public function test_only_http_and_https_schemes_are_allowed(): void
    {
        self::assertFalse(PublicHttpUrl::isSafe('ftp://8.8.8.8'));
        self::assertFalse(PublicHttpUrl::isSafe('file:///etc/passwd'));
        self::assertTrue(PublicHttpUrl::isSafe('https://8.8.8.8'), 'public IPv4');
    }

    public function test_private_and_special_use_addresses_are_blocked(): void
    {
        foreach ([
            'http://localhost',
            'http://127.0.0.1',
            'http://10.0.0.1',
            'http://172.16.0.1',
            'http://192.168.1.1',
            'http://100.64.0.1',
            'http://100.127.255.254',
            'http://169.254.169.254',
            'http://[::1]',
            'http://[fe80::1]',
            'http://[::ffff:127.0.0.1]',
            'http://[64:ff9b::7f00:1]',
            'http://[2002:7f00:1::]',
            'http://[2001:0:4136:e378:8000:63bf:3fff:fdd2]',
            'http://[2001:db8::1]',
            'http://metadata.google.internal',
            'http://service.local',
        ] as $url) {
            self::assertFalse(PublicHttpUrl::isSafe($url), $url);
        }
    }

    public function test_public_ip_literals_are_allowed(): void
    {
        self::assertTrue(PublicHttpUrl::isSafe('https://8.8.8.8'));
        self::assertTrue(PublicHttpUrl::isSafe('https://[2001:4860:4860::8888]'), 'public IPv6');
        self::assertTrue(PublicHttpUrl::isSafe('https://[::ffff:8.8.8.8]'), 'public mapped IPv4');
    }

    public function test_safe_request_options_do_not_re_resolve_literal_ip_addresses(): void
    {
        self::assertSame([], PublicHttpUrl::safeRequestOptions('https://8.8.8.8'));
    }

    public function test_https_redirects_cannot_downgrade_to_http(): void
    {
        $options = PublicHttpUrl::safeRedirectOptions();
        self::assertSame(0, $options['max']);

        $this->expectException(\RuntimeException::class);
        ($options['on_redirect'])();
    }

    public function test_automatic_redirects_are_rejected_even_for_public_targets(): void
    {
        $options = PublicHttpUrl::safeRedirectOptions();
        self::assertSame(0, $options['max']);

        $this->expectException(\RuntimeException::class);
        ($options['on_redirect'])();
    }

    public function test_credentials_and_unsupported_hosts_are_rejected(): void
    {
        self::assertFalse(PublicHttpUrl::isSafe('https://user:pass@8.8.8.8'));
        self::assertFalse(PublicHttpUrl::isSafe('https://localhost.localdomain'));
        self::assertFalse(PublicHttpUrl::isSafe('https://internal.example'));
    }
}
