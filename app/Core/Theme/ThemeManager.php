<?php

namespace App\Core\Theme;

use App\Models\InstalledTheme;
use Illuminate\Support\Facades\Schema;

class ThemeManager
{
    private array $activeThemes = [];

    public function bootActiveThemes(): void
    {
        if (!$this->isDatabaseReady()) return;

        $themes = InstalledTheme::where('is_active', true)->get();

        foreach ($themes as $theme) {
            $this->activeThemes[$theme->area] = $theme->slug;
        }
    }

    public function activate(string $area, string $slug): array
    {
        $themePath = base_path("themes/{$area}/{$slug}/theme.json");
        if (!file_exists($themePath)) {
            return ['success' => false, 'message' => "Theme '{$slug}' tidak ditemukan di area '{$area}'."];
        }

        InstalledTheme::where('area', $area)->update(['is_active' => false]);

        $manifest = $this->getManifest($area, $slug);
        InstalledTheme::updateOrCreate(
            ['area' => $area, 'slug' => $slug],
            [
                'name'      => $manifest['name'] ?? $slug,
                'version'   => $manifest['version'] ?? '1.0.0',
                'is_active' => true,
            ]
        );

        $this->activeThemes[$area] = $slug;

        return ['success' => true, 'message' => "Theme '{$slug}' aktif untuk area '{$area}'."];
    }

    public function getActive(string $area): string
    {
        return $this->activeThemes[$area] ?? config("themes.defaults.{$area}", 'default');
    }

    public function getAll(string $area): array
    {
        $themes = [];
        foreach (glob(base_path("themes/{$area}/*/theme.json")) as $file) {
            $manifest = json_decode(file_get_contents($file), true);
            if ($manifest) {
                $installed        = InstalledTheme::where('area', $area)->where('slug', $manifest['slug'])->first();
                $manifest['active'] = $installed?->is_active ?? false;
                $themes[]           = $manifest;
            }
        }
        return $themes;
    }

    private function getManifest(string $area, string $slug): array
    {
        $path = base_path("themes/{$area}/{$slug}/theme.json");
        return file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
    }

    private function isDatabaseReady(): bool
    {
        try {
            return Schema::hasTable('installed_themes');
        } catch (\Exception) {
            return false;
        }
    }
}
