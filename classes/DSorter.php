<?php

    class DSorter{

        public static function up($table, $key_fields_values=array(), $where=array(), $sort_field){
            return self::_move($table, $key_fields_values, $where, $sort_field, true);
        }

        public static function down($table, $key_fields_values=array(), $where=array(), $sort_field){
            return self::_move($table, $key_fields_values, $where, $sort_field, false);
        }

        private static function _move($table, $key_fields_values=array(), $where=array(), $sort_field, $up){

            //select
            $select_fields=array();
            foreach($key_fields_values as $key=>$value){
                $select_fields[]=$key;     
            }
            $select_fields[]=$sort_field;
            
            foreach ($select_fields as &$select_field){
              $select_field='`'.$select_field.'`';     
            }
            
            $select_fields_str=implode(', ',$select_fields);

            //where
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

            $sql="SELECT $select_fields_str FROM ${table} $w ORDER BY `${sort_field}`";
            if ($up)
                $sql.=' DESC';

            $records=db::q($sql, $params);

            for ($i = 0; $i < count($records); $i++) {

                $record=$records[$i];

                $found=true;
                foreach($key_fields_values as $key=>$value){
                    if ($record[$key]!==$value){
                        $found=false;
                    }   
                } 

                if ($found){
                    $sort_order=$record[$sort_field]; //spremimo stari sort order
                    if (isset($records[$i+1])){
                        $record=$records[$i+1];

                        $next_key_fields_values=array();
                        foreach($key_fields_values as $key=>$value){
                            $next_key_fields_values[$key]=$record[$key];   
                        }   

                        $next_sort_order=$record[$sort_field]; //spremimo novi sort order

                        db::update($table, array(
                            'sort_order'=>db::numerator($table, $sort_field, $key_fields_values)+1,
                            ), $next_key_fields_values
                        );
                        //
                        db::update($table, array(
                            'sort_order'=>$next_sort_order,
                            ), $key_fields_values);
                        //
                        db::update($table, array(
                            'sort_order'=>$sort_order,
                            ), $next_key_fields_values);

                        return true;
                    }
                }
            }

            return false;
        }     
    }



    /*     private function question_move($question_id, $up){
    $sql='SELECT * FROM super_pairing_test_example_question ORDER BY sort_order';
    if ($up)
    $sql.=' DESC';

    $params=array();
    $records=db::q($sql, $params);

    for ($i = 0; $i < count($records); $i++) {

    $record=$records[$i];

    if ($record['question_id']===$question_id){
    $sort_order=$record['sort_order'];
    if (isset($records[$i+1])){
    $record=$records[$i+1];
    $down_question_id=$record['question_id'];
    $down_sort_order=$record['sort_order'];

    db::update('super_pairing_test_example_question', array(
    'sort_order'=>9999,
    ),array(
    'question_id'=>$down_question_id,
    ));
    //
    db::update('super_pairing_test_example_question', array(
    'sort_order'=>$down_sort_order,
    ),array(
    'question_id'=>$question_id,
    ));
    //
    db::update('super_pairing_test_example_question', array(
    'sort_order'=>$sort_order,
    ),array(
    'question_id'=>$down_question_id,
    ));

    return true;
    }
    }
    }

    return false;
}        */