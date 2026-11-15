<?php

namespace Tests\Feature;

use App\Support\Installer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PublicApplicationRoutesTest extends TestCase
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

    public static function publicRoutes(): array
    {
        return [
            'homepage' => ['/'],
            'alternative finder' => ['/alternatives'],
            'comparison page' => ['/alternatives/compare'],
            'category browse hub' => ['/browse/categories'],
            'language browse hub' => ['/browse/languages'],
            'license browse hub' => ['/browse/licenses'],
            'trending page' => ['/trending'],
            'leaderboard page' => ['/leaderboard'],
            'whats new page' => ['/whats-new'],
            'API documentation' => ['/api-docs'],
            'collections index' => ['/collections'],
            'domain combinator' => ['/domains'],
            'contact form' => ['/contact'],
            'alternative suggestion form' => ['/suggest'],
            'collection suggestion form' => ['/suggest-collection'],
            'about page' => ['/about'],
            'privacy page' => ['/privacy'],
            'terms page' => ['/terms'],
            'disclosure page' => ['/disclosure'],
            'RSS feed' => ['/feed'],
            'Atom feed' => ['/feed/atom'],
        ];
    }

    /**
     * @dataProvider publicRoutes
     */
    public function test_public_application_routes_render_successfully(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    public function test_guest_authentication_pages_render_successfully(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_authenticated_routes_redirect_guests_to_login(): void
    {
        $this->get('/account')->assertRedirect('/login');
        $this->get('/favorites')->assertRedirect('/login');
        $this->get('/notifications')->assertRedirect('/login');
    }

    public function test_legacy_tool_url_redirects_to_the_canonical_alternativesto_route(): void
    {
        $this->get('/tools/example-tool')
            ->assertStatus(301)
            ->assertRedirect(route('alternativesto.show', ['slug' => 'example-tool']));
    }
}
