<?php

    class DCombine{

        const robocopy='c:\Apps\scripts\bin\robocopy.exe';

        public static function combine($includes, $sourceEx, $id, $dir){
            $result=false;
            $sources=[];

            foreach ($includes as $i){
                include(C_LIBRARY ."/sources/${i}.php");  //ne include_once
            }

            $sources=array_merge($sources, $sourceEx);

            self::less_init($sources);

            $r1=self::js_init($sources, $dir, $id);
            $r2=self::css_init($sources, $dir, $id, true); 

            if($r1 || $r2){ 
                self::copy_init($sources, $dir);
                $result=true;
            }  
            return $result;
        }

        private static function copy_init($sources, $output_dir){
            //$files=array();
            foreach ($sources as $source){
                $command=$source[0];

                if ($command==='copy'){
                    $source_filename=$source[1];
                    $target_filename=$output_dir.$source[2];
                    if (! file_exists($source_filename))
                        die('Not found: '.$source_filename);
                    //
                    xcopy($source_filename, $target_filename); 
                }
                else if ($command==='robocopy'){
                    $source_filename=$source[1];
                    $target_filename=$output_dir.$source[2];
                    $params=isset($source[3])?$source[3]:'';
                    if (! file_exists($source_filename))
                        die('Not found: '.$source_filename);
                    //
                    if (file_exists(self::robocopy)){
                        exec(self::robocopy.' "'.$source_filename.'" "'.$target_filename.'" '.$params);
                    }
                    else
                    {
                        xcopy($source_filename, $target_filename); 
                    }
                }
            }
        }

        private static function js_init($sources, $dir, $id){
            $files=array();
            foreach ($sources as $source){
                if ($source[0]=='js'){
                    $files[]=$source[1];
                    if (! file_exists($source[1])){
                        die('Not found: '.$source[1]);
                    }
                }
            }
            return self::js_combine($files, $dir, $id);
        }

        private static function js_combine($files, $dir, $id){
            require_once(C_LIBRARY."/php/JShrink/src/JShrink/Minifier.php");

            $x = '';
            foreach($files as $file){ 
                $x.= (string)filemtime($file);
            }
            $md5=md5($x);

            $output_file="${dir}${id}.${md5}.js";


            if (! file_exists($output_file)){

                //pobrišemo sve .js datoteke u folderu
                del_files($dir."$id.*.js");


                $table=array();
                $js = '';
                foreach($files as $file){ 
                    if (strpos($file,'min.js')!=0){ 
                        $j= file_get_contents($file); 

                        $js .=$j;
                        $table[]=array(extract_file_name($file), mb_strlen($j),'');   
                    } 
                    else{  
                        $j= \JShrink\Minifier::minify(file_get_contents($file));
                        $js .=$j;
                        $table[]=array(extract_file_name($file), filesize($file),  mb_strlen($j));  
                    }      
                    $js .=';';     
                }





                $header=array();
                $header[]='/*!';
                $header[]=str_pad('', 64,chr(151));
                $header[]='|'.str_pad('Infoplus JS Compactor',62,' ', STR_PAD_BOTH).'|'; 
                $header[]=str_pad('', 64,chr(151));
                $header[]='|'.str_pad('File Name',40).'|'.str_pad('Orig. size',10,' ',STR_PAD_LEFT).'|'.str_pad('Comp. size',10,' ',STR_PAD_LEFT).'|'; 
                $header[]=str_pad('', 64,chr(151));
                for ($i = 0; $i <= count($table)-1 ; $i++) {
                    $header[]='|'.str_pad($table[$i][0],40).'|'.str_pad($table[$i][1],10,' ',STR_PAD_LEFT).'|'.str_pad($table[$i][2],10,' ',STR_PAD_LEFT).'|';              
                }
                $header[]=str_pad('', 64, chr(151));
                $header[]='*/';
                $header[]='';












                file_put_contents($output_file, implode("\n",$header).$js);
                // file_put_contents($md5_file, $md5); 

                return true;
            }
            else
                return false;
        }

        private static function valid_ext($ext){
            $ext=strtolower($ext);
            return ($ext=='gif') or ($ext=='png') or ($ext=='jpg') or ($ext=='jpeg');      
        } 

        private static function less_init($sources){
            $files=array();
            foreach ($sources as $source){
                if ($source[0]=='less'){
                    if (! file_exists($source[1]))
                        die('Not found: '.$source[1]);
                    self::less_compile($source);
                }
            }
        }

        private static function less_compile($source){
            require_once(C_LIBRARY."/php/lessphp/lessc.inc.php");
            
            self::autoCompileLess($source[1], $source[2]);
           /*$less = new lessc;
            $less->setImportDir(array(C_LIBRARY));
            $less->checkedCompile($source[1], $source[2]);*/
           //$less->compileFile($source[1], $source[2]);
        }
        
        private static function autoCompileLess($inputFile, $outputFile) {
  // load the cache
  $cacheFile = $inputFile.".cache";

  if (file_exists($cacheFile)) {
    $cache = unserialize(file_get_contents($cacheFile));
  } else {
    $cache = $inputFile;
  }

  $less = new lessc;
  $less->setImportDir(array(C_LIBRARY));
  $newCache = $less->cachedCompile($cache);

  if (!is_array($cache) || $newCache["updated"] > $cache["updated"]) {
    file_put_contents($cacheFile, serialize($newCache));
    file_put_contents($outputFile, $newCache['compiled']);
  }
}

        private static function css_init($sources, $dir, $id, $dataURI=false){
            $files=array();
            foreach ($sources as $source){
                if ($source[0]=='css'){
                    $files[]=$source[1];
                    if (! file_exists($source[1]))
                        die('Not found: '.$source[1]);
                }
            }
            return self::css_combine($files, $dir, $id, $dataURI);
        }    

        private static function css_combine($files, $dir, $id, $dataURI=false){
            require_once(C_LIBRARY."/php/cssmin/cssmin.php");

            $x=''; 


            /**** md5 slika! ****/
            if ($dataURI){

                foreach($files as $file){ 
                    $css = file_get_contents($file);
                    preg_match_all('/url\((.*)\)/',$css,$matches);

                    foreach ($matches[0] as $match){
                        $vowels = array("'", '\"', '"', 'url(', ')');   
                        $s = str_replace($vowels, "", $match);

                        $i=mb_strripos($file, '/');
                        $image_name=substr($file,0,$i).'/'.$s;

                        if (file_exists($image_name))  {


                            $ext=extract_file_ext($image_name); 

                            if (self::valid_ext($ext)){
                                $x.= (string)filemtime($image_name); 
                            }   
                        } 
                    }
                }
            }





            foreach($files as $file){ 
                $x.= (string)filemtime($file);
            }
            $md5=md5($x);


            $output_file="${dir}${id}.${md5}.css";

            if (! file_exists($output_file)){
                //pobrišemo sve .css datoteke u folderu
                del_files($dir."$id.*.css");

                $css = '';
                $table=array();


                $output_css='';
                $compressor = new CSSmin(); 
                foreach($files as $file){ 
                    if (strpos($file,'min.css')!=0) 
                    {
                        $css = file_get_contents($file);  
                        $output_css.=$css;  

                        $table[]=array(extract_file_name($file), mb_strlen($css),'');  
                    }
                    else
                    {   
                        if ($dataURI)
                            $css = self::uri_file_get_contents($file);  
                        else     
                            $css = file_get_contents($file);
                        //
                        $css = $compressor->run($css); 
                        $output_css.=$css;

                        $table[]=array(extract_file_name($file), filesize($file),  mb_strlen($css));  
                    } 
                }


                $header=array();
                $header[]='/*!';
                $header[]=str_pad('', 64,chr(151));
                $header[]='|'.str_pad('Infoplus CSS Compactor',62,' ', STR_PAD_BOTH).'|'; 
                $header[]=str_pad('', 64,chr(151));
                $header[]='|'.str_pad('File Name',40).'|'.str_pad('Orig. size',10,' ',STR_PAD_LEFT).'|'.str_pad('Comp. size',10,' ',STR_PAD_LEFT).'|'; 
                $header[]=str_pad('', 64,chr(151));
                for ($i = 0; $i <= count($table)-1 ; $i++) {
                    $header[]='|'.str_pad($table[$i][0],40).'|'.str_pad($table[$i][1],10,' ',STR_PAD_LEFT).'|'.str_pad($table[$i][2],10,' ',STR_PAD_LEFT).'|';              
                }
                $header[]=str_pad('', 64, chr(151));
                $header[]='*/';
                $header[]='';

                file_put_contents($output_file, implode("\n",$header).$output_css);
                return true;
            }
            else
                return false;
        }

        private static function uri_file_get_contents($file){
            $css=file_get_contents($file);

            return preg_replace_callback( '/url\((.*)\)/', 
                function($m) use ($file) { return self::preg_replacex($m[0], $file); },
                $css); 
        }

        private static function preg_replacex($source, $css_file) {

            $vowels = array("'", '\"', '"', 'url(', ')');
            $s = str_replace($vowels, "", $source);


            $i=mb_strripos($css_file, '/');
            $image_name=substr($css_file,0,$i).'/'.$s;
            if (file_exists($image_name))
            {
                // $i= strripos($image_name, '.');
                // $ext=strtolower(substr($image_name,$i+1,strlen($image_name)-$i));
                $ext=extract_file_ext($image_name); 
                $size=filesize($image_name); 
                if (self::valid_ext($ext) and ($size<10000) ){ //veće slike od 10kb izostavljamo iz kompresije    (strpos($image_name,'!')===false)
                    return "url(data:image/${ext};base64,".base64_encode(file_get_contents($image_name)).')';
                }
                {
                    return "url('${s}')";     
                }
            }
            else    
                return "url('${s}')";
        }

}