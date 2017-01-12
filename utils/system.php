<?php

    /**
    * Copyright © 2014-2016 Dubravko Loborec
    */

    /* string utils */

    function lefts($string, $n){
        return mb_substr($string, 0, $n);
    }

    function rights($string, $n){
        return mb_substr($string, mb_strlen($string) - $n, $n);
    }

    function ldel($string, $n){
        return mb_substr($string, $n, mb_strlen($string) - $n);
    }

    function rdel($string, $n){
        return mb_substr($string, 0, mb_strlen($string) - $n);
    }

    function rpart($string, $separator){
        $p=mb_strrpos($string,$separator);
        if ($p===false)
            return $string;
        else  
            return (ldel($string,$p+mb_strlen($separator)));
    }

    function lpart($string, $separator){
        $p=mb_strpos($string,$separator);
        if ($p===false)
            return $string;
        else  
            return (lefts($string,$p));
    }

    function len($string){
        return mb_strlen($string);   
    }

    function add_ellipsis($string, $length, $stopanywhere=false) {
        //truncates a string to a certain char length, stopping on a word if not specified otherwise.
        /*   if (mb_strlen($string) > $length) {
        //limit hit!
        $string = mb_substr($string,0,($length -3));
        if ($stopanywhere) {
        //stop anywhere
        $string .= '...';
        } else{
        //stop on a word.
        $string = mb_substr($string,0,mb_strrpos($string,' ')).'...';
        }
        }
        return $string; */
        return mb_strimwidth($string, 0, $length, "...");
    }

    function rand_string($length = 8){
        /**
        * rand_string
        * generates random string
        */
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $string = "";    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }

    function rand_color(){
        /* generates random color */
        mt_srand((double)microtime()*1000000);
        $c = '';
        while(strlen($c)<6){
            $c .= sprintf("%02X", mt_rand(100, 200));
        }
        return $c;
    }

    /* file utils */

    function xcopy($source, $dest, $permissions = 0755){
        /**
        * Copy a file, or recursively copy a folder and its contents
        * @param       string   $source    Source path
        * @param       string   $dest      Destination path
        * @param       string   $permissions New folder creation permissions
        * @return      bool     Returns true on success, false on failure
        */

        // Check for symlinks
        if (is_link($source)){
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            xcopy("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }

    function extract_file_name($fileName){
        return rpart($fileName,'/');  
    }

    function extract_file_name2($fileName){ 
        //bez extenzije
        $s=extract_file_name($fileName);
        $i=mb_strripos($s, '.');
        return lefts($s,$i);
    }

    function extract_file_ext($fileName, $toLowercase=true){
        $a=explode('.',$fileName); 
        return $toLowercase?mb_strtolower(end($a)):end($a); 
    }

    function extract_file_dir($fileName){

        $i=mb_strripos($fileName, '/');
        return lefts($fileName,$i);  
    }

    function del_files($str){
        //deletes multilple files using wildcards
        foreach(glob($str) as $fn){
            unlink($fn);
        }
    }

    function list_files($str){ // D:/images/*.*
        return glob($str);
    }

    function file_to_str($filename){
        /**
        * read file to string
        */
        /*    $fh = fopen($filename, 'r');
        $value = fread($fh, filesize($filename));
        fclose($fh);  
        return $value;    */

        return file_get_contents($filename);
    }

    function str_to_file($filename,$s){
        /**
        * save string to file
        * returns true od successful, false on unsuccessful
        */
        $fh = fopen($filename, 'w');
        $saved= (fwrite($fh, $s))==false?false:true; 
        fclose($fh);
        return $saved;
    }

    function curl_get_file_contents($url){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return FALSE;
    }

    function get_remote_image_size($image_url) {
        $count=0;
        $handle = fopen ($image_url, "rb");
        $contents = "";
        if ($handle) {
            do {
                $count += 1;
                $data = fread($handle, 8192);
                if (strlen($data) == 0) {
                    break;
                }
                $contents .= $data;
            } while(true);
        } else { return false; }
        fclose ($handle);

        $im = ImageCreateFromString($contents);
        if (!$im) { return false; }
        $gis[0] = ImageSX($im);
        $gis[1] = ImageSY($im);
        // array member 3 is used below to keep with current getimagesize standards
        $gis[3] = "width={$gis[0]} height={$gis[1]}";
        ImageDestroy($im);
        return $gis;
    }

    /* log utils*/

    function dump($var=null,$fileName=null){
        $fileName=(is_null($fileName)?'dump '.date('Y-m-d',time()):$fileName);

        $result="/**\n";
        $result.='* '.date('Y-m-d H:i:s:u',time())."\n";
        $result.="*/\n\n";

        ob_start();

        if ($var===null){
            echo "\$_GET:"; 
            var_dump($_GET);

            echo "\n\$_POST:";  
            var_dump($_POST); 

            echo "\n\$_FILES:";  
            var_dump($_FILES);
        }    
        else    
            var_dump($var);

        $result .= ob_get_contents();
        $result .="\n";
        ob_get_clean();  
        file_put_contents($fileName,$result,FILE_APPEND);   
    } 

    /* arrays */

    function array_split($array, $pieces=2){
        if ($pieces < 2) 
            return array($array); 
        $newCount = ceil(count($array)/$pieces); 
        $a = array_slice($array, 0, $newCount); 
        $b = array_split(array_slice($array, $newCount), $pieces-1); 
        return array_merge(array($a),$b); 
    } 

    function array_chunk_vertical($data, $columns) {
        $n = count($data) ;
        $per_column = floor($n / $columns) ;
        $rest = $n % $columns ;

        // The map
        $per_columns = array( ) ;
        for ( $i = 0 ; $i < $columns ; $i++ ) {
            $per_columns[$i] = $per_column + ($i < $rest ? 1 : 0) ;
        }

        $tabular = array( ) ;
        foreach ( $per_columns as $rows ) {
            for ( $i = 0 ; $i < $rows ; $i++ ) {
                $tabular[$i][ ] = array_shift($data) ;
            }
        }

        return $tabular ;
    }

    /* datetime utils */

    function now(){ //:timestamp
        return time();
    }

    function format_timestamp($dateFormat, $timestamp){
        return date($dateFormat, $timestamp);
    }

    function add_minutes($timestamp,$minutes) {
        return strtotime("+${minutes} minutes", $timestamp);
    }

    function sub_minutes($timestamp, $minutes) {
        return strtotime("-${minutes} minutes", $timestamp);
    }

    function add_months($timestamp,$months) {
        return strtotime("+${months} months", $timestamp);
    }

    function add_days($timestamp, $days=0) {
        return strtotime("+${days} days", $timestamp);
    }

    function sub_days($timestamp, $days) {
        return strtotime("-${days} days", $timestamp);
    }

    function days_between($fromDate, $toDate, $abs=true){

        if ($abs)
            $distanceInSeconds = round(abs($toDate - $fromDate));
        else
            $distanceInSeconds = round($toDate - $fromDate);

        $distanceInMinutes = round($distanceInSeconds / 60);
        $distanceInHours = round($distanceInMinutes / 60);
        $distanceInDays= round($distanceInHours / 24);
        return $distanceInDays; 
    }

    function timestamp_to_mysql_datetime($timestamp){
        return date('Y-m-d H:i:s', $timestamp);
    }

    function timestamp_to_mysql_date($timestamp){
        return date('Y-m-d', $timestamp);
    }

    function mysql_date_to_timestamp($mysql_date){
        //$a = explode("-",$datetime);
        //return mktime(0,0,0,$date[1],$date[2],$date[0]); //mktime ima bug, radi do 2032

        list($year, $month, $day) = explode('-', $mysql_date);

        return mktime(0, 0, 0, $month, $day, $year); 
    }

    function mysql_datetime_to_timestamp($mysql_datetime){
        /*   $val = explode(" ",$mysql_datetime);
        $date = explode("-",$val[0]);
        $time = explode(":",$val[1]);
        return mktime($time[0],$time[1],$time[2],$date[1],$date[2],$date[0]);     */

        list($date, $time) = explode(' ', $mysql_datetime);
        list($year, $month, $day) = explode('-', $date);
        list($hour, $minute, $second) = explode(':', $time);

        return mktime($hour, $minute, $second, $month, $day, $year);    
    }

    function format_mysql_datetime($dateFormat, $datetime){
        return date($dateFormat, mysql_datetime_to_timestamp($datetime));
    } 

    function format_mysql_date($dateFormat, $date){
        return date($dateFormat, mysql_date_to_timestamp($date));
    } 

    function time_elapsed_string($ptime) {
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

    /* numbers */

    function is_odd($num){
        return( $num & 1 );
    }

    function round_float($d, $precision=2){
        return round($d, $precision); 
    }

    /* url */

    function base64url_encode($data){ 
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 

    function base64url_decode($data){ 
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 


    function slugify($text){
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /*  function m($template, $params){

    require_once (C_LIBRARY.'/mustache/mustache.php'); 
    //require_once (C_LIBRARY.'/Mustache/Autoloader.php'); 
    $m = new Mustache_Engine;
    return $m->render($template, $params);
    }     */

    function m($template, $params){
        $tagRegex = "|{{{(.*?)}}}|is";
        return preg_replace_callback(
            $tagRegex,
            function ($matches) use ($params) {
                if (key_exists($matches[1], $params))
                    return $params[$matches[1]];
                else
                    return '';
            },
            $template
        );
    }

    function unique_id(){
        //vraća string duljine 40 karaktera
        return sha1(uniqid(mt_rand(), true));   
    }

    /*
    function eval_php_tags($string){
    return  preg_replace_callback(
    '/<\?php(.+?)\?>/is',
    function ($matches) {
    ob_start();
    eval($matches[1]);
    $result = ob_get_contents();
    ob_get_clean(); 
    return $result;
    },
    $string
    );
    }
    */

    function fix_url($url){
        if  ( $ret = parse_url($url) ) {

            if ( !isset($ret["scheme"]) )
            {
                $url = "http://{$url}";
            }
        }   

        return $url;
    }
