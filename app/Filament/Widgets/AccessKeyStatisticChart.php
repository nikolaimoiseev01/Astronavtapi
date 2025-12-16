<?php

namespace App\Filament\Widgets;

use App\Models\AccessKey;
use App\Models\AccessKeyStatistic;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AccessKeyStatisticChart extends ChartWidget
{
    protected ?string $heading = 'Использование ключей по часам';
    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';
    private function colorFromId(int $id): string
    {
        return '#' . substr(md5((string) $id), 0, 6);
    }

    protected function getData(): array
    {
        $start = now()->subDay()->startOfHour();
        $end   = now()->endOfHour();

        $accessKeys = AccessKey::all();

        $labels = [];
        $datasets = [];

        foreach ($accessKeys as $accessKey) {
            $trend = Trend::query(
                AccessKeyStatistic::where('access_key_id', $accessKey->id)
            )
                ->between(start: $start, end: $end)
                ->perHour()
                ->count();

            // labels берём один раз
            if (empty($labels)) {
                $labels = $trend->map(fn (TrendValue $value) =>
                $value->date
                )->toArray();
            }

            $datasets[] = [
                'label' => $accessKey->name,
                'borderColor' => $this->colorFromId($accessKey->id),
                'data' => $trend->map(fn (TrendValue $value) =>
                $value->aggregate
                )->toArray(),
                'fill' => false,
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
