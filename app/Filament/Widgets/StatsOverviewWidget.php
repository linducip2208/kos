<?php

namespace App\Filament\Widgets;

use App\Services\DashboardMetricsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = 'Ringkasan Operasional & Keuangan';

    protected ?string $description = 'Indikator utama properti pada periode berjalan.';

    /**
     * Grid responsif: 1 kolom di mobile, 2 di tablet,
     * 3–4 di desktop, 5 di layar sangat lebar.
     */
    protected function getColumns(): int|array|null
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 3,
            'xl' => 4,
            '2xl' => 5,
        ];
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $filters = $this->pageFilters ?? [];
        $period = $filters['period'] ?? 'month';
        [$from, $to] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfDay()],
            'custom' => [Carbon::parse($filters['from'] ?? now()->startOfMonth()), Carbon::parse($filters['to'] ?? now()->endOfDay())->endOfDay()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
        $metrics = app(DashboardMetricsService::class)->for($user, $from, $to, isset($filters['property_id']) ? (int) $filters['property_id'] : null);
        $money = fn (float $value): string => 'Rp '.number_format($value, 0, ',', '.');
        $financeVisible = in_array($user->role, ['super_admin', 'owner', 'finance', 'cashier', 'auditor'], true);

        $stats = [
            Stat::make('Total Kamar', $metrics['total_rooms'])
                ->description($metrics['available_rooms'].' tersedia • '.$metrics['occupied_rooms'].' terisi')
                ->icon('heroicon-m-home')->color('primary'),
            Stat::make('Tingkat Hunian', $metrics['occupancy_rate'].'%')
                ->description('Kamar aktif yang sedang dihuni')
                ->icon('heroicon-m-chart-bar')
                ->color($metrics['occupancy_rate'] >= 80 ? 'success' : ($metrics['occupancy_rate'] >= 50 ? 'warning' : 'danger')),
            Stat::make('Kamar Maintenance', $metrics['maintenance_rooms'])
                ->description($metrics['open_maintenance'].' pekerjaan terbuka')
                ->icon('heroicon-m-wrench-screwdriver')->color($metrics['maintenance_rooms'] ? 'warning' : 'success'),
            Stat::make('Penyewa Aktif', $metrics['active_tenants'])
                ->description('Kontrak aktif / segera berakhir')
                ->icon('heroicon-m-users')->color('info'),
            Stat::make('Kontrak ≤ 30 Hari', $metrics['expiring_leases'])
                ->description('Perlu follow-up perpanjangan')
                ->icon('heroicon-m-calendar-days')->color($metrics['expiring_leases'] ? 'warning' : 'success'),
            Stat::make('Booking Menunggu', $metrics['pending_bookings'])
                ->description('Prospek perlu respons')
                ->icon('heroicon-m-clock')->color($metrics['pending_bookings'] ? 'warning' : 'success'),
        ];

        if ($financeVisible) {
            array_splice($stats, 2, 0, [
                Stat::make('Ditagihkan', $money($metrics['billed'] ?? 0))->description('Nilai invoice periode berjalan')->icon('heroicon-m-document-text')->color('info'),
                Stat::make('Kas Diterima', $money($metrics['collected'] ?? 0))->description('Pembayaran terverifikasi')->icon('heroicon-m-arrow-down-circle')->color('success'),
                Stat::make('Piutang Berjalan', $money($metrics['receivable'] ?? 0))->description(($metrics['overdue_invoices'] ?? 0).' invoice overdue')->icon('heroicon-m-exclamation-triangle')->color(($metrics['overdue_invoices'] ?? 0) ? 'danger' : 'success'),
                Stat::make('Pengeluaran', $money($metrics['expenses'] ?? 0))->description('Pada periode berjalan')->icon('heroicon-m-arrow-trending-down')->color('warning'),
            ]);
        }

        return $stats;
    }
}
