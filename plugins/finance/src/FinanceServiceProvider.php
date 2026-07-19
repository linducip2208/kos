<?php

namespace Plugins\Finance;

use App\Core\Plugin\PluginManager;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        PluginManager::register('finance', [
            'version' => '1.0.0',
            'name'    => 'Finance Plugin',
        ]);

        $configPath = __DIR__ . '/../config/finance.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'finance');
        }
    }

    public function boot(): void
    {
        $apiRoutes  = __DIR__ . '/../routes/api.php';
        $webRoutes  = __DIR__ . '/../routes/web.php';
        $migrations = __DIR__ . '/../database/migrations';
        $views      = __DIR__ . '/../resources/views';

        if (file_exists($apiRoutes))   $this->loadRoutesFrom($apiRoutes);
        if (file_exists($webRoutes))   $this->loadRoutesFrom($webRoutes);
        if (is_dir($migrations))       $this->loadMigrationsFrom($migrations);
        if (is_dir($views))            $this->loadViewsFrom($views, 'finance');
    }
}
