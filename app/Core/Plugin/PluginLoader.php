<?php

namespace App\Core\Plugin;

use App\Models\InstalledPlugin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PluginLoader
{
    public static function bootActivePlugins(): void
    {
        if (!static::isDatabaseReady()) return;

        $activePlugins = InstalledPlugin::where('is_active', true)->get();

        foreach ($activePlugins as $plugin) {
            // Tiap plugin di-load dengan try/catch agar 1 plugin yang rusak tidak menjatuhkan seluruh aplikasi.
            try {
                $manifestPath = base_path("plugins/{$plugin->plugin_slug}/plugin.json");
                if (!file_exists($manifestPath)) {
                    Log::warning("Plugin '{$plugin->plugin_slug}' di-skip: plugin.json tidak ditemukan.");
                    continue;
                }

                $manifest      = json_decode(file_get_contents($manifestPath), true);
                $providerClass = static::resolveProviderClass($plugin->plugin_slug, $manifest['main'] ?? null);

                if ($providerClass && class_exists($providerClass)) {
                    app()->register($providerClass);
                }
            } catch (Throwable $e) {
                Log::error("Plugin '{$plugin->plugin_slug}' gagal di-load: {$e->getMessage()}", [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                // Auto-deactivate plugin yang error agar tidak crash di request berikutnya.
                // Admin bisa aktifkan ulang setelah memperbaiki di /admin/plugin-management.
                try {
                    $plugin->update(['is_active' => false]);
                    Log::warning("Plugin '{$plugin->plugin_slug}' otomatis dinonaktifkan karena error saat load.");
                } catch (Throwable) {
                    // ignore — DB mungkin tidak siap
                }
            }
        }
    }

    private static function resolveProviderClass(string $slug, ?string $mainFile): ?string
    {
        if (!$mainFile) return null;

        $className = pathinfo($mainFile, PATHINFO_FILENAME);
        $namespace = 'Plugins\\' . str()->studly($slug) . '\\';

        return $namespace . $className;
    }

    private static function isDatabaseReady(): bool
    {
        try {
            return Schema::hasTable('installed_plugins');
        } catch (\Exception) {
            return false;
        }
    }
}
