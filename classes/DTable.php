<?php
  
  class DTable{
      
      
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        // table
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        private static function _implode($prefix, $sufix, $array){
            $s='';
            foreach ($array as $a)
                $s.=$prefix.$a.$sufix;
            return $s;
        } 

        public static function table($id, $data, $options){

            if ($id===false)
                $id='';

            if (isset($options['class']))
                $class=$options['class'];
            else
                $class='table';

            if (isset($options['style']))
                $style=$options['style'];
            else
                $style='';

            $explain= isset($options['explain']) and ($options['explain']===true);

            $head=isset($data['head'])?$data['head']:array();
            $body=isset($data['body'])?$data['body']:array();
            $foot=isset($data['foot'])?$data['foot']:array();

            $html=array();
            $html[]="<div class='table-responsive'>";
            $html[]="<table id='$id' class='$class' style='$style'>";

            if (count($head)>0){
                $html[]="<thead>";
                $html[]="<tr>";

                $index=0;
                foreach ($head as $h){
                    $class=$explain!==false?"class='".$id."_thead_tr_td_$index'":'';
                    $html[]="<td $class>$h</td>";
                    $index++;  
                }

                $html[]="</tr>";
                $html[]="</thead>";
            }    

            $html[]="<tbody>";

            $tr_index=0;
            foreach ($body as $bbq){
                $class=$explain!==false?"class='".$id."_tr_$tr_index'":'';
                $html[]= "<tr $class>";

                $s1='';
                $td_index=0;
                foreach ($bbq as $b1){
                    $class=$explain!==false?"class='".$id."_td_$td_index'":'';
                    $s1.="<td $class>".$b1.'</td>'; 
                    $td_index++;    
                }   
                $html[]=$s1;  



                //   $html[]= self::_implode('<td $class>','</td>', $bb);



                $html[]= '</tr>'; 
                $tr_index++; 
            }
            $html[]="</tbody>";  

            if (count($foot)>0){
                $html[]="<tfoot>";
                $html[]="<tr>";
                foreach ($foot as $f)
                    $html[]="<td>$f</td>";
                $html[]="</tr>";
                $html[]="</tfoot>";
            }  

            $html[]="</table>";
            $html[]="</div><!-- /.table-responsive-->";
            return implode("\n", $html);
        }
      
      
  }
  
?>