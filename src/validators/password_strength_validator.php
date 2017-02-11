<?php
    class password_strength_validator extends CValidator{

        public $minLength;
        public $requireNumbers;
        public $requireLowercase;
        public $requireUppercase;
        public $requireSpecialChars;

        /**
        * Validates the attribute of the object.
        * If there is any error, the error message is added to the object.
        * @param CModel $object the object being validated
        * @param string $attribute the attribute being validated
        */
        protected function validateAttribute($object, $attribute)
        {
            $value=$object->$attribute;
            $valid = true;
            $errorStr=Yii::t('PublicModule.register', 'The password is not secure enough.');

            if ($this->minLength!==null){
                $valid = $valid && (strlen($value) > $this->minLength); // min size
                $errorStr.=' '.Yii::t('PublicModule.register', 'Minimum length is {number}.', array('{number}'=>$this->minLength));
            }

            $use=[];
            if ($this->requireNumbers===true){
                $valid = $valid && preg_match('/[0-9]/', $value); // digit
                $use[]=Yii::t('PublicModule.register', 'numbers');
            }

            if ($this->requireLowercase===true){
                $valid = $valid && preg_match('/[a-z]/', $value);
                $use[]=Yii::t('PublicModule.register', 'lowercase characters');
            }

            if ($this->requireUppercase===true){
                $valid = $valid && preg_match('/[A-Z]/', $value);
                $use[]=Yii::t('PublicModule.register', 'uppercase characters');
            }

            if ($this->requireSpecialChars===true){
                $valid = $valid && preg_match('/[\W]+/', $value);
                $use[]=Yii::t('PublicModule.register', 'special characters');
            }

            if (count($use)>0){
               $errorStr.=' '.Yii::t('PublicModule.register', 'Use: {s}.', array('{s}'=>implode(', ', $use)));
            }

            if ($valid) {
                // verified!
            } else {
                $this->addError($object, $attribute, $errorStr);
            }  

        }

    } 

