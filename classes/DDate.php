<?php

    class DDate
    {

        /**
        * Function time_elapsed_string creates the string containing textual representation of the period.
        * 
        * @param mixed $ptime
        * @return string
        */
        public static function time_elapsed_string($ptime) {
            $etime = time() - $ptime;

            if ($etime < 1) {
                return '0 seconds';
            }

            $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
            );

            foreach ($a as $secs => $str) {
                $d = $etime / $secs;
                if ($d >= 1) {
                    $r = round($d);
                    return $r . ' ' . $str . ($r > 1 ? 's' : '');
                }
            }
        }

    } 

