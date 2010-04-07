<?php
error_reporting(E_ALL ^ E_NOTICE);
ob_start("ob_gzhandler");

session_start();

// Systemangaben
define("explore_admin_pwd","admin");// Passwort zum login
define("explore_optionBar",true);         // Rechts oben die Optionsleiste zum Einlogen, Hochladen, Neuen Ordner an und ausblenden
define("explore_allowed_ext","*");        // Liste der erlauben/angezeigten ext. mit Komma trennen, z.B. "jpg,gif" oder "*" für alles
define("explore_cache_avaible",false);    // Möglichkeit eine Bildervorschau zu cachen, sinnvoll bei sehr großen Bilder. Dann muß der explorer path schreibbar sein.
define("explore_maxram",600000000);       // Maximale größe für php scripte.


// Pfadangaben
define("exp_dir", "");                    // Falls der Explorer in einem Unterordner liegt
define("js_url","./exp/js");              // absolute oder relative angabe Zum Javascript Order
define("css_url","./exp/js");             // absolute oder relative Angabe Zum CSS Ordner
define("exp_url", "./exp/");              // absolute oder relative Angabe zum Explorer Data Ordner (default: ./exp/)
define("FCKEditor_path","./exp/FCKEditor"); // Falls sie den FCKEditor verwenden möchten, den path
define("FCKEditor_url","./exp/FCKEditor");  // und die URL hier eintragen.

define("exp_mode", "ajax");               // ajax oder normal mode 

// Autodetections
define("exp_root", dirname(__FILE__));      
define("exp_path", exp_root."/exp/");
define("exp_root_url", "http://".$_SERVER['SERVER_NAME']."/".exp_dir);
define("exp_file", exp_root_url."/index.php");

$FCKEditor_avaible = is_file(FCKEditor_path."/fckeditor.php");


