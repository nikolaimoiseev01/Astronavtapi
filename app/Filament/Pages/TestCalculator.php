<?php

namespace App\Filament\Pages;

use App\Models\AccessKey;
use App\Models\AccessKeyStatistic;
use App\Models\CalculatorMeta\Hexagram;
use App\Models\CalculatorMeta\PbCity;
use App\Services\NatalCalculator\NatalService;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Тестовый рассчет';

    protected static ?int $navigationSort = 5;

    public $result;

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
                        ->format('Y-m-d'),
                    TimePicker::make('time')
                        ->label('Время рождения')
                        ->seconds(false)
                        ->format('H:i'),
                    Select::make('city_id')
                        ->label('Город')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            $result = PbCity::query()
                                ->where(function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                                        ->orWhere('english', 'like', "%{$search}%");
                                })
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn($city) => [
                                    $city->id => $city->label,
                                ])
                                ->toArray();
                            return $result;
                        })
                        ->getOptionLabelUsing(fn($value): ?string => PbCity::find($value)?->label)
                        ->placeholder('Начните вводить город')
                        ->preload(false),
                    Checkbox::make('ddResult')
                        ->default(true)
                        ->label('Со строками'),
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

        $this->result = [
            'celectials' => [
                'Sun' => [
                    'black ephemeris' => 161.7982211,
                    'black ephemeris next day' => 162.7674064,
                    'difference' => 0.9691852999999924,
                    'velocity' => 1.1217422453703615e-5,
                    'distortion' => 0.44219079312499654,
                    'black coordinate' => 162.240411893125,
                    'black hexagram' => '64.1',
                    'black perturbation' => '',
                    'black result perturbation' => 'DET',
                    'black opposite hexagram' => [47],
                    'black gate' => 3,
                    'red date' => [
                        'red coordinate' => 74.24041189312501,
                        'red hexagram' => '35.4',
                        'red opposite hexagram' => [36],
                        'red perturbation' => '',
                        'red result perturbation' => '',
                        'design date_time' => '04.06.2004 13:11:47',
                        'design date' => '04.06.2004',
                        'design time' => '13:11:47',
                    ],
                    'red coordinate' => 74.24041189312501,
                    'red hexagram' => '35.4',
                    'red opposite hexagram' => [36],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'design date_time' => '04.06.2004 13:11:47',
                    'design date' => '04.06.2004',
                    'design time' => '13:11:47',
                    'black hexagram start' => '161.3750',
                    'black hexagram difference' => 0.8654118931250139,
                    'black line' => 1,
                    'black line start' => 0,
                    'black color coordinate' => 0.8654118931250139,
                    'black color' => 6,
                    'black color start' => 0.78125,
                    'black tone coordinate' => 0.08416189312501388,
                    'black tone' => 4,
                    'black tone start' => 0.07812501,
                    'black base coordinate' => 0.006036883125013881,
                    'black base' => 2,
                    'black arrow' => 'right',
                    'red hexagram start' => '71.3750',
                    'red hexagram difference' => 2.865411893125014,
                    'red line' => 4,
                    'red line start' => 2.8125,
                    'red color coordinate' => 0.052911893125013876,
                    'red color' => 1,
                    'red color start' => 0,
                    'red tone coordinate' => 0.052911893125013876,
                    'red tone' => 3,
                    'red tone start' => 0.05208333999999999,
                    'red base coordinate' => 0.0008285531250138839,
                    'red base' => 1,
                    'red arrow' => 'left',
                ],

                'Earth' => [
                    'black coordinate' => 342.24041189312504,
                    'black hexagram' => '63.1',
                    'black opposite hexagram' => [4],
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black gate' => 1,
                    'red hexagram' => '5.4',
                    'red opposite hexagram' => [15],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 4,
                ],

                'trueNode' => [
                    'black ephemeris' => 33.3422882,
                    'black ephemeris next day' => 33.3607208,
                    'difference' => 0.01843260000000413,
                    'velocity' => 2.1334027777782557e-7,
                    'distortion' => 0.008409873750001884,
                    'black coordinate' => 33.35069807375,
                    'black hexagram' => '27.2',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [50],
                    'black gate' => 1,
                    'red coordinate' => ' 40.8872909',
                    'red hexagram' => '24.4',
                    'red opposite hexagram' => [61],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 1,
                    'black hexagram start' => '32.0000',
                    'black hexagram difference' => 1.350698073750003,
                    'black line' => 2,
                    'black line start' => 0.9375,
                    'black color coordinate' => 0.41319807375000295,
                    'black color' => 3,
                    'black color start' => 0.3125,
                    'black tone coordinate' => 0.10069807375000295,
                    'black tone' => 4,
                    'black tone start' => 0.07812501,
                    'black base coordinate' => 0.022573063750002953,
                    'black base' => 5,
                    'black arrow' => 'right',
                    'red hexagram start' => '37.6250',
                    'red hexagram difference' => 3.2622909000000035,
                    'red line' => 4,
                    'red line start' => 2.8125,
                    'red color coordinate' => 0.44979090000000355,
                    'red color' => 3,
                    'red color start' => 0.3125,
                    'red tone coordinate' => 0.13729090000000355,
                    'red tone' => 6,
                    'red tone start' => 0.13020835,
                    'red base coordinate' => 0.007082550000003546,
                    'red base' => 2,
                    'red arrow' => 'right',
                ],

                'SouthNode' => [
                    'black coordinate' => 213.35069807375,
                    'black hexagram' => '28.2',
                    'black opposite hexagram' => [38],
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black gate' => 3,
                    'red coordinate' => 220.8872909,
                    'red hexagram' => '44.4',
                    'red opposite hexagram' => [26],
                    'red perturbation' => '',
                    'red result perturbation' => 'EX',
                    'red gate' => 3,
                    'through red channel' => [
                        ['channel' => '44 - 26', 'impact planet' => 'Pluto'],
                    ],
                    'through cross red channel' => [
                        ['channel' => '44 - 26', 'impact planet' => 'Pluto'],
                    ],
                    'black hexagram start' => '212.0000',
                    'black hexagram difference' => 1.35069807375001,
                    'black line' => 2,
                    'black line start' => 0.9375,
                    'black color coordinate' => 0.41319807375001005,
                    'black color' => 3,
                    'black color start' => 0.3125,
                    'black tone coordinate' => 0.10069807375001005,
                    'black tone' => 4,
                    'black tone start' => 0.07812501,
                    'black base coordinate' => 0.02257306375001006,
                    'black base' => 5,
                    'black arrow' => 'right',
                    'red hexagram start' => '217.6250',
                    'red hexagram difference' => 3.2622909000000107,
                    'red line' => 4,
                    'red line start' => 2.8125,
                    'red color coordinate' => 0.44979090000001065,
                    'red color' => 3,
                    'red color start' => 0.3125,
                    'red tone coordinate' => 0.13729090000001065,
                    'red tone' => 6,
                    'red tone start' => 0.13020835,
                    'red base coordinate' => 0.007082550000010651,
                    'red base' => 2,
                    'red arrow' => 'right',
                ],

                'Moon' => [
                    'black ephemeris' => 42.4479675,
                    'black ephemeris next day' => 54.7378972,
                    'difference' => 12.289929700000002,
                    'velocity' => 0.00014224455671296298,
                    'distortion' => 5.607280425625,
                    'black coordinate' => 48.055247925625,
                    'black hexagram' => '2.6',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [14],
                    'black gate' => 2,
                    'red coordinate' => ' 273.7862219',
                    'red hexagram' => '10.6',
                    'red opposite hexagram' => [20, 34, 57],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 4,
                ],

                'Mercury' => [
                    'black ephemeris' => 145.8868288,
                    'black ephemeris next day' => 146.1674881,
                    'difference' => 0.2806593000000248,
                    'velocity' => 3.248371527778065e-6,
                    'distortion' => 0.1280508056250113,
                    'black coordinate' => 146.014879605625,
                    'black hexagram' => '29.2',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [46],
                    'black gate' => 3,
                    'red coordinate' => ' 58.1558697',
                    'red hexagram' => '8.4',
                    'red opposite hexagram' => [1],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 2,
                ],

                'Venus' => [
                    'black ephemeris' => 116.8792614,
                    'black ephemeris next day' => 117.9405996,
                    'difference' => 1.0613381999999945,
                    'velocity' => 1.228400694444438e-5,
                    'distortion' => 0.48423555374999744,
                    'black coordinate' => 117.36349695375,
                    'black hexagram' => '56.2',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [11],
                    'black gate' => 2,
                    'red coordinate' => ' 80.2516922',
                    'red hexagram' => '45.4',
                    'red opposite hexagram' => [21],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 2,
                ],

                'Mars' => [
                    'black ephemeris' => 165.6250534,
                    'black ephemeris next day' => 166.263894,
                    'difference' => 0.6388405999999804,
                    'velocity' => 7.3939884259257e-6,
                    'distortion' => 0.2914710237499911,
                    'black coordinate' => 165.91652442375,
                    'black hexagram' => '64.5',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [47],
                    'black gate' => 3,
                    'red coordinate' => ' 107.8237872',
                    'red hexagram' => '53.3',
                    'red opposite hexagram' => [42],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 2,
                ],

                'Jupiter' => [
                    'black ephemeris' => 175.4492793,
                    'black ephemeris next day' => 175.6621879,
                    'difference' => 0.21290859999999157,
                    'velocity' => 2.4642199074073097e-6,
                    'distortion' => 0.09713954874999615,
                    'black coordinate' => 175.54641884875,
                    'black hexagram' => '6.4',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [59],
                    'black gate' => 3,
                    'red coordinate' => ' 160.2713453',
                    'red hexagram' => '40.5',
                    'red opposite hexagram' => [37],
                    'red perturbation' => '',
                    'red result perturbation' => 'EX',
                    'red gate' => 3,
                    'through red channel' => [
                        ['channel' => '40 - 37', 'impact planet' => 'Uranus'],
                    ],
                ],

                'Saturn' => [
                    'black ephemeris' => 113.7238362,
                    'black ephemeris next day' => 113.8242774,
                    'difference' => 0.10044120000000589,
                    'velocity' => 1.1625138888889571e-6,
                    'distortion' => 0.04582629750000269,
                    'black coordinate' => 113.7696624975,
                    'black hexagram' => '62.4',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [17],
                    'black gate' => 2,
                    'red coordinate' => ' 102.5126371',
                    'red hexagram' => '39.4',
                    'red opposite hexagram' => [55],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 2,
                    'through cross red channel' => [
                        ['channel' => '39 - 55', 'impact planet' => 'Uranus'],
                    ],
                    'return day time' => '08.07.2034 13:09:55',
                    'Chiron' => [
                        'data' => [
                            'difference' => -0.027214499999956843,
                            'velocity' => -3.149826388883894e-7,
                            'distortion' => -0,
                            'coordinate' => 290.8248215,
                            'zodiac_sigh' => 'Capricorn',
                        ],
                    ],
                ],

                'Uranus' => [
                    'black ephemeris' => 334.5274839,
                    'black ephemeris next day' => 334.4880521,
                    'difference' => -0.03943179999998847,
                    'velocity' => -4.563865740739406e-7,
                    'distortion' => -0.01799075874999474,
                    'black coordinate' => 334.50949314125,
                    'black hexagram' => '55.5',
                    'black perturbation' => 'EX',
                    'black result perturbation' => 'EX',
                    'black opposite hexagram' => [39],
                    'black gate' => 1,
                    'red coordinate' => ' 336.7806277',
                    'red hexagram' => '37.2',
                    'red opposite hexagram' => [40],
                    'red perturbation' => '',
                    'red result perturbation' => 'EX',
                    'red gate' => 1,
                    'through red channel' => [
                        ['channel' => '37 - 40', 'impact planet' => 'Jupiter'],
                    ],
                    'through cross black channel' => [
                        ['channel' => '55 - 39', 'impact planet' => 'Saturn'],
                    ],
                ],

                'Neptune' => [
                    'black ephemeris' => 313.2482041,
                    'black ephemeris next day' => 313.2252929,
                    'difference' => -0.022911200000010012,
                    'velocity' => -2.6517592592604184e-7,
                    'distortion' => -0.01045323500000457,
                    'black coordinate' => 313.237750865,
                    'black hexagram' => '19.6',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [49],
                    'black gate' => 4,
                    'red coordinate' => ' 315.3069733',
                    'red hexagram' => '13.3',
                    'red opposite hexagram' => [33],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 1,
                ],

                'Pluto' => [
                    'black ephemeris' => 259.5482525,
                    'black ephemeris next day' => 259.550794,
                    'difference' => 0.0025415000000066357,
                    'velocity' => 2.941550925933606e-8,
                    'distortion' => 0.0011595593750030274,
                    'black coordinate' => 259.549412059375,
                    'black hexagram' => '26.3',
                    'black perturbation' => '',
                    'black result perturbation' => '',
                    'black opposite hexagram' => [44],
                    'black gate' => 4,
                    'red coordinate' => ' 261.0890887',
                    'red hexagram' => '26.5',
                    'red opposite hexagram' => [44],
                    'red perturbation' => '',
                    'red result perturbation' => '',
                    'red gate' => 4,
                    'through cross black channel' => [
                        ['channel' => '26 - 44', 'impact planet' => 'SouthNode'],
                    ],
                    'through red channel' => [
                        ['channel' => '26 - 44', 'impact planet' => 'SouthNode'],
                    ],
                ],
            ],

            'channels' => [
                '26 - 44' => ['def_left' => 'EGO', 'def_right' => 'INTUITION'],
                '37 - 40' => ['def_left' => 'EMOTIONAL', 'def_right' => 'EGO'],
                '39 - 55' => ['def_left' => 'DRIVE', 'def_right' => 'EMOTIONAL'],
            ],

            'centers' => [
                'IDN' => 'O',
                'FRC' => 'O',
                'MND' => 'O',
                'EMO' => 'D',
                'EXP' => 'O',
                'INT' => 'D',
                'DRV' => 'D',
                'EGO' => 'D',
                'INS' => 'O',
            ],

            'type' => 'COORDINATOR',
            'determinant' => 'ОДИНАРНАЯ',
            'incarnation_cross' => 'cross: 64/63|35/5; quarter: Duality; description: The Right-Angle Cross of Consciousness 3',

            'lines' => [
                'P' => ['6' => 2, '5' => 2, '4' => 2, '3' => 1, '2' => 4, '1' => 2],
                'D' => ['6' => 1, '5' => 2, '4' => 7, '3' => 2, '2' => 1, '1' => 0],
                'total' => ['6' => 3, '5' => 4, '4' => 9, '3' => 3, '2' => 5, '1' => 2],
            ],

            'personal_sun' => '3 QTR ',
            'sun_venus' => 44,
            'gates' => ['1' => 6, '2' => 7, '3' => 7, '4' => 5],
            'total_gates' => 24,


            'additional_properties' => [
                'brith_location_time_zone' => 'Europe/Moscow (+4)',
                'birth_datetime' => '2004-09-04 14:57:00',
                'birth_datetime_utc' => '2004-09-04 10:57:00',
                'birth_location' => 'Москва, ',
                'perspective' => 'Political/ Problems',
                'health' => 'Appetite',
                'sense' => 'Action/Outer Vision',
                'environment' => 'Kitchens',
                'digestion' => 'Consecutive',
                'strategy' => 'Wait for the Invitation',
                'motivation' => 'INNOCENCE',
                'variables' => 'RR-LR',
                'goal-shadow' => 'Satisfaction/Bitterness',
                'zodiac_sign' => 'Virgo',
                'inner_authority' => 'EMOTIONAL AUTHORITY',
            ],

            'some_additional_gates_circuit' => [
                'SOCIETAL' => ['0' => 27, '1' => 45, '2' => 40, '3' => 6, '4' => 44, '6' => 26, '7' => 19, '8' => 37],
                'INDIVIDUAL' => [24, 2, 8, 39, 28, 10, 55],
                'COMMUNAL' => ['0' => 35, '1' => 53, '2' => 62, '3' => 56, '4' => 29, '6' => 64, '7' => 5, '8' => 13, '9' => 63],
            ],
        ];

        $this->result = app(NatalService::class)->calculate(
            $data['date'],
            $data['time'],
            PbCity::findOrFail($data['city_id'])
        );

        $hexagramsByValue = Hexagram::query()
            ->get(['hexagram', 'yin_yang_balance', 'role', 'mind', 'decision'])
            ->keyBy('hexagram')
            ->toArray();

        $this->calculateSomeAdditionalData($hexagramsByValue);
        $this->someAditionalGatesDataToStandart();

        $this->calculateSomeAdditionalDataShares($hexagramsByValue);

        $stat->update([
            'status' => 'success'
        ]);

        if(!$data['ddResult']) {
            dd($this->result);
        }
    }

    private function someAditionalGatesDataToStandart() {
        $initialValues = $this->result['some_additional_gates_circuit'];
        foreach ($this->result['some_additional_gates_circuit'] as $key => &$value) {
            $value = [];
            $value['values'] = $initialValues[$key];
        }
    }
    private function buildGroupedCounts($someAditionals): array
    {
        $rows = Hexagram::query()
            ->get($someAditionals);

        $result = [];

        foreach ($someAditionals as $field) {
            $result[$field] = $rows
                ->pluck($field)
                ->filter()               // на случай null
                ->countBy()
                ->toArray();
        }

        return $result;
    }

    private function calculateSomeAdditionalDataShares($hexagrams): void
    {
        $someAditionals = ['yin_yang_balance', 'mind', 'decision', 'circuit'];
        $hexagramsGateGroupsFromDB = $this->buildGroupedCounts($someAditionals);
        foreach ($someAditionals as $field) {
            $totalFields = $hexagramsGateGroupsFromDB[$field];
            foreach ($this->result["some_additional_gates_{$field}"] as $gate => &$group) {
                $count = count($group['values'] ?? []);
                $group['share'] = $count / $totalFields[$gate];
                $group["total of {$gate} in DB"] = $totalFields[$gate];
            }

            unset($group); // важно при foreach по ссылке
        }

    }

    private function calculateSomeAdditionalData($hexagrams)
    {
        $someAditionals = ['yin_yang_balance', 'mind', 'decision'];
        foreach ($someAditionals as $column) {
            foreach ($this->result['celectials'] as $planet => $celectial) {
                $hexagramValue = $celectial['black hexagram'];
                $yinYanBalanceValueFromDB = $hexagrams[$hexagramValue][$column];
                $this->result['some_additional_gates_' . $column][$yinYanBalanceValueFromDB]['values']['(Black sun) ' . $planet] = $hexagramValue;

                $hexagramValue = $celectial['red hexagram'];
                $yinYanBalanceValueFromDB = $hexagrams[$hexagramValue][$column];
                $this->result['some_additional_gates_' . $column][$yinYanBalanceValueFromDB]['values']['(Red sun) ' . $planet] = $hexagramValue;
            }
        }

        $hexagramValue = $this->result['celectials']['Sun']['black hexagram'];
        $this->result['some_additional_gates_role']['First role (Black hexagram)'] =
            [
                'hexagram' => $hexagramValue,
                'role' => $hexagrams[$hexagramValue]['role']
            ];
        $hexagramValue = $this->result['celectials']['Sun']['red hexagram'];
        $this->result['some_additional_gates_role']['Second role (Red hexagram)'] = [
            'hexagram' => $hexagramValue,
            'role' => $hexagrams[$hexagramValue]['role']
        ];;
    }
}
