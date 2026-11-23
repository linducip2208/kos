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

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Gate::allows('booking.view');
    }

    public function getHeading(): ?string
    {
        return 'Funnel Booking';
    }

    public function getDescription(): ?string
    {
        return 'Perkembangan prospek booking dari lead hingga check-in.';
    }

    protected function getOptions(): ?array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['padding' => 12, 'cornerRadius' => 10],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'grid' => ['color' => 'rgba(100, 116, 139, 0.12)'],
                    'border' => ['display' => false],
                ],
                'y' => ['grid' => ['display' => false]],
            ],
        ];
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
