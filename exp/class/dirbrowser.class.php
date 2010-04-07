<?php
define("dir_browser_listmode",1);
define("dir_browser_picview",2);

include("extensions.inc.php");

function byteSizeCalc($string) {
    $wert = $string+0;
		if($wert > 0) {
                if(($wert/1024) < 1024){ // Wert in KB
                   $arrRet['content'] = sprintf("%0.1f", $wert/1024);
                   $arrRet['unit'] = "KB";
                } elseif(($wert/1048576) < 1024){ // Wert in MB
                  $arrRet['content'] = sprintf("%0.1f", $wert/1048576);
                   $arrRet['unit'] = "MB";
                } else{  // Wert in GB
                   $arrRet['content'] = sprintf("%0.1f", $wert/1073741824);
                   $arrRet['unit'] = "GB";
                }
                $arrRet['align'] = CELL_HZ_right;

			$arrRet['content'] .= " ".$arrRet['unit'];
			$arrRet['unit'] = "";
              } else {
				$arrRet['align'] = CELL_HZ_center;
				$arrRet['content'] = "-";
				$arrRet['unit'] = "";
		  }
	 $ret = $arrRet['content']."&nbsp;".$arrRet['unit'];
	 return $ret;
}

class dir_browser {
var $_dir;
var $_login;
var $_folder;
var $_extIcons;
var $_picExtensions;
var $_allowExtensions;
var $_msg;
var $_error;

	function dir_browser($dir,$folder) {
    $this->_dir = $dir;
    $this->_folder = $folder;
    $this->_login = new login();
    $this->setExtIcons();
    $this->setExtPics();
    $this->setExtAlow();
    $this->catchDirOptions();
    $this->_error = "<span style=\"color: red; font-weight: bold;\">Fehler</span>: ";
	}
	
  function remote_filesize($url, $user = "", $pw = "") {
     ob_start();
     $ch = curl_init($url);
     curl_setopt($ch, CURLOPT_HEADER, 1);
     curl_setopt($ch, CURLOPT_NOBODY, 1);
     if(!empty($user) && !empty($pw)) {
         $headers = array('Authorization: Basic ' . base64_encode("$user:$pw"));
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
     }
     $ok = curl_exec($ch);
     curl_close($ch);
     $head = ob_get_contents();
     ob_end_clean();
     $regex = '/Content-Length:\s([0-9].+?)\s/';
     $count = preg_match($regex, $head, $matches);
  
     return isset($matches[1]) ? $matches[1] : "unknown";
  }
  
  function remote_getState($filesize, $rfilesize) {
    // 100 %  = remote
    // x   %  = filesize
    // x = 100/filesize*remote
    $res = intval(100*$filesize/$rfilesize);
    return $res."% fertig"; 
  }
	
