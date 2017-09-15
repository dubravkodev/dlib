<?php

  class DAccordion{
      

        public static function accordion($data=array(), $options=array()){ 

            $id=(isset($options) and isset($options['id']))?$options['id']:rand_string(8); 

            $html=array();
            $html[]="<div class='panel-group' id='$id' role='tablist' aria-multiselectable='true'>"; 

            $i=1;
            foreach($data as $d){
                $title=isset($d['title'])?$d['title']:'-------';
                $_html=isset($d['html'])?$d['html']:'';
                $help=isset($d['help'])?$d['help']:'';  

                $panelClass=(isset($options) and isset($options['panelClass']))?$options['panelClass']:'bg-white';
                $panelClass=isset($d['panelClass'])?$d['panelClass']:$panelClass;

                $panelBodyStyle=(isset($options) and isset($options['panelBodyStyle']))?$options['panelBodyStyle']:'';

                if ($panelClass=='bg-white'){
                    $panelBodyStyle=$panelBodyStyle.' border-width:1px 0 0 0; border-style:solid; border-color:#DDDDDD;';
                    $defaultTitleColorClass='color-black';
                }
                else
                {
                    $defaultTitleColorClass='color-white';  
                }

                $titleColorClass=(isset($options) and isset($options['titleColorClass']))?$options['titleColorClass']:$defaultTitleColorClass;

                $titleColorClass=(isset($d['titleColorClass']))?$d['titleColorClass']:$titleColorClass;
                
                $panelStyle=(isset($options) and isset($options['panelStyle']))?$options['panelStyle']:'';

                $panelBodyClass=(isset($options) and isset($options['panelBodyClass']))?$options['panelBodyClass']:'bg-white';

                $buttons= isset($d['buttons'])?$d['buttons']:'';
                if (is_array($buttons))
                    $buttons=implode(' ',$buttons);

                $help_expanded= isset($d['helpExpanded'])?$d['helpExpanded']:false;
                $help_collapsed_str=$help_expanded?'':'collapsed';
                $help_collapsed_in=$help_expanded?'in':'';

                if ($help!=''){
                    $buttons.= l('', "#${id}_collapse_help_${i}", array(
                        'data-toggle'=>"collapse",
                        'class'=>"btn-panel-toolbar ion-help $titleColorClass $help_collapsed_str",
                    ));
                } 

                $buttons.=l('', "#${id}_collapse_${i}", array(
                    'id'=>"${id}_link_${i}",
                    'data-toggle'=>"collapse",
                    'data-parent'=>"#$id",
                    'aria-expanded'=>$i==1?"true":"false",
                    'aria-controls'=>"${id}_collapse_${i}",
                    'class'=>"btn-panel-toolbar panel-toggle $titleColorClass",
                ));

                $in=$i==1?"in":"";

                $html[]= <<<EOT
                    <div class="panel panel-material $panelClass" style="$panelStyle">
                        <div class="panel-heading" role="tab" id="${id}_heading_${i}">
                        <h3 class="panel-title $titleColorClass">$title</h3>
                            <div class="panel-toolbar">
                                $buttons
                            </div>
                        </div>
                        <div id='${id}_collapse_help_${i}' class="panel-help panel-collapse collapse $help_collapsed_in $titleColorClass">
                            <div class='panel-help-overflow'> 
                                $help
                            </div>
                        </div>
                        <div id="${id}_collapse_${i}" class="panel-collapse collapse $in" role="tabpanel" aria-labelledby="${id}_heading_${i}">
                            <div id="${id}_panel_body" class="panel-body $panelBodyClass" style="$panelBodyStyle">
                                $_html
                            </div>
                        </div>
                    </div>
EOT
                ;
                $i++; 
            }

            $html[]="</div>"; 
            return implode("\n", $html);
        }  
      
  }

