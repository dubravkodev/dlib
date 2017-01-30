<?php

    /**
    * Copyright © 2014-2016 Dubravko Loborec
    */

    class DScript{
        
        public static function loader(){
            return "$('body').append('<div id=\'dynamicLoader\' class=\'loading\'>Loading</div>');";
        }

        public static function ready($text){
            return CHtml::script("jQuery(document).ready(function(){".$text."});")."\n"; 
        }

        public static function panel_dialog($title, $options=array()){
            $options['showCollapseButton']=false;
            $options['showCloseButton']=true;

            $dialogSizeClass='';
            if (isset($options) and isset($options['dialogSize'])){
                if ($options['dialogSize']=='large')
                    $dialogSizeClass='modal-lg';
                else if ($options['dialogSize']=='small')
                    $dialogSizeClass='modal-sm';

                    unset($options['dialogSize']);
            }

            $panel=panel($title, $options);


            $panel=preg_replace("/\r?\n/", "\\n", addslashes($panel));

            $html=<<<EOT
            <div id="dynamicModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="confirm-modal" aria-hidden="true">
                <div class="modal-dialog $dialogSizeClass">
                   $panel
                </div>
            </div>
EOT
            ;

            $js=array(); 
            $js[]="var html ='';";

            $lines=explode("\n",$html);
            foreach ($lines as $line){
                $js[]="html += '".trim($line)."';";
            }

            $js[]=<<<EOT
                $("#dynamicModal").remove();   /* fix prethodnog dialoga */
                $(".modal-backdrop").remove(); /* fix prethodnog dialoga */ 
            
                $('body').append(html);
                
                $("#dynamicModal").modal('show');
                
                $('#dynamicModal').on('hidden.bs.modal', function (e) {
                    $(this).remove();
                }); 
                
            /*    $('#dynamicModal').on('shown.bs.modal', function (e) {
                    $(this).find(".panel-body :input:not('[data-not-focused=1]'):not('.tt-hint'):visible").first().focus();
                });*/
EOT
            ;  

            return implode("\n", $js);       
        }

        public static function hide_dialogs(){
            return "$('#dynamicModal').modal('hide');"; //bootbox.hideAll();    
        }    

        public static function redirect($url){
            return "window.location.href='${url}';";
        }

        public static function reload(){
            return "location.reload();";
        }

        public static function back(){
            return "history.back();";
        }

        public static function alert($text){
            return "alert('${text}');";
        }

        public static function refresh_grid($grid_id, $options=null){  
            $params=array();

            if (isset($options) and (isset($options['data']))){
                $data=$options['data'];

                $out=array(); 
                foreach($data as $key=>$value){
                    if (is_string($value)){
                        $out[]="'${key}':'$value'"; 
                    }
                    else
                    {
                        $out[]="'${key}':$value"; 
                    }
                }
                $d= implode(',', $out);

                $params[]="data: { ${d} }";
            };

            if (isset($options) and (isset($options['onSuccess']))){
                $onSuccess=$options['onSuccess'];
                $params[]="complete: function(jqXHR, status) {if (status=='success'){ ${onSuccess} }}";
            };

            $params=implode(',',$params); 
            return  "jQuery.fn.yiiGridView.update('${grid_id}', { ${params} });";
        }

       public static function post_form($id, $url){
            return "post_form('$id', '$url');";   
       }

    }
