<?php

namespace Tests\Feature\Filament;

use App\Filament\Widgets\BookingFunnelChart;
use App\Filament\Widgets\DashboardActionCenterWidget;
use App\Filament\Widgets\MaintenanceOverviewChart;
use App\Filament\Widgets\ReceivableAgingChart;
use App\Filament\Widgets\RecentPaymentsWidget;
use App\Filament\Widgets\RevenueTrendChart;
use App\Filament\Widgets\RoomStatusWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardRedesignTest extends TestCase
{
    public function test_owner_dashboard_renders_premium_header_and_filters(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner', 'is_active' => true]));

        $response = $this->get('/admin');

        $response->assertOk();
        // Greeting dinamis WIB + nama pengguna.
        $response->assertSee('Selamat', false);
        // Tanggal Indonesia + ringkasan.
        $response->assertSee('Ringkasan operasional dan keuangan', false);
        // Filter properti & periode tetap ada.
        $response->assertSee('Properti', false);
        $response->assertSee('Periode', false);
    }

    public function test_owner_dashboard_widgets_render_their_sections(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner', 'is_active' => true]));

        Livewire::test(StatsOverviewWidget::class)
            ->assertSee('Ringkasan Operasional', false)
            ->assertSee('Total Kamar', false);

        Livewire::test(DashboardActionCenterWidget::class)
            ->assertSee('Pusat Tindakan', false);

        Livewire::test(RevenueTrendChart::class)
            ->assertSee('Tagihan vs Kas Diterima', false);

        Livewire::test(ReceivableAgingChart::class)
            ->assertSee('Umur Piutang', false);

        Livewire::test(BookingFunnelChart::class)
            ->assertSee('Funnel Booking', false);

        Livewire::test(MaintenanceOverviewChart::class)
            ->assertSee('Status Pemeliharaan', false);

        Livewire::test(RoomStatusWidget::class)
            ->assertSee('Status Kamar', false);

        Livewire::test(RecentPaymentsWidget::class)
            ->assertSee('Tagihan Terbaru', false);
    }

    public function test_role_based_widget_visibility_is_preserved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'maintenance', 'is_active' => true]));

        $this->assertFalse(RevenueTrendChart::canView());
        $this->assertFalse(ReceivableAgingChart::canView());
        $this->assertTrue(MaintenanceOverviewChart::canView());
        $this->assertTrue(DashboardActionCenterWidget::canView());

        $this->get('/admin')->assertOk();
    }

    public function test_dashboard_widget_sort_order_has_no_conflicts(): void
    {
        $sorts = [
            StatsOverviewWidget::getSort(),
            DashboardActionCenterWidget::getSort(),
            RevenueTrendChart::getSort(),
            ReceivableAgingChart::getSort(),
            BookingFunnelChart::getSort(),
            MaintenanceOverviewChart::getSort(),
            RoomStatusWidget::getSort(),
            RecentPaymentsWidget::getSort(),
        ];

        $this->assertSame(count($sorts), count(array_unique($sorts)), 'Sort order widget dashboard harus unik.');

        $ordered = $sorts;
        sort($ordered);
        $this->assertSame(
            [
                StatsOverviewWidget::getSort(),
                DashboardActionCenterWidget::getSort(),
                RevenueTrendChart::getSort(),
                ReceivableAgingChart::getSort(),
                BookingFunnelChart::getSort(),
                MaintenanceOverviewChart::getSort(),
                RoomStatusWidget::getSort(),
                RecentPaymentsWidget::getSort(),
            ],
            $ordered,
            'Urutan widget harus mengikuti hierarki section dashboard.'
        );
    }
}
