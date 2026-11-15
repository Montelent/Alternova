<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminPathSettings;
use App\Filament\Pages\SiteSeoSettings;
use App\Filament\Resources\LicenseTypeResource\Pages\ManageLicenseTypes;
use App\Filament\Resources\OssCategoryResource\Pages\ManageOssCategories;
use App\Models\LicenseType;
use App\Models\OssCategory;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_admin_can_create_and_edit_categories_without_automatic_slug_rewrites(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::test(ManageOssCategories::class)
            ->callAction('create', data: [
                'name' => 'Network & WiFi',
                'slug' => 'net-wifi',
                'description' => 'Networking tools',
                'is_active' => true,
            ])
            ->assertHasNoFormErrors();

        $category = OssCategory::query()->where('slug', 'net-wifi')->firstOrFail();
        $this->assertSame('Network & WiFi', $category->name);

        Livewire::test(ManageOssCategories::class)
            ->callTableAction('edit', $category, data: [
                'name' => 'Network and Wireless',
                'slug' => 'net-wifi',
                'description' => 'Updated description',
                'is_active' => true,
            ])
            ->assertHasNoFormErrors();

        $category->refresh();
        $this->assertSame('Network and Wireless', $category->name);
        $this->assertSame('net-wifi', $category->slug);
        $this->assertSame('Updated description', $category->description);
    }

    public function test_admin_can_create_and_edit_license_types_and_preserve_existing_slug(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::test(ManageLicenseTypes::class)
            ->callAction('create', data: [
                'name' => 'Example License',
                'slug' => 'example-license',
                'spdx_id' => 'EXAMPLE',
                'description' => 'A test license',
                'is_osi_approved' => false,
                'is_active' => true,
            ])
            ->assertHasNoFormErrors();

        $license = LicenseType::query()->where('slug', 'example-license')->firstOrFail();

        Livewire::test(ManageLicenseTypes::class)
            ->callTableAction('edit', $license, data: [
                'name' => 'Renamed Example License',
                'slug' => 'example-license',
                'spdx_id' => 'EXAMPLE',
                'description' => 'Updated license description',
                'is_osi_approved' => false,
                'is_active' => true,
            ])
            ->assertHasNoFormErrors();

        $license->refresh();
        $this->assertSame('Renamed Example License', $license->name);
        $this->assertSame('example-license', $license->slug);
        $this->assertSame('Updated license description', $license->description);
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
            ], statePath: 'data')
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

        Livewire::test(AdminPathSettings::class)
            ->set('data.admin_path', 'changed-admin-path')
            ->call('save')
            ->assertForbidden();

        $this->assertSame('admin', SiteSetting::get('admin_path'));
    }

}
