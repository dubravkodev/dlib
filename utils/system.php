<?php
    /**
    * This file contains the system unit.
    *
    * @author Dubravko Loborec <info@dubravkodev.com>
    * @link http://www.dubravkodev.com/
    * @copyright 2014-2017 Dubravko Loborec
    * @license http://www.dubravkodev.com/license/
    */

    /**
    * Function returns the left part of a string.
    * 
    * @param string $string
    * @param integer $n part length
    * @return string
    */
    function lefts($string, $n){
        return mb_substr($string, 0, $n);
    }

    /**
    * Function returns the right part of a string.
    * 
    * @param string $string
    * @param integer $n part length
    * @return string
    */
    function rights($string, $n){
        return mb_substr($string, mb_strlen($string) - $n, $n);
    }

    /**
    * Function returns a string with the part removed from the left side.
    * 
    * @param string $string
    * @param integer $n part length
    * @return string
    */
    function ldel($string, $n){
        return mb_substr($string, $n, mb_strlen($string) - $n);
    }

    /**
    * Function returns a string with the part removed from the right side.
    * 
    * @param string $string
    * @param integer $n part length
    * @return string
    */
    function rdel($string, $n){
        return mb_substr($string, 0, mb_strlen($string) - $n);
    }

    /**
    * Function returns a string from the right side of the string delimited with separator.
    * 
    * @param mixed $string
    * @param mixed $separator
    * @return string
    */
    function rpart($string, $separator){
        $p=mb_strrpos($string,$separator);
        if ($p===false)
            return $string;
        else  
            return (ldel($string,$p+mb_strlen($separator)));
    }

    /**
    * Function returns a string from the left side of the string delimited with separator.
    * 
    * @param mixed $string
    * @param mixed $separator
    * @return string
    */
    function lpart($string, $separator){
        $p=mb_strpos($string,$separator);
        if ($p===false)
            return $string;
        else  
            return (lefts($string,$p));
    }

    /**
    * Shortcut for the PHP strlen function.
    * 
    * @param string $string
    * @return int
    */
    function len($string){
        return mb_strlen($string);   
    }

    /**
    * Function returns truncated string with specified width
    * 
    * @param string $string
    * @param int $length
    * @param booldean $stopanywhere
    * @return string
    */
    function add_ellipsis($string, $length, $stopanywhere=false) {
        return mb_strimwidth($string, 0, $length, "...");
    }

    /**
    * Function generates a random string.
    * 
    * @param int $length
    * @return string
    */
    function rand_string($length = 8){
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $string = "";    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }

    /**
    * Mini mustache like implementation
    * Function replaces a string inside the curly brackets {{{...}}} with params in array.
    * Example:
    * echo m('Today is a {{{var1}}} day', array('var1'=>'nice'));
    * Result:
    * Today is a nice day
    * 
    * @param string $template
    * @param array $params
    * @return string
    */
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

    /**
    * Copy a file, or recursively copy a folder and its contents
    * @param       string   $source    Source path
    * @param       string   $dest      Destination path
    * @param       string   $permissions New folder creation permissions
    * @return      bool     Returns true on success, false on failure
    */
    function xcopy($source, $dest, $permissions = 0755){

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

    /**
    * Function extracts file name with the extension from the specified string.
    * 
    * @param string $fileName
    * @return string file name with the extension
    */
    function extract_file_name($fileName){
        return rpart($fileName,'/');  
    }

    /**
    * Function extracts file name without the extension from the specified string.
    * 
    * @param string $fileName
    * @return string file name without the extension
    */ 
    function extract_file_name2($fileName){ 
        $s=extract_file_name($fileName);
        $i=mb_strripos($s, '.');
        return lefts($s,$i);
    }

    /**
    * Function extracts file extension from the specified string.
    * 
    * @param string $fileName
    * @param boolean $toLowercase
    * @return string extension
    */
    function extract_file_ext($fileName, $toLowercase=true){
        $a=explode('.',$fileName); 
        return $toLowercase?mb_strtolower(end($a)):end($a); 
    }

    /**
    * Function extracts file directory from the specified string.
    * 
    * @param mixed $fileName
    * @return string 
    */
    function extract_file_dir($fileName){
        $i=mb_strripos($fileName, '/');
        return lefts($fileName,$i);  
    }

    /**
    * Function deletes multilple files using wildcards.
    * 
    * @param mixed $str
    */
    function del_files($str){
        foreach(glob($str) as $fn){
            unlink($fn);
        }
    }

    /**
    * The list_files function searches for all the pathnames matching pattern.
    * 
    * @param mixed $str
    * @return Returns an array containing the matched files/directories, an empty array if no file matched or FALSE on error.
    */
    function list_files($pattern){
        return glob($pattern);
    }

    /**
    * The file_to_str function reads the file into a string.
    * 
    * @param string $filename
    */
    function file_to_str($filename){
        return file_get_contents($filename);
    }

    /**
    * The str_to_file function writes the file from the string.
    * 
    * @param string $filename
    * @param boolean returns true od successful, false on unsuccessful
    */
    function str_to_file($filename,$s){
        $fh = fopen($filename, 'w');
        $saved= (fwrite($fh, $s))==false?false:true; 
        fclose($fh);
        return $saved;
    }

    /**
    * Get file from $url with curl.
    * 
    * @param string $url
    * @return mixed string content or false
    */
    function curl_get_file_contents($url){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return false;
    }

    /**
    * Function get_remote_image_size reads image size from $url using image header data.
    * 
    * @param mixed $image_url
    */
    function get_remote_image_size($image_url){
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

    /**
    * Function dump is usefull for simple debugging to the file.
    * 
    * @param mixed $var
    * @param mixed $fileName
    */
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

    /**
    * Function array_split splits the array into the pieces.
    * 
    * @param array $array
    * @param integer $pieces
    * @return array new array
    */
    function array_split($array, $pieces=2){
        if ($pieces < 2) 
            return array($array); 
        $newCount = ceil(count($array)/$pieces); 
        $a = array_slice($array, 0, $newCount); 
        $b = array_split(array_slice($array, $newCount), $pieces-1); 
        return array_merge(array($a),$b); 
    } 

    /**
    * Function array_chunk_vertical splits the array into the columns.
    * 
    * @param mixed $data
    * @param mixed $columns
    * @return array new array
    */
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

    /**
    * Function now is shortcut to PHP function time.
    * @return timestamp
    */
    function now(){
        return time();
    }

    /**
    * Function add_minutes adds minutes to the given timestamp 
    * 
    * @param timestamp $timestamp
    * @param integer $minutes
    * @return timestamp
    */
    function add_minutes($timestamp, $minutes) {
        return strtotime("+${minutes} minutes", $timestamp);
    }

    /**
    * Function sub_minutes subtracts minutes from the given timestamp 
    * 
    * @param timestamp $timestamp
    * @param integer $minutes
    * @return timestamp
    */
    function sub_minutes($timestamp, $minutes) {
        return strtotime("-${minutes} minutes", $timestamp);
    }

    /**
    * Function add_days add minutes days the given timestamp 
    * 
    * @param timestamp $timestamp
    * @param integer $days
    * @return timestamp
    */
    function add_days($timestamp, $days=0) {
        return strtotime("+${days} days", $timestamp);
    }

    /**
    * Function sub_days subtracts days from the given timestamp 
    * 
    * @param timestamp $timestamp
    * @param integer $days
    * @return timestamp
    */
    function sub_days($timestamp, $days) {
        return strtotime("-${days} days", $timestamp);
    }

    /**
    * Function add_months adds months to the given timestamp 
    * 
    * @param timestamp $timestamp
    * @param integer $months
    * @return timestamp
    */
    function add_months($timestamp, $months) {
        return strtotime("+${months} months", $timestamp);
    }

    /**
    * Function days_between calculates the number of days betwssn two dates.
    * 
    * @param mixed $fromDate
    * @param mixed $toDate
    * @param mixed $abs
    * @return integer number of days
    */
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

    /**
    * Function converts the timestamp to mysql datetime string.
    * 
    * @param mixed $timestamp
    * @return string
    */
    function timestamp_to_mysql_datetime($timestamp){
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
    * Function converts the mysql datetime string to timestamp
    * 
    * @param mixed $mysql_datetime
    * @return timestamp
    */
    function mysql_datetime_to_timestamp($mysql_datetime){
        list($date, $time) = explode(' ', $mysql_datetime);
        list($year, $month, $day) = explode('-', $date);
        list($hour, $minute, $second) = explode(':', $time);
        return mktime($hour, $minute, $second, $month, $day, $year);    
    }

    /**
    * Function converts the timestamp to mysql date string.
    * 
    * @param mixed $timestamp
    * @return string
    */
    function timestamp_to_mysql_date($timestamp){
        return date('Y-m-d', $timestamp);
    }

    /**
    * Function converts the mysql date string to timestamp
    * 
    * @param mixed $mysql_date
    * @return timestamp
    */
    function mysql_date_to_timestamp($mysql_date){
        list($year, $month, $day) = explode('-', $mysql_date);
        return mktime(0, 0, 0, $month, $day, $year); 
    }

    /*
    function format_mysql_datetime($dateFormat, $datetime){
        return date($dateFormat, mysql_datetime_to_timestamp($datetime));
    } 

    function format_mysql_date($dateFormat, $date){
        return date($dateFormat, mysql_date_to_timestamp($date));
    } 
    */

    /**
    * Function time_elapsed_string creates the string containing textual representation of the period.
    * 
    * @param mixed $ptime
    * @return string
    */
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

    /**
    * The function examines whether the number is odd or not.
    *   
    * @param mixed $num
    * @return boolean
    */
    function is_odd($num){
        return( $num & 1 );
    }

    /**
    * Function is a shortcut to PHP function round.
    * 
    * @param mixed $d
    * @param integer $precision
    */
    function round_float($d, $precision=2){
        return round($d, $precision); 
    }

    /* url handling*/

    /**
    * Base64 encoding suitable for encoding url parameters
    * Taken from: http://php.net/manual/en/function.base64-encode.php
    * 
    * @param string $data
    * @return string
    */
    function base64url_encode($data){ 
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 

    /**
    * Base64 decoding suitable for decoding url parameters
    * Taken from: http://php.net/manual/en/function.base64-encode.php
    * 
    * @param string $data
    * @return string
    */
    function base64url_decode($data){ 
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 

    /**
    * Function converts the string in a form convenient for use in the url
    * Taken from: http://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
    * 
    * @param mixed $text
    * @return string
    */
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

    /**
    * Checks and sets the url scheme if missing
    * 
    * @param string $url
    * @return string
    */
    function fix_url_scheme($url){
        if  ( $ret = parse_url($url) ){
            if ( !isset($ret["scheme"]) )
            {
                $url = "http://{$url}";
            }
        }   
        return $url;
    }

    /**
    * Generates random color
    * 
    */
    function rand_color(){
        mt_srand((double)microtime()*1000000);
        $c = '';
        while(strlen($c)<6){
            $c .= sprintf("%02X", mt_rand(100, 200));
        }
        return $c;
    }

    function normal_dir($dir){
        $c=rights($dir,1);
        if (in_array($c, ['\\', '/'])){
            return $dir;
        }
        else
            return $dir.DIRECTORY_SEPARATOR;   
    }