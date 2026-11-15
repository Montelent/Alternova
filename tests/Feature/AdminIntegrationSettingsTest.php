<?php

namespace Tests\Feature;

use App\Filament\Pages\ApiSettingsPage;
use App\Filament\Pages\IntegrationsPage;
use App\Filament\Pages\MailSettingsPage;
use App\Mail\TestMail;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\ApiSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class AdminIntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::query()->create([
            'name' => 'Verified Admin',
            'email' => 'integrations-admin@example.test',
            'password' => 'test-password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_save_and_test_an_encrypted_github_token(): void
    {
        $this->actingAs($this->makeAdmin());

        Http::preventStrayRequests();
        Http::fake([
            'https://api.github.com/rate_limit' => Http::response([
                'resources' => [
                    'core' => [
                        'remaining' => 4990,
                        'limit' => 5000,
                    ],
                ],
            ], 200),
        ]);

        Livewire::test(IntegrationsPage::class)
            ->fillForm(['github_token' => 'ghp_test_token'])
            ->call('testGithub')
            ->assertHasNoFormErrors()
            ->assertSet('lastOutput', 'OK — core rate limit remaining: 4990 / 5000');

        $stored = SiteSetting::query()->where('key', 'github_token')->value('value');
        $this->assertStringStartsWith('enc:v1:', (string) $stored);
        $this->assertSame('ghp_test_token', SiteSetting::get('github_token'));

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.github.com/rate_limit'
            && $request->hasHeader('Authorization', 'Bearer ghp_test_token'));
    }

    public function test_admin_can_save_api_switches_and_rate_limits(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::test(ApiSettingsPage::class)
            ->fillForm([
                'api_globally_enabled' => false,
                'api_require_key' => true,
                'api_rate_limit_anon' => 80,
                'api_rate_limit_key' => 800,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse(ApiSettings::isGloballyEnabled());
        $this->assertTrue(ApiSettings::requireKey());
        $this->assertSame(80, ApiSettings::rateLimitAnonymous());
        $this->assertSame(800, ApiSettings::rateLimitAuthenticated());
    }

    public function test_admin_can_save_encrypted_smtp_credentials_and_send_a_test_email(): void
    {
        $this->actingAs($this->makeAdmin());
        Mail::fake();

        Livewire::test(MailSettingsPage::class)
            ->fillForm([
                'mail_mailer' => 'array',
                'mail_from_address' => 'hello@alternova.example',
                'mail_from_name' => 'Alternova',
                'mail_admin_email' => 'admin@alternova.example',
                'mail_smtp_host' => 'smtp.example.test',
                'mail_smtp_port' => '587',
                'mail_smtp_username' => 'smtp-user',
                'mail_smtp_password' => 'smtp-secret-value',
                'mail_smtp_encryption' => 'tls',
                'mail_resend_key' => '',
                'test_to' => 'recipient@example.test',
            ])
            ->call('sendTest')
            ->assertHasNoFormErrors();

        $stored = SiteSetting::query()->where('key', 'mail_smtp_password')->value('value');
        $this->assertStringStartsWith('enc:v1:', (string) $stored);
        $this->assertSame('smtp-secret-value', SiteSetting::get('mail_smtp_password'));

        Mail::assertSent(TestMail::class, fn (TestMail $mail): bool => $mail->hasTo('recipient@example.test'));
    }

    public function test_editor_cannot_save_integration_or_api_settings_actions(): void
    {
        $editor = User::query()->create([
            'name' => 'Verified Editor',
            'email' => 'integrations-editor@example.test',
            'password' => 'test-password',
            'role' => User::ROLE_EDITOR,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($editor);

        try {
            (new IntegrationsPage())->save();
            $this->fail('An editor must not save integration settings.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        try {
            (new ApiSettingsPage())->save();
            $this->fail('An editor must not save API settings.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
