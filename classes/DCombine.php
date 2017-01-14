<?php

    class DCombine{

        const robocopy='c:\Apps\scripts\bin\robocopy.exe';

        public static function combine($includes, $sourceEx, $id, $dir){
            $result=false;
            $sources=[];

            foreach ($includes as $i){
                $sources[]=(include C_LIBRARY ."/sources/${i}.php");
            }
            $sources[]=$sourceEx;
            $sources= call_user_func_array('array_merge_recursive', $sources);

            self::less_init($sources['less']);

            $r1=self::js_init($sources['js'], $dir, $id);
            $r2=self::css_init($sources['css'], $dir, $id); 

            if($r1 || $r2){ 
                self::copy_init($sources['copy'], $dir);
                $result=true;
            }  
            return $result;
        }

        private static function copy_init($sources, $output_dir){
            foreach ($sources as $source){
                $source_filename=$source['source'];
                $target_filename=$output_dir.$source['target'];
                $params=isset($source['options'])?$source['options']:'';
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

        private static function js_init($sources, $dir, $id){
            require_once(C_LIBRARY."/php/JShrink/src/JShrink/Minifier.php");

            $x = '';
            foreach($sources as $source){ 
                $x.= (string)filemtime($source['source']);
            }
            $md5=md5($x);

            $output_file="${dir}${id}.${md5}.js";


            if (! file_exists($output_file)){

                //pobrišemo sve .js datoteke u folderu
                del_files($dir."$id.*.js");


                $table=array();
                $js = '';
                foreach($sources as $source){ 
                    if (strpos($source['source'],'min.js')!=0){ 
                        $j= file_get_contents($source['source']); 

                        $js .=$j;
                        $table[]=array(extract_file_name($source['source']), mb_strlen($j),'');   
                    } 
                    else{  
                        $j= \JShrink\Minifier::minify(file_get_contents($source['source']));
                        $js .=$j;
                        $table[]=array(extract_file_name($source['source']), filesize($source['source']),  mb_strlen($j));  
                    }      
                    $js .=';';     
                }

                $header=array();
                $header[]='/*!';
                $header[]=str_pad('', 64,chr(151));
                $header[]='|'.str_pad('dubravko.dev javascript compactor',62,' ', STR_PAD_BOTH).'|'; 
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
            foreach ($sources as $source){
                if (! file_exists($source['source']))
                    die('Not found: '.$source['source']);
                self::less_compile($source);
            }
        }

        private static function less_compile($source){
            require_once(C_LIBRARY."/php/lessphp/lessc.inc.php"); 

            $inputFile=$source['source'];
            $outputFile=$source['target'];

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

        private static function css_init($sources, $dir, $id){
            require_once(C_LIBRARY."/php/cssmin/cssmin.php");

            $x=''; 


            /**** md5 slika! ****/
            foreach($sources as $source){ 
                $dataURI=isset($source['dataURI']) and ($source['dataURI']===true); 
                if ($dataURI){
                    $css = file_get_contents($source['source']);
                    preg_match_all('/url\((.*)\)/',$css, $matches);

                    foreach ($matches[0] as $match){
                        $vowels = array("'", '\"', '"', 'url(', ')');   
                        $image_name = str_replace($vowels, "", $match);

                        $ext=extract_file_ext($image_name); 
                        if (self::valid_ext($ext)){
                            $result=self::image_file($image_name, $source);
                            if ($result!==false){
                                $size=filesize($result); 
                                $dataURI_max_filesize=isset($source['dataURI_max_filesize'])?$source['dataURI_max_filesize']:10000;
                                if ($size<$dataURI_max_filesize){   
                                    $x.= (string)filemtime($result); 
                                }
                            } 
                            else
                            {
                                die("Image not found: '$image_name' in '".$source['source']."'"); 
                            } 
                        } 
                    }
                }
            }



            foreach($sources as $source){ 
                $x.= (string)filemtime($source['source']);
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
                foreach($sources as $source){ 
                    $dataURI=isset($source['dataURI']) and ($source['dataURI']===true); 
                    //
                    if (strpos($source['source'],'min.css')!=0) 
                    {
                        $css = file_get_contents($source['source']);  
                        $output_css.=$css;  

                        $table[]=array(extract_file_name($source['source']), mb_strlen($css),'', '');  
                    }
                    else
                    {   
                        if ($dataURI)
                            $css = self::uri_file_get_contents($source);  
                        else     
                            $css = file_get_contents($source['source']);
                        //
                        $css = $compressor->run($css); 
                        $output_css.=$css;

                        $table[]=array(extract_file_name($source['source']), filesize($source['source']),  mb_strlen($css), $dataURI?'Yes':'');  
                    } 
                }

                $header=array();
                $header[]='/*!';
                $header[]=str_pad('', 72, chr(151));
                $header[]='|'.str_pad('dubravko.dev css compactor',71,' ', STR_PAD_BOTH).'|'; 
                $header[]=str_pad('', 72,chr(151));
                $header[]='|'.str_pad('File Name',40).'|'.str_pad('Orig. size',10,' ',STR_PAD_LEFT).'|'.str_pad('Comp. size',10,' ',STR_PAD_LEFT).'|'.str_pad('dataURI',7,' ',STR_PAD_LEFT).'|'; 
                $header[]=str_pad('', 72,chr(151));
                for ($i = 0; $i <= count($table)-1 ; $i++) {
                    $header[]='|'.str_pad($table[$i][0],40).'|'.str_pad($table[$i][1],10,' ',STR_PAD_LEFT).'|'.str_pad($table[$i][2],10,' ',STR_PAD_LEFT).'|'.str_pad($table[$i][3],7,' ',STR_PAD_BOTH).'|';              
                }
                $header[]=str_pad('', 72, chr(151));
                $header[]='*/';
                $header[]='';

                file_put_contents($output_file, implode("\n",$header).$output_css);
                return true;
            }
            else
                return false;
        }

        private static function uri_file_get_contents($source){
            $css=file_get_contents($source['source']);

            return preg_replace_callback( '/url\((.*)\)/', 
                function($m) use ($source) { return self::preg_replacex($m[0], $source); },
                $css); 
        }

        private static function preg_replacex($match, $source) {
            $vowels = array("'", '\"', '"', 'url(', ')');
            $image_name = str_replace($vowels, "", $match);

            $ext=extract_file_ext($image_name); 
            if (self::valid_ext($ext)){
                $result=self::image_file($image_name, $source);
                if ($result!==false){
                    $size=filesize($result);
                    $dataURI_max_filesize=isset($source['dataURI_max_filesize'])?$source['dataURI_max_filesize']:10000;
                    if ($size<$dataURI_max_filesize){ 
                        return "url(data:image/${ext};base64,".base64_encode(file_get_contents($result)).')';
                    }
                    {
                        return "url('${image_name}')";     
                    }
                }
                else    
                    return "url('${image_name}')";
            }
        }

        private static function image_file($image_name, $source){
            $search_paths=array();
            $search_paths[]=extract_file_dir($source['source']);

            if (isset($source['dataURI_search_paths'])){
                $search_paths=array_merge($search_paths, $source['dataURI_search_paths']);
            }

            foreach ($search_paths as $search_path)
            {
                $file=normal_dir($search_path).$image_name;
                if (file_exists($file)){
                    return $file;
                }  
            }
            return false;
        }

}