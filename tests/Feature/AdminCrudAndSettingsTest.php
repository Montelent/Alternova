<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminPathSettings;
use App\Filament\Pages\SiteSeoSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminCrudAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::query()->create([
            'name' => 'Verified Admin',
            'email' => 'admin@example.test',
            'password' => 'test-password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_catalog_category_and_license_records_support_create_and_edit_persistence(): void
    {
        $this->actingAs($this->makeAdmin());

        $category = \\App\\Models\\OssCategory::query()->create([
            'name' => 'Network & WiFi',
            'slug' => 'net-wifi',
            'description' => 'Networking tools',
            'is_active' => true,
        ]);
        $category->update([
            'name' => 'Network and Wireless',
            'description' => 'Updated description',
        ]);

        $this->assertDatabaseHas('oss_categories', [
            'id' => $category->id,
            'name' => 'Network and Wireless',
            'slug' => 'net-wifi',
            'description' => 'Updated description',
        ]);

        $license = \\App\\Models\\LicenseType::query()->create([
            'name' => 'Example License',
            'slug' => 'example-license',
            'spdx_id' => 'EXAMPLE',
            'description' => 'A test license',
            'is_osi_approved' => false,
            'is_active' => true,
        ]);
        $license->update([
            'name' => 'Renamed Example License',
            'description' => 'Updated license description',
        ]);

        $this->assertDatabaseHas('license_types', [
            'id' => $license->id,
            'name' => 'Renamed Example License',
            'slug' => 'example-license',
            'description' => 'Updated license description',
        ]);
    }

    public function test_admin_can_save_seo_settings_and_boolean_flags_are_persisted(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::test(SiteSeoSettings::class)
            ->fillForm([
                'seo_site_name' => 'Alternova Test',
                'seo_separator' => '—',
                'seo_site_noindex' => true,
                'seo_sitemap_domains' => false,
                'seo_sitemap_compare' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Alternova Test', SiteSetting::get('seo_site_name'));
        $this->assertSame('—', SiteSetting::get('seo_separator'));
        $this->assertTrue(SiteSetting::getBool('seo_site_noindex'));
        $this->assertFalse(SiteSetting::getBool('seo_sitemap_domains', true));
        $this->assertTrue(SiteSetting::getBool('seo_sitemap_compare'));
    }

    public function test_non_admin_cannot_access_system_seo_settings(): void
    {
        $editor = User::query()->create([
            'name' => 'Verified Editor',
            'email' => 'editor@example.test',
            'password' => 'test-password',
            'role' => User::ROLE_EDITOR,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($editor);

        $this->assertFalse(SiteSeoSettings::canAccess());
    }
    public function test_editor_cannot_change_the_admin_panel_path(): void
    {
        $editor = User::query()->create([
            'name' => 'Verified Editor',
            'email' => 'path-editor@example.test',
            'password' => 'test-password',
            'role' => User::ROLE_EDITOR,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        SiteSetting::set('admin_path', 'admin');

        $this->actingAs($editor);

        try {
            (new AdminPathSettings())->save();
            $this->fail('An editor must not be able to change the admin path.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertSame('admin', SiteSetting::get('admin_path'));
    }

}
