<?php

namespace App\Filament\Widgets;

use App\Services\DashboardMetricsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Gate;

class MaintenanceOverviewChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected ?string $pollingInterval = '120s';

    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return Gate::allows('maintenance.view');
    }

    public function getHeading(): ?string
    {
        return 'Status Pemeliharaan';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $values = app(DashboardMetricsService::class)->maintenanceOverview(auth()->user(), $this->pageFilters['property_id'] ?? null);

        return ['labels' => array_keys($values), 'datasets' => [[
            'data' => array_values($values),
            'backgroundColor' => ['#f59e0b', '#2563eb', '#7c3aed', '#059669'],
        ]]];
    }
}
