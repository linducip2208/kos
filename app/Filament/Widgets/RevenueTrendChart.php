<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
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
        $labels = [];
        $billed = [];
        $collected = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            $billed[] = (float) Invoice::query()->whereYear('due_date', $date->year)->whereMonth('due_date', $date->month)->sum('total');
            $collected[] = (float) Invoice::query()->whereYear('due_date', $date->year)->whereMonth('due_date', $date->month)->withSum(['verifiedPayments as collected_sum' => fn ($q) => $q->whereYear('paid_at', $date->year)->whereMonth('paid_at', $date->month)], 'amount')->get()->sum('collected_sum');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Ditagihkan', 'data' => $billed, 'borderColor' => '#2563eb', 'backgroundColor' => 'rgba(37,99,235,.12)', 'fill' => true, 'tension' => .35],
                ['label' => 'Kas diterima', 'data' => $collected, 'borderColor' => '#059669', 'backgroundColor' => 'rgba(5,150,105,.08)', 'fill' => true, 'tension' => .35],
            ],
        ];
    }
}
