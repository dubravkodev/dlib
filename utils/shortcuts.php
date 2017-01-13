<?php
    /**
    * This file contains the shortcuts unit.
    *
    * @author Dubravko Loborec <info@dubravkodev.com>
    * @link http://www.dubravkodev.com/
    * @copyright 2014-2017 Dubravko Loborec
    * @license http://www.dubravkodev.com/license/
    */

    /**
    * Function appdir returns the root project directory without a backslash at the end of string.
    * 
    * @param string $dir directory inside project directory
    * @return string
    */
    function appdir($dir=null){
        return $dir===null ? Yii::getPathOfAlias('webroot') : Yii::getPathOfAlias('webroot').'/'.ltrim($dir,'/');
    }

    /**
    * Function appurl returns relative url of the project directory
    * 
    * @param mixed $url
    * @return string
    */
    function appurl($url=null){ 
        static $baseUrl;
        if ($baseUrl===null)
            $baseUrl=Yii::app()->getRequest()->getBaseUrl();
        return $url===null ? $baseUrl : $baseUrl.'/'.ltrim($url,'/');
    }

    /**
    * Function url is s shosrtcut to the Yii method createUrl.
    * 
    * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
    * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
    * @param string $ampersand the token separating name-value pairs in the URL.
    * @return string
    */
    function url($route,$params=array(),$ampersand='&'){
        return Yii::app()->createUrl($route,$params,$ampersand);
    }

    /**
    * Function url is s shortcut to the Yii method createAbsoluteUrl.
    * 
    * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
    * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
    * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
    * @param string $ampersand the token separating name-value pairs in the URL.
    * @return string
    */
    function absurl($route, $params=array(), $schema='', $ampersand='&'){
        return Yii::app()->createAbsoluteUrl($route,$params,$schema,$ampersand);  
    }

    /**
    * Function url is s shortcut to the Yii method name. Defaults to 'My Application'.
    * 
    */
    function appname(){
        return Yii::app()->name;
    }       

    /**
    * Function uid returns current logged useer ID.
    * 
    */
    function uid(){ 
        return Yii::app()->user->id; 
    }

    /**
    * Function logged checks if user is logged or not
    * 
    * @return boolean true if logged false if not
    */
    function logged(){
        return uid()!=null;
    }

    /**
    * Function ctrl returns current controller instance
    * 
    */
    function ctrl(){
        return Yii::app()->controller;
    }   

    /**
    * Function route returns current route
    * 
    * @return string
    */
    function route(){
        $s= Yii::app()->controller->id."/".Yii::app()->controller->action->id;

        $module=module();
        if ($module!==null)
            $s=$module->id."/".$s;
        return $s;
    }

    /**
    * Function token is a shortcut to the Yii methods request->csrfToken.
    * Returns the random token used to perform CSRF validation. The token will be read from cookie first. If not found, a new token will be generated.
    */
    function token(){
        return Yii::app()->request->csrfToken;    
    }

    /**
    * Function h is a shortcut to the PHP function htmlspecialchars.
    * 
    * @param string $text
    * @return string
    */
    function h($text){
        return htmlspecialchars($text, ENT_QUOTES, Yii::app()->charset);
    }

    /**
    * Function e is a shortcut to the Yii CHtml::encode method.
    * 
    * @param string $text
    * @return string
    */
    function e($text) {
        return CHtml::encode($text);
    }

    /**
    * Function l is a shortcut to the html a tag.
    * 
    * @param string $text
    * @param string $url
    * @param array $options
    * @return string
    */
    function l($text, $url = '#', $options = array()){

        if ($url===''){
            $url='javascript:void(0)';   

            if (isset($options) and isset($options['onClick'])){
                $options['onClick']=$options['onClick'].'return false;';
            }
        }
        return CHtml::link($text, $url, $options);
    } 

    /**
    * Function a is a shortcut to the dlib DHtml a method.
    * 
    * @param string $text
    * @param string $url
    * @param array $options
    * @return string
    */
    function a($text, $url = '#', $options = array()){
        return DHtml::a($text, $url, $options);
    }

    /**
    * Function l is a shortcut to the Yii CHtml::image method.
    * 
    * @param string $src
    * @param string $alt
    * @param array $htmlOptions
    * @return string
    */
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

    /**
    * Function li renders li tag.
    * 
    * @param string $content
    * @param array $options
    * @return string
    */
    function li($content, $options = array()){
        return CHtml::tag('li', $options, $content);
    }

    /**
    * Function css is a shortcut to the Yii CHtml::css method.
    * 
    * @param string $text
    * @param string $media
    * @return string
    */
    function css($text, $media=''){
        return CHtml::css($text,$media)."\n"; 
    }  

    /**
    * Function cssFile is a shortcut to the Yii CHtml::cssFile method.
    * 
    * @param string $url
    * @param string $media
    * @return string
    */
    function cssFile($url, $media=''){
        echo CHtml::cssFile($url,$media)."\n"; 
    }

    /**
    * Function scriptFile is a shortcut to the Yii CHtml::scriptFile method.
    * 
    * @param string $url
    * @return string
    */
    function scriptFile($url){
        echo CHtml::scriptFile($url)."\n"; 
    }       

    /**
    * Function gread returns $_GET param.
    * 
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
    function gread($key, $default=null){ 
        return Yii::app()->request->getQuery($key, $default);
    }

    /**
    * Function greada returrns $_GET param only from ajax request.
    * 
    * @param mixed $key
    * @param mixed $default
    * @return mixed
    */
    function greada($key, $default=null){ 
        if (! Yii::app()->request->isAjaxRequest){
            return Yii::app()->request->getQuery($key, $default);
        }
        else
            return $default;
    }

    /**
    * Function pread returns $_POST param.
    * 
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
    function pread($key, $default=null){ 
        return Yii::app()->request->getPost($key, $default);
    }

    /**
    * Function pread reads param from the session.
    * 
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
    function sread($key, $default=null){
        $x=Yii::app()->session[$key];
        return isset($x)?$x:$default;   
    }

    /**
    * Function swrite writes param to the session.
    * 
    * @param string $key
    * @param mixed $value
    */
    function swrite($key, $value){
        Yii::app()->session[$key]=$value;
    }

    /**
    * Function sdel deletes key from the session.
    * 
    * @param string $key
    */
    function sdel($key){
        if (isset(Yii::app()->session[$key]))
            unset(Yii::app()->session[$key]); 
    }    

    /**
    * Function cread reads param from the cookie.
    * 
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
    function cread($key, $default=null){
        if (isset(Yii::app()->request->cookies[$key]))
            return Yii::app()->request->cookies[$key]->value;
        else
            return $default;   
    }

    /**
    * Function cwrite writes param to the cookie.
    * 
    * @param string $key
    * @param mixed $value
    * @param mixed $expire
    */
    function cwrite($key, $value, $expire=15552000){
        $v=cread($key, null);
        if ($v!=$value){  
            $cookie = new CHttpCookie($key, $value);
            $cookie->expire = time()+$expire; //60*60*24*180
            Yii::app()->request->cookies[$key] = $cookie;
        }      
    }

    /**
    * Function cdel deletes cookie.
    * 
    * @param mixed $key
    */
    function cdel($key){
        if (isset(Yii::app()->request->cookies[$key])) 
            unset(Yii::app()->request->cookies[$key]);
    } 

    /**
    * Function error is a shortcut to the Yii CHttpException exception.
    * 
    * @param string $code
    * @param string $message
    */
    function error($code, $message){
        throw new CHttpException($code, $message);
    }

    /**
    * Function format_float formats number into the string, using simplified Delphi pattern.
    * 
    * @param string $delphi_pattern
    * @param mixed $value
    * @param mixed $currency
    */
    function format_float($delphi_pattern, $value, $currency=null){
        $pattern=$delphi_pattern; 
        if (strpos($delphi_pattern, ',')!==false){
            $pattern=ldel($delphi_pattern,1); 
            $pattern='#,##'.$pattern;            
        }
        return Yii::app()->numberFormatter->format($pattern, $value, $currency);
    }

    /**
    * Function module returns module instance
    * 
    * @param string $moduleName
    */
    function module($moduleName=null){
        if ($moduleName===null)
            return Yii::app()->controller->module;        
        else
            return Yii::app()->getModule($moduleName); 
    }

    /**
    * Function rp is a shortcut to the Yii renderPartial method.
    * 
    * @param mixed $ctrl
    * @param string $view
    * @param array $data
    * @param boolean $processOutput
    * @return string
    */
    function rp($ctrl, $view, $data=array(), $processOutput=false){
        return $ctrl->renderPartial($view, $data, true, $processOutput); 
    } 

    /**
    * Function panel is a shortcut to the dlib DPanel::panel method.
    * 
    * @param mixed $title
    * @param mixed $options
    * @return string
    */
    function panel($title, $options=array()){
        return DPanel::panel($title, $options);
    }

    /**
    * Function panel_dialog is a shortcut to the dlib DScript::panel_dialog.
    * 
    * @param mixed $title
    * @param mixed $options
    * @return string
    */
    function panel_dialog($title, $options=array()){
        return DScript::panel_dialog($title, $options);
    }
    
    /**
    * Shortcuts to the dlib DSqlGrid::sql_grid method.
    * 
    * @param mixed $controller
    * @param array $params
    * @return string
    */
    function sql_grid($controller, $params=array()){
        return DSqlGrid::sql_grid($controller, $params);   
    }

    /**
    * Function script is a shortcut to the Yii CHtml::script method.
    * 
    * @param mixed $text
    * @return string
    */
    function script($text){
        return CHtml::script($text)."\n"; 
    }

    /**
    * Function global_counter is useful for generating unique IDs.
    * 
    * @param string $key
    * @return integer
    */
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
    * Function reg_head is a shortcut to the Yii registerScript method.
    * 
    * @param string $script
    */
    function reg_head($script){
        $i=global_counter('_SCRIPT_COUNTER');

        $cs=Yii::app()->getClientScript();
        $cs->registerScript("registered_script_${i}", $script, CClientScript::POS_HEAD); 
    }

    /**
    * Function reg_end is a shortcut to the Yii registerScript method.
    * 
    * @param string $script
    */

    function reg_end($script){
        $i=global_counter('_SCRIPT_COUNTER');

        $cs=Yii::app()->getClientScript();
        $cs->registerScript("registered_script_${i}", $script, CClientScript::POS_END); 
    }

    /**
    * Function reg_ready is a shortcut to the Yii registerScript method.
    * 
    * @param string $script
    */
    function reg_ready($script){
        $i=global_counter('_SCRIPT_COUNTER');

        $cs=Yii::app()->getClientScript();
        $cs->registerScript("registered_script_${i}", $script, CClientScript::POS_READY); 
    }

    /**
    * Function reg_css is a shortcut to the Yii registerCSS method.
    * 
    * @param string $script
    */
    function reg_css($script){
        $i=global_counter('_SCRIPT_COUNTER');
        $cs=Yii::app()->getClientScript();
        $cs->registerCSS("registered_script_${i}", $script, 'screen'); 
    } 

    /**
    * Function grid_data intercepts CGridView ajax update post.
    * 
    * @param string $grid_id
    * @return mixed array of data or false
    */
    function grid_data($grid_id){
        $data=$_GET;
        if (key_exists('ajax', $data) and ($data['ajax']===$grid_id)){
            unset($data['ajax']);
            return $data;  
        }
        else
            return false;
    }

    /**
    * Shortcuts to the dlib DValidator::save() method.
    * 
    * @param mixed $model
    * @param array $data
    */
    function save($model, $data=null){
        return DValidator::save($model, $data);   
    }

    /**
    * Shortcuts to the dlib DValidator::validate() method.
    * 
    * @param mixed $model
    * @param array $data
    */
    function validate($model, $data=null){
        return DValidator::validate($model, $data);   
    }



