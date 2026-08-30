<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\BookingFunnelChart;
use App\Filament\Widgets\DashboardActionCenterWidget;
use App\Filament\Widgets\MaintenanceOverviewChart;
use App\Filament\Widgets\PendingVerificationsWidget;
use App\Filament\Widgets\ReceivableAgingChart;
use App\Filament\Widgets\RecentPaymentsWidget;
use App\Filament\Widgets\RevenueTrendChart;
use App\Filament\Widgets\RoomStatusWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Support\NavigationGroups;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors(['primary' => Color::Blue])
            ->brandName(setting('app_name', 'Kos Kosan Pro'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15.5rem')
            ->collapsedSidebarWidth('4rem')
            ->topbar(true)
            ->navigationGroups([
                NavigationGroup::make(NavigationGroups::OPERATIONAL)->collapsed(false),
                NavigationGroup::make(NavigationGroups::TENANCY)->collapsed(false),
                NavigationGroup::make(NavigationGroups::FINANCE)->collapsed(false),
                NavigationGroup::make(NavigationGroups::BOOKINGS)->collapsed(false),
                NavigationGroup::make(NavigationGroups::REPORTS)->collapsed(true),
                NavigationGroup::make(NavigationGroups::SETTINGS)->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                StatsOverviewWidget::class,
                DashboardActionCenterWidget::class,
                RevenueTrendChart::class,
                ReceivableAgingChart::class,
                BookingFunnelChart::class,
                MaintenanceOverviewChart::class,
                RoomStatusWidget::class,
                RecentPaymentsWidget::class,
                PendingVerificationsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
