<?php

namespace App\Filament\Widgets;

use App\Models\InvoicePayment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class RevenueTrendChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '120s';

    protected ?string $maxHeight = '320px';

    public static function canView(): bool
    {
        return Gate::allows('finance.report');
    }

    public function getHeading(): ?string
    {
        return 'Tagihan vs Kas Diterima';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        return Cache::remember('revenue-trend:6m', 300, function () {
            $labels = [];
            $keys = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->translatedFormat('M Y');
                $keys[] = $date->format('Y-m');
            }

            $from = now()->subMonths(5)->startOfMonth()->toDateString();
            $to = now()->endOfMonth()->toDateString();

            // 1 query agregat, bukan 12x load model.
            $billedRows = Invoice::query()
                ->whereBetween('due_date', [$from, $to])
                ->selectRaw("DATE_FORMAT(due_date, '%Y-%m') as ym, SUM(total) as total")
                ->groupBy('ym')->pluck('total', 'ym');

            $collectedRows = InvoicePayment::query()
                ->where('type', 'payment')->where('status', 'verified')
                ->whereBetween('paid_at', [$from, $to.' 23:59:59'])
                ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
                ->groupBy('ym')->pluck('total', 'ym');

            $billed = array_map(fn ($k) => (float) ($billedRows[$k] ?? 0), $keys);
            $collected = array_map(fn ($k) => (float) ($collectedRows[$k] ?? 0), $keys);

            return [
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Ditagihkan', 'data' => $billed, 'borderColor' => '#2563eb', 'backgroundColor' => 'rgba(37,99,235,.12)', 'fill' => true, 'tension' => .35],
                    ['label' => 'Kas diterima', 'data' => $collected, 'borderColor' => '#059669', 'backgroundColor' => 'rgba(5,150,105,.08)', 'fill' => true, 'tension' => .35],
                ],
            ];
        });
    }
}
