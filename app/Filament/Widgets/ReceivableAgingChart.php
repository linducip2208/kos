<?php

namespace App\Filament\Widgets;

use App\Services\DashboardMetricsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Gate;

class ReceivableAgingChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '120s';

    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return Gate::allows('finance.report');
    }

    public function getHeading(): ?string
    {
        return 'Umur Piutang';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $values = app(DashboardMetricsService::class)->receivableAging(auth()->user(), $this->pageFilters['property_id'] ?? null);

        return ['labels' => array_keys($values), 'datasets' => [[
            'label' => 'Belum Terbayar', 'data' => array_values($values),
            'backgroundColor' => ['#2563eb', '#f59e0b', '#f97316', '#ef4444', '#991b1b'],
        ]]];
    }
}
