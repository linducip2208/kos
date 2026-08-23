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
use Illuminate\Support\Facades\Gate;
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

        $this->registerPermissionGates();

        PluginLoader::bootActivePlugins();

        app('theme.manager')->bootActiveThemes();
    }

    /**
     * Registrasi Laravel Gate dari matriks permission granular.
     * super_admin & owner bypass semua gate.
     */
    protected function registerPermissionGates(): void
    {
        Gate::before(function (\App\Models\User $user, string $ability) {
            if ($user->isSuperAdmin() || $user->isOwner()) {
                return true;
            }

            return null;
        });

        foreach (\App\Support\Permissions::PERMISSIONS as $permission) {
            Gate::define($permission, function (\App\Models\User $user) use ($permission) {
                return in_array($permission, \App\Support\Permissions::permissionsFor($user->role), true);
            });
        }
    }
}
