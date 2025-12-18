<?php

namespace App\Services\NatalCalculator;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NatalService
{
    public function calculate(
        string $date,
        string $time,
        $city
    ): array {
        /** @var \PDO $pdo */
        $pdo = DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();
        $timezone = $city->tz;

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

        $data['additional_properties']['birth_location'] =
            $city->name . ', ' . $city->countryRelation?->name;

        $data['additional_properties']['perspective'] = $this->getPerspective($data);
        $data['additional_properties']['health'] = $this->getHealth($data);
        $data['additional_properties']['sense'] = $this->getSense($data);
        $data['additional_properties']['environment'] = $this->getEnvironment($data);
        $data['additional_properties']['digestion'] = $this->getDigestion($data);
        $data['additional_properties']['strategy'] = $this->getStrategy($data);
        $data['additional_properties']['motivation'] = $this->getMotivation($data);
        $data['additional_properties']['variables'] = $this->getVariables($data);
        $data['additional_properties']['goal-shadow'] = $this->getGoalShadow($data);
        $data['additional_properties']['zodiac_sign'] = $this->getZodiacSign($data);
        $data['additional_properties']['inner_authority'] = $this->getInnerAuthority($data);

        return $data;
    }

    protected function getInnerAuthority(array $data): string
    {
        /* Приоритеты Центров (1-выше, 6-ниже):
            EMO – 1 EMOTIONAL AUTHORITY
            FRC - 2 SACRED AUTHORITY
            INT - 3 SPLENIC AUTHORITY
            EGO - 4 EGO AUTHORITY
            IDN - 5 SELF-PROJECTED AUTHORITY
            MND - 6 MENTAL AUTHORITY
            INS - 6 MENTAL AUTHORITY
         */
        //lunar
        if (! in_array('D', $data['centers'])) {
            return 'LUNAR';
        }
        if ($data['centers']['EMO'] === 'D') {
            return 'EMOTIONAL AUTHORITY';
        }
        if ($data['centers']['FRC'] === 'D') {
            return 'SACRED AUTHORITY';
        }
        if ($data['centers']['INT'] === 'D') {
            return 'SPLENIC AUTHORITY';
        }
        if ($data['centers']['EGO'] === 'D') {
            return 'EGO AUTHORITY';
        }
        if ($data['centers']['IDN'] === 'D') {
            return 'SELF-PROJECTED AUTHORITY';
        }
        if ($data['centers']['MND'] === 'D' || $data['centers']['INS'] === 'D') {
            return 'MENTAL AUTHORITY';
        }

        return '';
    }

    protected function getZodiacSign(array $data): string
    {
        $ephemeris = floatval($data['celectials']['Sun']['black ephemeris']);
        $item = DB::table('hexagrams')
            ->where('start', '<=', $ephemeris)
            ->where('end', '>=', $ephemeris)
            ->first();

        if ($item) {
            $item = json_decode(json_encode($item), true);
            return $item['zodiac_sign'];
        }

        return '';
    }

    protected function getGoalShadow(array $data): string
    {
        $goal_shadow = '';
        if (! empty($data['type'])) {
            $goal_shadow_names = [
                'OBSERVER' => 'Surprise/Disappointment',
                'BUILDER' => 'Satisfaction/Frustration',
                'SPECIALIST' => 'Satisfaction/Impatient',
                'INITIATOR' => 'Peace/Anger',
                'COORDINATOR' => 'Satisfaction/Bitterness',
            ];

            $goal_shadow = $goal_shadow_names[$data['type']];
        }

        return $goal_shadow;
    }

    protected function getVariables(array $data): string
    {
        $variables = '';
        if (
            ! empty($data['celectials']['Sun']['black arrow'])
            && ! empty($data['celectials']['trueNode']['black arrow'])
            && ! empty($data['celectials']['Sun']['red arrow'])
            && ! empty($data['celectials']['SouthNode']['red arrow'])

        ) {
            $directions = [
                'right' => 'R',
                'left' => 'L',
            ];

            $sun_black_arrow = $data['celectials']['Sun']['black arrow'];
            $true_node_black_arrow = $data['celectials']['trueNode']['black arrow'];
            $sun_red_arrow = $data['celectials']['Sun']['red arrow'];
            $SouthNode_red_arrow = $data['celectials']['SouthNode']['red arrow'];

            $variables = $directions[$sun_black_arrow]
                .$directions[$true_node_black_arrow]
                .'-'
                .$directions[$sun_red_arrow]
                .$directions[$SouthNode_red_arrow];
        }

        return $variables;
    }

