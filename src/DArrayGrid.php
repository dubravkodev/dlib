<?php
  
  class DArrayGrid{
      
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        // sqlGrid
        //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
        public static function render($controller, $params=array()){ 
            
            $data=$params['data'];
            
            /* CGridView + CSqlDataProvider shortcut - summer 2012. */
        /*    $sql=isset($params['sql'])?$params['sql']:'';  
            if (is_array($sql)){
                $sql=implode(' ', $sql); 
            }

            $countSql=isset($params['countSql'])?$params['countSql']:"SELECT count(*) z FROM ($sql) x";  
            if (is_array($countSql)){
                $countSql=implode(' ', $countSql); 
            }

            $sqlParams=isset($params['sqlParams'])?$params['sqlParams']:array();
            $countSqlParams=isset($params['countSqlParams'])?$params['countSqlParams']:$sqlParams; 


            $keyField=isset($params['keyField'])?$params['keyField']:null;
            */
            $pageSize=isset($params['pageSize'])?$params['pageSize']:null;


            $dataParams=isset($params['dataParams'])?$params['dataParams']:array();
            $gridParams=isset($params['gridParams'])?$params['gridParams']:array();

            if (! isset($gridParams['enableSorting'])) //defaults
                $gridParams['enableSorting']=true;  

            if (! isset($gridParams['hideHeader']))
                $gridParams['hideHeader']=true; 

            if (! isset($gridParams['selectableRows']))
                $gridParams['selectableRows']=0; 

            if (! isset($gridParams['showTableOnEmpty']))
                $gridParams['showTableOnEmpty']=false;   

            if (! isset($gridParams['emptyText']))
                $gridParams['emptyText']="No records found";         

            //    if (!isset($gridParams['itemsCssClass']))
            //  $gridParams['itemsCssClass']='teble table-responsive';

            if (! isset($gridParams['template']))
                //$gridParams['template']="{items}\n<div class=\"row-fluid\"><div class=\"span6\">{pager}</div><div class=\"span6\">{summary}</div></div>";  
                //    $gridParams['template']="{items}\n{summary}\n{pager}";

                $gridParams['template']="{items}\n{pager}";
            //    
            // if (! isset($params['summaryText']))
            //  $params['summaryText']=false;   

            if (! isset($gridParams['htmlOptions'])){
                $options=array();
                $options['class']='grid-view table-responsive';
                $gridParams['htmlOptions']=$options;
            } 
            else {
                if (! isset($gridParams['htmlOptions']['class']))
                    $gridParams['htmlOptions']['class']='grid-view table-responsive';
            };

            /*
            $command=Yii::app()->db->createCommand($countSql);
            $command->params=$countSqlParams;

            $dataParams['totalItemCount']=$command->queryScalar();  
            if ($keyField!=null)
                $dataParams['keyField']=$keyField;
            

            $dataParams['params']=$sqlParams;
            $dataProvider=new CSqlDataProvider($sql, $dataParams);
*/

if ($pageSize!=null) 
                $dataParams['pagination']=array('pageSize'=>$pageSize);

        $dataProvider = new CArrayDataProvider($data, $dataParams);

            $gridParams['dataProvider']=$dataProvider; 

            return $controller->widget('zii.widgets.grid.CGridView', $gridParams ,true); 
        } 
        
  }
  
