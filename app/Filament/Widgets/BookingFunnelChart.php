<?php

namespace App\Filament\Widgets;

use App\Services\DashboardMetricsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Gate;

class BookingFunnelChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected ?string $pollingInterval = '120s';

    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return Gate::allows('booking.view');
    }

    public function getHeading(): ?string
    {
        return 'Funnel Booking';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $values = app(DashboardMetricsService::class)->bookingFunnel(auth()->user(), $this->pageFilters['property_id'] ?? null);

        return ['labels' => array_keys($values), 'datasets' => [[
            'label' => 'Prospek', 'data' => array_values($values),
            'backgroundColor' => '#4f46e5', 'borderRadius' => 8,
        ]]];
    }
}
