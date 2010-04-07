<?php
    class picloader {
    var $img;
    
        function picloader() {
          include(exp_path."/img/16/base64.php");
          $this->img = $img;
        }
    
     function printToScreen() {
      if (!defined("explore_cache_avaible")) {
        define("explore_cache_avaible",false);
      }
    
      if (!defined("explore_maxram")) {
        $maxram = 60000000;
      } else {
        $maxram = explore_maxram;
      }
    
       // Test image.
       $fn = realpath(exp_root."/".$_GET['url']);
       $basename = basename($_GET['url']);
       $error_img['jpg'] = exp_path."/img/16/jpg.gif";
       $error_img['gif'] = exp_path."/img/16/gif.gif";
       $error_img['png'] = exp_path."/img/16/png.gif";
        
       if ($_GET['max']>0) {
        $maxsize = $_GET['max'];
       } else {
        $maxsize = 2048;
       }
       
       // Checking if the client is validating his cache and if it is current.
       // Image not cached or cache outdated, we respond '200 OK' and output the image.
       // header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT', true, 200);
       $file_info = pathinfo($fn);
       $file_size = getimagesize($fn);
       $file_md5 = md5_file($fn);
       $file_md5_path = substr($file_md5,0,2);
    
       $cachepath = exp_path."/pic_cache/";       
       
       $mem_usage = $file_size[0]*$file_size[0]*24;
       if ($_GET['debug']) {
          print_r($_GET);          
          echo $mem_usage."<br>"; 
          echo $file_md5;
          exit;
       }
    
       if ($mem_usage>$maxram && !$_GET['viewError']) {
           header('Content-Type: image/gif');
           echo file_get_contents($error_img[strtolower($file_info['extension'])]);
       } else {
           if (is_file($cachepath.$file_md5_path."/".$file_md5) && !$_GET['force_relocate']) {
              header('Content-Type: image/jpg');
              echo file_get_contents($cachepath.$file_md5_path."/".$file_md5);
           } else {
              if (!$_GET['viewError']) 
              {
                header('Content-Type: image/'.strtolower($file_info['extension']));
              }     
             switch (strtolower($file_info['extension'])) {
              case "jpg":
              case "jpeg":
                $im = imagecreatefromjpeg($fn);
                break;
              case "gif":
                $im = imagecreatefromgif($fn);
                break;
              case "png":
                $im = imagecreatefrompng($fn);
                break;   
             }
             
             $x = imagesx($im);
             $y = imagesy($im);
            
             if ($x>$y) {
                $divisor = ceil($x/$maxsize);
             } else {
                $divisor = ceil($y/$maxsize);
             }
             
            $new_width = $x / $divisor;
            $new_height = $y / $divisor;
    
            settype($new_width, 'integer');
            settype($new_height, 'integer');
                 // sizes should be integers
          
                 // load original image
            $image_small = imagecreatetruecolor($new_width, $new_height);
                 // create new image
            imagecopyresampled($image_small, $im, 0,0, 0,0, $new_width,$new_height, $x,$y);
                 // imageresampled whill result in a much higher quality
                 // than imageresized
            imagedestroy($im);
    
             imagejpeg($image_small);         
             if (explore_cache_avaible==true && $_GET['max']>0 && $_GET['max']<250) {
                  if (!is_dir($cachepath)) {
                    mkdir($cachepath,0777);
                    chmod($cachepath,0777);
                  }
                  if (is_writable($cachepath)) { 
                    if (!is_dir($cachepath.$file_md5_path)) {
                      mkdir($cachepath.$file_md5_path,0777);
                      chmod($cachepath.$file_md5_path,0777);
                    }
                    $full_cache_path = $cachepath.$file_md5_path."/".$file_md5;
                    imagejpeg($image_small,$full_cache_path);
                    chmod($full_cache_path,0777);
                  }
             }
             
        }
      }
    } // end printToScreen
  }
?>
