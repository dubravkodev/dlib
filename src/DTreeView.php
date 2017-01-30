<?php

  class DTreeView{
      
      
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        // treeView
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        private static function _tree_loop($item){
            $h=array();
            $h[]= "{";
            $h[]="text: '<span data-id=".$item['id'].">".$item['name']."</span>',";
            if (isset($item['href']))
                $h[]="href: '".$item['href']."',";

            $h[]="state: {";
            if (isset($item['checked']) && ($item['checked']===true))
                $h[]="checked: true,";                                 
            if (isset($item['selected']) && ($item['selected']===true))
                $h[]="selected: true,";
            if (isset($item['expanded']) && ($item['expanded']===true))
                $h[]="expanded: true,";  
            $h[]="},";

            $xx=$item['children'];
            if (count($xx)>0){
                $h[]= "nodes: ["; 
                foreach($xx as $x)
                {
                    $h[]=self::_tree_loop($x);  
                }  
                $h[]= "],"; 
            }
            $h[]= "},";
            return implode(' ',$h);
        }

        public static function treeView($id, $items, $options=array()){

            $html=array();

            $html[]="<div id='$id'></div>";

            $js=array();
            $js[]= "var ${id}_data = ["; 
            foreach ($items as $item){
                $js[]=self::_tree_loop($item); 
            }  



            $js[]= "];"; 

            $h=array();
            $h[]="data: ${id}_data";

            if (isset($options['enableLinks']))
                $h[]='enableLinks: '.($options['enableLinks']===true?'true':'false');

            if (isset($options['showBorder']))
                $h[]='showBorder: '.($options['showBorder']===true?'true':'false');    

            if (isset($options['showCheckbox']))
                $h[]='showCheckbox: '.($options['showCheckbox']===true?'true':'false');  

            if (isset($options['highlightSelected']))
                $h[]='highlightSelected: '.($options['highlightSelected']===true?'true':'false');      

            if (isset($options['levels']))
                $h[]='levels: '.$options['levels'];

            if (isset($options['backColor']))
                $h[]='backColor: '.$options['backColor']; 

            if (isset($options['onNodeSelected']))
                $h[]='onNodeSelected: '.$options['onNodeSelected'];



            if (isset($options['icon']))
                $h[]='icon: \''.$options['icon'].'\'';       

            if (isset($options['selectedIcon']))
                $h[]='selectedIcon: \''.$options['selectedIcon'].'\'';  

            if (isset($options['uncheckedIcon']))
                $h[]='uncheckedIcon: \''.$options['uncheckedIcon'].'\'';  

            if (isset($options['checkedIcon']))
                $h[]='checkedIcon: \''.$options['checkedIcon'].'\'';     


            $js[]= "$('#$id').treeview({".implode(',',$h)."});";

            $js[]= "var node=($('#$id').treeview('getSelected'));
            $('#$id').treeview('revealNode', [ node, { silent: true } ]);
            ";  

            if (isset($options['disabled'])){
                $disabled=$options['disabled'];
            }
            else
                $disabled=false;  

            if ($disabled)
                $js[]= "$('#$id').treeview('disableAll', { silent: true });";  



            $html[]= DScript::ready(implode("\n", $js));

            return implode("\n", $html);
        }





  }

  ?>
