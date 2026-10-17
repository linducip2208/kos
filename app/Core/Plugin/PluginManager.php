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
        if (! SlugGuard::plugin($slug)) {
            return ['success' => false, 'message' => 'Slug plugin tidak valid.'];
        }

        $manifest = $this->loadManifest($slug);
        if (!$manifest) {
            return ['success' => false, 'message' => "Plugin '{$slug}' tidak ditemukan."];
        }

        $error = $this->validateManifest($slug, $manifest);
        if ($error) {
            return ['success' => false, 'message' => $error];
        }

        if ($manifest['license_required'] ?? false) {
            if (!$activationKey) {
                return ['success' => false, 'message' => 'Activation key diperlukan untuk plugin ini.'];
            }
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($slug, $activationKey, $manifest) {
            if ($manifest['migrations'] ?? false) {
                $migPath = base_path("plugins/{$slug}/database/migrations");
                if (is_dir($migPath)) {
                    $exit = Artisan::call('migrate', [
                        '--path'  => "plugins/{$slug}/database/migrations",
                        '--force' => true,
                    ]);
                    if ($exit !== 0) {
                        throw new \RuntimeException("Migrasi plugin '{$slug}' gagal (exit {$exit}).");
                    }
                }
            }

            if (! $this->runHook($slug, 'on_install', $manifest)) {
                throw new \RuntimeException("Hook on_install plugin '{$slug}' gagal.");
            }

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
        });
    }

    public function activate(string $slug): array
    {
        if (! SlugGuard::plugin($slug)) {
            return ['success' => false, 'message' => 'Slug plugin tidak valid.'];
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($slug) {
            $plugin = InstalledPlugin::where('plugin_slug', $slug)->first();
            if (!$plugin) {
                return ['success' => false, 'message' => 'Plugin belum diinstall.'];
            }

            $this->runHook($slug, 'on_activate');
            $plugin->update(['is_active' => true, 'activated_at' => now()]);

            return ['success' => true, 'message' => "Plugin '{$slug}' diaktifkan."];
        });
    }

    public function deactivate(string $slug): array
    {
        if (! SlugGuard::plugin($slug)) {
            return ['success' => false, 'message' => 'Slug plugin tidak valid.'];
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($slug) {
            $plugin = InstalledPlugin::where('plugin_slug', $slug)->first();
            if (!$plugin) {
                return ['success' => false, 'message' => 'Plugin tidak ditemukan.'];
            }

            $this->runHook($slug, 'on_deactivate');
            $plugin->update(['is_active' => false]);

            return ['success' => true, 'message' => "Plugin '{$slug}' dinonaktifkan."];
        });
    }

    public function uninstall(string $slug, bool $dropMigrations = false): array
    {
        if (! SlugGuard::plugin($slug)) {
            return ['success' => false, 'message' => 'Slug plugin tidak valid.'];
        }

        $exists = InstalledPlugin::where('plugin_slug', $slug)->exists();
        if (! $exists) {
            return ['success' => false, 'message' => 'Plugin belum diinstall.'];
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($slug, $dropMigrations) {
            $this->deactivate($slug);
            $this->runHook($slug, 'on_uninstall');

            if ($dropMigrations) {
                Artisan::call('migrate:rollback', [
                    '--path'  => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
            }

            InstalledPlugin::where('plugin_slug', $slug)->delete();

            return ['success' => true, 'message' => "Plugin '{$slug}' di-uninstall. (File tidak dihapus.)"];
        });
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
            try {
                $manifest = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }
            if (! is_array($manifest) || ($manifest['slug'] ?? null) !== basename(dirname($file))) {
                continue;
            }
            $installed           = InstalledPlugin::where('plugin_slug', $manifest['slug'])->first();
            $manifest['installed'] = !is_null($installed);
            $manifest['active']    = $installed?->is_active ?? false;
            $plugins[]             = $manifest;
        }
        return $plugins;
    }

    public function loadManifest(string $slug): ?array
    {
        if (! SlugGuard::plugin($slug)) {
            return null;
        }

        $path = base_path("plugins/{$slug}/plugin.json");

        if (! SlugGuard::containedIn(base_path('plugins'), $path) || ! file_exists($path)) {
            return null;
        }

        try {
            $manifest = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($manifest) ? $manifest : null;
    }

    /**
     * Validasi manifest: field wajib, slug cocok direktori, kompatibilitas versi.
     * Return null bila valid, pesan error bila tidak.
     */
    public function validateManifest(string $slug, array $manifest): ?string
    {
        foreach (['name', 'slug', 'version'] as $field) {
            if (empty($manifest[$field]) || ! is_string($manifest[$field])) {
                return "Manifest plugin '{$slug}' tidak valid: field '{$field}' wajib diisi.";
            }
        }

        if ($manifest['slug'] !== $slug) {
            return "Manifest plugin '{$slug}' tidak valid: slug manifest ('{$manifest['slug']}') tidak cocok dengan direktori.";
        }

        $checks = [
            'requires_php' => PHP_VERSION,
            'requires_laravel' => \Illuminate\Foundation\Application::VERSION,
            'requires_app' => (string) config('koskosan.version', '1.0.0'),
        ];

        foreach ($checks as $key => $current) {
            $required = $manifest[$key] ?? null;
            if ($required && version_compare($current, ltrim($required, 'v'), '<')) {
                return "Plugin '{$slug}' membutuhkan {$key} {$required} (saat ini {$current}).";
            }
        }

        return null;
    }

    private function runHook(string $slug, string $hookName, array $manifest = []): bool
    {
        $manifest  = $manifest ?: ($this->loadManifest($slug) ?? []);
        $hookClass = $manifest['hooks'][$hookName] ?? null;

        if ($hookClass && class_exists($hookClass)) {
            try {
                app($hookClass)->handle();

                return true;
            } catch (\Exception $e) {
                Log::error("Plugin hook error [{$slug}:{$hookName}]: " . $e->getMessage());

                return false;
            }
        }

        return true;
    }
}
