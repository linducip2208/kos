<?php

namespace Tests\Feature\Api;

use App\Core\Plugin\PluginManager;
use App\Core\Theme\ThemeManager;
use App\Models\InstalledPlugin;
use App\Models\InstalledTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TestDummyHook
{
    public function handle(): void
    {
        File::put(storage_path('framework/testing/dummy-hook.txt'), 'hooked');
    }
}

class PluginThemeLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private string $pluginDir;

    private string $themeDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pluginDir = base_path('plugins/test-dummy');
        $this->themeDir = base_path('themes/frontend/test-dummy');
        File::ensureDirectoryExists($this->pluginDir);
        File::ensureDirectoryExists($this->themeDir);
        File::put($this->pluginDir.'/plugin.json', json_encode([
            'name' => 'Test Dummy',
            'slug' => 'test-dummy',
            'version' => '1.0.0',
            'requires_php' => '8.0',
            'requires_laravel' => '1.0',
            'requires_app' => '0.0.1',
            'license_required' => false,
            'migrations' => false,
            'hooks' => ['on_install' => TestDummyHook::class],
        ]));
        File::put($this->themeDir.'/theme.json', json_encode([
            'name' => 'Test Dummy', 'slug' => 'test-dummy', 'version' => '1.0.0',
        ]));
        File::delete(storage_path('framework/testing/dummy-hook.txt'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->pluginDir);
        File::deleteDirectory($this->themeDir);
        parent::tearDown();
    }

    public function test_plugin_lifecycle_install_activate_deactivate_uninstall(): void
    {
        $manager = app(PluginManager::class);

        $install = $manager->install('test-dummy');
        $this->assertTrue($install['success']);
        $this->assertTrue(File::exists(storage_path('framework/testing/dummy-hook.txt')));
        $this->assertTrue(InstalledPlugin::where('plugin_slug', 'test-dummy')->where('is_active', true)->exists());

        $this->assertTrue($manager->deactivate('test-dummy')['success']);
        $this->assertFalse(InstalledPlugin::where('plugin_slug', 'test-dummy')->first()->is_active);
        $this->assertTrue($manager->activate('test-dummy')['success']);

        $this->assertTrue($manager->uninstall('test-dummy')['success']);
        $this->assertFalse(InstalledPlugin::where('plugin_slug', 'test-dummy')->exists());

        $again = $manager->uninstall('test-dummy');
        $this->assertFalse($again['success']);
    }

    public function test_plugin_rejects_traversal_invalid_manifest_and_version_mismatch(): void
    {
        $manager = app(PluginManager::class);

        $this->assertFalse($manager->install('../evil')['success']);
        $this->assertFalse($manager->activate('..')['success']);
        $this->assertFalse($manager->uninstall('a/b')['success']);

        File::put($this->pluginDir.'/plugin.json', json_encode(['name' => 'Bad', 'slug' => 'test-dummy']));
        $bad = $manager->install('test-dummy');
        $this->assertFalse($bad['success']);
        $this->assertStringContainsString('version', $bad['message']);

        File::put($this->pluginDir.'/plugin.json', json_encode([
            'name' => 'Future', 'slug' => 'test-dummy', 'version' => '9.9.9', 'requires_app' => '99.0.0',
        ]));
        $future = $manager->install('test-dummy');
        $this->assertFalse($future['success']);
        $this->assertStringContainsString('membutuhkan', $future['message']);
    }

    public function test_theme_activate_rejects_bad_area_and_slug(): void
    {
        $manager = app(ThemeManager::class);

        $this->assertFalse($manager->activate('../../etc', 'x')['success']);
        $this->assertFalse($manager->activate('frontend', '../x')['success']);

        $ok = $manager->activate('frontend', 'test-dummy');
        $this->assertTrue($ok['success']);
        $this->assertTrue(InstalledTheme::where('area', 'frontend')->where('slug', 'test-dummy')
            ->where('is_active', true)->exists());
    }

    public function test_plugin_api_rejects_invalid_slug(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'owner', 'is_active' => true]));

        $this->postJson('/api/v1/admin/plugins/install', ['slug' => '../evil'])->assertStatus(422);
        $this->postJson('/api/v1/admin/plugins/../evil/activate')->assertStatus(404);
        $this->postJson('/api/v1/admin/themes/activate', ['area' => 'frontend', 'slug' => '../x'])->assertStatus(422);
    }
}
