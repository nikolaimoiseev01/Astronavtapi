<?php
namespace App\Services\NatalCalculator;

use DateInterval;
use DateTime;
use DateTimeZone;
use PDO;

class AstroService
{
    private $date, $time, $lib_path, $array_celectial_name, $array_node_name, $pdo, $database, $swetest_application, $time_zone;
    public function test()
    {
        set_time_limit(300); // 5 минут
        $array_date = $this->prepare_date($this->date);
        $array_celectials = $this->fetch_celestials_data($array_date);
        // return $array_celectials;
        $array_celectials = $this->channel_connects($array_celectials);
        $array_celectials = $this->calculate_action($array_celectials);
        $array_celectials = $this->color_tone_base_arrow($array_celectials);
        $array_celectials = $this->get_saturn_chiron_return($array_celectials);
        return $array_celectials;
    }
    public function coordinate_360_ajust($coordinate)
    {
        if($coordinate >= 88&&$coordinate < 360)
        {
            return $coordinate - 88;
        }
        else
        {
            return $coordinate + 272;
        }
    }
    public function get_ephemeris($date, $celestial_body)
    {
        $output = [];
        putenv("PATH=".$this->lib_path);
        exec("$this->swetest_application -edir$this->lib_path -b$date -p$celestial_body -eswe -fl, -head", $output, $retval);
        return (float) trim($output[0]);
    }
    public function get_ephemeris_date_time($date, $time, $celestial_body)
    {
        $output = [];
        putenv("PATH=".$this->lib_path);
        exec("$this->swetest_application -edir$this->lib_path -b$date -t$time -p$celestial_body -eswe -fl, -head", $output, $retval);
        return $output[0];
    }
    private function fetch_celestials_data($array_date)
    {
        foreach ($this->array_celectial_name as $key => $value)
        {
            $start_coordinate = $this->get_ephemeris($array_date['date'], $value);
            $end_coordinate = $this->get_ephemeris($array_date['date_next_day'], $value);
            $this->array_node_name[$key]['black ephemeris'] = $start_coordinate;
            $this->array_node_name[$key]['black ephemeris next day'] = $end_coordinate;
            $array_calculated_data =
                $this->get_planet_coordinate($start_coordinate, $end_coordinate, $array_date['decimal_seconds']);
            foreach ($array_calculated_data as $parameter_name => $parameter_value)
            {
                if($parameter_name == 'coordinate')
                {
                    $this->array_node_name[$key]['black '.$parameter_name] = $this->ajust_coorditane_bag($parameter_value);
                    $hexagram = $this->get_hexagram($parameter_value);
                    $this->array_node_name[$key]['black hexagram'] = $hexagram;
                    $this->array_node_name[$key]['black perturbation'] = $this->get_perturbation($key, $hexagram);
                    $this->array_node_name[$key]['black result perturbation'] = '';
                    $black_opposite_hexagram = $this->get_opposite($hexagram);
                    $this->array_node_name[$key]['black opposite hexagram']	= $black_opposite_hexagram;
                    $this->array_node_name[$key]['black gate']	= $this->get_gate($hexagram)['quarter'];
                }
                else
                {
                    $this->array_node_name[$key][$parameter_name] = $parameter_value;
                }
            }
        }

        $array_add_black_data =
            [
                'Earth'			=> 'Sun',
                'SouthNode'		=> 'trueNode'
            ];
        foreach($array_add_black_data as $key => $value)
        {
            $black_coordinate = $this->coordinate_modul($this->array_node_name[$value]['black coordinate']);
            $hexagram = $this->get_hexagram($black_coordinate);
            $black_opposite_hexagram = $this->get_opposite($hexagram);
            $perturbation = $this->get_perturbation(strtolower($key), $hexagram);
            $this->array_node_name[$key]['black coordinate'] = $black_coordinate;
            $this->array_node_name[$key]['black hexagram'] = $hexagram;
            $this->array_node_name[$key]['black opposite hexagram'] = $black_opposite_hexagram;
            $this->array_node_name[$key]['black perturbation'] = $perturbation;
            $this->array_node_name[$key]['black result perturbation'] = '';
            $this->array_node_name[$key]['black gate']	= $this->get_gate($hexagram)['quarter'];
        }

        $array_red_sun_data = $this->red_sun($this->date, $this->array_node_name['Sun']['black coordinate']);
        $this->array_node_name['Sun']['red date'] = $array_red_sun_data;
        foreach ($array_red_sun_data as $key => $value)
        {
            $this->array_node_name['Sun'][$key] = $value;
        }
        foreach ($this->array_node_name as $key => $value)
        {
            switch($key)
            {
                case 'Sun':
                    break;
                //По Земле берём зеркальные модули координат Солнца
                case 'Earth':
                    $red_coordinate = $this->coordinate_modul($this->array_node_name['Sun']['red coordinate']);
                    $red_hexagram = $this->get_hexagram($red_coordinate);
                    $this->array_node_name['Earth']['red hexagram'] = $red_hexagram;
                    $this->array_node_name['Earth']['red opposite hexagram'] = $this->get_opposite($red_hexagram);
                    $red_perturbation = $this->get_perturbation('earth', $hexagram);
                    $this->array_node_name['Earth']['red perturbation'] = $red_perturbation;
                    $this->array_node_name['Earth']['red result perturbation'] = '';
                    $this->array_node_name['Earth']['red gate']	= $this->get_gate($red_hexagram)['quarter'];
                    break;
                //По Южному Ноду берём зеркальные модули координат Северного Нода
                case 'SouthNode':
                    $red_coordinate = $this->coordinate_modul($this->array_node_name['trueNode']['red coordinate']);
                    $red_hexagram = $this->get_hexagram($red_coordinate);

                    $this->array_node_name['SouthNode']['red coordinate'] = $red_coordinate;
                    $this->array_node_name['SouthNode']['red hexagram'] = $red_hexagram;
                    $this->array_node_name['SouthNode']['red opposite hexagram'] = $this->get_opposite($red_hexagram);
                    $red_perturbation = $this->get_perturbation('SouthNode', $hexagram);
                    $this->array_node_name['SouthNode']['red perturbation'] = $red_perturbation;
                    $this->array_node_name['SouthNode']['red result perturbation'] = '';
                    $this->array_node_name['SouthNode']['red gate']	= $this->get_gate($red_hexagram)['quarter'];
                    break;
                default:
                    $red_coordinate	 = $this->get_ephemeris_date_time(
                        $this->array_node_name['Sun']['design date'],
                        $this->array_node_name['Sun']['design time'],
                        $this->array_celectial_name[$key]
                    );
                    $red_hexagram = $this->get_hexagram($red_coordinate);
                    $this->array_node_name[$key]['red coordinate'] = $red_coordinate;
                    $this->array_node_name[$key]['red hexagram'] = $red_hexagram;
                    $this->array_node_name[$key]['red opposite hexagram'] = $this->get_opposite($red_hexagram);
                    $this->array_node_name[$key]['red perturbation'] = $red_perturbation;
                    $this->array_node_name[$key]['red result perturbation'] = '';
                    $this->array_node_name[$key]['red gate']	= $this->get_gate($red_hexagram)['quarter'];

                    unset($red_coordinate);
                    break;
            }
        }
        return $this->array_node_name;
    }
    private function get_nearest_date_time($datum, $coordinate, $end, $interval)
    {
        $date = new DateTime($datum);
        $array_result = [];
        for ($i = 0; $i < $end; $i++)
        {
            $date->add(new DateInterval($interval));
            $datum = $date->format('d.m.Y');
            $time = $date->format('H:i:s');
            $output = [];
            exec("$this->swetest_application -edir$this->lib_path -b$datum -t$time -p6 -eswe -fl, -head", $output, $retval);
            $array_result[$datum] = abs($coordinate - $output[0]);
        }
        $datum = array_search(min($array_result), $array_result);
        return $datum;
    }
    private function get_nearest_time_range($interval_add, $planeta_number, $start, $end, $date, $coordinate)
    {
        $array_result = [];
        for ($i = $start; $i < $end; $i++)
        {
            $date->add(new DateInterval($interval_add));
            $datum = $date->format('d.m.Y');
            $time = $date->format('H:i:s');
            $output = [];
            exec("$this->swetest_application -edir$this->lib_path -b$datum -t$time -p$planeta_number -eswe -fl, -head", $output, $retval);
            $array_result[$datum.' '.$time] = abs($coordinate - $output[0]);
        }
        $array_result['datum'] = array_search(min($array_result), $array_result);
        return $array_result;
    }
    public function get_zodiac_sigh($coordinate)
    {
        if($coordinate == 359.1881||$coordinate == 0.1251||$coordinate >= 359.188)
        {
            $zodiac_sign = 'Aries';
        }
        else
        {
            $sql =
                "SELECT `zodiac_sign` FROM `$this->database`.`hexagrams` WHERE `start` <= {$coordinate} AND `end` >= {$coordinate}";
            $request = $this->pdo->prepare($sql);
            $request->execute();
            $zodiac_sign = $request->fetch(PDO::FETCH_COLUMN);
        }
        return $zodiac_sign;
    }
    public function get_day_return_by_zodiac($year_quantity, $planeta_number, $coordinate, $year_add)
    {
        $zodiac_sign_birthday = $this->get_zodiac_sigh($coordinate);
        $birth_hexagram = $this->get_hexagram($coordinate);
        $date = new DateTime($this->date);
        $date->add(new DateInterval('P'.$year_add.'Y'));
        $end = ($year_quantity * 365) + 1;
        $array_result = [];
        for($i = 0; $i < $end; $i++)
        {
            $date_day = $date->format('d.m.Y');
            $current_coordinate = $this->get_ephemeris($date_day, $planeta_number);
            $current_zodiac_sign = $this->get_zodiac_sigh($current_coordinate);
            $hexagram = $this->get_hexagram($current_coordinate);
            if
            (
                $current_zodiac_sign == $zodiac_sign_birthday&&
                $hexagram == $birth_hexagram
            )
            {
                $zodiac_hexagram = 'true';
            }
            else
            {
                $zodiac_hexagram = 'false';
            }
            // if
            // (
            // 	$current_zodiac_sign == $zodiac_sign_birthday&&
            // 	$hexagram == $birth_hexagram
            // )
            // {
            $difference = abs($coordinate - $current_coordinate);
            $array_result[$date_day] =
                [
                    'difference'				=> $difference,
                    'current_zodiac_sign'		=> $current_zodiac_sign,
                    'zodiac_sign_birthday'		=> $zodiac_sign_birthday,
                    'hexagram'					=> $hexagram,
                    'birth_hexagram'			=> $birth_hexagram,
                    'zodiac_hexagram'			=> $zodiac_hexagram

                ];
            // }
            // else
            // {
            // 	if(count($array_result) != 0)
            // 	{
            // 		break;
            // 	}
            // }
            $date->add(new DateInterval('P1D'));
        }
        // return $array_result;
        return array_search(min($array_result), $array_result);
    }
    public function get_day_return($year_quantity, $planeta_number, $coordinate, $year_add)
    {
        $previous_coordinate = 0;
        $zodiac_sign = $this->get_zodiac_sigh($coordinate);

        $date = new DateTime($this->date);
        $date->add(new DateInterval('P'.$year_add.'Y'));
        $end = ($year_quantity * 365) + 1;
        $array_result = [];
        for($i = 0; $i < $end; $i++)
        {
            $date_day = $date->format('d.m.Y');

            if(count($array_result) > 1)
            {
                $previous_coordinate = $current_coordinate;
            }
            $current_coordinate = $this->get_ephemeris($date_day, $planeta_number);
            $current_zodiac_sign = $this->get_zodiac_sigh($current_coordinate);
            //проверка на ретроградность
            // if(($current_coordinate - $previous_coordinate) < 0)
            // {
            // 	return $array_result;
            // }
            if($zodiac_sign == $current_zodiac_sign)
            {
                // $difference = abs($coordinate - $current_coordinate);
                $difference = $coordinate - $current_coordinate;

                $hexagram = $this->get_hexagram($current_coordinate);

                $array_result[$date_day] =
                    [
                        'difference'			=> $difference,
                        'current coordinate'	=> $current_coordinate,
                        'hexagram'				=> $hexagram,
                        'zodiac_sign'			=> $zodiac_sign,
                        'current_zodiac_sign'	=> $current_zodiac_sign,
                        // 'retro'					=> $current_coordinate - $previous_coordinate
                    ];
            }
            $date->add(new DateInterval('P1D'));
        }
        return $array_result;
    }
    public function get_saturn_chiron_return($array_celectials)
    {
        $saturn_black_coordinate = $array_celectials['Saturn']['black coordinate'];
        // $array_return_day_data = $this->get_day_return(5, 6, $saturn_black_coordinate, 28);
        // $minimun_difference = min($array_return_day_data);
        // $datum = array_search($minimun_difference, $array_return_day_data);

        $datum = $this->get_day_return_by_zodiac(5, 6, $saturn_black_coordinate, 28);

        $date = new DateTime($datum);
        $date->sub(new DateInterval('PT24H'));

        $datum = $this->get_nearest_time_range('PT1H', 6, 0, 73, $date, $saturn_black_coordinate)['datum'];

        $date = new DateTime($datum);
        $date->sub(new DateInterval('PT60M'));

        $datum = $this->get_nearest_time_range('PT1M', 6, 0, 181, $date, $saturn_black_coordinate)['datum'];

        $date = new DateTime($datum);
        $date->sub(new DateInterval('PT60S'));

        $datum = $this->get_nearest_time_range('PT1S', 6, 0, 181, $date, $saturn_black_coordinate)['datum'];

        $array_celectials['Saturn']['return day time'] = $datum;
        // $array_celectials['Saturn']['array_return_day_data'] = $array_return_day_data;

        //Chiron
        $date = new DateTime($this->date);
        $datum = $date->format('d.m.Y');

        $start_coordinate = $this->get_ephemeris($datum, 'D');

        $date->add(new DateInterval('P1D'));

        $datum = $date->format('d.m.Y');
        $end_coordinate = $this->get_ephemeris($datum, 'D');

        $time = $date->format('H:i:s');

        $decimal_seconds = $this->time_to_seconds($time);
        $array_chiron = $this->get_planet_coordinate($start_coordinate, $end_coordinate, $decimal_seconds);
        $array_chiron['zodiac_sigh'] = $this->get_zodiac_sigh($array_chiron['coordinate']);

        $array_celectials['Saturn']['Chiron']['data'] = $array_chiron;

        // $chiron_black_coordinate = $array_chiron['coordinate'];
        // $array_return_day_data = $this->get_day_return(5, 'D', $chiron_black_coordinate, 48);
        // $minimun_difference = min($array_return_day_data);
        // $datum = array_search($minimun_difference, $array_return_day_data);

        // $date = new DateTime($datum);
        // $date->sub(new DateInterval('PT24H'));

        // $datum = $this->get_nearest_time_range('PT1H', 'D', 0, 73, $date, $chiron_black_coordinate)['datum'];

        // $date = new DateTime($datum);
        // $date->sub(new DateInterval('PT60M'));

        // $datum = $this->get_nearest_time_range('PT1M', 'D', 0, 181, $date, $chiron_black_coordinate)['datum'];

        // $date = new DateTime($datum);
        // $date->sub(new DateInterval('PT60S'));

        // $datum = $this->get_nearest_time_range('PT1S', 'D', 0, 181, $date, $chiron_black_coordinate)['datum'];

        // $array_celectials['Saturn']['Chiron']['data']['return day time'] = $datum;

        return $array_celectials;
    }
    public function get_red_date_time($datum, $coordinate)
    {

        $date = new DateTime($datum);
        $date->sub(new DateInterval('P96D'));
        $datum = $date->format('d.m.Y');

        $array_result = [];
        for ($i = 0; $i < 11; $i++)
        {
            $date->add(new DateInterval('P1D'));
            $datas = $date->format('d.m.Y');
            $output = [];
            exec("$this->swetest_application -edir$this->lib_path -b$datas -p0 -eswe -fl, -head", $output, $retval);
            $array_result[$datas] = abs($coordinate - $output[0]);
        }
        $datum = array_search(min($array_result), $array_result);

        $date = new DateTime($datum);
        $date->sub(new DateInterval('PT25H'));
        //
        $array_result = [];
        for($i = 0; $i < 43; $i++)
        {
            $date->add(new DateInterval('PT1H'));
            $datas = $date->format('d.m.Y');
            $time = $date->format('H:i:s');
            $output = [];
            exec("$this->swetest_application -edir$this->lib_path -b$datas -t$time -p0 -eswe -fl, -head", $output, $retval);
            $array_result[$datas.' '.$time] = abs($coordinate - $output[0]);
        }
        $datum = array_search(min($array_result), $array_result);
        // return $array_result;
        // return $datum;

        $date = new DateTime($datum);
        $date->sub(new DateInterval('PT60M'));
        $datas = $date->format('d.m.Y H:i:s');

        $array_result = [];
        for($i = 0; $i < 180; $i++)
        {
            $date->add(new DateInterval('PT1M'));
            $datas = $date->format('d.m.Y');
            $time = $date->format('H:i:s');
            $output = [];
            exec("$this->swetest_application -edir$this->lib_path -b$datas -t$time -p0 -eswe -fl, -head", $output, $retval);
            $array_result[$datas.' '.$time] = abs($coordinate - $output[0]);
        }
        $datum = array_search(min($array_result), $array_result);
        // return $datum;
        // return $array_result;
        $date = new DateTime($datum);
        $date->sub(new DateInterval('PT60S'));
        $array_result = [];
        for($i = 0; $i < 120; $i++)
        {
            $date->add(new DateInterval('PT1S'));
            $datas = $date->format('d.m.Y');
            $time = $date->format('H:i:s');
            $output = [];
            exec("$this->swetest_application -edir$this->lib_path -b$datas -t$time -p0 -eswe -fl, -head", $output, $retval);
            $array_result[$datas.' '.$time] = abs($coordinate - $output[0]);
        }
        $datum = array_search(min($array_result), $array_result);
        return $datum;
    }
    private function red_sun($date, $coordinate)
    {
        $coordinate = $this->coordinate_360_ajust($coordinate);
        $datum = $this->get_red_date_time($date, $coordinate);
        $datas = new DateTime($datum);

        $hexagram = $this->get_hexagram($coordinate);
        $red_opposite_hexagram = $this->get_opposite($hexagram);

        $perturbation = $this->get_perturbation('sun', $hexagram);

        $array_red_data =
            [
                'red coordinate'					=> $coordinate,
                'red hexagram'						=> $hexagram,
                'red opposite hexagram'				=> $red_opposite_hexagram,
                'red perturbation'					=> $perturbation,
                'red result perturbation'			=> '',
                'design date_time'					=> $datum,
                'design date'						=> $datas->format('d.m.Y'),
                'design time'						=> $datas->format('H:i:s'),

            ];
        return $array_red_data;
    }
    private function get_date_value($array_ephemeris_range, $coordinate)
    {
        $array_difference = array_column($array_ephemeris_range, 'difference');
        $key_minimum_value = array_search(min($array_difference), $array_difference);
        if($coordinate > $array_ephemeris_range[$key_minimum_value]['planet coordinate'])
        {
            return $array_ephemeris_range[$key_minimum_value];
        }
        else
        {
            return $array_ephemeris_range[$key_minimum_value - 1];
        }
    }
    private function get_ephemeris_range($datum, $coordinate, $time, $start, $end, $interval, $key, $planet_number)
    {
        $date = new DateTime($datum.' '.$time);
        $datum = $date->format('d.m.Y');
        $array_ephemeris_range = [];
        for($i = $start; $i < $end; $i++)
        {
            switch ($key)
            {
                case 'day':
                    $clavis = $date->format('d.m.Y');
                    break;
                case 'hour':
                    $clavis = $date->format('H');
                    break;
                case 'minute':
                    $clavis = $date->format('i');
                    break;
                case 'seconds':
                    $clavis = $date->format('s');
                    break;
            }
            exec("$this->swetest_application -edir$this->lib_path -b$datum -p$planet_number -eswe -fl, -head", $output, $retval);
            $planet_coordinate = (float)(preg_replace('/[^0-9.]/', '', $output[0]));
            $output = [];
            $date->add(new DateInterval($interval));
            $datum = $date->format('d.m.Y');
            $time = $date->format('H:i:s');
            array_push(
                $array_ephemeris_range,
                [
                    'date' 				=> $clavis,
                    'planet coordinate'	=> $planet_coordinate,
                    'difference'		=> abs($planet_coordinate - $coordinate)
                ]
            );
        }
        return $array_ephemeris_range;
    }
    private function get_opposite($hexagram)
    {
        $line = $hexagram - (int)($hexagram);
        $hexagram = (int)($hexagram);
        $sql = "SELECT `channel_2` FROM `$this->database`.`channels` WHERE `channel_1` = {$hexagram}";
        $request = $this->pdo->prepare($sql);
        $request->execute();
        $result = $request->fetchAll(PDO::FETCH_COLUMN);
        $array_channels = [];
        foreach ($result as $key => $value)
        {
            $array_channels[$key] = $value;
        }
        return $array_channels;
    }
    public function get_hexagram($coordinate)
    {
        if($coordinate == 359.1881||$coordinate == 0.1251||$coordinate >= 359.188)
        {
            return 25.2;
        }
        $sql = "SELECT `hexagram` FROM `{$this->database}`.`hexagrams` WHERE `start` <= {$coordinate} AND `end` >= {$coordinate}";
        $request = $this->pdo->prepare($sql);
        $request->execute();
        return $request->fetch(PDO::FETCH_COLUMN);
    }
    private function get_perturbation($key, $hexagram)
    {
        //действие планеты из бд по названию планеты и гексаграмме
        $key = strtolower($key);
        if(!in_array($key, ['truenode', 'southnode']))
        {
            $sql = "SELECT `{$key}` FROM `{$this->database}`.`hexagrams` WHERE `hexagram` = {$hexagram}";
            $request = $this->pdo->prepare($sql);
            $request->execute();
            return $request->fetch(PDO::FETCH_COLUMN);
        }
        else
        {
            return '';
        }
    }
    private function ajust_coorditane_bag($coorditane)
    {
        //если координата получается меньше 0.125 или больше 359.188, то нужно изменить координату, чтобы из бд взять гексаграмму 25.2
        if($coorditane < 0.125)
        {
            return 0.1251;
        }
        if($coorditane > 359.188)
        {
            return 359.1881;
        }
        return $coorditane;
    }
    public function prepare_date($date)
    {
        date_default_timezone_set($this->time_zone);
        $UTC_zone = new DateTimeZone('UTC');// объект UTC
        $date_reg_zone = new DateTimeZone($this->time_zone);// объект локальное место

        //время и дата UTC
        $date_reg = new DateTime($this->date, $date_reg_zone);

        $time_offset = $date_reg->getOffset();//смещение

        $utc_offset = $time_offset/3600;//смещение в секундах

        $birth_timestamp = strtotime($this->date.' '.$this->time);//дата и время рождение, переданные с клиента
        $utc_timestamp = $birth_timestamp - $utc_offset*3600;//смещение в секундах от времени UTC
        //дата и время рождение UTC
        $date = date('d.m.Y', $utc_timestamp);
        $time = date('H:i:s', $utc_timestamp);

        $decimal_seconds = $this->time_to_seconds($time);

        $date_object = new DateTime($date);
        $date_object->modify('+1 day');//сдвигаем дату на 1 день
        $date_next_day = $date_object->format('d.m.Y');

        return ['date' => $date, 'date_next_day' => $date_next_day, 'decimal_seconds' => $decimal_seconds];
    }
    private function coordinate_modul($coordinate)
    {
        if($coordinate <= 180)
        {
            $coordinate = $coordinate + 180;
            if($coordinate >= 359.188)
            {
                return 359.188;
            }
            else
            {
                return $coordinate;
            }
        }
        else
        {
            $coordinate = $coordinate - 180;
            if($coordinate <= 0.125)
            {
                return 0.126;
            }
            else
            {
                return $coordinate;
            }
        }
    }
    private function get_planet_coordinate($ephemeris_start, $ephemeris_end, $decimal_seconds)
    {
        $ephemeris_delta = $ephemeris_end - $ephemeris_start;
        if($ephemeris_delta > 330)//переход через 360°
        {
            $difference = 360 - ($ephemeris_start + $ephemeris_end);
        }
        else
        {
            $difference = $ephemeris_end - $ephemeris_start;
        }

        $velocity = $difference/86400;

        $distortion = $decimal_seconds*$velocity;

        $coordinate = $ephemeris_start + $distortion;
        if($coordinate > 360)
        {
            $coordinate = $coordinate - 360;
        }
        return
            [
                'difference'		=> $difference,
                'velocity'			=> $velocity,
                'distortion'		=> $distortion,
                'coordinate'		=> $coordinate,
            ];
    }
    public function time_to_seconds($time)
    {
        $array_time = explode(':', $time);
        return 3600*$array_time[0] + 60*$array_time[1] + $array_time[2];
    }
    private function channel_connects($array_celectials)
    {
        foreach($array_celectials as $infected_planet_name => $infected_planet_date)//перебор планет на которые действуют
        {
            foreach($array_celectials as $affected_planet_name => $affected_planet_date)//перебор планет которые действуют
            {
                $black_hexagram = (int)($affected_planet_date['black hexagram']);
                $red_hexagram = (int)($affected_planet_date['red hexagram']);
                // if($infected_planet_name != $affected_planet_name)
                // {
                foreach($array_celectials[$infected_planet_name]['black opposite hexagram'] as $clave => $opposite_hexagram)
                {
                    if($black_hexagram == $opposite_hexagram)
                    {
                        $array_celectials[$infected_planet_name]['through black channel'][] =
                            [
                                'channel' 		=> (int)($infected_planet_date['black hexagram']).' - '.$opposite_hexagram,
                                'impact planet'	=> $affected_planet_name
                            ];
                    }
                    if($red_hexagram == $opposite_hexagram)
                    {
                        $array_celectials[$infected_planet_name]['through cross black channel'][] =
                            [
                                'channel' 		=> (int)($infected_planet_date['black hexagram']).' - '.$opposite_hexagram,
                                'impact planet'	=> $affected_planet_name
                            ];
                    }
                }
                foreach($array_celectials[$infected_planet_name]['red opposite hexagram'] as $clave => $opposite_hexagram)
                {
                    if($red_hexagram == $opposite_hexagram)
                    {
                        $array_celectials[$infected_planet_name]['through red channel'][] =
                            [
                                'channel' 			=> (int)($infected_planet_date['red hexagram']).' - '.$opposite_hexagram,
                                'impact planet'		=> $affected_planet_name
                            ];
                    }
                    if($black_hexagram == $opposite_hexagram)
                    {
                        $array_celectials[$infected_planet_name]['through cross red channel'][] =
                            [
                                'channel' 			=> (int)($infected_planet_date['red hexagram']).' - '.$opposite_hexagram,
                                'impact planet'		=> $affected_planet_name
                            ];
                    }
                }
                // }
            }
        }
        return $array_celectials;
    }
    public function calculate_action($array_celectials)
    {
        foreach ($array_celectials as $infected_planet_name => $infected_planet_data)
        {
            //действие через красный канал
            if(isset($infected_planet_data['through red channel']))
            {
                //перебор оппозитных каналов через красный канал
                foreach ($infected_planet_data['through red channel'] as $key => $value)
                {
                    //берём из бд действие
                    $impact =
                        $this->get_perturbation($value['impact planet'], $infected_planet_data['red hexagram']);
                    if($impact != '')
                    {
                        //если действие есть, то вычисляем результат по каналу
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['red result perturbation'],
                            $infected_planet_data['red perturbation'],
                            $impact
                        );
                        //заполняем напрямую в массив, чтобы избежать ссылочные глюки
                        $array_celectials[$infected_planet_name]['red result perturbation'] = $impact;
                    }
                }
            }
            //действие через кросс красный канал - когда действие на красную планету через красный канал чёрная планета
            if(isset($infected_planet_data['through cross red channel']))
            {
                //перебор оппозитных каналов через кросс красный канал
                foreach($infected_planet_data['through cross red channel'] as $key => $value)
                {
                    //берём из бд действие
                    $impact = $this->get_perturbation($value['impact planet'], $infected_planet_data['red hexagram']);
                    if($impact != '')
                    {
                        //если действие есть, то вычисляем результат по каналу
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['red result perturbation'],
                            $infected_planet_data['red perturbation'],
                            $impact
                        );
                        //заполняем напрямую в массив, чтобы избежать ссылочные глюки
                        $array_celectials[$infected_planet_name]['red result perturbation'] = $impact;
                    }
                }
            }
            //действие через чёрный канал
            if(isset($infected_planet_data['through black channel']))
            {
                //перебор оппозитных каналов через чёрный канал
                foreach ($infected_planet_data['through black channel'] as $key => $value)
                {
                    //берём из бд действие
                    $impact = $this->get_perturbation($value['impact planet'], $infected_planet_data['black hexagram']);
                    if($impact != '')
                    {
                        //если действие есть, то вычисляем результат по каналу
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['black result perturbation'],
                            $infected_planet_data['black perturbation'],
                            $impact
                        );
                        //заполняем напрямую в массив, чтобы избежать ссылочные глюки
                        $array_celectials[$infected_planet_name]['black result perturbation'] = $impact;
                    }
                }
            }
            //действие через кросс чёрный канал - когда действие на чёрную планету через чёрный канал красная планета
            if(isset($infected_planet_data['through cross black channel']))
            {
                //перебор оппозитных каналов через кросс чёрный канал
                foreach ($infected_planet_data['through cross black channel'] as $key => $value)
                {
                    //берём из бд действие
                    $impact = $this->get_perturbation($value['impact planet'], $infected_planet_data['black hexagram']);
                    if($impact != '')
                    {
                        //если действие есть, то вычисляем результат по каналу
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['black result perturbation'],
                            $infected_planet_data['black perturbation'],
                            $impact
                        );
                        //заполняем напрямую в массив, чтобы избежать ссылочные глюки
                        $array_celectials[$infected_planet_name]['black result perturbation'] = $impact;
                    }
                }
            }
            //действия через гексаграммы
            foreach ($array_celectials as $affected_planet_name => $affected_planet_data)
            {
                //здесь проверяем, чтобы действующая и на которую действуют планеты не были ранвны сами себе и нодам
                if
                (
                    $affected_planet_name != $infected_planet_name&&
                    $affected_planet_name != 'trueNode'&&
                    $affected_planet_name != 'SouthNode'
                )
                {
                    //для удобства целые части гексаграммы в переменные
                    $infected_hexagram_red = (int)($infected_planet_data['red hexagram']);
                    $affected_hexagram_red = (int)($affected_planet_data['red hexagram']);
                    $infected_hexagram_black = (int)($infected_planet_data['black hexagram']);
                    $affected_hexagram_black = (int)($affected_planet_data['black hexagram']);
                    //если целые части красных гексаграмм совпадают, то берём действие
                    if($infected_hexagram_red == $affected_hexagram_red)
                    {
                        $perturbation =
                            $this->get_perturbation($affected_planet_name, $infected_planet_data['red hexagram']);
                        //вычисляем результат
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['red result perturbation'],
                            $infected_planet_data['red perturbation'],
                            $perturbation
                        );
                        if($impact != '')
                        {
                            //заполняем напрямую в массив, чтобы избежать ссылочные глюки, если результат не пустой
                            $array_celectials[$infected_planet_name]['red result perturbation'] = $impact;
                        }
                    }
                    //если целые части красной и чёрной гексаграмм совпадают, то берём действие
                    if($infected_hexagram_red == $affected_hexagram_black)
                    {
                        $perturbation =
                            $this->get_perturbation($affected_planet_name, $infected_planet_data['red hexagram']);
                        //вычисляем результат
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['red result perturbation'],
                            $infected_planet_data['red perturbation'],
                            $perturbation
                        );
                        if($impact != '')
                        {
                            //заполняем напрямую в массив, чтобы избежать ссылочные глюки, если результат не пустой
                            $array_celectials[$infected_planet_name]['red result perturbation'] = $impact;
                        }
                    }
                    //если целые части чёрной и красной гексаграмм совпадают, то берём действие
                    if($infected_hexagram_black == $affected_hexagram_red)
                    {
                        $perturbation =
                            $this->get_perturbation($affected_planet_name, $infected_planet_data['black hexagram']);
                        //вычисляем результат
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['black result perturbation'],
                            $infected_planet_data['black perturbation'],
                            $perturbation
                        );
                        if($impact != '')
                        {
                            //заполняем напрямую в массив, чтобы избежать ссылочные глюки, если результат не пустой
                            $array_celectials[$infected_planet_name]['black result perturbation'] = $impact;
                        }
                    }
                    //если целые части чёрных гексаграмм совпадают, то берём действие
                    if($infected_hexagram_black == $affected_hexagram_black)
                    {
                        $perturbation =
                            $this->get_perturbation($affected_planet_name, $infected_planet_data['black hexagram']);
                        //вычисляем результат
                        $impact = $this->determine_impact(
                            $array_celectials[$infected_planet_name]['black result perturbation'],
                            $infected_planet_data['black perturbation'],
                            $perturbation
                        );
                        if($impact != '')
                        {
                            //заполняем напрямую в массив, чтобы избежать ссылочные глюки, если результат не пустой
                            $array_celectials[$infected_planet_name]['black result perturbation'] = $impact;
                        }
                    }
                }
                //если в массиве результат действия что красного, что чёрного пустой, то устанавливаем результат по возмущению из бд
                if(!in_array($array_celectials[$infected_planet_name]['red result perturbation'], ['EX', 'DET', 'JUX']))
                {
                    $array_celectials[$infected_planet_name]['red result perturbation'] =
                        $array_celectials[$infected_planet_name]['red perturbation'];
                }
                if($array_celectials[$infected_planet_name]['black result perturbation'] == '')
                {
                    $array_celectials[$infected_planet_name]['black result perturbation'] =
                        $array_celectials[$infected_planet_name]['black perturbation'];
                }
            }
        }
        return $array_celectials;
    }
    private function color_tone_base_arrow($array_celectials)
    {
        $array_tone_color_base =
            [
                'Sun'		=> 'Sun',
                'trueNode'	=> 'trueNode',//'Nord Node',
                'SouthNode'	=> 'SouthNode', //'South Node'
            ];
        foreach ($array_tone_color_base as $key => $value)
        {
            foreach (['black', 'red'] as $clave => $black_red)
            {
                $array_celectials[$key][$black_red.' hexagram start'] =
                    $this->hexagram_start($array_celectials[$key][$black_red.' hexagram']);

                if(($array_celectials[$key][$black_red.' coordinate'] - $array_celectials[$key][$black_red.' hexagram start']) < 0)
                {
                    $array_celectials[$key][$black_red.' hexagram difference'] = 360 +
                        $array_celectials[$key][$black_red.' coordinate'] - $array_celectials[$key][$black_red.' hexagram start'];
                }
                else
                {
                    $array_celectials[$key][$black_red.' hexagram difference'] =
                        $array_celectials[$key][$black_red.' coordinate'] - $array_celectials[$key][$black_red.' hexagram start'];
                }

                $array_celectials[$key][$black_red.' line'] =
                    $this->get_line($array_celectials[$key][$black_red.' hexagram difference'])[0];

                $array_celectials[$key][$black_red.' line start'] =
                    $this->get_line($array_celectials[$key][$black_red.' hexagram difference'])[1];

                $array_celectials[$key][$black_red.' color coordinate'] =
                    $array_celectials[$key][$black_red.' hexagram difference'] -
                    $array_celectials[$key][$black_red.' line start'];

                $array_celectials[$key][$black_red.' color'] = $this->get_color($array_celectials[$key][$black_red.' color coordinate'])[0];

                $array_celectials[$key][$black_red.' color start'] = $this->get_color($array_celectials[$key][$black_red.' color coordinate'])[1];

                $array_celectials[$key][$black_red.' tone coordinate'] =
                    $array_celectials[$key][$black_red.' color coordinate'] - $array_celectials[$key][$black_red.' color start'];

                $array_celectials[$key][$black_red.' tone'] = $this->get_tone($array_celectials[$key][$black_red.' tone coordinate'])[0];

                $array_celectials[$key][$black_red.' tone start'] = $this->get_tone($array_celectials[$key][$black_red.' tone coordinate'])[1];

                $array_celectials[$key][$black_red.' base coordinate'] =
                    $array_celectials[$key][$black_red.' tone coordinate'] - $array_celectials[$key][$black_red.' tone start'];

                $array_celectials[$key][$black_red.' base'] = $this->get_base($array_celectials[$key][$black_red.' base coordinate']);

                $array_celectials[$key][$black_red.' arrow'] =
                    $this->get_arrow($array_celectials[$key][$black_red.' tone']);
            }
        }
        return $array_celectials;
    }
    private function get_color($coordinate)
    {
        $color = 0;
        for($i = 0; $i < 0.9375; $i = $i + 0.15625)
        {
            if($coordinate < $i)
            {
                break;
            }
            $color++;
        }
        return [$color, $i - 0.15625];
    }
    private function get_tone($coordinate)
    {
        $tone = 0;
        for($i = 0; $i < 0.15625; $i = $i + 0.02604167)
        {
            if($coordinate < $i)
            {
                break;
            }
            $tone++;
        }
        return [$tone, $i - 0.02604167];
    }
    function get_base($coordinate)
    {
        $base = 0;
        for($i = 0; $i < 0.02604167; $i = $i + 0.00520833)
        {
            if($coordinate < $i)
            {
                break;
            }
            $base++;
        }
        return $base;
    }
    //функция вычисления стрелки
    function get_arrow($tone)
    {
        if(in_array($tone, [1, 2, 3]))
        {
            return 'left';
        }
        if(in_array($tone, [4, 5, 6]))
        {
            return 'right';
        }
    }
    private function get_line($coordinate)
    {
        $line = 0;
        for($i = 0; $i < 5.6250; $i = $i + 0.9375)
        {
            if($coordinate < $i)
            {
                break;
            }
            $line++;
        }
        return [$line, $i - 0.9375];
    }
    private function hexagram_start($hexagram)
    {
        $array_hexagram = explode('.', $hexagram);
        $hexagram = (float)($array_hexagram[0] + 0.1);
        $sql = "SELECT `start` FROM `{$this->database}`.`hexagrams` WHERE `hexagram` = {$hexagram}";
        $request = $this->pdo->prepare($sql);
        $request->execute();
        $hexagram_start = $request->fetch(PDO::FETCH_COLUMN);
        return $hexagram_start;
    }
    private function determine_impact($result_perturbation, $infect_perturbation, $affect_perturbaion)
    {
        switch($result_perturbation)
        {
            case 'JUX':
                return 'JUX';
                break;
            case 'EX':
                if($affect_perturbaion == 'DET')
                {
                    return 'JUX';
                }
                else
                {
                    return 'EX';
                }
                break;
            case 'DET':
                if($affect_perturbaion == 'EX')
                {
                    return 'JUX';
                }
                else
                {
                    return 'DET';
                }
                break;
            case '':
                if
                (
                    ($infect_perturbation == 'EX'&&$affect_perturbaion == 'DET')||
                    ($infect_perturbation == 'DET'&&$affect_perturbaion == 'EX')
                )
                {
                    return 'JUX';
                }
                if($infect_perturbation == $affect_perturbaion)
                {
                    return $affect_perturbaion;
                }
                if
                (
                    ($infect_perturbation == 'EX'&&$affect_perturbaion == '')||
                    ($infect_perturbation == ''&&$affect_perturbaion == 'EX')
                )
                {
                    return 'EX';
                }
                if
                (
                    ($infect_perturbation == 'DET'&&$affect_perturbaion == '')||
                    ($infect_perturbation == ''&&$affect_perturbaion == 'DET')
                )
                {
                    return 'DET';
                }
                break;
        }
    }
    public function get_gate($hexagram)
    {
        $sql = "SELECT 	`quarter`, `circuit` FROM `{$this->database}`.`hexagrams` WHERE `hexagram` = {$hexagram}";
        $request = $this->pdo->prepare($sql);
        $request->execute();
        $gate = $request->fetch();
        return $gate;
    }
    public function __construct($date, $time, $pdo, $database, $time_zone)
    {
        $this->date = $date;
        $this->time = $time;
        $this->time_zone = $time_zone;
        $this->lib_path = config('natal.lib_path'); //путь к файлам эфемерид

        $this->array_celectial_name =
            [
                'Sun'			=> '0',
                'Moon'			=> '1',
                'Mercury'		=> '2',
                'Venus'		=> '3',
                'Mars'			=> '4',
                'Jupiter'		=> '5',
                'Saturn'		=> '6',
                'Uranus'		=> '7',
                'Neptune'		=> '8',
                'Pluto'		=> '9',
                'trueNode'		=> 't',
            ];

        $this->array_node_name =
            [
                'Sun'		=> [],
                'Earth'		=> [],
                'trueNode'	=> [],
                'SouthNode'	=> [],
                'Moon'		=> [],
                'Mercury'	=> [],
                'Venus'		=> [],
                'Mars'		=> [],
                'Jupiter'	=> [],
                'Saturn'	=> [],
                'Uranus'	=> [],
                'Neptune'	=> [],
                'Pluto'		=> [],
            ];
        $this->pdo = $pdo;
        $this->database = $database;
        $this->swetest_application = config('natal.swetest_application');
    }
    public function __destruct()
    {
    }
}
