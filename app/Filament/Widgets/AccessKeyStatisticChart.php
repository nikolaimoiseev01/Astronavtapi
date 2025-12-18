<?php

namespace App\Filament\Widgets;

use App\Models\AccessKey;
use App\Models\AccessKeyStatistic;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class AccessKeyStatisticChart extends ChartWidget
{
    use HasFiltersSchema;

    protected ?string $heading = 'Использование ключей по часам';
    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'today';

    protected ?string $maxHeight = '400px';

    private function colorFromId(int $id): string
    {
        return '#' . substr(md5((string)$id), 0, 6);
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate')
                ->default(now()->subDays(30)),
            DatePicker::make('endDate')
                ->default(now()),
            Select::make('byWhatFilter')
                ->options([
                    'perHour' => 'Per hour',
                    'perDay' => 'Per day'
                ])
                ->default('perHour')
        ]);
    }

    protected function getData(): array
    {

        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $start = $startDate
            ? Carbon::parse($startDate)->startOfDay()
            : now()->subDay()->startOfHour();

        $end = $endDate
            ? Carbon::parse($endDate)->endOfDay()
            : now()->endOfHour();
        $byWhat = $this->filters['byWhatFilter'] ?? 'perHour';


        $accessKeys = AccessKey::all();

        $labels = [];
        $datasets = [];

        foreach ($accessKeys as $accessKey) {
            $trendQuery = Trend::query(
                AccessKeyStatistic::where('access_key_id', $accessKey->id)
            )
                ->between(start: $start, end: $end);

            $trend = match ($byWhat) {
                'perDay'  => $trendQuery->perDay()->count(),
                'perHour' => $trendQuery->perHour()->count(),
            };


            // labels берём один раз
            if (empty($labels)) {
                $labels = $trend->map(fn(TrendValue $value) => $value->date
                )->toArray();
            }

            $datasets[] = [
                'label' => $accessKey->name,
                'borderColor' => $this->colorFromId($accessKey->id),
                'data' => $trend->map(fn(TrendValue $value) => $value->aggregate
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