	function catchDirOptions() {

    if($_GET['view']=="ajax") {
      if(!empty($_GET['new_folder'])) {
         $_POST['new_folder'] = $_GET['new_folder'];
      }
    }

    if(is_array($_SESSION['remote_upload']) && count($_SESSION['remote_upload'])>0) {
      foreach ($_SESSION['remote_upload'] as $file=>$remote) {
         if (is_file($file)) {
           $filesize = filesize($file);
           if($remote>$filesize) {
            $this->_msg[] = "remote upload/download (".basename($file).") in process! ".$this->remote_getState($filesize,$remote)."";
           } else {
            $this->_msg[] = "remote upload/download (".basename($file).") complete";
            unset($_SESSION['remote_upload'][$file]);
           }
        } else {
          unset($_SESSION['remote_upload'][$file]);
        } 
      }
    }

    if($_POST['upload_url']) {
      // $file = file_get_contents($_POST['upload_url']);
      // file_put_contents($dest_file,$file);
      
      $dest_file = exp_root.'/'.$this->_folder."/".basename($_POST['upload_url']);
      chdir(dirname($dest_file));
      $exec_str = "wget ".urldecode($_POST['upload_url'])." ".$dest_file;
      exec($exec_str." > /dev/null &");
      $remote_filesize = $this->remote_filesize($_POST['upload_url']);
      $_SESSION['remote_upload'][$dest_file] = $remote_filesize;
      sleep(5);
      if(is_file($dest_file)) {
       // $this->_msg[] = "remote upload/download gestartet! ".$this->remote_getState(filesize($dest_file),$remote_filesize)." <br><br>";
        $exp_redirect = true;
      } else {
        $exec_res = shell_exec($exec_str);
        $this->_msg[] = "remote upload/download Fehler :( '".$exec_str."'<br>";
      }
    } elseif ($_FILES['userfile']) {
      $dest_path = exp_root.'/'.$this->_folder;
      $dest_file = $dest_path."/".$_FILES['userfile']['name'];
      if (move_uploaded_file($_FILES['userfile']['tmp_name'], $dest_file)) {
          $this->_msg[] = "upload erfolgreich!<br><br>";
          $exp_redirect = true;
      } else {
          $this->_msg[] = "upload Fehler :(<br>";
      }
    }

    if($_POST['unpack']=="Ja") {
      @include(exp_path."/class/unzip.class.php");
      $unpack_file = exp_root."/".$_POST['unpack_file'];
      $unpack_file = realpath($unpack_file);
      if(class_exists("dUnzip2")) {
        $unzip = new dUnzip2($unpack_file);
        if(is_object($unzip)) {
          print_r($unzip->unzipAll(dirname($unpack_file)));
        }
      } else {
        $this->_msg[] = $this->_error."Unzip Modul nicht gefunden!"; 
      }
    }

	  if($_POST['del_file']=="Ja") {
	    if (!empty($_POST['del_filename'])) {
	     $del_file = exp_root."/".$_POST['del_filename'];
       $basename = basename($del_file);	     
       $type = "Datei";
	    } else {
       $del_folder = exp_root."/".$_POST['del_foldername'];
       $basename = basename($del_folder);
       $dieses_type = "s";
       $type = "Verzeichniss";
      }
      if(is_file($del_file) || is_dir($del_folder)) {
        if ($this->_login->loginCheck()) {
          if (is_dir($del_folder)) {
            $res = @rmdir($del_folder);
          } else {
            $res = @unlink($del_file);
          }
          if($res) {
            $this->_msg[] = $type." (".$basename.") erfolgreich gel&ouml;scht";
            $exp_redirect = true;
          } else {
            $this->_msg[] = "<span style=\"color: red; font-weight: bold;\">Fehler</span>:  ".$type." (".$basename.") konte nicht gel&ouml;scht werden!";
          }
        } else {
          $this->_msg[] = "<span style=\"color: red; font-weight: bold;\">Fehler</span>:  Sie sind nicht berechtigt diese".$dieses_type." ".$type." zu l&ouml;schen!";
        }
      } else {
        $this->_msg[] = "<span style=\"color: red; font-weight: bold;\">Fehler</span>:  ".$type." (".$basename.") wurde nicht gefunden!";
      }
    }
	
    if ($_GET['DirMode']>0) {
      $this->_mode = $_GET['DirMode'];
      setcookie("DirMode", $this->_mode, time()+3600*24*30);
    } elseif ($_REQUEST['DirMode']>0) {
      $this->_mode = $_REQUEST['DirMode'];
    }
    
    if($_POST['new_folder'] && $this->_login->loginCheck()) {
     $dir = realpath(exp_root."/".$_REQUEST['folder']);
     $_POST['folder'] = $_REQUEST['folder'];
     if (@mkdir($dir."/".$_POST['new_folder'],0777)) {
      //chown($dir."/".$_GET['new_folder'],"web1");
      $this->_msg[] = "Folder: "."/".$_REQUEST['new_folder']." erfolgreich erstellt...";
      $exp_redirect = true;
     } else {
      $this->_msg[] = "<span style=\"color: red; font-weight: bold;\">Fehler</span>: Folder ("."/".$_REQUEST['new_folder'].") konnte nicht angelegt werden!";
     }
    } elseif ($_POST['new_folder'] && !$this->_login->loginCheck()) {
      $this->_msg[] = "<span style=\"color: red; font-weight: bold;\">Fehler</span>: Du musst eingelogt sein um Verzeichnisse anlegen zu d&uuml;rfen!"; 	
    }
 
    if($_GET['redirect_msg']) {
      $this->_msg[] = $_GET['redirect_msg'];
    }
    
    if ($exp_redirect===true) {
        $redirect_add = "?folder=".rawurlencode($this->_folder);
        if (is_array($this->_msg)) {
          $redirect_add.= "&redirect_msg=".rawurlencode(implode(" ",$this->_msg));
        }
        
        header("Location: index.php".$redirect_add);
    }
    
  }
	
	function setExtPics($ext="jpg,gif,png") {
    $ext = explode(",",$ext);
    foreach($ext as $e) {
      $this->_picExtensions[strtolower($e)] = true;
    }
  }
  
	function setExtAlow($ext="*") {
	  if(defined("explore_allowed_ext")) {
      $ext = explode(",",explore_allowed_ext);
    } else {
      $ext = explode(",",$ext);
    }
    foreach($ext as $e) {
      $this->_allowExtensions[strtolower($e)] = true;
    }
  }
	
	function setExtIcons() {
    // Extensions 
    include(exp_url."/img/16/base64.php"); 
    foreach ($img as $key=>$base64) {
      $ii[$key] = $key; 
    }   
    $this->_extIcons = $ii;
  }
	
