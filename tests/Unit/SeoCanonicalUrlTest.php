<?php

namespace Tests\\Unit;

use App\\Services\\SeoManager;
use Illuminate\\Http\\Request;
use Tests\\TestCase;

class SeoCanonicalUrlTest extends TestCase
{
    public function test_canonical_url_uses_a_trusted_active_domain_and_drops_query_parameters(): void
    {
        config([
            'app.url' => 'https://primary.example',
            'alternova.allowed_hosts' => ['secondary.example'],
        ]);

        $request = Request::create('https://secondary.example/catalog/items?sort=popular&page=2');

        $this->assertSame(
            'https://secondary.example/catalog/items',
            app(SeoManager::class)->currentCanonicalUrl($request)
        );
    }

    public function test_canonical_url_falls_back_to_app_url_for_an_untrusted_host(): void
    {
        config([
            'app.url' => 'https://primary.example',
            'alternova.allowed_hosts' => ['secondary.example'],
        ]);

        $request = Request::create('https://attacker.example/catalog/items?next=https://attacker.example');

        $this->assertSame(
            'https://primary.example/catalog/items',
            app(SeoManager::class)->currentCanonicalUrl($request)
        );
    }

    public function test_homepage_canonical_has_a_trailing_slash(): void
    {
        config([
            'app.url' => 'https://primary.example',
            'alternova.allowed_hosts' => ['secondary.example'],
        ]);

        $request = Request::create('https://secondary.example/?utm_source=newsletter');

        $this->assertSame(
            'https://secondary.example/',
            app(SeoManager::class)->currentCanonicalUrl($request)
        );
    }
}
