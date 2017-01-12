<?php

    class DDrawer{

        public static function panel($title, $options=array()){

            $n=sread('counter',0)+1;
            swrite('counter',$n);
            $id=(isset($options) and isset($options['id']))?$options['id']:'panel_'.$n; 

            $_html=(isset($options) and isset($options['html']))?$options['html']:'';

            $help=(isset($options) and isset($options['help']))?$options['help']:'';  

            $buttons=(isset($options) and isset($options['buttons']))?$options['buttons']:'';
            if (is_array($buttons))
                $buttons=implode(' ',$buttons);

            $panelClass=(isset($options) and isset($options['panelClass']))?$options['panelClass']:'bg-white';
            $panelBodyStyle=(isset($options) and isset($options['panelBodyStyle']))?$options['panelBodyStyle']:'';

            if ($panelClass=='bg-white'){
                $defaultTitleColorClass='color-black';
            }
            else
            {
                $defaultTitleColorClass='color-white';  
            }
           
            $titleColorClass=(isset($options) and isset($options['titleColorClass']))?$options['titleColorClass']:$defaultTitleColorClass;

            $panelStyle=(isset($options) and isset($options['panelStyle']))?$options['panelStyle']:'';
            


            $expanded=(isset($options) and isset($options['expanded']))?$options['expanded']:true;
            $collapsed_str=$expanded?'':'collapsed';
            $collapsed_in=$expanded?'in':'';

            $wrapperClass=(isset($options) and isset($options['wrapperClass']))?$options['wrapperClass']:false; 

            $panelBodyClass=(isset($options) and isset($options['panelBodyClass']))?$options['panelBodyClass']:'';

            $overflow=(isset($options) and isset($options['overflow']))?$options['overflow']:0;

            if ($overflow!=0){
                //$_html="<div style='overflow-y: auto; max-height:${overflow}px;'>".$_html."</div>"; 
                $panelBodyStyle=trim($panelBodyStyle);
                if (rights($panelBodyStyle,1)!==';'){
                    $panelBodyStyle=$panelBodyStyle.=';'; 
                }

                $panelBodyStyle=$panelBodyStyle.="overflow-y: auto; max-height:${overflow}px;"; 
            }


            $showCollapseButton=(isset($options) and isset($options['showCollapseButton']))?$options['showCollapseButton']:true;
            if ($showCollapseButton){
                $buttons.= "<span class='btn-panel-toolbar panel-toggle $titleColorClass '></span>";
             /*  $buttons.= l('', "#${id}_collapse", array(
                    //'id'=>"${id}_collapse_button",
                    'data-toggle'=>"collapse",
                    'class'=>"btn-panel-toolbar panel-toggle $titleColorClass $collapsed_str",
                ));*/
            }

            $html=array();
            
            if ($wrapperClass!==false){
                $html[]="<div class='${wrapperClass}'>";  
            }

            $html[]=<<<EOT
            <div id="$id" class="panel panel-expandable $panelClass" style="$panelStyle">
                <a class="panel-heading clearfix $collapsed_str" href="#${id}_collapse" id="${id}_collapse_button" data-toggle="collapse">
                    <span class="panel-title $titleColorClass">$title</span>
                    <div class="panel-toolbar">
                        $buttons
                    </div>
                </a>
EOT;
            if ($help!=''){ 
                $html[]=<<<EOT
                    <div id='${id}_collapse_help' class="panel-help panel-collapse collapse $help_collapsed_in $titleColorClass">
                          <div class='panel-help-overflow'> 
                                $help
                          </div>
                    </div>
EOT;
            };

    


            $html[]=<<<EOT
                <div id="${id}_collapse" class="panel-collapse collapse $collapsed_in">
EOT;







            $html[]=<<<EOT
                    <div id="${id}_panel_body" class="panel-body $panelBodyClass" style="$panelBodyStyle">
                        $_html
                    </div>
                </div>   
            </div>
EOT;

            if ($wrapperClass!==false){
                $html[]="</div>";  
            }

            if (isset($options) and isset($options['onExpand'])){
                $s=$options['onExpand'];
                $html[]=DScript::ready(<<<EOT
                    $("#${id}_collapse").on("show.bs.collapse", function (){ ${s} });
EOT
                );    
            }

            $html[]=DScript::ready(<<<EOT
                $("#${id}_collapse").on("shown.bs.collapse", function (){ 
                    $('#${id}_splitter').hide();
                });
                $("#${id}_collapse").on("hidden.bs.collapse", function (){ 
                    $('#${id}_splitter').show();
                });
EOT
            );  

            if (isset($options) and isset($options['url'])){
                $url=$options['url'];

                if (isset($options['data'])){
                    $data_arr=$options['data'];

                    $data_arr['YII_CSRF_TOKEN']=token();

                    $x=array();
                    foreach($data_arr as $key=>$value){

                        if (is_string($value)){
                            if (lefts($value,1)=='<'){
                                $value=substr($value,1 , strlen($value)-2); 
                                $x[]="'${key}':${value}";  
                            }
                            else
                                $x[]="'${key}':'${value}'";
                        }  
                        else
                            $x[]="'${key}':${value}";    
                    }   

                    $_data=", {".implode(',',$x).'} ';
                }
                else
                    $_data="";

                $html[]=script(<<<EOT
                        function ${id}_refresh(){
                            $('#${id}_panel_body').prepend("<div class='loading'></div>");
                            $('#${id}_panel_body').load('${url}' ${_data});
                            $("#${id}_collapse").collapse('show');
                        };
EOT
                );                

                if (! $expanded){ 
                    $html[]=DScript::ready(<<<EOT
                        $("#${id}_collapse").on("show.bs.collapse", function (){ 
                            if ($('#${id}_panel_body').html().trim()==''){
                                ${id}_refresh();
                            }
                        });
EOT
                    );    
                }
                else
                {
                    $html[]=DScript::ready(<<<EOT
                    if ($('#${id}_panel_body').html().trim()==''){
                        ${id}_refresh();
                    }
EOT
                    );
                }
            }

            return implode( " ", $html);
        }


    }
?>