	function mkDirList($path=false,$pic_size=16) {
  	if ($path==false) {
       $path = $this->_dir;
    }
    
    if(is_dir($path))
    {
    // Wichtig für die Listklass
    // ext, aoptions, filename, name, url, filesize
    if ($pic_size==16) { 
      $folder_type = 'folder';
      $folder_up = '/16/folder_up_16.gif'; 
      $img_wh = " width=\"16px\" height=\"16px\"";
    } else { 
      $folder_type = 'l_folder'; 
      $folder_up = '/32/folder_up_32.gif';
      $img_wh = " width=\"96px\" height=\"96px\"";      
    }    
      if (!empty($_REQUEST['folder'])) {
        $_REQUEST['folder'] = urldecode($_REQUEST['folder']);
         ++$rnr;
         $myfolders = explode("/",$_REQUEST['folder']);
         $akt_folder = array_pop($myfolders);
         $new_folder = implode("/",$myfolders);
         $res[$rnr]['ext'] = "<img border=\"0\" src=\"".exp_url."//img".$folder_up."\"$img_wh>";
         $res[$rnr]['aoptions'] = "";
         $res[$rnr]['filename'] = "..";
         $res[$rnr]['name'] = "<a href=\"index.php?folder=".urlencode($new_folder)."\">..</a>";
         $res[$rnr]['url'] = "index.php?folder=".urlencode($new_folder);

         if(exp_mode=="ajax") {    
           $res[$rnr]['name'] = "<a href=\"index.php?folder=".urlencode($new_folder)."\" onclick=\"ajax_call(this.href); return false;\">..</a>";
           $res[$rnr]['aoptions'] = " onclick=\"ajax_call(this.href); return false;\"";
         } 
         
         // $res[$rnr]['name'] = "<a href=\"javascript:sndReq('reload','".urlencode($new_folder)."')\">..</a>";
         // $res[$rnr]['url'] = "javascript:sndReq('reload','".urlencode($new_folder)."')";
         $res[$rnr]['filesize'] = " - ";
         $res[$rnr]['f_options'] = "";
     }

     $folders = glob($path."/*");
     natsort($folders);
     $files = array();
     // ############################   FOLDER   #####################################
     
     foreach ($folders as $file)
     {
      $file_info = pathinfo($file);
      if (!is_dir($file) && $file != "." && $file != ".." && $file_info['extension']!="php")
      {
         $files[] = $file;
      } elseif (is_dir($file) && basename($file)!="exp") {
         ++$rnr;
         $res[$rnr]['ext'] = "<img border=\"0\"$img_wh src=\"".exp_file."?view=ico&v=".$this->_extIcons[$folder_type]."\">";
         $res[$rnr]['aoptions'] = "";
         $res[$rnr]['filename'] = basename($file);
         $res[$rnr]['name'] = "<a href=\"index.php?folder=".urlencode($this->_folder)."/".basename($file)."\">".basename($file)."</a>";
         $res[$rnr]['url'] = "index.php?folder=".urlencode($this->_folder."/".basename($file));
         if(exp_mode=="ajax") {
            $res[$rnr]['name'] = "<a href=\"index.php?folder=".urlencode($this->_folder."/".basename($file))."\" onclick=\"ajax_call(this.href); return false;\">".basename($file)."</a>";
            $res[$rnr]['aoptions'] = " onclick=\"ajax_call(this.href); return false;\"";
         }
         // $res[$rnr]['name'] = "<a href=\"javascript:sndReq('reload','".urlencode($this->_folder."/".basename($file))."')\">".basename($file)."</a>";
         // $res[$rnr]['url'] = "javascript:sndReq('reload','".urlencode($this->_folder."/".basename($file))."')";
         $res[$rnr]['filesize'] = " - ";
         $res[$rnr]['f_options'] = "folder";
      }
     }
     
     
     // ############################   FILES   #####################################
     foreach ($files as $file)
     {
      $file_info = pathinfo($file);
      if ($this->_allowExtensions['*'] && !$this->_allowExtensions[strtolower($file_info['extension'])]) {
        $this->_allowExtensions[strtolower($file_info['extension'])]=true;
      }
      
      if ($this->_allowExtensions[strtolower($file_info['extension'])]) {
        if (!is_dir($file) && $file != "." && $file != ".." && $file_info['extension']!="php")
        {
           if (!isset($this->_extIcons[strtolower($file_info['extension'])])) {
              $this->_extIcons[strtolower($file_info['extension'])] = $this->_extIcons['file'];
           }
           $icon = "<img border=\"0\" src=\"".exp_file."?view=ico&v=".$this->_extIcons[strtolower($file_info['extension'])]."\">";;
           $pic = $pic = "<img border=\"0\" src=\"".exp_file."?view=pic&url=".urlencode($this->_folder."/".basename($file))."&max=98\">";
           
           if (isset($this->_picExtensions[strtolower($file_info['extension'])])) {
            // ############## BILDER FÜR BILDVORSCHAU
             if ($pic_size==16) {
              $ext = $icon;
             } else {
              $ext = $pic;
             }
             $img_info = getimagesize($file);
             $img_str = "Orginal: ".$img_info[0]."x".$img_info[1];
             $name = "<a href=\"".$this->_folder."/".basename($file)."\" rel=\"lightbox[galery]\" title=\"".basename($file)." ".$img_str."\">".basename($file)."</a>";
             $aoptions = " rel=\"lightbox[galery]\" title=\"".basename($file)." ".$img_str."\"";

           } else {
            // ############### Andere Dateien
             // echo "<tr><td><img border=\"0\" src=\"".exp_file."?view=ico&v=".$img[]."\"></td><td>&nbsp;&nbsp;<a href=\"".$folder."/".basename($file)."\">".basename($file)."</a></td><td>&nbsp;&nbsp;".filesize($file)." byte</td></tr>";
             $ext = "<img border=\"0\" src=\"".exp_file."?view=ico&v=".$this->_extIcons[strtolower($file_info['extension'])]."\">";
             $name = "<a href=\"".$this->_folder."/".basename($file)."\">".basename($file)."</a>";
             $aoptions = "";
           }
          $rnr++;
          $res[$rnr]['ext'] = $ext;
          $res[$rnr]['name'] = $name;
          $res[$rnr]['url'] = $this->_folder."/".basename($file);
          $res[$rnr]['aoptions'] = $aoptions;
          $res[$rnr]['filename'] = basename($file);
          $res[$rnr]['filesize'] = filesize($file);
          $res[$rnr]['f_options'] = $file_info['extension'];
        }
      }
     }
    }
    
    return $res;
  }
  
