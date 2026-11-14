<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Webhook;
use App\Support\CanonicalUrl;
use App\Support\Installer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductionHardeningSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Installer::lock();
    }

    protected function tearDown(): void
    {
        File::delete(Installer::lockPath());
        File::delete(storage_path('app/'.Installer::REOPEN_FILE));
        parent::tearDown();
    }

    public function test_public_robots_and_sitemap_endpoints_render_with_security_headers(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertSee('Sitemap: http://localhost/sitemap.xml');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('sitemap-static.xml', false);
    }

    public function test_forwarded_https_header_is_ignored_from_an_untrusted_proxy(): void
    {
        $response = $this->call('GET', '/robots.txt', [], [], [], [
            'HTTP_HOST' => 'localhost',
            'REMOTE_ADDR' => '192.0.2.10',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $response->assertOk();
        self::assertFalse($response->headers->has('Strict-Transport-Security'));
    }

    public function test_forwarded_https_is_accepted_from_an_explicitly_trusted_proxy(): void
    {
        config(['alternova.trusted_proxies' => ['127.0.0.1']]);

        $response = $this->call('GET', '/robots.txt', [], [], [], [
            'HTTP_HOST' => 'localhost',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $response->assertOk();
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_robots_uses_the_current_allowlisted_domain_for_its_sitemap(): void
    {
        config([
            'app.url' => 'https://primary.example.test',
            'alternova.allowed_hosts' => ['secondary.example.test'],
        ]);

        $this->call('GET', '/robots.txt', [], [], [], [
            'HTTP_HOST' => 'secondary.example.test',
            'HTTPS' => 'on',
            'REMOTE_ADDR' => '127.0.0.1',
        ])
            ->assertOk()
            ->assertSee('Sitemap: https://secondary.example.test/sitemap.xml');
    }

    public function test_authenticated_account_routes_remain_protected_from_guests(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_api_key_in_query_string_does_not_authenticate_a_request(): void
    {
        SiteSetting::set('api_require_key', '1');

        $this->getJson('/api/suggest?q=alternova&api_key=not-a-valid-key')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'api_key_required');
    }

    public function test_new_accounts_default_to_member_role(): void
    {
        $user = User::query()->create([
            'name' => 'New Member',
            'email' => 'new-member@example.test',
            'password' => 'test-password',
        ]);

        self::assertSame(User::ROLE_MEMBER, $user->fresh()->role);
        self::assertFalse($user->fresh()->canManageSystem());
    }

    public function test_sensitive_site_settings_and_webhook_secrets_are_encrypted_at_rest(): void
    {
        SiteSetting::set('github_token', 'site-secret-value');
        $storedSetting = SiteSetting::query()->where('key', 'github_token')->value('value');

        self::assertIsString($storedSetting);
        self::assertStringStartsWith('enc:v1:', $storedSetting);
        self::assertStringNotContainsString('site-secret-value', $storedSetting);
        self::assertSame('site-secret-value', SiteSetting::get('github_token'));

        $webhook = Webhook::query()->create([
            'name' => 'Test webhook',
            'url' => 'https://hooks.example.test/endpoint',
            'secret' => 'webhook-secret-value',
            'events' => [Webhook::EVENT_ALTERNATIVE_PUBLISHED],
            'is_active' => true,
        ]);
        $storedWebhookSecret = $webhook->getRawOriginal('secret');

        self::assertNotSame('webhook-secret-value', $storedWebhookSecret);
        self::assertSame('webhook-secret-value', $webhook->fresh()->secret);
    }

    public function test_canonical_origin_uses_only_configured_or_allowlisted_hosts(): void
    {
        config([
            'app.url' => 'https://primary.example.test',
            'alternova.allowed_hosts' => ['secondary.example.test'],
        ]);

        self::assertSame(
            'https://secondary.example.test',
            CanonicalUrl::base(Request::create('https://secondary.example.test/path'))
        );
        self::assertSame(
            'https://primary.example.test',
            CanonicalUrl::base(Request::create('https://attacker.example.test/path'))
        );
    }
}
