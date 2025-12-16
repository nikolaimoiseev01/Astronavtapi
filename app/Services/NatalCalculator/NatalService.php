<?php

namespace App\Services\NatalCalculator;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NatalService
{
    public function calculate(
        string $date,
        string $time,
        string $timezone
    ): array {
        /** @var \PDO $pdo */
        $pdo = DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();

        /**
         * ===== ASTRO =====
         */
        $astro = new AstroService($date, $time, $pdo, $database, $timezone);
        $celectials = $astro->test();

        $gate = $astro->get_gate($celectials['Sun']['black hexagram']);
        $personalSun = $gate['quarter'] . ' QTR ';

        /**
         * ===== INCARNATION =====
         */
        $incarnation = new IncarnationService($pdo, $database);

        $channels = $incarnation->get_active_channels($celectials);
        $lines = $incarnation->get_lines_quantities($celectials);
        $incarnationCross = $incarnation->get_incarnation_cross($celectials);

        /**
         * ===== CHANNELS / CENTERS =====
         */
        $data = [];
        $data['celectials'] = $celectials;

        $centersDefined = [];
        $centersDefinedAll = [];

        foreach ($channels[0] as $value) {
            [$c1, $c2] = explode(' - ', $value);

            $bind = DB::table('channels')
                ->select('def_left', 'def_right')
                ->where('channel_1', $c1)
                ->where('channel_2', $c2)
                ->first();

            if (! $bind) {
                continue;
            }

            $data['channels'][$value] = (array) $bind;

            $centersDefined[$value] = $bind->def_left . ' - ' . $bind->def_right;
            $centersDefinedAll[] = $bind->def_left;
            $centersDefinedAll[] = $bind->def_right;
        }

        $centersDefinedAll = array_values(array_unique($centersDefinedAll));

        $centersShort = [
            'IDENTITY' => 'IDN',
            'FORCE' => 'FRC',
            'MIND' => 'MND',
            'EMOTIONAL' => 'EMO',
            'EXPRESSION' => 'EXP',
            'INTUITION' => 'INT',
            'DRIVE' => 'DRV',
            'EGO' => 'EGO',
            'INSPIRATION' => 'INS',
        ];

        foreach ($centersShort as $key => $short) {
            $data['centers'][$short] = in_array($key, $centersDefinedAll, true)
                ? 'D'
                : 'O';
        }

        /**
         * ===== TYPE / DETERMINANT =====
         */
        $data['type'] = $incarnation->get_type($centersDefined, $centersDefinedAll);

        $centersDefined = array_values(array_unique($centersDefined));

        $graf = empty($centersDefined)
            ? 0
            : $incarnation->obtain_determinancy(
                $incarnation->prepare_graph($centersDefined)
            );

        $determinants = [
            'NODEFINITION',
            'ОДИНАРНАЯ',
            'ДВОЙНАЯ',
            'ТРОЙНАЯ',
            'ЧЕТВЕРТИЧНАЯ',
        ];

        $data['determinant'] = $determinants[$graf] ?? 'NODEFINITION';
        $data['incarnation_cross'] = $incarnationCross;

        /**
         * ===== LINES / GATES =====
         */
        $data['lines'] = $lines;
        $data['personal_sun'] = $personalSun;

        $data['sun_venus'] = $incarnation->venus_sun_leash(
            $celectials['Sun']['black coordinate'],
            $celectials['Venus']['black coordinate']
        );

        $gates = array_merge(
            array_column($celectials, 'black gate'),
            array_column($celectials, 'red gate')
        );

        $data['gates'] = collect($gates)
            ->countBy()
            ->sortKeys()
            ->toArray();

        $additionalGates = $incarnation->fetch_gates($celectials);

        $data['total_gates'] = array_sum(
            array_map('count', $additionalGates)
        );

        $data['some_additional_gates_data'] = $additionalGates;

        /**
         * ===== TIME =====
         */
        $birthDateTime = Carbon::parse("$date $time", $timezone);
        $offset = $birthDateTime->getOffset() / 3600;
        $offset = $offset > 0 ? '+' . $offset : (string) $offset;

        $data['additional_properties'] = [
            'brith_location_time_zone' => "$timezone ($offset)",
            'birth_datetime' => $birthDateTime->toDateTimeString(),
            'birth_datetime_utc' => $birthDateTime
                ->clone()
                ->setTimezone('UTC')
                ->toDateTimeString(),
        ];

        return $data;
    }
}
