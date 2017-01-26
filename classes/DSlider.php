<?php

    class DSlider {

        public static function slider($id,  $options=array()){
            $html=array();

            $options['data-id']=$id;

            if (isset($options['min'])){
                $min=$options['min'];
                unset($options['min']);
            }
            else
                $min=100;

            if (isset($options['max'])){
                $max=$options['max'];
                unset($options['max']);
            }
            else
                $max=100;

            if (isset($options['from'])){
                $from=$options['from'];
                unset($options['from']);
            }
            else
                $from=0;

            if ((isset($options['disabled'])) and ($options['disabled']==true)){
                $disable='true';
                unset($options['disabled']);
            }
            else
                $disable='false';

            $html[]=CHtml::textField($id, '',$options);     

            $html[]=script("
                $('[data-id=${id}]').ionRangeSlider({
                type: 'single',
                min: $min,
                max: $max,
                step: 1,
                from: $from,
                disable: $disable,
                grid: true,
                grid_snap: true,
                hide_min_max:true,
                hide_from_to:true,
                });
                ");  

            return implode(' ',$html);           
        }

        public function form_slider($form, $model, $attribute, $options=array()){
            $html=array();

            $id=CHtml::activeID($model,$attribute);

            $options['data-id']=$id;

            $from=(int)$model->$attribute;

            if (isset($options['min'])){
                $min=$options['min'];
                unset($options['min']);
            }
            else
                $min=100;

            if (isset($options['max'])){
                $max=$options['max'];
                unset($options['max']);
            }
            else
                $max=100;

            $html[]=$form->textField($model,$attribute, $options);   

            $html[]=script("
                $('[data-id=${id}]').ionRangeSlider({
                type: 'single',
                min: $min,
                max: $max,
                step: 1,
                from: ${from},
                grid: true,
                grid_snap: true,
                hide_min_max:true,
                hide_from_to:true,
                });
                ");  

            return implode(' ',$html);           
        }





    }

?>
