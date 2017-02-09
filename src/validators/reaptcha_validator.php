<?php

    class reaptcha_validator extends CValidator
    {

        public $secret_key;

        /**
        * Validates the attribute of the object.
        * If there is any error, the error message is added to the object.
        * @param CModel $object the object being validated
        * @param string $attribute the attribute being validated
        */
        protected function validateAttribute($object, $attribute)
        {
            $recaptcha = new \ReCaptcha\ReCaptcha($this->secret_key);

            $value=$object->$attribute;

            $resp = $recaptcha->verify($value, $_SERVER['REMOTE_ADDR']);
            if ($resp->isSuccess()) {
                // verified!
            } else {
                $errors = $resp->getErrorCodes();
                $this->addError($object, $attribute, 'reCAPTCHA: '. implode(', ', $errors));
            }  
        }

    } 

