<?php

    class DSitemap{

        const max_items_count=50000;

        public static function sitemap($items){
            $index=[];
            $sitemaps=array_chunk($items, self::max_items_count);

            foreach ($sitemaps as $sitemap){
                self::save_sitemap($index, $sitemap);   
            }

            self::save_index($index); 
        }

        private static function save_sitemap(&$index, $items){

            $i=count($index);
            $file_name="sitemap-${i}.xml";
            $file=APP_ROOT."/${file_name}";
            $sitemap_lastmod=0;

            $handle = fopen($file, "w");
            fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            fwrite($handle, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

            foreach ($items as $item){
                fwrite($handle,'  <url>' . "\n");

                $loc=$item[0];
                fwrite($handle, "    <loc>${loc}</loc>\n");

                $lastmod=date('Y-m-d', $item[1]);
                fwrite($handle, "    <lastmod>${lastmod}</lastmod>\n");

                if ($item[1]>$sitemap_lastmod){
                    $sitemap_lastmod=$item[1];   
                }

                $changefreq=$item[2];
                if (!empty($changefreq) && in_array(strtolower($changefreq), array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'))) {
                    fwrite($handle, "    <changefreq>${changefreq}</changefreq>\n");
                }

                $priority=$item[3];
                if (is_numeric($priority)){
                    $priority= ($priority < 1) ? round(abs($priority), 1) : '1.0';
                    fwrite($handle, "    <priority>${priority}</priority>\n");
                }        


                fwrite($handle,'  </url>' . "\n"); 
            }

            fwrite($handle, '</urlset>' . "\n");
            fclose($handle);

            $index[]=[absurl($file_name), date('Y-m-d', $sitemap_lastmod)]; 
        }

        private static function save_index($index){
            $file=APP_ROOT."/sitemap.xml";

            $handle = fopen($file, "w");
            fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            fwrite($handle, '<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">' . "\n");

            foreach ($index as $i){
                fwrite($handle, " <sitemap>\n");

                $loc=$i[0];
                fwrite($handle, "    <loc>${loc}</loc>\n");
                
                $lastmod=$i[1];
                fwrite($handle, "    <lastmod>${lastmod}</lastmod>\n");
                
                fwrite($handle, " </sitemap>\n");
            }
            
            fwrite($handle, " </sitemapindex>\n");
            fclose($handle);
        }

        
        
      private static function generate_sitemap($items){
            $result='';
            
            $result.= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
               $result.=  '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($items as $item){
                  $result.= '  <url>' . "\n";

                $loc=$item[0];
                  $result.= "    <loc>${loc}</loc>\n";

                $lastmod=date('Y-m-d', $item[1]);
                   $result.=  "    <lastmod>${lastmod}</lastmod>\n";

                $changefreq=$item[2];
                if (!empty($changefreq) && in_array(strtolower($changefreq), array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'))) {
                       $result.=  "    <changefreq>${changefreq}</changefreq>\n";
                }

                $priority=$item[3];
                if (is_numeric($priority)){
                    $priority= ($priority < 1) ? round(abs($priority), 1) : '1.0';
                      $result.= "    <priority>${priority}</priority>\n";
                }        


                   $result.= '  </url>' . "\n"; 
            }

               $result.=  '</urlset>' . "\n";
           
           return $result;
          
      }
        
        public static function output($items){
            header("Content-type: text/xml");
           echo self::generate_sitemap($items); 
        }
            
            
        
    }        
