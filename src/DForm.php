<?php     

    class DForm
    {

        public static function autocompleteField($form, $model, $attribute, $options=array()){
            /* specijalni parametri
            * url
            * highlight
            * minLength
            * limit
            * templates
            * label //defaultna vrijednost
            * onChanged data.value or data.label
            */

            if (isset($options['url'])){
                $url=$options['url'];
                unset($options['url']);
            }
            else
                $url=false; 

            if (isset($options['highlight'])){
                $highlight=($options['highlight']==false)?'false':'true';
                unset($options['highlight']);
            }
            else
                $highlight='true';

            if (isset($options['minLength'])){
                $minLength=$options['minLength'];
                unset($options['minLength']);
            }
            else
                $minLength=2;

            if (isset($options['limit'])){
                $limit=$options['limit'];
                unset($options['limit']);
            }
            else
                $limit=10;

            if (isset($options['templates'])){
                $templates='templates:'.$options['templates'];
                unset($options['templates']);
            }
            else
                $templates='';

            if (isset($options['label'])){
                $label=$options['label'];
                unset($options['label']);
            }
            else
                $label='';

            if (isset($options['onChanged'])){
                $onChanged=$options['onChanged'];
                unset($options['onChanged']);
            }
            else
                $onChanged='';

            /* end specijalni parametri */

            $html=array();

            $html[]=$form->hiddenField($model, $attribute);

            $hidden_id=CHtml::activeID($model, $attribute);
            $tmp_id=$hidden_id.'_tmp';
            $html[]= CHtml::textField($tmp_id, $label, $options);
            $html[]= $form->error($model, $attribute);

            $js=array(); 

            $js[]=DScript::ready("
                var ${tmp_id}_engine = new Bloodhound({ 
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                limit: $limit, 
                remote: {
                url: '$url/q/%QUERY',
                filter: function (response) {
                return $.map(response.data, function (d) {
                return {value: d.value, label: d.label, url: d.url, prefix: d.prefix}})}
                },
                });

                ${tmp_id}_engine.initialize(); 

                $('#$tmp_id').typeahead({  
                highlight : $highlight,
                minLength :$minLength,
                },{
                displayKey: 'label',
                source: ${tmp_id}_engine.ttAdapter(), 
                $templates
                });

                $('#${tmp_id}').bind('typeahead:selected', function(evt, data) {  $('#${hidden_id}').val(data.value); $onChanged });

                ");    
            $html[]=implode(' ', $js);
            return implode("\n", $html);
        }  

        //tabs

        private static function _tab($id, $i, $j, $label, $tabIndex, $tabVisible){
            if ($j===null)
                $tabId=$id.'_tab_'.$i; 
            else
                $tabId=$id.'_tab_'.$i.'_'.$j; 

            $params=array();
            $params['id']=$tabId.'-tab';
            $params['role']='tab';
            $params['data-toggle']='tab';
            $params['aria-controls']=$tabId;

            if (! $tabVisible){
                $params['style']='display:none'; 
            }

            if ($j!==null)       
                $params['tabindex']='-1';

            $link=CHtml::link($label, "#${tabId}", $params);

            $class=array();

            if ($j==null){        
                if ($tabIndex==$i)
                    $class[]='active';
            }

            $params=array();
            if ($j===null)
                $params['role']="presentation";

            $params['class']=implode(' ',$class);

            return CHtml::tag('li', $params, $link );   
        }

        private static function _tab_content($id, $i, $j, $content,  $tabIndex, $fade){
            if ($j===null)
                $tabId=$id.'_tab_'.$i; 
            else
                $tabId=$id.'_tab_'.$i.'_'.$j; 

            $class=array();
            $class[]='tab-pane';

            if ($fade)
                $class[]='fade';

            if ($tabIndex==$i){     
                if ($j===null){
                    if ($fade)
                        $class[]='in active';
                    else
                        $class[]='active';
                }
                else if ($j===0){
                    if ($fade)
                        $class[]='in active';
                    else
                        $class[]='active';
                }    
            }    

            $params=array();
            $params['role']='tabpanel';
            $params['class']=implode(' ',$class);
            $params['id']=$tabId;
            return CHtml::tag('div',$params,$content );
        }

        /*
        echo XForm::tabs(array(
        'tabs' => array(
        array('label' => 'Prvi', 'content' => '...', 'visible'=>...),
        array('label' => 'Drugi', 'content' => '...'),
        array('label' => 'Treći', 'items'=>array(
        array('label' => 'A', 'content' => '...'),
        array('label' => 'B', 'content' => '...'),
        )
        ))));
        */
        public static function tabs($form, $model, $attribute, $options=array()){
            // za korištenje bez modela staviti $form=false, $model=false, $attribute=id

            if (isset($options['showTabs'])){
                $showTabs=$options['showTabs'];
                unset($options['showTabs']);
            }
            else
                $showTabs=true; 


            if ($model!==false){     
                $id=CHtml::activeID($model,$attribute);   

                if ($model[$attribute]===null){
                    $tabIndex=0; //prvi tab je aktivan
                    $model[$attribute]=0;
                }
                else
                    $tabIndex=$model[$attribute];
            }
            else
            {
                $id=$attribute;
                $tabIndex=0;    
            }

            $tabs=isset($options['tabs'])?$options['tabs']:array();
            $fade=(isset($options['fade'])&&($options['fade']===true))?true:false;

            $html=array();

            if ($form!==false){
                $html[]=$form->hiddenField($model, $attribute);
            }

            $html[]="<div role='tabpanel'>";

            if (! $showTabs){
                $html[]="<div style='display:none'>"; 
            }

            $html[]="<ul id='${id}_tab' class='nav nav-tabs' role='tablist'>";
            for ($i = 0; $i < count($tabs); $i++) {
                $tab=$tabs[$i];
                $tabId=$id.'_tab_'.$i;

                if (isset($tab['items']))
                {
                    $class=array();
                    $class[]='dropdown';
                    if ($tabIndex==$i)
                        $class[]='active';
                    $params=array();
                    $params['role']="presentation";
                    $params['class']=implode(' ',$class);
                    $html[]=CHtml::tag('li', $params, false,false ); 

                    $params=array();
                    $params['id']=$tabId.'-_TabDrop';
                    $params['class']='dropdown-toggle';
                    $params['data-toggle']='dropdown';
                    $params['aria-controls']=$tabId.'_TabDrop-contents';
                    $html[]=CHtml::link($tab['label']. "<span class='caret'></span>", '#', $params);

                    $params=array();
                    $params['class']="dropdown-menu";
                    $params['role']="menu";
                    $params['aria-labelledby']=$tabId.'_TabDrop';
                    $params['id']=$tabId.'_TabDrop-contents';
                    $html[]=CHtml::tag('ul', $params, false,false ); 

                    $items=$tab['items'];
                    for ($j = 0; $j < count($items); $j++) {
                        $item=$items[$j];
                        $label=$item['label'];
                        $tabVisible=(isset($item['visible']) and ($item['visible']===false))?false:true;
                        $html[]=self::_tab($id, $i, $j, $label,  $tabIndex, $tabVisible);
                    }
                    $html[]="</ul>";
                    $html[]="</li>";
                }
                else
                {
                    $label=$tab['label'];
                    $tabVisible=(isset($tab['visible']) and ($tab['visible']===false))?false:true;
                    $html[]=self::_tab($id, $i, null, $tab['label'],  $tabIndex,$tabVisible); 
                } 
            }
            $html[]="</ul>";

            if (! $showTabs){
                $html[]="</div>"; 
            }


            $tab_content_class='tab-content';
            if ($showTabs)
                $tab_content_class.=' tab-content-frame';

            $html[]="<div id='${id}_tab_content' class='$tab_content_class'>";
            for ($i = 0; $i < count($tabs); $i++) {
                $tab=$tabs[$i];

                if (isset($tab['items']))
                {
                    $items=$tab['items'];
                    for ($j = 0; $j < count($items); $j++) {
                        $item=$items[$j];
                        $html[]= self::_tab_content($id, $i, $j, $item['content'], $tabIndex, $fade); 
                    }
                }
                else
                {
                    $html[]= self::_tab_content($id, $i, null, $tab['content'], $tabIndex, $fade); 
                }
            }
            $html[]="</div><!-- /tab-content -->"; 
            $html[]="</div> <!-- /tabpanel -->"; 

            //javascript koji zapisuje vrijednosti u hidden field
            $js=array();
            for ($i = 0; $i < count($tabs); $i++) {
                $tab=$tabs[$i];
                $tabId=$id.'_tab_'.$i.'-tab';
                $js[]="\n$('#${tabId}').on('shown.bs.tab', function (e) {\$('#${id}').val($i);});";
            }
            $html[]=DScript::ready(implode("", $js)."\n"); 

            return implode("\n", $html)."\n";
        }

        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        // form controls group
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

        private static function _labelOptions($form, &$options, $defWidth=2){

            if (isset($options['labelOptions'])){
                $labelOptions=$options['labelOptions'];
                unset($options['labelOptions']);
            }
            else
                $labelOptions=array();


            $class=array();
            $class[]='control-label';

            if (self::isFormHorizontal($form)){
                $class[]="col-sm-${defWidth} "; //default width
            }

            if (isset($labelOptions['class'])){
                $class=array();
                $class[]=$labelOptions['class'];
            }
            $labelOptions['class']=implode(' ',$class); 

            return $labelOptions;
        }

        private static function _divOptions($form, &$options, $defWidth=10){
            if (isset($options['divOptions'])){
                $divOptions=$options['divOptions'];
                unset($options['divOptions']);
            }
            else
                $divOptions=array();

            $class=array();

            if (self::isFormHorizontal($form))
                $class[]="col-sm-${defWidth}"; //default width

            if (isset($divOptions['class'])){
                $class=array();
                $class[]=$divOptions['class'];
            }
            $divOptions['class']=implode(' ',$class); 

            return $divOptions;
        }

        private static function _renderFormGroup(&$options){
            if (isset($options['renderFormGroup'])){
                $render=$options['renderFormGroup'];
                unset($options['renderFormGroup']);
            }
            else
                $render=true; 

            return $render;   
        }

        private static function _renderLabel(&$options){
            if (isset($options['renderLabel'])){
                $render=$options['renderLabel'];
                unset($options['renderLabel']);
            }
            else
                $render=true; 

            return $render; 
        }

        private static function _customLabel(&$options){
            if (isset($options['customLabel'])){
                $label=$options['customLabel'];
                unset($options['customLabel']);
            }
            else
                $label=false; 

            return $label; 
        }

        private static function _renderError(&$options){
            if (isset($options['renderError'])){
                $render=$options['renderError'];
                unset($options['renderError']);
            }
            else
                $render=true; 

            return $render; 
        }

        private static function _helpBlock($form, &$options){
            if (isset($options['help'])){
                $helpBlock=$options['help'];
                unset($options['help']);
            }
            else
                $helpBlock=''; 

            return $helpBlock; 
        }

        private static function isFormHorizontal($form){
            $result=false;
            $htmlOptions=$form->htmlOptions;
            if (isset($htmlOptions['class'])){
                $class=$htmlOptions['class'];  
                $classa=explode(' ', $class);
                $result=in_array('form-horizontal', $classa);   
            }

            return $result;
        }

        public static function textField($form, $model, $attribute, $options=array()){
            $html=array();

            if (isset($options['onKeypressEnter'])){
                $render=$options['onKeypressEnter'];
                unset($options['onKeypressEnter']);
            }
            else
                $render=false; 



            $html[]=$form->textField($model, $attribute, $options);

            if ($render!==false){
                $ctrl=CHtml::activeId($model, $attribute);
                $html[]= script("$('#$ctrl').keypress(function(e) { if(e.which == 13) { $render } });");

            }
            return implode("\n", $html);
        }

        public static function textFieldGroup($form, $model, $attribute, $options=array()){
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            $customLabel=self::_customLabel($options);
            //-------------------------------------------------------------

            $class='form-control '; 
            if (isset($options['class'])){
                $class=$class.$options['class'];
            }
            $options['class']=$class;

            $ctrl=CHtml::ActiveID($model, $attribute); 
            if ($helpBlock!='')
                $options['aria-describedby']=$ctrl.'_helpBlock_';

            $html=array();

            if ($renderFormGroup)   
                $html[]="<div class='form-group'>";

            if ($renderLabel){
                if ($customLabel===false)
                    $html[]= $form->labelEx($model, $attribute, $labelOptions);
                else
                    $html[]=CHtml::label($customLabel, CHtml::activeId($model, $attribute));
            }

            if (self::isFormHorizontal($form))
                $html[]=CHtml::openTag('div',$divOptions); 

            //$html[]= $form->textField($model, $attribute, $options);
            $html[]=self::textField($form, $model, $attribute, $options);


            $id=CHtml::activeID($model, $attribute);
            $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }

            if (self::isFormHorizontal($form))
                $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";
            return implode("\n", $html);
        }

        public static function passwordFieldGroup($form, $model, $attribute, $options=array()){
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options);
            $renderFormGroup=isset($options['renderFormGroup'])?$options['renderFormGroup']:true; 
            $renderLabel=isset($options['renderLabel'])?$options['renderLabel']:true;
            //-------------------------------------------------------------

            $htmlOptions=isset($options['htmlOptions'])?$options['htmlOptions']:array();

            $ctrl=CHtml::ActiveID($model, $attribute); 
            if ($helpBlock!='')
                $htmlOptions['aria-describedby']=$ctrl.'_helpBlock_';

            if (isset($options['maxlength']))
                $htmlOptions['maxlength']=$options['maxlength'];

            if (isset($options['placeholder']))
                $htmlOptions['placeholder']=$options['placeholder'];   

            $class=array();
            $class[]='form-control';   

            if (isset($htmlOptions['class']))
                $class[]=$htmlOptions['class']; 

            $htmlOptions['class']=implode(' ', $class);   

            $html=array();


            if ($renderFormGroup)   
                $html[]="<div class='form-group'>";

            if ($renderLabel)
                $html[]= $form->labelEx($model, $attribute, $labelOptions);

            if (self::isFormHorizontal($form))
                $html[]=CHtml::openTag('div',$divOptions); 
            $html[]= $form->passwordField($model, $attribute, $htmlOptions);
            $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }


            if (self::isFormHorizontal($form))
                $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";
            return implode("\n", $html);
        }

        public static function textAreaGroup($form, $model, $attribute, $options=array()){
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            $customLabel=self::_customLabel($options);
            //-------------------------------------------------------------


            $ctrl=CHtml::ActiveID($model, $attribute); 

            if ($helpBlock!='')
                $options['aria-describedby']=$ctrl.'_helpBlock_';

            if (! isset($options['rows']))
                $options['rows']=2;   

            $class='form-control '; 
            if (isset($options['class'])){
                $class=$class.$options['class'];
            }
            $options['class']=$class;

            $html=array();    


            if ($renderFormGroup)
                $html[]="<div class='form-group'>";

            if ($renderLabel){
                if ($customLabel===false)
                    $html[]= $form->labelEx($model, $attribute, $labelOptions);
                else
                    $html[]=CHtml::label($customLabel, CHtml::activeId($model, $attribute));
            }

            if (self::isFormHorizontal($form))
                $html[]=CHtml::openTag('div',$divOptions); 
            $html[]= $form->textArea($model, $attribute, $options);
            $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }

            if (self::isFormHorizontal($form))
                $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";
            return implode("\n", $html);
        }

        /*     public static function formDatepickerGroup2($form, $model, $attribute, $options=array()){ 
        $helpBlock=self::_helpBlock($form, $options);
        $labelOptions=self::_labelOptions($form, $options);
        $divOptions=self::_divOptions($form, $options,3);
        $renderFormGroup=self::_renderFormGroup($options);
        $renderLabel=self::_renderLabel($options);
        $renderError=self::_renderError($options);

        $ctrl=CHtml::activeID($model, $attribute);   
        if ($helpBlock!='')
        $options['aria-describedby']=$ctrl.'_helpBlock_';

        $html=array();   

        if ($renderFormGroup)
        $html[]="<div class='form-group'>";

        if ($renderLabel)
        $html[]= $form->labelEx($model, $attribute, $labelOptions);

        if (self::isFormHorizontal($form))
        $html[]=CHtml::openTag('div',$divOptions);  
        $html[]=self::formDatepicker2($form, $model, $attribute, $options);

        if ($renderError)
        $html[]= $form->error($model, $attribute);

        if ($helpBlock!=''){
        $html[]= CHtml::tag('span', array(
        'id'=>$ctrl.'_helpBlock_',
        'class'=>'help-block',
        ),$helpBlock);
        }   

        if (self::isFormHorizontal($form))
        $html[]="</div>";

        if ($renderFormGroup)
        $html[]="</div>";


        return implode("\n", $html);  
        }    */

        public static function datePickerGroup($form, $model, $attribute, $options=array()){ 
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options,3);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            $renderError=self::_renderError($options);

            $ctrl=CHtml::activeID($model, $attribute);   
            if ($helpBlock!='')
                $options['aria-describedby']=$ctrl.'_helpBlock_';

            $html=array();   

            if ($renderFormGroup)
                $html[]="<div class='form-group'>";

            if ($renderLabel)
                $html[]= $form->labelEx($model, $attribute, $labelOptions);

            if (self::isFormHorizontal($form))
                $html[]=CHtml::openTag('div',$divOptions);  
            $html[]=self::datePicker($form, $model, $attribute, $options);

            if ($renderError)
                $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }   

            if (self::isFormHorizontal($form))
                $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";


            return implode("\n", $html);  
        }       

        public static function dateTimePickerGroup($form, $model, $attribute, $options=array()){ 
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options,3);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            $renderError=self::_renderError($options);

            $ctrl=CHtml::activeID($model, $attribute);   
            if ($helpBlock!='')
                $options['aria-describedby']=$ctrl.'_helpBlock_';

            $html=array();   

            if ($renderFormGroup)
                $html[]="<div class='form-group'>";

            if ($renderLabel)
                $html[]= $form->labelEx($model, $attribute, $labelOptions);

            if (self::isFormHorizontal($form))
                $html[]=CHtml::openTag('div',$divOptions);  
            $html[]=self::dateTimePicker($form, $model, $attribute, $options);

            if ($renderError)
                $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }   

            if (self::isFormHorizontal($form))
                $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";

            return implode("\n", $html);  
        }

        public static function dropDownListGroup($form, $model, $attribute, $options=array()){ 
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options,3);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            $customLabel=self::_customLabel($options);

            $ctrl=CHtml::activeID($model, $attribute);   
            if ($helpBlock!='')
                $options['aria-describedby']=$ctrl.'_helpBlock_';


            $class='form-control '; 
            if (isset($options['class'])){
                $class=$options['class'];
            }
            else
                $options['class']=$class;


            $html=array();


            if ($renderFormGroup)
                $html[]="<div class='form-group'>";

            if ($renderLabel){
                if ($customLabel===false)
                    $html[]= $form->labelEx($model, $attribute, $labelOptions);
                else
                    $html[]=CHtml::label($customLabel, CHtml::activeId($model, $attribute));
            }

            if (self::isFormHorizontal($form))
                $html[]=CHtml::openTag('div',$divOptions);
            $html[]= self::dropDownList($form, $model, $attribute, $options);
            $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }


            if (self::isFormHorizontal($form))
                $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";



            return implode("\n", $html);
        }

        public static function tokenGroup($form, $model, $attribute, $options=array()){
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            //-------------------------------------------------------------

            $ctrl=CHtml::activeID($model, $attribute);   
            if ($helpBlock!='')
                $options['aria-describedby']=$ctrl.'_helpBlock_';

            $afterInsert=isset($options['afterInsert'])?$options['afterInsert']:'';

            if (isset($options['initData'])){
                $initData=$options['initData'];
                unset($options['initData']); 
            }
            else
                $initData=array();

            if (isset($options['url'])){
                $url=$options['url'];
                unset($options['url']); 
            }
            else 
                $url='';


            $class='form-control '; 
            if (isset($options['class'])){
                $class=$class.$options['class'];
            }
            $options['class']=$class;

            $html=array();


            if ($renderFormGroup)
                $html[]="<div class='form-group'>";

            if ($renderLabel)
                $html[]= $form->labelEx($model, $attribute, $labelOptions);


            $html[]=CHtml::openTag('div',$divOptions); 
            $html[]= $form->textField($model, $attribute, $options);
            $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }

            $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";


            $ctrl=CHtml::activeID($model, $attribute);
            $html[]= DScript::ready("

                var engine = new Bloodhound({ 
                limit: 20, 
                remote: {
                url: '$url'+'/q/%QUERY',
                filter: function (response) {
                var tagged_user = $('#${ctrl}').tokenfield('getTokens');
                return $.map(response.data, function (d) {

                var exists = false;
                for (i=0; i < tagged_user.length; i++) {

                if (d.id == tagged_user[i].value) {
                var exists = true;
                }
                }
                if (!exists) {
                return {
                value: d.id,
                label: d.label
                };
                }
                });
                }
                },
                datumTokenizer: function (d) {
                return Bloodhound.tokenizers.whitespace(d.value);
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace
                });

                engine.initialize();

                $('#${ctrl}').tokenfield({
                beautify:false, //miče space iz rezultata
                typeahead: [
                {
                highlight :true,
                minLength :2,
                },
                {     
                //name: 'users',
                displayKey: 'label',
                source: engine.ttAdapter(),    
                }
                ]
                });

                $('#${ctrl}').on('tokenfield:createtoken', function (e) {
                var tokens = $(this).tokenfield('getTokens');

                if (tokens.length) {

                $.each(tokens, function(index, token) {
                if (token.value === e.attrs.value) {  //nema duplo
                e.preventDefault();
                }
                });
                }

                if (e.attrs.value===e.attrs.label) //ne dozvoljavamo upis ukoliko je nadodan ručno
                {
                e.preventDefault();
                }; 

                });

                $('#${ctrl}').on('tokenfield:createdtoken', function (e) {
                var tokens = $(this).tokenfield('getTokens');

                //konvertiramo tokens object to array
                var data=[];
                $.each(tokens, function(index, token) {
                data.push([token.value,token.label]);
                });

                var myFn = function(data){
                ${afterInsert}
                }
                myFn(data);
                });

                ");

            if (count($initData)>0){
                $initData_json=json_encode($initData);
                $html[]= DScript::ready("$('#${ctrl}').tokenfield('setTokens', ${initData_json});");            
            }

            return implode("\n", $html);
        }

        /* formSwitchGroup

        $html[]= XForm::switchGroup($form, $model,'consultant_employers',array(
        'onText'=>'Yes',
        'offText'=>'No',
        'help'=>settings::help('xxxxxxxxxxhelp_employer_companies_privacy_settings_anonymous'),
        'onChange'=>'if (state==true){$("#consultant_employers_container").collapse("show")}else{$("#consultant_employers_container").collapse("hide")};',
        )); 
        ...
        $html[]= "<div class='collapse' id='consultant_employers_container'>";
        ...
        $html[]=  "</div>";
        */
        public static function switchGroup($form, $model, $attribute, $options=array()){
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options,10 );
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            //------------------------------------------------------------- 

            $renderInnerDiv=isset($options['renderInnerDiv'])?$options['renderInnerDiv']:true; 

            $onChange=isset($options['onChange'])?$options['onChange']:'';

            $htmlOptions=array();

            $class=array();
            $class[]='form-control';   

            if (isset($options['class']))
                $class[]=$options['class']; 

            $htmlOptions['class']=implode(' ', $class);     


            $html=array();    

            if ($renderFormGroup)    
                $html[]="<div class='form-group'>";

            if ($renderLabel)   
                $html[]= $form->labelEx($model, $attribute, $labelOptions);

            if ($renderInnerDiv) 
                $html[]=CHtml::openTag('div',$divOptions);

            $ctrl=CHtml::activeID($model, $attribute);    

            $a=array();
            $a['value'] = 'Y';     
            $a['uncheckValue'] = 'N';
            if ($helpBlock!='')
                $a['aria-describedby']=$ctrl.'_helpBlock_';

            $html[]= $form->checkBox($model, $attribute, $a); 
            $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }



            $params=array();
            $params['onText']=Yii::t('bhtml', 'ON');
            $params['offText']=Yii::t('bhtml','OFF');

            if (isset($options['onText']))
                $params['onText']=$options['onText'];

            if (isset($options['offText']))
                $params['offText']=$options['offText'];

            if (isset($options['onColor']))
                $params['onColor']=$options['onColor'];    

            if (isset($options['offColor']))
                $params['offColor']=$options['offColor'];

            if (isset($options['size']))
                $params['size']=$options['size'];

            //if (isset($options['state'])) {

            // $params['state']=$model[$attribute]==='N'?false:true;
            //   }

            if (isset($options['disabled']))
                $params['disabled']=$options['disabled'];

            if (isset($options['readonly']))
                $params['readonly']=$options['readonly'];

            $json=json_encode($params);

            if ($renderInnerDiv) 
                $html[]="</div>";

            if ($renderFormGroup) 
                $html[]="</div><!-- /form-group -->";

            $script=array();

            $script[]="$('#${ctrl}').bootstrapSwitch($json);";
            $script[]="$('#${ctrl}').on('switchChange.bootstrapSwitch', function(event, state) { ${onChange} });"; //state=true / state=false    

            //init
            /*     if ($model[$attribute]==='Y')
            $script[]="(function () {var state=true; ${onChange} })();";
            else
            $script[]="(function () {var state=false; ${onChange} })();";
            */
            $html[]= DScript::ready(implode(" ",$script));

            //$html[]= CHtml::script(implode(" ",$script));

            return implode("\n", $html)."\n";
        }

        public static function colorPickerGroup($form, $model, $attribute, $options=array()){
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            //-------------------------------------------------------------



            $htmlOptions=array();

            $htmlOptions['class']='form-control ';
            if (isset($options['class']))
                $htmlOptions['class'].=$options['class']; 

            if($renderFormGroup)
                $html="<div class='form-group'>";

            if($renderLabel)
                $html.= $form->labelEx($model, $attribute, $labelOptions);

            $html[]=CHtml::openTag('div',$divOptions);
            $html.= $form->textField($model, $attribute, $htmlOptions);
            $html[]= $form->error($model, $attribute);
            $html.="</div>";

            if($renderFormGroup)
                $html.="</div><!-- /form-group -->";


            $ctrl=CHtml::activeID($model, $attribute);
            $html.=DScript::ready("
                $('input#$ctrl').minicolors({
                animationSpeed: 50,
                animationEasing: 'swing',
                change: null,
                changeDelay: 0,
                control: 'hue',
                dataUris: true,
                defaultValue: '',
                hide: null,
                hideSpeed: 100,
                inline: false,
                letterCase: 'lowercase',
                opacity: false,
                position: 'bottom left',
                show: null,
                showSpeed: 100,
                theme: 'bootstrap'    
                });


                ");

            return $html;
        }

        public static function fileFieldGroup($form, $model, $attribute, $options=array()){
            $helpBlock=self::_helpBlock($form, $options);
            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            //-------------------------------------------------------------

            $ctrl=CHtml::activeID($model, $attribute);   
            if ($helpBlock!='')
                $options['aria-describedby']=$ctrl.'_helpBlock_';

            $renderInnerDiv=isset($options['renderInnerDiv'])?$options['renderInnerDiv']:true;


            $class='*form-control '; 
            if (isset($options['class'])){
                $class=$class.$options['class'];
            }
            $options['class']=$class;  


            $html=array();


            if ($renderFormGroup)   
                $html[]="<div class='form-group'>";

            if ($renderLabel)
                $html[]= $form->labelEx($model, $attribute, $labelOptions);
            if ($renderInnerDiv) 
                $html[]=CHtml::openTag('div',$divOptions);
            $html[]= $form->fileField($model, $attribute, $options);
            $html[]= $form->error($model, $attribute);

            if ($helpBlock!=''){
                $html[]= CHtml::tag('span', array(
                    'id'=>$ctrl.'_helpBlock_',
                    'class'=>'help-block',
                    ),$helpBlock);
            }



            if ($renderInnerDiv) 
                $html[]="</div>";

            if ($renderFormGroup)
                $html[]="</div>";



            $ctrl=CHtml::activeID($model, $attribute);
            $html[]=DScript::ready("
                $('#$ctrl').bootstrapFileInput();
                ");

            return implode("\n", $html);
        }

        public static function radioButtonGroup($form, $model, $attribute, $options=array()){
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            // Example
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            /*
            <?php echo XForm::activeRadioButtonGroup($model, 'experience', array(
            'data'=>array(
            array(
            'value'=>'E',
            'text'=>'Experience', 
            //'labelOptions=array(),
            //'radioOptions=array(),
            ),
            array(
            'value'=>'I',
            'text'=>'Internship', 
            //'labelOptions=array(),
            //'radioOptions=array(),
            ),
            ) 
            )); ?>
            */

            $labelOptions=self::_labelOptions($form, $options);
            $divOptions=self::_divOptions($form, $options);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            //-------------------------------------------------------------

            $class='form-control '; 
            if (isset($options['class'])){
                $class=$class.$options['class'];
            }
            $options['class']=$class;

            if (isset($options['onchange'])){
                $onchange=$options['onchange'];
                unset($options['onchange']);
            }
            else
                $onchange=false; 
            
            
            
            $data=$options['data'];
            unset($options['data']);

            $html=array();

            if ($renderFormGroup)   
                $html[]="<div class='form-group'>";

            if ($renderLabel)
                $html[]= $form->labelEx($model, $attribute, $labelOptions);

            $html[]= $form->hiddenField($model, $attribute);


            $id= CHtml::activeID($model, $attribute);    

            $html[]=CHtml::openTag('div',$divOptions); 

            $html[]="<div class='btn-group' data-toggle='buttons'>";


            $n=1;
            foreach ($data as $d){

                $xxxOptions=(isset($d['labelOptions']))?$d['labelOptions']:array();

                if (isset($xxxOptions['class']))
                    $xxxOptions['class']="btn btn-default ".$xxxOptions['class'];
                else
                    $xxxOptions['class']="btn btn-default ";

                if ($d['value']==$model[$attribute])    
                    $xxxOptions['class']= $xxxOptions['class'].'active';

                $btn_id= $id.'_btn_'.$n;
                $xxxOptions['id']=$btn_id;


                $html[]=CHtml::openTag('label', $xxxOptions);

                $name=$id.'_options';

                $radioOptions=(isset($d['radioOptions']))?$d['radioOptions']:array();
                $radioOptions['autocomplete']="off";
                $html[]=CHtml::radioButton($name, true, $radioOptions);
                $html[]=$d['text'];

                $html[]="</label>";
                $n++;
            }

            $html[]="</div><! --/btn-group -->";

            $html[]= $form->error($model, $attribute);
            $html[]="</div><! --/control-div -->";

            if ($renderFormGroup)
                $html[]="</div><! --/form-group -->";

            $script=array();
            
            
            
            $n=1;
            foreach ($data as $d){
                $btn_id= $id.'_btn_'.$n;
                $value=$d['value'];
                //
                $script[]="$('#${btn_id}').on('click', function () {";
                $script[]="$('#${id}').val('${value}');";
                
                if ($onchange!==false){
                  $script[]="$onchange"; 
                }
                
                $script[]="});";
                //
                $n++; 
            }    
            $html[]=DScript::ready(implode(' ',$script));   

            return implode("\n", $html);
        }

        public static function radioGroup($form, $model, $attribute, $options=array()){
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            // Example
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            /*
            <?php echo XForm::radioGroup($form, $model, 'experience', array(
            'data'=>array(
            array(
            'value'=>'E',
            'text'=>'Experience', 
            //'labelOptions=array(),
            //'radioOptions=array(),
            ),
            array(
            'value'=>'I',
            'text'=>'Internship', 
            //'labelOptions=array(),
            //'radioOptions=array(),
            ),
            ) 
            )); ?>
            */

            $labelOptions=self::_labelOptions($form, $options);
            //$divOptions=self::_divOptions($form, $options);
            $renderFormGroup=self::_renderFormGroup($options);
            $renderLabel=self::_renderLabel($options);
            $divOptions=self::_divOptions($form, $options, 10);
            //-------------------------------------------------------------

            $class='form-control '; 
            if (isset($options['class'])){
                $class=$class.$options['class'];
            }
            $options['class']=$class;

            $data=$options['data'];
            unset($options['data']);

            $html=array();

            if ($renderFormGroup)   
                $html[]="<div class='form-group'>";



            if ($renderLabel)
                $html[]= $form->labelEx($model, $attribute, $labelOptions);

            $html[]= $form->hiddenField($model, $attribute);


            $id= CHtml::activeID($model, $attribute);    

            if (self::isFormHorizontal($form))
                $html[]=CHtml::openTag('div',$divOptions);

            //     $html[]="<div class='btn-group' data-toggle='buttons'>";


            $n=1;
            foreach ($data as $d){

                $xxxOptions=(isset($d['labelOptions']))?$d['labelOptions']:array();

                if (isset($xxxOptions['class']))
                    $xxxOptions['class']="".$xxxOptions['class'];
                else
                    $xxxOptions['class']="";

                if ($d['value']==$model[$attribute])    
                    $xxxOptions['class']= $xxxOptions['class'].'active';

                $btn_id= $id.'_btn_'.$n;
                //$xxxOptions['id']=$btn_id;

                $disabled=(isset($d['disabled']) and $d['disabled']===true)?'disabled':'';

                $html[]="<div class='radio $disabled'>"; 
                $html[]=CHtml::openTag('label', $xxxOptions);

                $name=$id.'_options';

                $radioOptions=(isset($d['radioOptions']))?$d['radioOptions']:array();
                if ($disabled)
                    $radioOptions['disabled']=true;

                $radioOptions['id']=$btn_id;       
                $html[]=CHtml::radioButton($name, $model[$attribute]==$d['value'], $radioOptions);
                $html[]='<span>'.$d['text'].'</span>';

                $html[]="</label>";




                if (isset($d['help'])){
                    $html[]= CHtml::tag('span', array(
                        // 'id'=>$ctrl.'_helpBlock_',
                        'class'=>'help-block',
                        ),$d['help']);
                }



                $html[]="</div>";  
                $n++;
            }

            //     $html[]="</div><! --/btn-group -->";

            $html[]= $form->error($model, $attribute);


            if (self::isFormHorizontal($form))
                $html[]="</div><! --/control-div -->";

            if ($renderFormGroup)
                $html[]="</div><! --/form-group -->";



            $script=array();
            $n=1;
            foreach ($data as $d){
                $btn_id= $id.'_btn_'.$n;
                $value=$d['value'];
                //
                $script[]="$('#${btn_id}').on('click', function () {";
                $script[]="$('#${id}').val('${value}');";
                $script[]="});";
                //
                $n++; 
            }    
            $html[]=DScript::ready(implode(' ',$script));   

            return implode("\n", $html);
        }

        /* public static function filterGroup($id, $options=array()){
        $gridId=$options['grid_id'];
        $label=isset($options['label'])?$options['label']:'';

        $html=array();
        $html[]="<div class='form-group'>";

        if ($label!='')
        $html[]= CHtml::label($label, $id, array('style'=>'margin-right:5px'));
        $html[]= CHtml::textField($id, '', array('maxlength'=>20, 'class'=>'form-control', 'placeholder'=>''));
        $html[]="</div>";

        $html[]= DScript::ready("
        var ajaxUpdateTimeout;
        var ajaxRequest;
        $('input#$id').keyup(function() {
        ajaxRequest = '${id}_key='+$(this).val();
        clearTimeout(ajaxUpdateTimeout);
        ajaxUpdateTimeout = setTimeout(function() {
        $.fn.yiiGridView.update(
        '$gridId',
        {data: ajaxRequest}
        )
        },
        300);
        });
        ");

        return implode(' ', $html);
        } */



        /*
        onSelected - var value=$(this).val();

        */
        public static function dropDownList($form, $model, $attribute, $options=array()){ 
            $multiple=false;
            if (isset($options['multiple'])){
                $multiple=$options['multiple'];
            }

            if (isset($options['search'])){ 
                if ($options['search']==true)
                    $options['data-live-search']="true"; 
                unset($options['search']);      
            } 

            if (isset($options['items'])){
                $items=$options['items'];
                unset($options['items']);
            }
            else
                $items=array();

            if (isset($options['onChanged'])){
                $onChanged=$options['onChanged'];
                unset($options['onChanged']);
            }
            else
                $onChanged='';

            if (isset($model[$attribute])){
                $selected_id=$model[$attribute];

                $options['options']= array($selected_id=>array('selected'=>true));
            }
            $html=array();

            $html[]= $form->dropDownList($model, $attribute, $items, $options);

            $id =CHtml::activeID($model, $attribute);   
            $html[]=DScript::ready("$('#$id').selectpicker().on('changed.bs.select', function (e) { $onChanged }); ");  

            if ($multiple){
                $v=json_encode(explode(',',$model[$attribute]));
                $html[]= DScript::ready("$('#$id').selectpicker('val', $v);");            
            }  

            return implode("\n", $html);
        }

        public static function datePicker($form, $model, $attribute, $options=array()){ 

            $hidden=CHtml::activeID($model, $attribute);
            $tmp=$hidden.'_tmp';
            $ctrl=$hidden.'_div';

            $tmpOptions=array();
            $tmpOptions['class']='form-control';
            $tmpOptions['size']='16';
            $tmpOptions['data-not-focused']=1;

            foreach($options as $key => $value)
                $tmpOptions[$key]=$value;

            $html=array();   

            if (isset($options['iconClass'])){
                $iconClass=$options['iconClass'];
                unset($options['iconClass']);
            }
            else
                $iconClass='glyphicon glyphicon-calendar';  


            $html[]="<div id='${ctrl}' style='max-width:200px' class='input-group date'>";

            $html[]= CHtml::textField($tmp,'', $tmpOptions);
            $html[]="<span class='input-group-addon'><i class='$iconClass'></i></span>";
            $html[]="</div>";


            $html[]= $form->hiddenField($model,$attribute);
            //----------------------------------------------------------------------------

            if (isset($options['onChanged'])){ 
                $onChanged=$options['onChanged'];
                unset($options['onChanged']);
            }
            else
                $onChanged='';  

            $o=array();
            $dpoptions=array();

            $dpoptions['format']=isset($options['format'])?$options['format']:'?';    
            $dpoptions['locale']=isset($options['locale'])?$options['locale']:'en';
            $dpoptions['showTodayButton']=isset($options['showTodayButton'])?$options['showTodayButton']:true;
            $dpoptions['showClear']=isset($options['showClear'])?$options['showClear']:true;
            $dpoptions['allowInputToggle']=isset($options['allowInputToggle'])?$options['allowInputToggle']:true;
            $dpoptions['viewMode']=isset($options['viewMode'])?$options['viewMode']:'days';

            $date=$model[$attribute];
            if (!(($date==null))){
                $timestamp=mysql_date_to_timestamp($date);
                $sec=$timestamp*1000;
                $dpoptions['date']="<new Date($sec)>";
            };

            foreach ($dpoptions as $key=>$value){

                $type=gettype($value);
                switch ($type) {
                    case 'boolean':
                        $v=$value===true?'true':'false';
                        break;
                    case 'integer':
                        $v=$value;
                        break;
                    default:
                        if (lefts($value, 1)=='<')
                            $v=rdel(ldel($value, 1),1);
                        else
                            $v="'".$value."'";
                        break;
                }

                $o[]=$key.':'.$v; 
            }  

            $options_str=implode(', ', $o);

            $html[]= DScript::ready(<<<EOT
             /*  $('input#${tmp}').keyup(function(){
                    if ($('input#${tmp}').val().length==0){
                        $('input#${hidden}').val('');
                    }
                });    */

                $('#${ctrl}').datetimepicker({
                    ${options_str}
                }).on('dp.change', function(e){
                    if (e.date !== false) {
                        var mysql_date = e.date.format("YYYY-MM-DD");
                        $('input#${hidden}').val(mysql_date);
                        $onChanged
                    }
                    else
                    {
                        var mysql_date ='';
                        $('input#${hidden}').val(mysql_date);
                        $onChanged
                    };
                });
EOT
            );

            /*   $date=$model[$attribute];

            if (!(($date==null))){

            $timestamp=mysql_date_to_timestamp($date);

            $html[]= DScript::ready("   
            var date = new Date(${timestamp}*1000); 
            $('#${ctrl}').data('DateTimePicker').date(date);    
            ");    
            };*/
            return implode("\n", $html);
        }

        public static function dateTimePicker($form, $model, $attribute, $options=array()){ 

            $hidden=CHtml::activeID($model, $attribute);
            $tmp=$hidden.'_tmp';
            $ctrl=$hidden.'_div';

            $tmpOptions=array();
            $tmpOptions['class']='form-control';
            $tmpOptions['size']='16';
            $tmpOptions['data-not-focused']=1;

            foreach($options as $key => $value)
                $tmpOptions[$key]=$value;

            $html=array();   

            if (isset($options['iconClass'])){
                $iconClass=$options['iconClass'];
                unset($options['iconClass']);
            }
            else
                $iconClass='glyphicon glyphicon-calendar';  


            $html[]="<div id='${ctrl}' style='max-width:250px' class='input-group date'>";

            $html[]= CHtml::textField($tmp,'', $tmpOptions);
            $html[]="<span class='input-group-addon'><i class='$iconClass'></i></span>";
            $html[]="</div>";


            $html[]= $form->hiddenField($model,$attribute);
            //----------------------------------------------------------------------------

            if (isset($options['onChanged'])){  
                $onChanged=$options['onChanged'];
                unset($options['onChanged']);
            }
            else
                $onChanged='';  

            $o=array();
            $dpoptions=array();

            $dpoptions['format']=isset($options['format'])?$options['format']:'?';    
            $dpoptions['locale']=isset($options['locale'])?$options['locale']:'en';
            $dpoptions['showTodayButton']=isset($options['showTodayButton'])?$options['showTodayButton']:true;
            $dpoptions['showClear']=isset($options['showClear'])?$options['showClear']:true;
            $dpoptions['allowInputToggle']=isset($options['allowInputToggle'])?$options['allowInputToggle']:true;
            $dpoptions['viewMode']=isset($options['viewMode'])?$options['viewMode']:'days';

            $date=$model[$attribute];
            if (!(($date==null))){
                $timestamp=mysql_datetime_to_timestamp($date);
                $sec=$timestamp*1000;
                $dpoptions['date']="<new Date($sec)>";
            };

            foreach ($dpoptions as $key=>$value){

                $type=gettype($value);
                switch ($type) {
                    case 'boolean':
                        $v=$value===true?'true':'false';
                        break;
                    case 'integer':
                        $v=$value;
                        break;
                    default:
                        if (lefts($value, 1)=='<')
                            $v=rdel(ldel($value, 1),1);
                        else
                            $v="'".$value."'";
                        break;
                }

                $o[]=$key.':'.$v; 
            }  

            $options_str=implode(', ', $o);

            $html[]= DScript::ready(<<<EOT
             /*  $('input#${tmp}').keyup(function(){
                    if ($('input#${tmp}').val().length==0){
                        $('input#${hidden}').val('');
                    }
                });    */

                $('#${ctrl}').datetimepicker({
                    ${options_str}
                }).on('dp.change', function(e){
          
                    if (e.date !== false) {
                        var mysql_date_time = e.date.format("YYYY-MM-DD HH:mm:ss");
                        $('input#${hidden}').val(mysql_date_time);
                        $onChanged
                    }
                    else
                    {
                        var mysql_date_time ='';
                        $('input#${hidden}').val(mysql_date_time);
                        $onChanged
                    };
                });
EOT
            );

            return implode("\n", $html);
        }

        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        // forms
        // 8/2016
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        public static function formVertical($controller, $formId, &$form){
            ob_start();
            $form=$controller->beginWidget('CActiveForm', array('id'=>$formId, 'enableClientValidation' => true, 'action'=>null,  'htmlOptions'=>array(
                'role'=>'form',
                'onsubmit'=>'return false;',
            ))); 
            $html = ob_get_clean();
            return $html; 
        }

        public static function formHorizontal($controller, $formId, &$form){
            ob_start();
            $form=$controller->beginWidget('CActiveForm', array('id'=>$formId, 'enableClientValidation' => true, 'action'=>null, 'htmlOptions'=>array(
                'class'=>'form-horizontal',
                'role'=>'form',
                'onsubmit'=>'return false;',
            ))); 
            $html = ob_get_clean();
            return $html; 
        }

        public static function formInline($controller, $formId, &$form){
            ob_start();
            $form=$controller->beginWidget('CActiveForm', array('id'=>$formId, 'enableClientValidation' => true, 'action'=>null, 'htmlOptions'=>array(
                'class'=>'form-inline',
                'role'=>'form',
                'onsubmit'=>'return false;',
            ))); 
            $html = ob_get_clean();
            return $html; 
        }

        public static function formPanelToolbar($controller, $formId, &$form, $htmlOptions=array()){
            ob_start();

            $htmlOptions['role']='form';
            $htmlOptions['onsubmit']='return false;'; 
            if (isset($htmlOptions['class'])){
                $htmlOptions['class']='form-panel-toolbar '.$htmlOptions['class'];
            }
            else
            {
                $htmlOptions['class']='form-panel-toolbar';  
            }

            $form=$controller->beginWidget('CActiveForm', array('id'=>$formId, 'enableClientValidation' => true, 'action'=>null, 'htmlOptions'=>$htmlOptions));
            $html = ob_get_clean();
            return $html; 
        }

        public static function formEnd($controller){
            ob_start();
            $controller->endWidget(); 
            $html = ob_get_clean();
            return $html; 
        }   

        public static function errorSummary($form, $model){
            return $form->errorSummary($model);
        }

        public static function captcha($controller, $form, $model, $formId, $params=array()){
            $s='';
            if(CCaptcha::checkRequirements()){ 
                $params['imageOptions']=array('alt'=>'captcha-image');
                //'captchaAction'=>'login/captcha',

                $id=$formId.'_captcha';
                //
                $display= (sread('validation_error_count',0)>10)?'':'display:none;';
                $s.="<div id='$id' class='captcha_placeholder' style='$display'>";
                $s.= $form->label($model,'verifyCode', array('class'=>'control-label'));
                $s.='<div>';

                $s.= $controller->widget('CCaptcha', $params, true);



                $s.= $form->textField($model,'verifyCode', array('class'=>'form-control', 'style'=>'width:150px; margin-top:5px'));
                $s.='</div>';
                $s.='<span class="help-block">Please enter the text shown in the picture.</span>';
                $s.='</div>';
            }
            return $s;
        }  

        public static function recaptcha($form, $model, $attribute, $params=array()){
            $html=[];

            $id=str_replace('-', '_',$form->id).'_recaptcha';
            $site_key=$params['site_key'];
            $language=Yii::app()->language;
            
            $html[]="<script src='https://www.google.com/recaptcha/api.js?onload={$id}_callback&render=explicit&hl={$language}' async defer></script>";  

            $html[]="<div id='{$id}_div'></div>";
            $html[]=$form->hiddenField($model, $attribute, array('class'=>'recaptcha'));
            
            $html[]=<<<EOT
                <script type="text/javascript">
      var {$id}_callback = function () {
        console.log('recaptcha is ready');
        grecaptcha.render("{$id}_div", {
            sitekey: '$site_key',
            callback: function () {
                console.log('recaptcha callback');
            }
        });
      }
    </script>
EOT
            ;

            return implode(' ', $html);
        }
        //----------------------------------------------------------------------

        public static function postForm($formId, $url){
            return "XLib.post_form('$formId', '$url');";
        }




    } 
?>