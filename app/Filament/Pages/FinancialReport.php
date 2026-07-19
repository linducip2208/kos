<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use App\Models\Property;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialReport extends Page
{
    protected string $view = 'filament.pages.financial-report';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string { return __('navigation.group_reports'); }
    public static function getNavigationLabel(): string  { return 'Laporan Keuangan'; }

    public function getTitle(): string { return 'Laporan Keuangan'; }

    public function getRevenueByMonth(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $total = Invoice::where('status', 'paid')
                ->whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('total');
            $data[] = [
                'month' => $date->format('M Y'),
                'total' => $total,
            ];
        }
        return $data;
    }

    public function getOccupancyByProperty(): array
    {
        return Property::withCount([
            'rooms',
            'rooms as occupied_rooms_count' => fn ($q) => $q->where('status', 'occupied'),
        ])->get()->map(fn ($p) => [
            'name'     => $p->name,
            'total'    => $p->rooms_count,
            'occupied' => $p->occupied_rooms_count,
            'rate'     => $p->rooms_count > 0 ? round(($p->occupied_rooms_count / $p->rooms_count) * 100, 1) : 0,
        ])->toArray();
    }

    public function getOverdueSummary(): array
    {
        $overdue = Invoice::whereIn('status', ['sent', 'overdue'])->where('due_date', '<', now())->get();
        return [
            'count'  => $overdue->count(),
            'amount' => $overdue->sum('total'),
        ];
    }

    public function getStats(): array
    {
        $thisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('total');
        $lastMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->subMonth()->month)->whereYear('paid_at', now()->subMonth()->year)->sum('total');
        $ytd = Invoice::where('status', 'paid')->whereYear('paid_at', now()->year)->sum('total');

        return compact('thisMonth', 'lastMonth', 'ytd');
    }
}
