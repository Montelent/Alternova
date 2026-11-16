<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminPathSettings;
use App\Filament\Resources\LicenseTypeResource\Pages\ManageLicenseTypes;
use App\Filament\Resources\CollectionResource\Pages\ListCollections;
use App\Filament\Resources\OssCategoryResource\Pages\ManageOssCategories;
use App\Filament\Resources\ProprietaryToolResource\Pages\ListProprietaryTools;
use App\Filament\Resources\OpenSourceAlternativeResource\Pages\ListOpenSourceAlternatives;
use App\Filament\Pages\SiteSeoSettings;
use App\Models\SiteSetting;
use App\Models\LicenseType;
use App\Models\Collection;
use App\Models\OssCategory;
use App\Models\ProprietaryTool;
use App\Models\OpenSourceAlternative;
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

    public function test_admin_can_create_edit_and_soft_delete_proprietary_tools_through_filament_actions(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::test(ListProprietaryTools::class)
            ->callAction('create', data: [
                'name' => 'Example Proprietary Tool',
                'slug' => 'example-proprietary-tool',
                'website_url' => 'https://example.test',
                'description' => 'Initial description',
                'target_audience' => 'Developers',
                'is_published' => false,
            ])
            ->assertHasNoActionErrors();

        $tool = ProprietaryTool::query()->where('slug', 'example-proprietary-tool')->firstOrFail();

        Livewire::test(ListProprietaryTools::class)
            ->callTableAction('edit', $tool, data: [
                'name' => 'Renamed Proprietary Tool',
                'slug' => 'example-proprietary-tool',
                'website_url' => 'https://example.test/product',
                'description' => 'Updated description',
                'target_audience' => 'Teams',
                'is_published' => true,
            ])
            ->assertHasNoTableActionErrors();

        $tool->refresh();
        $this->assertSame('Renamed Proprietary Tool', $tool->name);
        $this->assertSame('example-proprietary-tool', $tool->slug);
        $this->assertSame('https://example.test/product', $tool->website_url);
        $this->assertTrue($tool->is_published);

        Livewire::test(ListProprietaryTools::class)
            ->callTableAction('delete', $tool)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('proprietary_tools', ['id' => $tool->id]);
    }

    public function test_admin_can_create_and_edit_collections_through_filament_actions(): void
    {
        $this->actingAs($this->makeAdmin());

        Livewire::test(ListCollections::class)
            ->callAction('create', data: [
                'name' => 'Developer Toolkit',
                'slug' => 'developer-toolkit',
                'description' => 'A curated developer collection',
                'intro_html' => '<p>Useful tools</p>',
                'is_published' => false,
                'is_featured' => false,
            ])
            ->assertHasNoActionErrors();

        $collection = Collection::query()->where('slug', 'developer-toolkit')->firstOrFail();
        $this->assertSame('Developer Toolkit', $collection->name);

        Livewire::test(ListCollections::class)
            ->callTableAction('edit', $collection, data: [
                'name' => 'Developer Essentials',
                'slug' => 'developer-toolkit',
                'description' => 'Updated curated collection',
                'intro_html' => '<p>Updated introduction</p>',
                'is_published' => true,
                'is_featured' => true,
            ])
            ->assertHasNoTableActionErrors();

        $collection->refresh();
        $this->assertSame('Developer Essentials', $collection->name);
        $this->assertSame('developer-toolkit', $collection->slug);
        $this->assertSame('Updated curated collection', $collection->description);
        $this->assertTrue($collection->is_published);
        $this->assertTrue($collection->is_featured);
    }

    public function test_admin_can_create_edit_and_soft_delete_alternatives_through_filament_actions(): void
    {
        $this->actingAs($this->makeAdmin());
        $tool = ProprietaryTool::factory()->create([
            'name' => 'Parent Tool',
            'slug' => 'parent-tool',
        ]);

        Livewire::test(ListOpenSourceAlternatives::class)
            ->callAction('create', data: [
                'proprietaryTools' => [$tool->id],
                'proprietary_tool_id' => $tool->id,
                'name' => 'Open Alternative',
                'slug' => 'open-alternative',
                'repo_url' => 'https://github.com/example/open-alternative',
                'website_url' => 'https://open-alternative.example.test',
                'is_published' => false,
                'is_featured' => false,
                'is_sponsored' => false,
            ])
            ->assertHasNoActionErrors();

        $alternative = OpenSourceAlternative::query()->where('slug', 'open-alternative')->firstOrFail();
        $this->assertSame($tool->id, $alternative->proprietary_tool_id);
        $this->assertTrue($alternative->proprietaryTools()->whereKey($tool->id)->exists());

        Livewire::test(ListOpenSourceAlternatives::class)
            ->callTableAction('edit', $alternative, data: [
                'proprietaryTools' => [$tool->id],
                'proprietary_tool_id' => $tool->id,
                'name' => 'Renamed Open Alternative',
                'slug' => 'open-alternative',
                'repo_url' => 'https://github.com/example/open-alternative',
                'website_url' => 'https://open-alternative.example.test/app',
                'is_published' => true,
                'is_featured' => true,
                'is_sponsored' => false,
            ])
            ->assertHasNoTableActionErrors();

        $alternative->refresh();
        $this->assertSame('Renamed Open Alternative', $alternative->name);
        $this->assertSame('open-alternative', $alternative->slug);
        $this->assertSame('https://open-alternative.example.test/app', $alternative->website_url);
        $this->assertTrue($alternative->is_published);
        $this->assertTrue($alternative->is_featured);

        Livewire::test(ListOpenSourceAlternatives::class)
            ->callTableAction('delete', $alternative)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('open_source_alternatives', ['id' => $alternative->id]);
    }

}
