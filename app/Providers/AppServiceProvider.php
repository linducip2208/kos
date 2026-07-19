<?php

namespace App\Providers;

use App\Core\License\LicenseService;
use App\Core\Plugin\PluginLoader;
use App\Core\Plugin\PluginManager;
use App\Core\Theme\ThemeManager;
use App\Models\Invoice;
use App\Models\Lease;
use App\Observers\InvoiceObserver;
use App\Observers\LeaseObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LicenseService::class);

        $this->app->singleton('plugin.manager', fn ($app) =>
            new PluginManager($app->make(LicenseService::class))
        );

        $this->app->singleton('theme.manager', fn () => new ThemeManager());

        $this->app->singleton(\App\Services\PaymentGatewayService::class);
    }

    public function boot(): void
    {
        Lease::observe(LeaseObserver::class);
        Invoice::observe(InvoiceObserver::class);

        PluginLoader::bootActivePlugins();

        app('theme.manager')->bootActiveThemes();
    }
}
