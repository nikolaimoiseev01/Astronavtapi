<?php

namespace App\Services\NatalCalculator;
use PDO;

class IncarnationService
{
    private  $pdo, $database, $circuit;
    public function test()
    {

        return $array_celectials;
    }
    public function get_lines_quantities($array)
    {
        $array_lines_P = [];
        $array_lines_D = [];
        foreach ($array as $value)
        {
            $array_permanent = explode('.', $value['black hexagram']);
            array_push($array_lines_P, $array_permanent[1]);
            $array_permanent = explode('.', $value['red hexagram']);
            array_push($array_lines_D, $array_permanent[1]);
        }
        $array_lines_P = array_count_values($array_lines_P);
        $array_lines_D = array_count_values($array_lines_D);
        for($i = 1; $i < 7; $i++)
        {
            if(!array_key_exists($i, $array_lines_P))
            {
                $array_lines_P[$i] = 0;
            }
            if(!array_key_exists($i, $array_lines_D))
            {
                $array_lines_D[$i] = 0;
            }
        }
        krsort($array_lines_P);
        krsort($array_lines_D);
        $array_total = [];
        for($i = 1; $i < 7; $i++)
        {
            $array_total[$i] = $array_lines_P[$i] + $array_lines_D[$i];
        }
        krsort($array_total);
        return [
            'P'		=> $array_lines_P,
            'D'		=> $array_lines_D,
            'total'	=> $array_total
        ];
    }
    public function get_incarnation_cross($array_celectials)
    {
        $cross = $this->get_hexagramm_int($array_celectials['Sun']['black hexagram'])[0].'/';
        $cross .= $this->get_hexagramm_int($array_celectials['Earth']['black hexagram'])[0].'|';
        $cross .= $this->get_hexagramm_int($array_celectials['Sun']['red hexagram'])[0].'/';
        $cross .= $this->get_hexagramm_int($array_celectials['Earth']['red hexagram'])[0];
        $profile =
            $this->get_hexagramm_int($array_celectials['Sun']['black hexagram'])[1].'|'.
            $this->get_hexagramm_int($array_celectials['Sun']['red hexagram'])[1];
        $sql = "SELECT `quarter`, `description` FROM `{$this->database}`.`incarnations` WHERE `profile` = :profile AND `cross` = :cross";
        $request = $this->pdo->prepare($sql);
        $request->bindParam(':profile', $profile);
        $request->bindParam(':cross', $cross);
        $request->execute();
        $array_query = $request->fetch(PDO::FETCH_ASSOC);
        return 'cross: '.$cross.'; quarter: '.$array_query['quarter'].'; description: '.$array_query['description'];
    }
    private function get_hexagramm_int($value)
    {
        $array_value = explode('.', $value);
        return $array_value;
    }
    public function get_active_channels($array_celectials)
    {
        $array_active_channels =
            [
                'red channel'	=> [],
                'black channel'	=> [],
            ];
        $array_result = [];
        foreach($array_celectials as $key => $value)
        {
            if(isset($value['through cross black channel']))
            {
                foreach($value['through cross black channel'] as $clavis => $array_channel)
                {
                    array_push($array_active_channels['black channel'], $array_channel['channel']);
                }
            }
            if(isset($value['through black channel']))
            {
                foreach($value['through black channel'] as $clavis => $array_channel)
                {
                    array_push($array_active_channels['black channel'], $array_channel['channel']);
                }
            }
            if(isset($value['through cross red channel']))
            {
                foreach ($value['through cross red channel'] as $clavis => $array_channel)
                {
                    array_push($array_active_channels['red channel'], $array_channel['channel']);
                }
            }
            if(isset($value['through red channel']))
            {
                foreach ($value['through red channel'] as $clavis => $array_channel)
                {
                    array_push($array_active_channels['red channel'], $array_channel['channel']);
                }
            }
        }
        $array_active_channels['red channel'] = array_flip(array_flip($array_active_channels['red channel']));
        $array_active_channels['black channel'] = array_flip(array_flip($array_active_channels['black channel']));
        $array_result = array_merge($array_active_channels['red channel'], $array_active_channels['black channel']);
        $array_result = $this->sort_channel_bind($array_result);
        $array_active_channels['red channel'] = $this->sort_channel_bind($array_active_channels['red channel']);
        $array_active_channels['black channel'] = $this->sort_channel_bind($array_active_channels['black channel']);
        return [$array_result, $array_active_channels];
    }
    public function sort_channel_bind($array)
    {
        foreach($array as $key => $value)
        {
            $array_intermediate = explode(' - ', $value);
            if($array_intermediate[0] > $array_intermediate[1])
            {
                $array[$key] = $array_intermediate[1].' - '.$array_intermediate[0];
            }
        }
        $array = array_flip(array_flip($array));
        return $array;
    }
    public function get_type($array_centers_defined, $array_centers_defined_all)
    {
        if(empty($array_centers_defined))
        {
            $type = 'OBSERVER';//определённых центров нет
        }
        else
        {
            //определённый центр есть
            if(in_array('FORCE', $array_centers_defined_all))//есть ли определённый FORCE центр
            {
                //есть определённый FORCE центр
                $type = $this->expression_bind(
                    $array_centers_defined,
                    ['DRIVE', 'FORCE', 'EGO', 'EMOTIONAL'],
                    'yes'
                );
            }
            else
            {
                //нет определённого FORCE центр
                $type = $this->expression_bind($array_centers_defined, ['DRIVE', 'EGO', 'EMOTIONAL'], 'no');
            }
        }
        return $type;
    }
    private function expression_bind($array_centers_defined, $array_motors, $force_center)
    {
        $array_motors = $this->add_expression($array_motors);//делаем массив нужных связок
        // return $force_center;
        foreach($array_centers_defined as $key => $value)
        {
            if(in_array($value, $array_motors))//найдена прямая связка
            {
                if($force_center == 'no')
                {
                    return 'INITIATOR';
                }
                else
                {
                    return 'SPESIALIST';
                }
            }
        }
        //прямая связка не найдена, проверяем непрямые связки через другие центры
        $cross_canal = $this->get_cross_chanel($array_centers_defined);
        // return $cross_canal;
        if($cross_canal)
        {
            if($force_center == 'no')
            {
                return 'INITIATOR';
            }
            else
            {
                return 'SPESIALIST';
            }
        }
        else
        {
            if($force_center == 'no')
            {
                return 'COORDINATOR';
            }
            else
            {
                return 'BUILDER';
            }
        }
    }
    public function add_expression($array_motors)
    {
        $array_result = [];
        foreach($array_motors as $key => $value)
        {
            array_push($array_result, $value.' - EXPRESSION');
            array_push($array_result, 'EXPRESSION - '.$value);
        }
        return $array_result;
    }
    public function prepare_graph($array_centers_defined)
    {
        $graf = [];
        foreach ($array_centers_defined as $key => $value)
        {
            $array_current_center = explode(' - ', $value);//разбиваем связку центров
            if(array_key_exists($array_current_center[0], $graf))//в графе уже есть первый центр связки
            {
                array_push($graf[$array_current_center[0]], $array_current_center[1]);//добавляем к существующей записи
            }
            else//центра нет
            {
                $graf[$array_current_center[0]] = [$array_current_center[1]];//создаём запись
            }
            if(array_key_exists($array_current_center[1], $graf))//в графе уже есть второй центр связки
            {
                array_push($graf[$array_current_center[1]], $array_current_center[0]);//добавляем к существующей записи
            }
            else//центра нет
            {
                $graf[$array_current_center[1]] = [$array_current_center[0]];//создаём запись
            }
        }
        return $graf;
    }
    public function obtain_determinancy($graph)
    {
        function testpath($graph, &$col, &$mypath, $cur_col)
        {
            $pkey = end($mypath);
            foreach($graph[$pkey] as $val)
            {
                if($col[$val] == 0)
                {
                    $col[$val] = $cur_col;
                    array_push($mypath, $val);
                    testpath($graph, $col, $mypath, $cur_col);
                }
            }
            array_pop($mypath);
            return;
        }

        foreach($graph as $key => $line)
        {
            $col[$key] = 0;
        }

        $cur_col = 0;
        while($pkey = array_search(0, $col))
        {
            $cur_col++;
            $mypath = [$pkey];
            $col[$pkey] = $cur_col;
            testpath($graph, $col, $mypath, $cur_col);
        }
        return $cur_col;
    }
    public function venus_sun_leash($sun_coordinate, $venus_coordinate)
    {
        if(abs($venus_coordinate - $sun_coordinate) >= 49)
        {
            if($venus_coordinate > $sun_coordinate)
            {
                $distance = (int)(abs($venus_coordinate - $sun_coordinate - 360));
            }
            else
            {
                $distance = (int)(abs($venus_coordinate - $sun_coordinate + 360));
            }
        }
        else
        {
            $distance = (int)(abs($venus_coordinate - $sun_coordinate));
        }
        return $distance;
        // if( =< 10)
        // {

        // }
        // 0-10
        // 11-24
        // 25-35
        // 45
        // 36-49

    }
    public function get_cross_chanel($array_centers_defined)
    {
        // центр EGO может только церез IDENTITY канал 25-51 и 7-31 или 1-8 или 13-33
        // или через INTUITION канал 26-44 и 16-48 или 20-57

        // центр EMOTION можно не рассматривать, т.к. FORCE центра нет, а другие моторы соединяться раньше

        //центр FORCE может только церез IDENTITY канал 2-14 или 5-15 или 29-46 и 7-31 или 1-8 или 13-33
        // или через INTUITION канал 27-50 и 16-48 или 20-57

        //центр DRIVE через канал FORCE отбрасываем; остаётся INTUITION напрямую канал 18-58 или 28-38 или 32-54 и 16-48 или 20-57 или через связку с IDENTITY канал 18-58 или 28-38 или 32-54 и 10-57 и 7-31 или 1-8 или 13-33

        if
        (
            (//EGO
                (
                    array_key_exists('25 - 51', $array_centers_defined)&&
                    (
                        array_key_exists('1 - 8', $array_centers_defined)||
                        array_key_exists('7 - 31', $array_centers_defined)||
                        array_key_exists('13 - 33', $array_centers_defined)
                    )
                )||
                (
                    array_key_exists('26 - 44', $array_centers_defined)&&
                    (
                        array_key_exists('16 - 48', $array_centers_defined)||
                        array_key_exists('20 - 57', $array_centers_defined)
                    )
                )
            )||
            (//FORCE
                (//через IDENTITY
                    (
                        array_key_exists('2 - 14', $array_centers_defined)||
                        array_key_exists('5 - 15', $array_centers_defined)||
                        array_key_exists('29 - 46', $array_centers_defined)
                    )&&
                    (
                        array_key_exists('1 - 8', $array_centers_defined)||
                        array_key_exists('7 - 31', $array_centers_defined)||
                        array_key_exists('13 - 33', $array_centers_defined)||
                        array_key_exists('10 - 20', $array_centers_defined)
                    )
                )||
                (//через INTUITION
                    array_key_exists('34 - 57', $array_centers_defined)&&
                    (
                        array_key_exists('16 - 48', $array_centers_defined)||
                        array_key_exists('20 - 57', $array_centers_defined)
                    )
                )
            )||
            (//DRIVE
                (//через INTUITION
                    array_key_exists('18 - 58', $array_centers_defined)||
                    array_key_exists('28 - 38', $array_centers_defined)||
                    array_key_exists('32 - 54', $array_centers_defined)
                )&&
                (
                    array_key_exists('16 - 48', $array_centers_defined)||
                    array_key_exists('20 - 57', $array_centers_defined)
                )
            )
        )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function fetch_gates($array)
    {
        $array_gates_isolated = [];
        $array_color = ['black', 'red'];
        foreach ($array_color as $color)
        {
            $array_template = array_column($array, $color.' hexagram');
            $array_gates_isolated = array_merge($array_gates_isolated, $array_template);
        }
        $string_gates = implode(',', $array_gates_isolated);
        $sql = "SELECT `circuit`, `hexagram` FROM `{$this->database}`.`hexagrams` WHERE `hexagram` IN({$string_gates})";
        $request = $this->pdo->prepare($sql);
        $request->execute();
        $array_query = $request->fetchAll(PDO::FETCH_ASSOC);

        foreach ($array_query as $value)
        {
            array_push($this->circuit[$value['circuit']], self::isolate_gate($value['hexagram']));
        }
        foreach ($this->circuit as $key => $value)
        {
            $this->circuit[$key] = array_flip(array_flip($value));
        }
        return $this->circuit;
    }
    private function isolate_gate($elem)
    {
        $array_element = explode('.', $elem);
        return $array_element[0];
    }
    public function __construct($pdo, $database)
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->circuit =
            [
                'SOCIETAL' 		=> [],
                'INDIVIDUAL'	=> [],
                'COMMUNAL'		=> []
            ];
    }
    public function __destruct()
    {
    }
}
