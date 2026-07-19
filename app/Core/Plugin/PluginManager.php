<?php

namespace App\Core\Plugin;

use App\Core\License\LicenseService;
use App\Models\InstalledPlugin;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PluginManager
{
    private static array $registered = [];

    public function __construct(private LicenseService $licenseService) {}

    public static function register(string $slug, array $config): void
    {
        static::$registered[$slug] = $config;
    }

    public function install(string $slug, ?string $activationKey = null): array
    {
        $manifest = $this->loadManifest($slug);
        if (!$manifest) {
            return ['success' => false, 'message' => "Plugin '{$slug}' tidak ditemukan."];
        }

        if ($manifest['license_required'] ?? false) {
            if (!$activationKey) {
                return ['success' => false, 'message' => 'Activation key diperlukan untuk plugin ini.'];
            }
        }

        if ($manifest['migrations'] ?? false) {
            $migPath = base_path("plugins/{$slug}/database/migrations");
            if (is_dir($migPath)) {
                Artisan::call('migrate', [
                    '--path'  => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
            }
        }

        $this->runHook($slug, 'on_install', $manifest);

        InstalledPlugin::updateOrCreate(
            ['plugin_slug' => $slug],
            [
                'version'        => $manifest['version'],
                'activation_key' => $activationKey,
                'is_active'      => true,
                'installed_at'   => now(),
                'activated_at'   => now(),
            ]
        );

        Log::info("Plugin installed: {$slug}");

        return ['success' => true, 'message' => "Plugin '{$manifest['name']}' berhasil diinstall."];
    }

    public function activate(string $slug): array
    {
        $plugin = InstalledPlugin::where('plugin_slug', $slug)->first();
        if (!$plugin) {
            return ['success' => false, 'message' => 'Plugin belum diinstall.'];
        }

        $this->runHook($slug, 'on_activate');
        $plugin->update(['is_active' => true, 'activated_at' => now()]);

        return ['success' => true, 'message' => "Plugin '{$slug}' diaktifkan."];
    }

    public function deactivate(string $slug): array
    {
        $plugin = InstalledPlugin::where('plugin_slug', $slug)->first();
        if (!$plugin) {
            return ['success' => false, 'message' => 'Plugin tidak ditemukan.'];
        }

        $this->runHook($slug, 'on_deactivate');
        $plugin->update(['is_active' => false]);

        return ['success' => true, 'message' => "Plugin '{$slug}' dinonaktifkan."];
    }

    public function uninstall(string $slug, bool $dropMigrations = false): array
    {
        $this->deactivate($slug);
        $this->runHook($slug, 'on_uninstall');

        if ($dropMigrations) {
            Artisan::call('migrate:rollback', [
                '--path'  => "plugins/{$slug}/database/migrations",
                '--force' => true,
            ]);
        }

        InstalledPlugin::where('plugin_slug', $slug)->delete();

        return ['success' => true, 'message' => "Plugin '{$slug}' di-uninstall."];
    }

    public function isActive(string $slug): bool
    {
        return InstalledPlugin::where('plugin_slug', $slug)->where('is_active', true)->exists();
    }

    public function getActive(): array
    {
        return InstalledPlugin::where('is_active', true)->pluck('plugin_slug')->toArray();
    }

    public function getAll(): array
    {
        $plugins = [];
        foreach (glob(base_path('plugins/*/plugin.json')) as $file) {
            $manifest = json_decode(file_get_contents($file), true);
            if ($manifest) {
                $installed           = InstalledPlugin::where('plugin_slug', $manifest['slug'])->first();
                $manifest['installed'] = !is_null($installed);
                $manifest['active']    = $installed?->is_active ?? false;
                $plugins[]             = $manifest;
            }
        }
        return $plugins;
    }

    public function loadManifest(string $slug): ?array
    {
        $path = base_path("plugins/{$slug}/plugin.json");
        return file_exists($path) ? json_decode(file_get_contents($path), true) : null;
    }

    private function runHook(string $slug, string $hookName, array $manifest = []): void
    {
        $manifest  = $manifest ?: ($this->loadManifest($slug) ?? []);
        $hookClass = $manifest['hooks'][$hookName] ?? null;

        if ($hookClass && class_exists($hookClass)) {
            try {
                app($hookClass)->handle();
            } catch (\Exception $e) {
                Log::error("Plugin hook error [{$slug}:{$hookName}]: " . $e->getMessage());
            }
        }
    }
}
