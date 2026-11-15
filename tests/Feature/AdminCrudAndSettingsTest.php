<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminPathSettings;
use App\Filament\Resources\LicenseTypeResource\Pages\ManageLicenseTypes;
use App\Filament\Resources\OssCategoryResource\Pages\ManageOssCategories;
use App\Filament\Pages\SiteSeoSettings;
use App\Models\SiteSetting;
use App\Models\LicenseType;
use App\Models\OssCategory;
use App\Support\AdminPanel;
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

        $category = \App\Models\OssCategory::query()->create([
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

        $license = \App\Models\LicenseType::query()->create([
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
    public function test_admin_can_open_catalog_management_pages(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->get(AdminPanel::url('oss-categories'))->assertOk();
        $this->get(AdminPanel::url('license-types'))->assertOk();
    }

    public function test_admin_can_create_edit_and_delete_categories_through_filament_actions(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::test(ManageOssCategories::class)
            ->callAction('create', data: [
                'name' => 'Network & WiFi',
                'slug' => 'net-wifi',
                'description' => 'Networking tools',
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $category = OssCategory::query()->where('slug', 'net-wifi')->firstOrFail();
        $this->assertSame('Network & WiFi', $category->name);

        Livewire::test(ManageOssCategories::class)
            ->callTableAction('edit', $category, data: [
                'name' => 'Network and Wireless',
                'slug' => 'net-wifi',
                'description' => 'Updated networking tools',
                'is_active' => false,
            ])
            ->assertHasNoTableActionErrors();

        $category->refresh();
        $this->assertSame('Network and Wireless', $category->name);
        $this->assertSame('net-wifi', $category->slug);
        $this->assertFalse($category->is_active);

        Livewire::test(ManageOssCategories::class)
            ->callTableAction('delete', $category)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('oss_categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_edit_and_delete_license_types_through_filament_actions(): void
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
            ->assertHasNoActionErrors();

        $license = LicenseType::query()->where('slug', 'example-license')->firstOrFail();
        $this->assertSame('EXAMPLE', $license->spdx_id);

        Livewire::test(ManageLicenseTypes::class)
            ->callTableAction('edit', $license, data: [
                'name' => 'Renamed Example License',
                'slug' => 'example-license',
                'spdx_id' => 'EXAMPLE-2',
                'description' => 'Updated license description',
                'is_osi_approved' => true,
                'is_active' => false,
            ])
            ->assertHasNoTableActionErrors();

        $license->refresh();
        $this->assertSame('Renamed Example License', $license->name);
        $this->assertSame('example-license', $license->slug);
        $this->assertSame('EXAMPLE-2', $license->spdx_id);
        $this->assertTrue($license->is_osi_approved);
        $this->assertFalse($license->is_active);

        Livewire::test(ManageLicenseTypes::class)
            ->callTableAction('delete', $license)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('license_types', ['id' => $license->id]);
    }

}
