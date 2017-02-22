<?php

    class DPanel{

        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        // Panel 27.10.2015., 5.2.2016.
        //
        // wrapperClass default false
        // expanded default true
        // help
        // helpExpanded default false
        // filter
        // filterExpanded default false
        // panelClass default bg-grey-500
        // titleColorClass default color-white
        // panelStyle default ""
        // panelBodyClass default bg-white
        // panelBodyStyle default ""
        // showCollapseButton default true
        // url - url od ajax poziva sadržaja
        // buttons - string ili array
        // showCollapseButton default true
        // showCloseButton default false
        // header
        // headerStyle default ""
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

        public static function panel($title, $options=array()){

            //$id=(isset($options) and isset($options['id']))?$options['id']:rand_string(8); 
            $n=sread('counter',0)+1;
            swrite('counter',$n);
            $id=(isset($options) and isset($options['id']))?$options['id']:'panel_'.$n; 

            $_html=(isset($options) and isset($options['html']))?$options['html']:'';

            $help=(isset($options) and isset($options['help']))?$options['help']:'';  

            $titleTag=(isset($options) and isset($options['titleTag']))?$options['titleTag']:'h3';  
            $hideHeading=(isset($options) and isset($options['hideHeading']))?$options['hideHeading']:false;  


            $header=(isset($options) and isset($options['header']))?$options['header']:''; 
            if (is_array($header))
                $header=implode(' ',$header);

            $filter=(isset($options) and isset($options['filter']))?$options['filter']:''; 
            if (is_array($filter))
                $filter=implode(' ',$filter);

            $buttons=(isset($options) and isset($options['buttons']))?$options['buttons']:'';
            if (is_array($buttons))
                $buttons=implode(' ',$buttons);

            $panelClass=(isset($options) and isset($options['panelClass']))?$options['panelClass']:'';//bg-white
            $panelBodyStyle=(isset($options) and isset($options['panelBodyStyle']))?$options['panelBodyStyle']:'';

            /*if ($panelClass=='bg-white'){
            $panelBodyStyle=$panelBodyStyle.' border-width:1px 0 0 0; border-style:solid; border-color:#DDDDDD;';
            $defaultTitleColorClass='color-black';
            }
            else
            {
            $defaultTitleColorClass='color-white'; 
            //$defaultTitleColorClass=''; 
            }*/
            $defaultTitleColorClass=''; 

            $titleColorClass=(isset($options) and isset($options['titleColorClass']))?$options['titleColorClass']:$defaultTitleColorClass;

            $panelStyle=(isset($options) and isset($options['panelStyle']))?$options['panelStyle']:'';
            /* if ($panelClass=='bg-white'){
            $panelStyle=$panelStyle.' border-width:1px; border-style:solid; border-color:#DDDDDD;';
            }*/


            $headerStyle=(isset($options) and isset($options['headerStyle']))?$options['headerStyle']:'';


            $expanded=(isset($options) and isset($options['expanded']))?$options['expanded']:true;
            $collapsed_str=$expanded?'':'collapsed';
            $collapsed_in=$expanded?'in':'';

            $help_expanded=(isset($options) and isset($options['helpExpanded']))?$options['helpExpanded']:false;
            $help_collapsed_str=$help_expanded?'':'collapsed';
            $help_collapsed_in=$help_expanded?'in':'';

            $filter_expanded=(isset($options) and isset($options['filterExpanded']))?$options['filterExpanded']:false;
            $filter_collapsed_str=$filter_expanded?'':'collapsed';
            $filter_collapsed_in=$filter_expanded?'in':'';



            $wrapperClass=(isset($options) and isset($options['wrapperClass']))?$options['wrapperClass']:false; 

            $panelBodyClass=(isset($options) and isset($options['panelBodyClass']))?$options['panelBodyClass']:''; //bg-white




            $overflow=(isset($options) and isset($options['overflow']))?$options['overflow']:0;

            if ($overflow!=0){
                //$_html="<div style='overflow-y: auto; max-height:${overflow}px;'>".$_html."</div>"; 
                $panelBodyStyle=trim($panelBodyStyle);
                if (rights($panelBodyStyle,1)!==';'){
                    $panelBodyStyle=$panelBodyStyle.=';'; 
                }

                $panelBodyStyle=$panelBodyStyle.="overflow-y: auto; max-height:${overflow}px;"; 
            }







            if ($filter!=''){
                $buttons.= l('', "#${id}_collapse_filter", array(
                    'data-toggle'=>"collapse",
                    'class'=>"btn-panel-toolbar ion-funnel $titleColorClass $filter_collapsed_str",
                ));
            }

            if ($help!=''){
                $buttons.= l('', "#${id}_collapse_help", array(
                    'data-toggle'=>"collapse",
                    'class'=>"btn-panel-toolbar ion-help $titleColorClass $help_collapsed_str",
                ));
            }

            $showCollapseButton=(isset($options) and isset($options['showCollapseButton']))?$options['showCollapseButton']:true;
            if ($showCollapseButton){
                $buttons.= l('', "#${id}_collapse", array(
                    'id'=>"${id}_collapse_button",
                    'data-toggle'=>"collapse",
                    'class'=>"btn-panel-toolbar panel-toggle $titleColorClass $collapsed_str",
                ));
            }

            $showCloseButton=(isset($options) and isset($options['showCloseButton']))?$options['showCloseButton']:false; 
            if ($showCloseButton){
                $buttons.= l('', '#', array(
                    'data-dismiss'=>'modal',
                    'class'=>"btn-panel-toolbar ion-close $titleColorClass",
                )); 
            }

            $html=array();

            if ($wrapperClass!==false){
                $html[]="<div class='${wrapperClass}'>";  
            }

            $html[]="    <div id='$id' class='panel panel-default $panelClass' style='$panelStyle'>";


            if ($hideHeading===false){
                $html[]=<<<EOT
        <div class="panel-heading clearfix" >
            <$titleTag class="panel-title $titleColorClass">$title</$titleTag>
            <div class="panel-toolbar">
                $buttons
            </div>
        </div>
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
            }



            if ($header!=''){
                $html[]=<<<EOT
        <div class='panel-header' style='${headerStyle}'> 
            $header
        </div>
EOT;
            };       

            if ($hideHeading===false){
            if ($showCollapseButton){
                $html[]=<<<EOT
        <div id="${id}_collapse" class="panel-collapse collapse $collapsed_in">
EOT;
            }
            }

            if ($filter!=''){
                $html[]=<<<EOT
            <div id='${id}_collapse_filter' class="panel-filter panel-collapse collapse $filter_collapsed_in $titleColorClass">
                <div class='panel-filter-overflow'> 
                    $filter
                </div>
            </div>
EOT;
            };



            $html[]=<<<EOT
            <div id="${id}_panel_body" class="panel-body $panelBodyClass" style="$panelBodyStyle">
                $_html
            </div><!-- /panel-body -->
EOT;


            if ($hideHeading===false) {
                if ($showCollapseButton){       
                $html[]="        </div><!-- /collapse -->";

                $html[]=l("<i class='ion-drag ion-lg'></i>", "#${id}_collapse", array(
                    'id'=>$id.'_splitter',
                    'data-toggle'=>"collapse",
                    'class'=>"btn-panel-toolbar panel-splitter $collapsed_str",
                    'style'=>$expanded?'display:none;':'',
                ));
            }
            }


            $html[]="    </div><!-- /panel -->";

            if ($wrapperClass!==false){
                $html[]="</div><!-- /wrapper -->";  
            }

            if (isset($options) and isset($options['onExpand'])){
                $s=$options['onExpand'];
                $html[]=DScript::ready(<<<EOT
                    $("#${id}_collapse").on("show.bs.collapse", function (){ ${s} });
EOT
                );    
            }


            if ($hideHeading===false){
                if ($showCollapseButton){   
                    $html[]=DScript::ready(<<<EOT
                $("#${id}_collapse").on("shown.bs.collapse", function (){ 
                    $('#${id}_splitter').hide();
                });
                $("#${id}_collapse").on("hidden.bs.collapse", function (){ 
                    $('#${id}_splitter').show();
                });
EOT
                    );  
                }
            }

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

            return implode( "\n", $html);
        }

    }