<?php

namespace App\Filament\Pages;

use App\Core\Plugin\PluginManager;
use App\Models\InstalledPlugin;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

class PluginManagement extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?int    $navigationSort = 20;
    protected string $view = 'filament.pages.plugin-management';

    public static function getNavigationGroup(): ?string { return '?? Sistem'; }
    public static function getNavigationLabel(): string  { return 'Plugin Management'; }

    public function getPlugins(): array
    {
        $plugins = [];
        $pluginsDir = base_path('plugins');

        if (!File::isDirectory($pluginsDir)) return [];

        foreach (File::directories($pluginsDir) as $dir) {
            $manifestPath = $dir . '/plugin.json';
            if (!File::exists($manifestPath)) continue;

            $manifest = json_decode(File::get($manifestPath), true);
            if (!$manifest) continue;

            $slug      = $manifest['slug'] ?? basename($dir);
            $installed = InstalledPlugin::where('plugin_slug', $slug)->first();

            $plugins[] = [
                'slug'        => $slug,
                'name'        => $manifest['name'] ?? $slug,
                'description' => $manifest['description'] ?? '',
                'version'     => $manifest['version'] ?? '1.0.0',
                'author'      => $manifest['author'] ?? '-',
                'is_installed'=> (bool) $installed,
                'is_active'   => (bool) $installed?->is_active,
                'installed_at'=> $installed?->installed_at,
                'license_required' => $manifest['license_required'] ?? false,
            ];
        }

        return $plugins;
    }

    public function install(string $slug): void
    {
        $manager = app(PluginManager::class);
        $result  = $manager->install($slug);

        if ($result['success']) {
            Notification::make()->title($result['message'])->success()->send();
        } else {
            Notification::make()->title($result['message'])->danger()->send();
        }
    }

    public function activate(string $slug): void
    {
        $manager = app(PluginManager::class);
        $result  = $manager->activate($slug);

        if ($result['success']) {
            Notification::make()->title($result['message'])->success()->send();
        } else {
            Notification::make()->title($result['message'])->danger()->send();
        }
    }

    public function deactivate(string $slug): void
    {
        $manager = app(PluginManager::class);
        $result  = $manager->deactivate($slug);

        if ($result['success']) {
            Notification::make()->title($result['message'])->warning()->send();
        } else {
            Notification::make()->title($result['message'])->danger()->send();
        }
    }

    public function uninstall(string $slug): void
    {
        $manager = app(PluginManager::class);
        $result  = $manager->uninstall($slug);

        if ($result['success']) {
            Notification::make()->title($result['message'])->warning()->send();
        } else {
            Notification::make()->title($result['message'])->danger()->send();
        }
    }
}
