<?php

    /***        ____  
    *      ____/ / /_ 
    *     / __  / __ \
    *    / /_/ / /_/ /
    *    \__,_/_.___/ 
    *                 
    * db class - simple sql
    * Copyright © 2014-2016 Dubravko Loborec
    * */    

    class DData
    {

        /* q is the alias of queryAll - returns false if no result */
        public static function query($sql, $params=array()){
            $connection=Yii::app()->db;
            $command=$connection->createCommand($sql);
            return $command->queryAll(true, $params);
        }

        /* r is the alias of queryRow - returns false if no result */
        public static function row($sql, array $params=array()){
            $connection=Yii::app()->db;
            $command=$connection->createCommand($sql);
            return $command->queryRow(true, $params); 
        }   

        /* e is the alias of execute - returns number of rows affected by the execution */
        public static function exec($sql, $params=array()){
            $connection=Yii::app()->db;
            $command=$connection->createCommand($sql);
            return $command->execute($params); 
        }

        public static function count($table, $where=array()){ 
            $x=array();
            $params=array(); 
            $i=1;

            if (count($where)>0){
                foreach ($where as $key=>$value){ 
                    if ($value===null)
                        $x[]="(`${key}` IS NULL)"; 
                    else {
                        $x[]="(`${key}`=:param_$i)"; //:param_$i FIX 5/2015
                        $params[":param_$i"]=$value;
                        $i++; 
                    }
                }
                $w="WHERE ".implode(' AND ',$x);
            }
            else
                $w='';

            $sql="SELECT Count(*) as n FROM `${table}` ".$w;  

            $record=self::row($sql, $params);
            
            return ($record['n']===null)?0:(int)$record['n'];
        } 

        public static function max($table, $field, $where=array()){ 
            $x=array();
            $params=array(); 
            $i=1;

            if (count($where)>0){
                foreach ($where as $key=>$value){ 
                    if ($value===null)
                        $x[]="(`${key}` IS NULL)"; 
                    else {
                        $x[]="(`${key}`=:param_$i)"; //:param_$i FIX 5/2015
                        $params[":param_$i"]=$value;
                        $i++;  
                    }
                }
                $w="WHERE ".implode(' AND ',$x);
            }
            else
                $w='';

            $sql="SELECT max(`${field}`) as n FROM `${table}` ".$w;   
            $record=self::row($sql, $params);
            
            return ($record['n']===null)?0:(int)$record['n'];
        } 
        
            public static function sum($table, $field, $where=array()){ 
            $x=array();
            $params=array(); 
            $i=1;

            if (count($where)>0){
                foreach ($where as $key=>$value){ 
                    if ($value===null)
                        $x[]="(`${key}` IS NULL)"; 
                    else {
                        $x[]="(`${key}`=:param_$i)"; //:param_$i FIX 5/2015
                        $params[":param_$i"]=$value;
                        $i++;  
                    }
                }
                $w="WHERE ".implode(' AND ',$x);
            }
            else
                $w='';

            $sql="SELECT sum(`${field}`) as n FROM `${table}` ".$w;   
            $record=self::row($sql, $params);
            
            return ($record['n']===null)?0:(int)$record['n'];
        } 

        public static function insert($table, $data=array(), $update=array(), $ignore=false){  
        /*
        If you use INSERT IGNORE, then the row won't actually be inserted if it results in a duplicate key. But the statement won't generate an error. It generates a warning instead.
        */ 
            $x=array();  
            $y=array();
            $z=array();   
            $params=array();

            foreach ($data as $key=>$value){
                $x[]="`${key}`";
                $y[]=":${key}";
                $params[":${key}"]=$value; 
            } 

            if (count($update)>0){
                foreach ($update as $key=>$value){
                    $z[]="`${key}`=:${key}"; 
                    $params[":${key}"]=$value;
                }
                $u='ON DUPLICATE KEY UPDATE '.implode(',',$z); 
            }
            else
                $u='';


            $ignore_str=$ignore==true?'IGNORE ':'';

            $sql="INSERT ${ignore_str}INTO `${table}` (".implode(',',$x).") VALUES (".implode(',',$y).") ".$u;

            return self::exec($sql, $params);
        } 

        public static function update($table, $data=array(), $where=array()){
            $x=array();  
            $y=array(); 
            $params=array();
            $i=1;

            foreach ($data as $key=>$value){
                $x[]="`${key}`=:param_$i"; //:param_$i FIX 5/2015
                $params[":param_$i"]=$value;
                $i++;
            }

            if (count($where)>0){
                foreach ($where as $key=>$value){
                    if ($value===null)
                        $y[]="(`${key}` IS NULL)"; 
                    else {
                        $y[]="(`${key}`=:param_$i)"; 
                        $params[":param_$i"]=$value;
                        $i++;
                    }
                }
                $w='WHERE '.implode(' AND ',$y); 
            }
            else
                $w='';

            $sql="UPDATE `${table}` SET ".implode(',',$x).' '.$w;

            return self::exec($sql, $params);     
        }

        /* summer 2012 */
        public static function delete($table, $where=array()){ 
            $y=array(); 
            $params=array();
            $i=1;

            if (count($where)>0){
                foreach ($where as $key=>$value){
                    if ($value===null)
                        $y[]="(`${key}` IS NULL)"; 
                    else {
                        $y[]="(`${key}`=:param_$i)"; //:param_$i FIX 5/2015
                        $params[":param_$i"]=$value;
                        $i++;
                    }
                }
                $w='WHERE '.implode(' AND ',$y); 
            } 

            $sql="DELETE FROM `${table}` ".$w;
            return self::exec($sql, $params);     
        }

        public static function fields($table, $fields, $where=array()){ 
            $y=array(); 
            $params=array();
            $i=1;

            $aa=explode(',',$fields);
            foreach ($aa as &$a){
                $a='`'.trim($a).'`';
            }

            $f=implode(',', $aa);

            if (count($where)>0){
                foreach ($where as $key=>$value){
                    if ($value===null)
                        $y[]="(`${key}` IS NULL)"; 
                    else {
                        $y[]="(`${key}`=:param_$i)"; //:param_$i FIX 5/2015
                        $params[":param_$i"]=$value;
                        $i++;
                    }
                }
                $w='WHERE '.implode(' AND ',$y); 
            } else
                $w='';

            $sql="SELECT $f FROM `${table}` ".$w;
            return self::row($sql, $params);     
        }

        public static function last_insert_id(){
            $record=self::row("SELECT LAST_INSERT_ID() as id");  
            return $record['id'];
        }

        public static function numerator($table, $field, $where=array(), $start=1){ 
            $n=self::max($table, $field, $where)+1;
            if ($n<$start)
                $n=$start;
            
            return $n;   
        }

        public static function field($table, $field, $default, $where=array()){
            $record=self::fields($table, $field, $where);
            return (($record===false) or ($record[$field]===null))?$default:$record[$field];  
        }
        
        
     public static function exists($table, $where=array()){ 
            $y=array(); 
            $params=array();
            $i=1;

              if (count($where)>0){
                foreach ($where as $key=>$value){
                    if ($value===null)
                        $y[]="(`${key}` IS NULL)"; 
                    else {
                        $y[]="(`${key}`=:param_$i)"; //:param_$i FIX 5/2015
                        $params[":param_$i"]=$value;
                        $i++;
                    }
                }
                $w='WHERE '.implode(' AND ',$y); 
            } else
                $w='';
              
            $sql="SELECT EXISTS (SELECT 1 FROM `${table}` ".$w.') x';
            $result=self::row($sql, $params); 
            
            return $result['x']==1;    
        }
        
    } 