  function getOuptput($path=false) {
      $_REQUEST['folder'] = urldecode($_REQUEST['folder']);
  
      if(!defined("explore_optionBar")) {
        define("explore_optionBar",true);
      }

      if(is_array($this->_msg)) {
         $msg= implode(" ",$this->_msg);
      }
      
      
      if (explore_optionBar==true) { 
        if ($this->_login->loginCheck()) {
          $all = IconDiv_wrapper();    
          $icons = $all['icons'];
          $divs = $all['divs'];
        } else {
          $std = new standard();
          $viewmode = new listview();
          $icon = $std->getIconHTML(); 
          $icon[] = $this->_login->getIconHTML();
          $icon[] = $viewmode->getIconHTML();           
          foreach($icon as $i) {    
            $icons.= $i['icon'];
            $divs.= $i['div'];
          }
        }
      } else {
        $icons = "&nbsp;";
        $divs = "&nbsp;";
      }
      
      $output['exp_info'] = $msg;
      $output['exp_options'] = $icons."<br/>".$divs;
      if ($this->_mode==dir_browser_picview) {
          $output['dir_output'] = $this->getHTMLDirBrowse($path);
      } else {
//          $html.= "<td style=\"font-size:10px\" valign=top align='right'>[<a href=\"?folder=".urlencode($_REQUEST['folder'])."&DirMode=".urlencode(dir_browser_picview)."\">Bildvorschau</a>".$this->_options['picview']."]</td></tr></table>";
          $output['dir_output'] = $this->getHTMLDirList($path);
      }
      return $output;
  }
 
  function getHTMLDirBrowse($path=false) {
     // require_once("browse.class.php");
     // $list = new pDbrowse("dirlist");
     // 
     require_once("list.class.php");
     $list = new pDlist("dirlist");
     $dir_list = $this->mkDirList($path,32);
     $list->setData($dir_list);
     $list->setColNoOrder("ext");
     $list->setColNoOrder("name");
     $list->setColNoOrder("filesize");
     $list->setConfigParam("noPageControl",true);
     $list->setColMaxSize("filename","12");
     return $list->getHTML_PicView();
  }
 
  function getHTMLDirList($path=false) {
     require_once("list.class.php");
     $list = new pDlist("dirlist");
     $dir_list = $this->mkDirList($path);
     $list->setData($dir_list);
     // url	filename
     
     $list->setCallBackFunktion("filesize","byteSizeCalc");
     $list->setColSum("filesize");
     $list->setColInvisible("url");
     $list->setColInvisible("filename");
     $list->setColInvisible("aoptions");
     $list->setColAlign("filesize","right");
     $list->setColName("f_options","&nbsp;");
     $list->setCallBackFunktion("f_options","CB_setFileOptions");
     
     $list->setColNoOrder("ext");
     $list->setColNoOrder("f_options");
     $list->setColWidth("f_options","16px");
     $list->setColNoOrder("name");
     $list->setColNoOrder("filesize");
     $list->setConfigParam("noPageControl",true);
     $list->setColWidth("ext","16px");
     $list->setColWidth("filesize","100px");
     return $list->getHTML();
  }
}


?>
