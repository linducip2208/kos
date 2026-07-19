<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRooms     = Room::where('is_active', true)->count();
        $occupiedRooms  = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();
        $occupancyRate  = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        $revenueThisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $overdueTotal = Invoice::whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<', now())
            ->count();

        $expiringSoon = Lease::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->count();

        return [
            Stat::make('Total Kamar', $totalRooms)
                ->description("{$availableRooms} tersedia • {$occupiedRooms} terisi")
                ->descriptionIcon('heroicon-m-home')
                ->color('primary'),

            Stat::make('Occupancy Rate', $occupancyRate . '%')
                ->description('Tingkat hunian')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger')),

            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->description('Tagihan lunas bulan ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Tunggakan', $overdueTotal)
                ->description('Tagihan melewati jatuh tempo')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($overdueTotal > 0 ? 'danger' : 'success'),

            Stat::make('Kontrak Hampir Berakhir', $expiringSoon)
                ->description('Dalam 30 hari ke depan')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($expiringSoon > 0 ? 'warning' : 'success'),
        ];
    }
}
