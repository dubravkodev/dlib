<?php

    class DArray
    {

        /**
    * Function array_split splits the array into the pieces.
    * 
    * @param array $array
    * @param integer $pieces
    * @return array new array
    */
    public static function array_split($array, $pieces=2){
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
    public static function array_chunk_vertical($data, $columns) {
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

        
    } 