switch ($_GET['view']) {
case "lb":
  $file_info = pathinfo($_GET['v']);
  $file_info['extension']=(empty($file_info['extension']))?"gif":$file_info['extension'];
  header('Content-Type: image/'.strtolower($file_info['extension']));
  readfile(exp_path."/img/lb/".$_GET['v']);
  exit;
  break;
case "mp3":
  include(exp_path."/class/TemplatePower.class.php");
  $tpl = new TemplatePower(exp_path."/templates/mp3_player.tpl.html");
  $tpl->prepare();
  $mp3name = exp_root_url.".".urldecode($_GET['file']);
  $tpl->assign("js_url",js_url);
  $tpl->assign("css_url",css_url);
  $tpl->assign("mod_url",exp_url."mods");
  $tpl->assign("mp3_file",$mp3name);
  $tpl->assign("iframe_title",basename($mp3name));
  $tpl->printToScreen();
  exit;
  break;
case "flv_file_stream":
  //full path to dir with video.
  $flvname = exp_root_url.".".urldecode($_GET['file']);
  
  $seekat = $_GET["position"];
  $filename = htmlspecialchars($_GET["file"]);
  $ext=strrchr($filename, ".");
  $file = realpath($flvname);
  
  
  if((file_exists($file)) && ($ext==".flv") && (strlen($filename)>2) && (!eregi(basename($_SERVER['PHP_SELF']), $filename)) && (ereg('^[^./][^/]*$', $filename)))
  {
          header("Content-Type: video/x-flv");
          if($seekat != 0) {
                  print("FLV");
                  print(pack('C', 1 ));
                  print(pack('C', 1 ));
                  print(pack('N', 9 ));
                  print(pack('N', 9 ));
          }
          $fh = fopen($file, "rb");
          fseek($fh, $seekat);
          while (!feof($fh)) {
            print (fread($fh, filesize($file))); 
          }
          fclose($fh);
  }
  	else
  {
          print("ERORR: The file does not exist"); }
 exit;
 break;
case "flv":
  include(exp_path."/class/TemplatePower.class.php");
  $tpl = new TemplatePower(exp_path."/templates/flv_player.tpl.html");
  $tpl->prepare();
  $flvname = exp_root_url.".".urldecode($_GET['file']);
  $tpl->assign("js_url",js_url);
  $tpl->assign("css_url",css_url);
  $tpl->assign("mod_url",exp_url."mods");
  $tpl->assign("flv_file",$flvname);
  $tpl->assign("iframe_title",basename($flvname));
  $tpl->printToScreen();
  exit;
  break;
case "edit":
  $filename = realpath(dirname(__FILE__)."/".urldecode($_GET['file']));
  if($filename) {
    $file_content = file_get_contents($filename);
    $file_info = pathinfo($filename);
    $fckedit = array_flip(explode(",","html,htm"));
    $txtedit = array_flip(explode(",","html,htm,xml,sql,js,css,txt"));
    
    echo '<script>
      parent.document.getElementById("my_iframe").style.width = "905px;";
      parent.document.getElementById("my_iframe").style.height = "650px;";
      parent.document.getElementById("my_iframe_title").innerHTML = "<b>Edit:'.basename($filename).'</b>";
    </script>';  
    
    if (isset($fckedit[strtolower($file_info['extension'])]) && $FCKEditor_avaible==true) { 
      $sValue = stripslashes( $_POST['FCKeditor1'] ) ;
      if($sValue!=$file_content && !empty($sValue)) {
        $fp = fopen($filename,"w");
        fputs($fp,$sValue);
        fclose($fp);
        $msg = "[gespeichert]";
        $file_content = $sValue;
      }
      include(FCKEditor_path."/fckeditor.php") ;

      
      $oFCKeditor = new FCKeditor('FCKeditor1');
      echo basename($filename)." ".$msg.":
        <form action=\"index.php?view=edit&file=".$_GET['file']."\" method=\"post\">";
      $oFCKeditor->BasePath = FCKEditor_url.'/FCKeditor/';
      $oFCKeditor->Value = $file_content;
      $oFCKeditor->Width  = '100%' ;
      $oFCKeditor->Height = '95%' ;
      $oFCKeditor->Create();
      echo "</form>";
    } elseif (isset($txtedit[strtolower($file_info['extension'])])) {
      if ($_POST['newcontent']!=$file_content && !empty($_POST['newcontent'])) {
        $fp = @fopen($filename,"w");
        if(is_resource($fp)) {
          fputs($fp,$_POST['newcontent']);
          fclose($fp);
          $msg = "[gespeichert]";
          $file_content = $_POST['newcontent'];
        } else {
          $msg = "[fehler, keine rechte?]";
        }
      } 
      echo basename($filename)." ".$msg.":
        <form action=\"index.php?view=edit&file=".$_GET['file']."\" method=\"post\">
        <textarea name=\"newcontent\" style=\"width:100%; height:90%;\">".$file_content."</textarea>
        <input style=\"text-align: right;\" type=\"submit\" value=\"speichern\">";
      echo "</form>";
    } else {
      echo "No suportet File";
    }
  }
  exit;
  break;
case "pic":
  include(exp_path."/class/pic.class.php");
  $pic_preview = new picloader();
  $pic_preview->printToScreen();
  exit;
  break;
case "ico":
  if($_REQUEST['DirMode']==1) {
    include(exp_path."/img/16/base64.php");
    echo base64_decode($img[$_GET['v']]);
  } else {
    include(exp_path."/img/16/base64.php");
    $data = base64_decode($img[$_GET['v']]);
    echo ($data);
    /*  Alternative möglichkeit:
    $src = imagecreatefromstring($data);
    $dest = imagecreatetruecolor (32,32);
    imagecopyresampled($dest, $src, 0, 0, 0, 0, 32, 32, 16, 16);
    imagepng($src);
    */
  }
  exit;
  break;
case "ajax":  
default:
  if(!isset($_GET['folder'])) {
      $_REQUEST['folder'] = $_SESSION['folder'];
  } else {
      $_SESSION['folder'] = $_REQUEST['folder'];
  }
  
  include(exp_path."/class/dirbrowser.class.php");
  include(exp_path."/class/TemplatePower.class.php");
  if ($_REQUEST['folder'] && substr_count($_REQUEST['folder'],"..")==0) {
    $folder = $_REQUEST['folder'];
    $path = realpath(dirname(__FILE__)."/".$folder);
  } else {
    if (substr_count($_REQUEST['folder'],"..")>0) {
        unset($_REQUEST['folder']);
    }
    $folder=".";
    $path = realpath(dirname(__FILE__)."/".$folder);
  }
  
  if ($_REQUEST['folder']==".") {
     unset($_REQUEST['folder']);
  }

  $dir = new dir_browser($path,$folder);
  if($_GET['view']=="ajax") {
    $ausgabe = $dir->getOuptput();
    foreach($ausgabe as $id=>$content) {
      echo $id."|*exp*|".$content."|*exp*|";
    }
    
    if($o) {
      echo "debug_output|*exp*|".$o."|*exp*|";
    }
    
    exit;
  } else {
    $ausgabe = $dir->getOuptput();
    $tpl = new TemplatePower(exp_path."/templates/index.tpl.html");
    $tpl->prepare();
    if($folder!=".") {
      $tpl->assign("folder_title",basename($folder)." - ");
    }
    
    $tpl->assign("js_url",js_url);
    $tpl->assign("css_url",css_url);

    foreach ($ausgabe as $target=>$data) {
      $tpl->assign($target,$data);
    }
    
    if($o) {
      $tpl->newBlock("debug_block");
      $tpl->assign("debug_output",$o);
      echo "ok:".$o;
    }
    
    $tpl->printToScreen();
  }
  break;
}
?>