    protected function getMotivation(array $data): string
    {
        $motivation = '';
        if (! empty($data['celectials']['Sun']['black color'])) {
            $motivation_names = [
                1 => 'FEAR',
                2 => 'HOPE',
                3 => 'DESIRE',
                4 => 'NEED',
                5 => 'GUILT',
                6 => 'INNOCENCE',
            ];

            $motivation = $motivation_names[$data['celectials']['Sun']['black color']];
        }

        return $motivation;
    }

    protected function getStrategy(array $data): string
    {
        $strategy = '';
        if (! empty($data['type'])) {
            $strategy_names = [
                'OBSERVER' => 'Wait a Lunar Cycle',
                'BUILDER' => 'Respond',
                'SPECIALIST' => 'Respond and inform',
                'INITIATOR' => 'Inform',
                'COORDINATOR' => 'Wait for the Invitation',
            ];

            $strategy = $strategy_names[$data['type']];
        }

        return $strategy;
    }

    protected function getDigestion(array $data): string
    {
        $digestion = '';
        if (! empty($data['celectials']['Sun']['red tone']) && ! empty($data['celectials']['Sun']['red color'])) {
            $ton = $data['celectials']['Sun']['red tone'];
            $color = $data['celectials']['Sun']['red color'];

            if ($color === 1 && in_array($ton, [1, 2, 3])) $digestion = 'Consecutive';
            if ($color === 1 && in_array($ton, [4, 5, 6])) $digestion = 'Alternating';
            if ($color === 2 && in_array($ton, [1, 2, 3])) $digestion = 'Open';
            if ($color === 2 && in_array($ton, [4, 5, 6])) $digestion = 'Closed';
            if ($color === 3 && in_array($ton, [1, 2, 3])) $digestion = 'Cold';
            if ($color === 3 && in_array($ton, [4, 5, 6])) $digestion = 'Hot';
            if ($color === 4 && in_array($ton, [1, 2, 3])) $digestion = 'Calm';
            if ($color === 4 && in_array($ton, [4, 5, 6])) $digestion = 'Nervous';
            if ($color === 5 && in_array($ton, [1, 2, 3])) $digestion = 'High';
            if ($color === 5 && in_array($ton, [4, 5, 6])) $digestion = 'Low';
            if ($color === 6 && in_array($ton, [1, 2, 3])) $digestion = 'Direct';
            if ($color === 6 && in_array($ton, [4, 5, 6])) $digestion = 'InDirect';
        }

        return $digestion;
    }

    protected function getEnvironment(array $data): string
    {
        $environment = '';
        if (! empty($data['celectials']['trueNode']['red color'])) {
            $environment_names = [
                1 => 'Caves',
                2 => 'Markets',
                3 => 'Kitchens',
                4 => 'Mountains',
                5 => 'Valleys',
                6 => 'Shores',
            ];

            $environment = $environment_names[$data['celectials']['trueNode']['red color']];
        }

        return $environment;
    }

    protected function getSense(array $data): string
    {
        $sense = '';
        if (! empty($data['celectials']['Sun']['red tone'])) {
            $sense_names = [
                1 => 'Security/Smell',
                2 => 'Uncertainty/Tast',
                3 => 'Action/Outer Vision',
                4 => 'Meditation/Inner Vision',
                5 => 'Judgment/Feeling',
                6 => 'Acceptance/Touch',
            ];

            $sense = $sense_names[$data['celectials']['Sun']['red tone']];
        }

        return $sense;
    }

    protected function getHealth(array $data): string
    {
        $health = '';
        if (! empty($data['celectials']['Sun']['red color'])) {
            $health_names = [
                1 => 'Appetite',
                2 => 'Taste',
                3 => 'Thirst',
                4 => 'Touch',
                5 => 'Sound',
                6 => 'Light',
            ];

            $health = $health_names[$data['celectials']['Sun']['red color']];
        }

        return $health;
    }

    protected function getPerspective(array $data): string
    {
        $perspective = '';
        if (! empty($data['celectials']['trueNode']['black color'])) {
            $perspectives = [
                1 => 'Investigative/ Priorities',
                2 => 'Philosophical/ Opportunities',
                3 => 'Political/ Problems',
                4 => 'Social/ People',
                5 => 'Realistic/ Probabilities',
                6 => 'Self-centered/ Potential'
            ];

            $perspective = $perspectives[$data['celectials']['trueNode']['black color']];
        }

        return $perspective;
    }
}
