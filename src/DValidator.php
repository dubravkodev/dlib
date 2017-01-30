<?php   

    /**
    * Copyright © 2015-2016 Dubravko Loborec
    */

    class DValidator{

        public static function save($model, $data=null){
            if ($data!==null)
                $model->attributes=$data;
            return self::_vv($model->save(), $model);
        }

        public static function validate($model, $data=null){
            if ($data!==null)
                $model->attributes=$data;
            return  self::_vv($model->validate(), $model);
        }

        private static function _vv($result, $model){
            if ($result){
                try {
                    self::captcha_reset_counter();
                    echo self::errors_clear_js();
                    return true; 
                }
                catch (Exception $e) {
                    $x=$e->getMessage();
                    $x=preg_replace("/\r?\n/", "\\n", addslashes($x));
                    echo self::js_alert($x);
                    exit;
                }
            }
            else
            {
                echo self::show_errors($model); 
                //$formId=$_POST['formId'];
                //self::captcha_inc_counter($model, $formId);
            }
        }

        public static function errors_clear_js(){
            return "
            $('.form-group').removeClass('error');
            $('.errorMessage').hide();
            $('.errorSummary').hide();
            $('.captcha_placeholder').hide();
            ";
        } 

        private static function show_errors($model){

            $formId=$_POST['formId'];


            $errors=$model->getErrors();
            $jserrors=base64_encode(json_encode($errors));  //fix base64_encode


            $ModelFormName=get_class($model);


            if (self::captcha_check($model, $formId))
            {
                $id=$formId.'_captcha';
                $captcha= "jQuery('.captcha_placeholder').show(); jQuery('#$id').find('a').first().trigger('click');";
            }
            else
            {
                $captcha='';
            };

            return     //set timeout treba jer se inače preklapa sa client validacijom
            "setTimeout(function() {". 

            self::errors_clear_js()."
            var form=jQuery('#'+'${formId}');

            var obj = jQuery.parseJSON(base64_decode('${jserrors}'));

            var summary='<p>Please fix the following input errors:</p>';
            jQuery.each(obj, function(key, value) {
            summary=summary+value+'<br>';

            var id='#${ModelFormName}'+'_'+key;
            var fc=$(id +'.form-control');

            var fg = fc.closest('.form-group'); /* form group */
            fg.addClass('error'); 

            $(id+'_em_').text(value);
            $(id+'_em_').show(); 
            });



            var ctrl=jQuery('#'+'${formId}_es_');
            ctrl.html(summary);
            ctrl.show();

            $captcha

            }, 500);

            ";    
        }

        public static function form_clear_js($formId){
            return "
            $('#$formId input[name!=\'YII_CSRF_TOKEN\']').val(''); //izbrišemo sve osim tokena
            $('#$formId textarea').val('');
            ";
        }

        private static function captcha_reset_counter(){
            swrite('validation_error_count',0);
        }

        private static function captcha_check($model, $formId){
            $result=false;
            swrite('validation_error_count', sread('validation_error_count',0)+1);
            if (sread('validation_error_count',0)>10){
                $result=true;
            }
            return $result;
        }

        public static function captcha_test_counter(){
            return (sread('validation_error_count',0)>10)?true:false;
        }

    }

