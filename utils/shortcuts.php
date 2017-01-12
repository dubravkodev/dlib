<?php

    /**
    * Copyright © 2014-2016 Dubravko Loborec
    */

    function appdir($dir=null){
        return $dir===null ? Yii::getPathOfAlias('webroot') : Yii::getPathOfAlias('webroot').'/'.ltrim($dir,'/');
    }

    function appurl($url=null){ 
        static $baseUrl;
        if ($baseUrl===null)
            $baseUrl=Yii::app()->getRequest()->getBaseUrl();
        return $url===null ? $baseUrl : $baseUrl.'/'.ltrim($url,'/');
    }

    function url($route,$params=array(),$ampersand='&'){
        return Yii::app()->createUrl($route,$params,$ampersand);
    }

    function absurl($route,$params=array(), $schema='', $ampersand='&'){
        return Yii::app()->createAbsoluteUrl($route,$params,$schema,$ampersand);  
    }

    function appname(){
        return Yii::app()->name;
    }       

    function uid(){ 
        return Yii::app()->user->id; 
    }

    function logged(){
        return uid()!=null;
    }

    function ctrl(){
        return Yii::app()->controller;
    }   

    function route(){
        $s= Yii::app()->controller->id."/".Yii::app()->controller->action->id;

        $module=module();
        if ($module!==null)
            $s=$module->id."/".$s;
        return $s;
    }

    function token(){
        return Yii::app()->request->csrfToken;    
    }

    function h($text){
        return htmlspecialchars($text,ENT_QUOTES,Yii::app()->charset);
    }

    function e($text) {
        /**
        * This is the shortcut to CHtml::encode
        */
        return CHtml::encode($text);
    }

    function l($text, $url = '#', $options = array()){
        
        if ($url===''){
            $url='javascript:void(0)';   
            
            if (isset($options) and isset($options['onClick'])){
                $options['onClick']=$options['onClick'].'return false;';
            }
        }
        
        return CHtml::link($text, $url, $options);
    } 
    
    function a($text, $url = '#', $options = array()){
        return DHtml::a($text, $url, $options);
    }

    function i($src, $alt='', $htmlOptions=array()){
        $n=strrpos($src, '/');
        if ($n!==false){
            $alt=ldel($src, $n+1);
            $n=strrpos($alt, '.');    
            if ($n!==false){
                $alt=lefts($alt, $n);
            }
        }
        return CHtml::image($src, $alt, $htmlOptions);  
    }

    function li($content, $options = array()){
        return CHtml::tag('li', $options, $content);
    }

    function css($text, $media=''){
        return CHtml::css($text,$media)."\n"; 
    }  

    function cssFile($url, $media=''){
        echo CHtml::cssFile($url,$media)."\n"; 
    }

    function scriptFile($url){
        echo CHtml::scriptFile($url)."\n"; 
    }       

    function gread($key, $default=null){ 
        //return isset($_GET[$key])?$_GET[$key]:$default;   
        return Yii::app()->request->getQuery($key, $default);
    }

    function gread1($key, $default=null){ 
        if (! Yii::app()->request->isAjaxRequest){
            return Yii::app()->request->getQuery($key, $default);
        }
        else
            return $default;
    }

    function pread($key, $default=null){ 
        //return isset($_POST[$key])?$_POST[$key]:$default;   
        return Yii::app()->request->getPost($key, $default);
    }

    function sread($key, $default=null){
        /* read from session */ 
        $x=Yii::app()->session[$key];
        return isset($x)?$x:$default;   
    }

    function swrite($key, $value){
        /* write to session */
        Yii::app()->session[$key]=$value;
    }

    function sdel($key){
        /* delete from session */
        if (isset(Yii::app()->session[$key]))
            unset(Yii::app()->session[$key]); 
    }    

    function cread($key, $default=null){
        /* read from cookie */ 
        if (isset(Yii::app()->request->cookies[$key]))
            return Yii::app()->request->cookies[$key]->value;
        else
            return $default;   
    }

    function cwrite($key, $value, $expire=15552000){
        /* write to cookie */
        $v=cread($key, null);
        if ($v!=$value){  
            $cookie = new CHttpCookie($key, $value);
            $cookie->expire = time()+$expire; //60*60*24*180
            Yii::app()->request->cookies[$key] = $cookie;
        }      
    }

    function cdel($key){
        /* delete cookie */
        if (isset(Yii::app()->request->cookies[$key])) 
            unset(Yii::app()->request->cookies[$key]);
    } 
 

    function error($code, $message){
        throw new CHttpException($code, $message);
    }

    function format_float($delphi_pattern, $value, $currency=null){
        $pattern=$delphi_pattern; 
        if (strpos($delphi_pattern, ',')!==false){
            $pattern=ldel($delphi_pattern,1); 
            $pattern='#,##'.$pattern;            
        }
        return Yii::app()->numberFormatter->format($pattern, $value, $currency);
    }

    function module($moduleName=null){
        if ($moduleName===null)
            return Yii::app()->controller->module;        
        else
            return Yii::app()->getModule($moduleName); 
    }

    function rp($ctrl, $view, $data=array(), $processOutput=false){
        return $ctrl->renderPartial($view, $data, true, $processOutput); 
    } 

    function panel($title, $options=array()){
        return DPanel::panel($title, $options);
    }

    function panel_dialog($title, $options=array()){
        return DScript::panel_dialog($title, $options);
    }

    function script($text){
        return CHtml::script($text)."\n"; 
    }

    function global_counter($key){
        if (isset($GLOBALS[$key])){
            $v=(int)$GLOBALS[$key]+1; 
            $GLOBALS[$key]=$v;
        }
        else
        {
            $v=1;
            $GLOBALS[$key]=$v;  
        }
        return  $v;
    }

    /**
    * put your comment there...
    * 
    * @param mixed $script
    */
    function reg_head($script){
        $i=global_counter('_SCRIPT_COUNTER');

        $cs=Yii::app()->getClientScript();
        $cs->registerScript("registered_script_${i}", $script, CClientScript::POS_HEAD); 
    }

    /**
    * Shortcut to registerScript
    * 
    * @param mixed $script
    */

    function reg_end($script){
        $i=global_counter('_SCRIPT_COUNTER');

        $cs=Yii::app()->getClientScript();
        $cs->registerScript("registered_script_${i}", $script, CClientScript::POS_END); 
    }

    /**
    * Shortcut to registerScript
    * 
    * @param mixed $script
    */
    function reg_ready($script){
        $i=global_counter('_SCRIPT_COUNTER');

        $cs=Yii::app()->getClientScript();
        $cs->registerScript("registered_script_${i}", $script, CClientScript::POS_READY); 
    }

    /**
    * Shortcut to registerCSS
    * 
    * @param mixed $script
    */
    function reg_css($script){
        $i=global_counter('_SCRIPT_COUNTER');
        $cs=Yii::app()->getClientScript();
        $cs->registerCSS("registered_script_${i}", $script, 'screen'); 
    } 

    function grid_data($id){
        $data=$_GET;
        if (key_exists('ajax', $data) and ($data['ajax']===$id)){
            unset($data['ajax']);
            return $data;  
        }
        else
            return false;
    }


    /**
    * shortcuts to XValidator::save()
    * 
    * @param mixed $model
    * @param mixed $data
    */
    function save($model, $data=null){
        return DValidator::save($model, $data);   
    }

    /**
    * shortcuts to XValidator::validate()
    * 
    * @param mixed $model
    * @param mixed $data
    */
    function validate($model, $data=null){
        return DValidator::validate($model, $data);   
    }

    function sql_grid($controller, $params=array()){
        return DSqlGrid::sql_grid($controller, $params);   
    }