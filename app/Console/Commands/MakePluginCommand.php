<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakePluginCommand extends Command
{
    protected $signature   = 'make:plugin {slug : Slug plugin, contoh: maintenance}';
    protected $description = 'Buat scaffold plugin baru di folder plugins/';

    public function handle(): int
    {
        $slug      = Str::slug($this->argument('slug'), '-');
        $studly    = Str::studly($slug);
        $pluginDir = base_path("plugins/{$slug}");

        if (is_dir($pluginDir)) {
            $this->error("Plugin '{$slug}' sudah ada.");
            return Command::FAILURE;
        }

        $this->createStructure($pluginDir, $slug, $studly);
        $this->info("Plugin '{$slug}' berhasil dibuat di plugins/{$slug}/");
        $this->line("Tambahkan autoload ke composer.json:");
        $this->line("  \"Plugins\\\\{$studly}\\\\\": \"plugins/{$slug}/src/\"");
        $this->line("Kemudian jalankan: composer dump-autoload");

        return Command::SUCCESS;
    }

    private function createStructure(string $dir, string $slug, string $studly): void
    {
        $dirs = [
            'src/Controllers', 'src/Models', 'src/Filament/Resources',
            'src/Hooks', 'database/migrations', 'resources/views', 'routes',
        ];

        foreach ($dirs as $d) {
            mkdir("{$dir}/{$d}", 0755, true);
        }

        // plugin.json
        file_put_contents("{$dir}/plugin.json", json_encode([
            'name'             => $studly . ' Plugin',
            'slug'             => $slug,
            'version'          => '1.0.0',
            'description'      => '',
            'author'           => 'whitelabel.co.id',
            'requires_app'     => '1.0.0',
            'requires_php'     => '8.2',
            'requires_laravel' => '13.0',
            'license_required' => false,
            'main'             => "src/{$studly}ServiceProvider.php",
            'migrations'       => true,
            'hooks'            => [
                'on_install'    => "Plugins\\{$studly}\\Hooks\\InstallHook",
                'on_activate'   => "Plugins\\{$studly}\\Hooks\\ActivateHook",
                'on_deactivate' => "Plugins\\{$studly}\\Hooks\\DeactivateHook",
                'on_uninstall'  => "Plugins\\{$studly}\\Hooks\\UninstallHook",
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // ServiceProvider
        file_put_contents("{$dir}/src/{$studly}ServiceProvider.php", $this->serviceProviderStub($slug, $studly));

        // Hooks
        foreach (['Install', 'Activate', 'Deactivate', 'Uninstall'] as $hook) {
            file_put_contents("{$dir}/src/Hooks/{$hook}Hook.php", $this->hookStub($studly, $hook));
        }

        // Empty route files
        file_put_contents("{$dir}/routes/api.php",  "<?php\n");
        file_put_contents("{$dir}/routes/web.php",  "<?php\n");
        file_put_contents("{$dir}/README.md", "# {$studly} Plugin\n");
    }

    private function serviceProviderStub(string $slug, string $studly): string
    {
        return <<<PHP
<?php

namespace Plugins\\{$studly};

use Illuminate\\Support\\ServiceProvider;
use App\\Core\\Plugin\\PluginManager;

class {$studly}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        PluginManager::register('{$slug}', ['version' => '1.0.0', 'name' => '{$studly}']);
    }

    public function boot(): void
    {
        \$this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        \$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        \$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        \$this->loadViewsFrom(__DIR__ . '/../resources/views', '{$slug}');
    }
}
PHP;
    }

    private function hookStub(string $studly, string $hook): string
    {
        $lower = strtolower($hook);
        return <<<PHP
<?php

namespace Plugins\\{$studly}\\Hooks;

class {$hook}Hook
{
    public function handle(): void
    {
        \\Log::info('Plugin {$studly}: {$lower}ed.');
    }
}
PHP;
    }
}
