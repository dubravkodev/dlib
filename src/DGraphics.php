<?php

    class DGraphics{

        
            /**
    * Generates random color
    * 
    */
    public static function rand_color(){
        mt_srand((double)microtime()*1000000);
        $c = '';
        while(strlen($c)<6){
            $c .= sprintf("%02X", mt_rand(100, 200));
        }
        return $c;
    }
    
    
    
        /**
    * Function get_remote_image_size reads image size from $url using image header data.
    * 
    * @param mixed $image_url
    */
    public static function get_remote_image_size($image_url){
        $count=0;
        $handle = fopen ($image_url, "rb");
        $contents = "";
        if ($handle) {
            do {
                $count += 1;
                $data = fread($handle, 8192);
                if (strlen($data) == 0) {
                    break;
                }
                $contents .= $data;
            } while(true);
        } else { return false; }
        fclose ($handle);

        $im = ImageCreateFromString($contents);
        if (!$im) { return false; }
        $gis[0] = ImageSX($im);
        $gis[1] = ImageSY($im);
        // array member 3 is used below to keep with current getimagesize standards
        $gis[3] = "width={$gis[0]} height={$gis[1]}";
        ImageDestroy($im);
        return $gis;
    }
        
        
        
        
        
        
        
        
        
        
        
        
        public static function image_create_thumb($image_str, $width, $height, $quality=80, $difference=5){

            if(!($image_str === false)) {

                $originalImage = @imagecreatefromstring($image_str);

                try {
                    if (!($originalImage===false)){
                        $tmp =self::image_crop_borders($originalImage, $difference);

                        $img= self::create_thumb($width,$height,$tmp, $quality);

                        imagedestroy($tmp); 
                        imagedestroy($originalImage);

                        return $img;
                    }
                } 
                catch (Exception $e) {
                    return false;
                } 
            }
            else
                return false;
        } 

        private static function create_thumb($maxWidth, $maxHeight, $tmp, $quality=80){
            $width=imagesx($tmp); 
            $height=imagesy($tmp);   

            $offsetX=0;
            $offsetY=0;

            $xRatio= $maxWidth / $width;
            $yRatio= $maxHeight / $height;

            if ($xRatio * $height < $maxHeight)
            { // Resize the image based on width
                $tnHeight= ceil($xRatio * $height);
                $tnWidth= $maxWidth;
            }
            else // Resize the image based on height
            {
                $tnWidth= ceil($yRatio * $width);
                $tnHeight= $maxHeight;
            }

            $xfrom=($maxWidth-$tnWidth)/2;  //centriramo
            $yfrom=($maxHeight-$tnHeight)/2;       

            $tmp2 = imagecreatetruecolor($maxWidth, $maxHeight);
            $color = imagecolorallocate($tmp2, 255, 255, 255);
            imagefill($tmp2, 0, 0, $color);
            imagecopyresampled($tmp2, $tmp , $xfrom, $yfrom, $offsetX, $offsetY, $tnWidth, $tnHeight, $width, $height); 

            $tmp2=self::UnsharpMask($tmp2,80,0.5,3);

            ob_start(); //Stdout --> buffer
            imagejpeg($tmp2,null,$quality); 
            $img2 = ob_get_contents(); //store stdout in $img2
            ob_end_clean(); //clear buffer
            imagedestroy($tmp2);

            return $img2;
        }

        private static function image_crop_borders($originalImage, $difference){

            $edge = self::detect_border($originalImage, $difference);    // x,y,width,height  
            $newWidth = $edge[2];
            $newHeight = $edge[3];

            $tmp = imagecreatetruecolor($newWidth, $newHeight);
            $color = imagecolorallocate($tmp, 255, 255, 255); //obojimo pozadinu u bijelo zbog gif slika
            imagefill($tmp, 0, 0, $color);

            imagecopyresized($tmp, $originalImage, 0, 0, $edge[0], $edge[1], $newWidth, $newHeight, $edge[2], $edge[3]);
            return $tmp;
        }

        private static function similar_colors($color1, $color2, $difference){
            $r1 = ($color1 >> 16) & 0xFF;
            $g1 = ($color1 >> 8) & 0xFF;
            $b1 = $color1 & 0xFF;

            $r2 = ($color2 >> 16) & 0xFF;
            $g2 = ($color2 >> 8) & 0xFF;
            $b2 = $color2 & 0xFF;

            return (abs($r1-$r2)<$difference) and (abs($g1-$g2)<$difference) and (abs($b1-$b2)<$difference);
        }

        private static function detect_border($img,$difference){
            $width = imagesx($img);
            $height = imagesy($img);



            $left= 0;
            $top= 0;
            $right= $width - 1;
            $bottom= $height - 1;  

            $color1=imagecolorat($img, 0, 0);  

            /* left top-bottom */  
            $same=true;
            do {   
                for ($i = $top; $i <= $bottom; $i++) {


                    if (! self::similar_colors($color1,imagecolorat($img, $left, $i),$difference)){
                        $same=false; 
                    } 
                } 
                $left++;
            }
            while ($same and $left<$width);
            if ($same)
                $left= 0;    


            /* right top-bottom */  
            $same=true;
            do {
                for ($i = $top; $i <= $bottom; $i++) {

                    if (! self::similar_colors($color1,imagecolorat($img, $right, $i),$difference)){
                        $same=false; 
                    } 
                } 
                $right--;
            }
            while ($same and ($right>=0)); 
            if ($same)
                $right= $width - 1;  



            /* top left-right */  
            $same=true;
            do {
                for ($i = $left; $i <= $right; $i++) {

                    if (! self::similar_colors($color1,imagecolorat($img, $i, $top),$difference)){
                        $same=false; 
                    } 
                } 
                $top++;
            }
            while ($same and $top<$height); 
            if ($same)
                $top= 0;  


            /* bottom left-right */  
            $same=true;
            do {
                for ($i = $left; $i <= $right; $i++) {

                    if (! self::similar_colors($color1,imagecolorat($img, $i, $bottom),$difference)){
                        $same=false; 
                    } 
                } 
                $bottom--;
            }
            while ($same and $bottom>=0);
            if ($same)
                $bottom= $height - 1;  


            return array($left, $top, $right-$left, $bottom- $top);   

        }

        private static function UnsharpMask($img, $amount, $radius, $threshold){  

            /* 

            New:  
            - In version 2.1 (February 26 2007) Tom Bishop has done some important speed enhancements. 
            - From version 2 (July 17 2006) the script uses the imageconvolution function in PHP  
            version >= 5.1, which improves the performance considerably. 


            Unsharp masking is a traditional darkroom technique that has proven very suitable for  
            digital imaging. The principle of unsharp masking is to create a blurred copy of the image 
            and compare it to the underlying original. The difference in colour values 
            between the two images is greatest for the pixels near sharp edges. When this  
            difference is subtracted from the original image, the edges will be 
            accentuated.  

            The Amount parameter simply says how much of the effect you want. 100 is 'normal'. 
            Radius is the radius of the blurring circle of the mask. 'Threshold' is the least 
            difference in colour values that is allowed between the original and the mask. In practice 
            this means that low-contrast areas of the picture are left unrendered whereas edges 
            are treated normally. This is good for pictures of e.g. skin or blue skies. 

            Any suggenstions for improvement of the algorithm, expecially regarding the speed 
            and the roundoff errors in the Gaussian blur process, are welcome. 

            */ 

            ////////////////////////////////////////////////////////////////////////////////////////////////   
            ////   
            ////                  Unsharp Mask for PHP - version 2.1.1   
            ////   
            ////    Unsharp mask algorithm by Torstein Hønsi 2003-07.   
            ////             thoensi_at_netcom_dot_no.   
            ////               Please leave this notice.   
            ////   
            ///////////////////////////////////////////////////////////////////////////////////////////////   



            // $img is an image that is already created within php using  
            // imgcreatetruecolor. No url! $img must be a truecolor image.  

            // Attempt to calibrate the parameters to Photoshop:  
            if ($amount > 500)    $amount = 500;  
            $amount = $amount * 0.016;  
            if ($radius > 50)    $radius = 50;  
            $radius = $radius * 2;  
            if ($threshold > 255)    $threshold = 255;  

            $radius = abs(round($radius));     // Only integers make sense.  
            if ($radius == 0) {  
                return $img; 
                //imagedestroy($img); 
                //break;        
            }  
            $w = imagesx($img); $h = imagesy($img);  
            $imgCanvas = imagecreatetruecolor($w, $h);  
            $imgBlur = imagecreatetruecolor($w, $h);  


            // Gaussian blur matrix:  
            //                          
            //    1    2    1          
            //    2    4    2          
            //    1    2    1          
            //                          
            //////////////////////////////////////////////////  


            if (function_exists('imageconvolution')) { // PHP >= 5.1   
                $matrix = array(   
                    array( 1, 2, 1 ),   
                    array( 2, 4, 2 ),   
                    array( 1, 2, 1 )   
                );   
                imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h);  
                imageconvolution($imgBlur, $matrix, 16, 0);   
            }   
            else {   

                // Move copies of the image around one pixel at the time and merge them with weight  
                // according to the matrix. The same matrix is simply repeated for higher radii.  
                for ($i = 0; $i < $radius; $i++)    {  
                    imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left  
                    imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right  
                    imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center  
                    imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);  

                    imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up  
                    imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down  
                }  
            }  

            if($threshold>0){  
                // Calculate the difference between the blurred pixels and the original  
                // and set the pixels  
                for ($x = 0; $x < $w-1; $x++)    { // each row 
                    for ($y = 0; $y < $h; $y++)    { // each pixel  

                        $rgbOrig = ImageColorAt($img, $x, $y);  
                        $rOrig = (($rgbOrig >> 16) & 0xFF);  
                        $gOrig = (($rgbOrig >> 8) & 0xFF);  
                        $bOrig = ($rgbOrig & 0xFF);  

                        $rgbBlur = ImageColorAt($imgBlur, $x, $y);  

                        $rBlur = (($rgbBlur >> 16) & 0xFF);  
                        $gBlur = (($rgbBlur >> 8) & 0xFF);  
                        $bBlur = ($rgbBlur & 0xFF);  

                        // When the masked pixels differ less from the original  
                        // than the threshold specifies, they are set to their original value.  
                        $rNew = (abs($rOrig - $rBlur) >= $threshold)   
                        ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))   
                        : $rOrig;  
                        $gNew = (abs($gOrig - $gBlur) >= $threshold)   
                        ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))   
                        : $gOrig;  
                        $bNew = (abs($bOrig - $bBlur) >= $threshold)   
                        ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))   
                        : $bOrig;  



                        if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {  
                            $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);  
                            ImageSetPixel($img, $x, $y, $pixCol);  
                        }  
                    }  
                }  
            }  
            else{  
                for ($x = 0; $x < $w; $x++)    { // each row  
                    for ($y = 0; $y < $h; $y++)    { // each pixel  
                        $rgbOrig = ImageColorAt($img, $x, $y);  
                        $rOrig = (($rgbOrig >> 16) & 0xFF);  
                        $gOrig = (($rgbOrig >> 8) & 0xFF);  
                        $bOrig = ($rgbOrig & 0xFF);  

                        $rgbBlur = ImageColorAt($imgBlur, $x, $y);  

                        $rBlur = (($rgbBlur >> 16) & 0xFF);  
                        $gBlur = (($rgbBlur >> 8) & 0xFF);  
                        $bBlur = ($rgbBlur & 0xFF);  

                        $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;  
                        if($rNew>255){$rNew=255;}  
                        elseif($rNew<0){$rNew=0;}  
                        $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;  
                        if($gNew>255){$gNew=255;}  
                        elseif($gNew<0){$gNew=0;}  
                        $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;  
                        if($bNew>255){$bNew=255;}  
                        elseif($bNew<0){$bNew=0;}  
                        $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;  
                        ImageSetPixel($img, $x, $y, $rgbNew);  
                    }  
                }  
            }  
            imagedestroy($imgCanvas);  
            imagedestroy($imgBlur);  

            return $img;  
        } 

}