<?php     

/**
* Copyright © 2015-2016 Dubravko Loborec
*/

class DHtml
{

   public static function email_link($email, $htmlOptions = array()){ 
      /**
      * secured mail link
      */
      $s=explode('@', $email);
      $w=$s[0];
      $u=lefts($s[1], strrpos($s[1], '.'));   
      $k=ldel($s[1], strrpos($s[1], '.'));     

      $id=$htmlOptions['id']; 

      return CHtml::link('please enable javascript to view', '#', $htmlOptions).DScript::ready("

         jQuery('#${id}').html(function(){
         var w = '${w}';
         var i = '@';
         var u = '${u}';
         var k = '${k}';
         var h = 'mailto:' + w + i + u + k;
         jQuery(this).attr('href', h);
         return w + i + u + k;
         });
         ");  
   }

   public static function a($text, $url = '#', $options = array()){  

      if (isset($options['formId'])){
         $formId=$options['formId'];
         unset($options['formId']);
      }
      else
         $formId=false;  

      if (isset($options['beforeSerialize'])){
         $beforeSerialize=$options['beforeSerialize'];
         unset($options['beforeSerialize']);
      }
      else
         $beforeSerialize='';

      if (isset($options['data'])){
         $data_arr=$options['data'];
         unset($options['data']);
      }
      else
         $data_arr=[];

      if (isset($options['onSuccess'])){
         $onSuccess=$options['onSuccess'];
         unset($options['onSuccess']);
      }
      else
         $onSuccess=''; 

      if (isset($options['onComplete'])){
         $onComplete=$options['onComplete'];
         unset($options['onComplete']);
      }
      else
         $onComplete=''; 

      if (isset($options['type'])){
         $type=$options['type'];
         unset($options['type']);
      }
      else
         $type='POST';  

      if (isset($options['dataType'])){
         $dataType=$options['dataType'];
         unset($options['dataType']);
      }
      else
         $dataType='script';  

         
      $beforeExecute='';
         
      if (isset($options['confirmText'])){
         $confirm_text=$options['confirmText'];
         $beforeExecute.="if (! confirm('${confirm_text}')){ event.preventDefault(); event.stopPropagation(); return false;};";
         unset($options['confirmText']);
      }
      
      if (isset($options['promptText'])){
         $promptText=$options['promptText'];
         $beforeExecute.="var prompt_return=prompt('$promptText', '');";
         unset($options['promptText']);
      }
      else
         $promptText=false;
      
      
      //loadanje elementa
      if (isset($options['targetId'])){
         $targetId=$options['targetId'];
         unset($options['targetId']);
         $type='GET';  
         $dataType='html';
         $beforeExecute.="$('#${targetId}').prepend(\"<div class='loading'></div>\");"; 
         $onSuccess="$('#${targetId}').html(data);";   
      }   

      if (isset($options['beforeExecute'])){
         $beforeExecute.=$options['beforeExecute'];
         unset($options['beforeExecute']);
      }

      $script=array();
      $script[]= <<<EOD
         $beforeExecute
         var btn = $(this);
         btn.attr('disabled', true);
         var fdata = new FormData();
EOD
      ; 

      if ($formId!==false){
         $script[]= <<<EOD
            var form=$('#${formId}');
                
            var ctrl=$(form).find('.recaptcha').first();
            if (ctrl.length){
               ctrl.val(grecaptcha.getResponse()); 
            }
                   
            $beforeSerialize
                   
            var params = $(form).serializeArray();
            $.each(params, function (i, val) {
               fdata.append(val.name, val.value);
            });
            fdata.append('formId', '${formId}');
                
            $.each($(form).find('input[type=\'file\']'), function(i, tag) {
               $.each($(tag)[0].files, function(i, file) {
                  fdata.append(tag.name, file);
               });
            }); 
EOD
         ;
      } 

      $token=token();
      if ($formId===false) //nadodamo token (forma ga nadoda sutomatski
         $script[]="fdata.append('YII_CSRF_TOKEN', '${token}');";   
      
      if ($promptText!==false)
         $script[]="fdata.append('prompt_return', prompt_return);";  

      foreach($data_arr as $key=>$value){
         if (is_string($value)){
            if (lefts($value,1)=='<'){
               $value=substr($value,1 , strlen($value)-2); 
               $script[]="fdata.append('${key}', ${value});";  
            }
            else
               $script[]="fdata.append('${key}', '${value}');";
         }  
         else
            $script[]="fdata.append('${key}', ${value});";   
      }    

      $script[]= <<<EOD
         jQuery.ajax({
            'url':'${url}',
            'data':fdata,
            'type':'${type}',
            'dataType':'${dataType}',
            'processData': false,
            'contentType': false,
            'cache':false,
            'success':function(data){
               ${onSuccess}
            },
            'complete':function(jqXHR, textStatus){
               btn.attr('disabled', false);
               ${onComplete}
            },
        });
        event.preventDefault();
        event.stopPropagation(); 
EOD
      ;                                                      
      $script_str=preg_replace("/\s+/", " ", implode(' ', $script)); //maknemo newline & spaces
      $options=array_merge($options,array('onclick'=>$script_str));
      return CHtml::link($text, '#', $options); 
   }












}  
?>
