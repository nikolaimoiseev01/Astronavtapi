<?php

namespace App\Filament\Pages;

use App\Models\AccessKey;
use App\Models\AccessKeyStatistic;
use App\Models\CalculatorMeta\PbCity;
use App\Services\NatalCalculator\NatalService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TestCalculator extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    protected string $view = 'volt-livewire::filament.pages.test-calculator';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Тестовый рассчет';

    protected static ?int $navigationSort = 5;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    DatePicker::make('date')
                        ->label('Дата рождения')
                        ->format('Y-m-d')
                        ->required(),
                    TimePicker::make('time')
                        ->label('Время рождения')
                        ->seconds(false)
                        ->format('H:i')
                        ->required(),

                    Select::make('city_id')
                        ->label('Город')
                        ->searchable()
                        ->required()
                        ->getSearchResultsUsing(function (string $search) {
                            return PbCity::query()
                                ->where(function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                                        ->orWhere('english', 'like', "%{$search}%");
                                })
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn ($city) => [
                                    $city->id => $city->label,
                                ])
                                ->toArray();
                        })
                        ->getOptionLabelUsing(fn ($value): ?string => PbCity::find($value)?->label)
                        ->placeholder('Начните вводить город')
                        ->preload(false)
                ])->columns(3)

            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $accessKey = AccessKey::first();
        $stat = AccessKeyStatistic::create([
            'access_key_id' => $accessKey['id'],
            'status' => 'pending',
        ]);

        $result = app(NatalService::class)->calculate(
            $data['date'],
            $data['time'],
            PbCity::findOrFail($data['city_id'])->tz
        );

        $stat->update([
            'status' => 'success'
        ]);

        dd($result);
    }
}
