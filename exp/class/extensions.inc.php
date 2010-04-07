<?php

// #########################################################################
// Div Klassen Erweiterungen
// #########################################################################

class standard {
  function standart() {
    
  }
  
  function getIconHTML() {
    $icon[]['icon'] = '<a href="#" onclick="sndReq(\'reload\',glob_folder)"><img border=\"0\" src="'.exp_url.'/img/16/reload.gif" border="0" alt="reload"></a>';
    $icon[]['div'] = "";
    
    return $icon;
  }

}

class login {
  function login() {
  }
  
  function loginCheck() {
    if($_POST['login_pwd']) {
      $_SESSION['login_pwd'] = $_POST['login_pwd'];
    }
    
    return ($_SESSION['login_pwd']==explore_admin_pwd);
  }
  
  
  function getIconHTML() {
    $icon['icon'] = '<a href="#" onclick="Effect.toggle(\'login\',\'slide\',{duration:0.5}); return false;"><img border=\"0\" src="'.exp_url.'/img/16/login.gif" border="0" alt="login"></a>';
    $icon['div'] = '
    <div id="login" style="text-align:left; right: 10px; font-size:10px; position:absolute; background-color: white; position:absolute; display:none; border: 1px solid #8b8bff;">
      <form style="display:inline;" action="index.php" method="POST">
      <b>Admin Login PW:</b><br> 
      <input type="hidden" name="folder" value="'.$_REQUEST['folder'].'">
      <input type="password" name="login_pwd"><input type="submit" value="login"></form>&nbsp;
    </div>';
    return $icon;
  }
}


class upload_file {
var $path;

  function upload_file($path) {
    $this->path = $path;
  }
  
  function getIconHTML() {
    $icon['icon'] = '<a href="#" onclick="Effect.toggle(\'upload\',\'slide\',{duration:0.5}); return false;"><img border=\"0\" src="'.exp_url.'/img/16/upload.gif" border="0" alt="upload einblenden"></a>';
    $icon['div'] = '
    <div id="upload" style="text-align:left; padding:5px; right: 10px; position:absolute; background-color: white; display:none; border: 1px solid #8b8bff; ">
      <form style="display:inline;" method="POST" action="index.php" enctype="multipart/form-data">
        <table style="font-size:10px;"><tr>
        <td colspan=2><b>Upload Ihrer Dateien:</b><input type="hidden" name="folder" value="'.$_REQUEST['folder'].'"></td>
        </tr>
        <tr>
        <td>URL: </td><td><input style="width:250px" type="text" name="upload_url"> oder </td>
        </tr>
        <tr>
        <td>Datei:</td><td><input style="width:200px" type="file" name="userfile"></td>
        </tr><tr>
        <td colspan=2 align=right><input type="submit" value="upload File"></td>
        </tr></table>
      </form>&nbsp;
    </div>';
    
    return $icon;
  }

}


class mkdir {
var $path;

  function mkdir($path) {
    $this->path = $path;
  }
  
  function getIconHTML() {
    $icon['icon'] = '<a href="#" onclick="Effect.toggle(\'new_folder\',\'slide\',{duration:0.5}); return false;"><img border=\"0\" src="'.exp_url.'/img/16/new_folder.gif" border="0" alt="neuer Ordner"></a>';
    $form_start = '<form style="display:inline;" method="POST" action="index.php" onsubmit="newfolder(); return false;">'; // Ajax
    $form_start = '<form style="display:inline;" method="POST" action="index.php">'; // Ohne Ajax
    $icon['div'] = '
    <div id="new_folder" style="text-align:left; padding:5px; font-size:10px; right: 10px; position:absolute; background-color: white; display:none; border: 1px solid #8b8bff;">
      '.$form_start.'
      <b>Ordner Anlegen:</b><br>
      <input type="hidden" name="folder" value="'.$_REQUEST['folder'].'">
      <input id="create_newfolder" type="text" name="new_folder"><input type="submit" value="anlegen"></form>&nbsp;
    </div>';
    return $icon;
  }
}

// #########################################################################
class listview {

  function listview() {
  }
  
  function getIconHTML() {
    $dirmode = 1; $list_button = "white;"; $bild_button = "white;";
    $icon['icon'] = '<a href="#" onclick="Effect.toggle(\'mode_change\',\'slide\',{duration:0.5}); return false;"><img border=\"0\" src="'.exp_url.'/img/16/mode_change.gif" border="0" alt="Viewmode &auml;ndern"></a>';
    if(isset($_GET['DirMode']) && $_GET['DirMode']!=$_REQUEST['DirMode']) {
      $dirmode = $_GET['DirMode'];
    } elseif (isset($_REQUEST['DirMode'])) {
      $dirmode = $_REQUEST['DirMode'];
    } 
    
    if($dirmode==1) { $bild_button = "yellow;";  } else {  $list_button = "yellow;"; }
    $blub = "&nbsp;";
    
    // $a_lst = '<a href="javascript:changeMode(\''.urlencode(dir_browser_listmode).'\')">';
    
    if(exp_mode=="ajax") {    
      // Ajax Links // #">'; // 
      $a_pic = '<a onclick="ajax_call(this.href); return false;" href="?folder='.urlencode($_REQUEST['folder']).'&DirMode='.urlencode(dir_browser_picview).'">';
      $a_lst = '<a onclick="ajax_call(this.href); return false;" href="?folder='.urlencode($_REQUEST['folder']).'&DirMode='.urlencode(dir_browser_listmode).'">';
    } else {
      // Normale Links
      $a_pic = '<a href="?folder='.urlencode($_REQUEST['folder']).'&DirMode='.urlencode(dir_browser_picview).'">';
      $a_lst = '<a href="?folder='.urlencode($_REQUEST['folder']).'&DirMode='.urlencode(dir_browser_listmode).'">';
    }
    $icon['div'] = '
    <div id="wrapper">
      <div id="mode_change" style="right: 10px; position:absolute; padding:5px; font-size:10px; background-color: white; display:none; border: 1px solid #8b8bff;">
         <div style="text-align:left;">
          <b>Viewmode Wechseln:</b><br>
          <table style="width:200px;">
          <tr>
           <td id="dirmode_'.dir_browser_listmode.'" style="font-size:10px; background-color: '.$list_button.'">'.$a_pic.'Bildvorschau</a><td>
          </tr>
          <tr>
           <td id="dirmode_'.dir_browser_picview.'" style="font-size:10px; background-color: '.$bild_button.'">'.$a_lst.'Liste</a></td>
          </tr>
        </table>
       </div>
      </div>
    </div>';
    return $icon;
  }
}

// #########################################################################
// CLASSEN mit InputBox
// #########################################################################
class delFile {
  var $file;
  var $is_folder;
  function delFile($file,$is_folder=false) {
    $this->file = $file;
    $this->is_folder = $is_folder==true;
  }
  
  function getIconHTML() {
    $icon['icon'] = '<a href="#" onclick="Effect.toggle(\'del_file_'.$this->file.'\',\'appear\',{duration:0.5}); return false;"><img border=\"0\" src="'.exp_url.'/img/16/del.gif" border="0" alt="löschen"></a>';
    if($this->is_folder==true) {
      $type = "Verzeichniss (nur leeres!)";
      $type_key = "del_foldername";
    } else {
      $type = "File";
      $type_key = "del_filename";
    }
    
    $icon['div'] = '
    <span id="del_file_'.$this->file.'" style="z-index:2; width: 250px; text-align:left; padding:5px; font-size:10px; top: 45%; left: 40%; position:absolute; background-color: yellow; display:none; border: 1px solid #8b8bff;">
      <form onsubmit="Effect.toggle(\'del_file_'.$this->file.'\',\'appear\',{duration:0.1});" style="display:inline;" method="POST" action="index.php">
      '.$type.' <b><i>'.$this->file.'</i></b> wirklich l&ouml;schen?<br>
      <input type="hidden" name="folder" value="'.$_REQUEST['folder'].'">
      <input type="hidden" name="'.$type_key.'" value="'.$_REQUEST['folder']."/".$this->file.'">
      <input type="submit" name="del_file" value="Ja"><input type="submit" onclick="Effect.toggle(\'del_file_'.$this->file.'\',\'appear\',{duration:0.5}); return false;" value="Nein"></form>&nbsp;
    </span>
    <script type="text/javascript">
      new Draggable(\'del_file_'.$this->file.'\',{revert:false});
    </script>
    ';
    return $icon;
  }
}
// #########################################################################
class unpack {
  var $file;
  function unpack($file) {
    $this->file = $file;
  }
  
  function getIconHTML() {
    $icon['icon'] = '<a href="#" onclick="Effect.toggle(\'unpack_file_'.$this->file.'\',\'appear\',{duration:0.5}); return false;"><img border=\"0\" src="'.exp_url.'/img/16/unpack.gif" border="0" alt="unpack"></a>';
    $type_key = "unpack_file";
    
    $icon['div'] = '
    <span id="unpack_file_'.$this->file.'" style="z-index:2; width: 250px; text-align:left; padding:5px; font-size:10px; top: 45%; left: 40%; position:absolute; background-color: yellow; display:none; border: 1px solid #8b8bff;">
      <form onsubmit="Effect.toggle(\'unpack_file_'.$this->file.'\',\'appear\',{duration:0.1});" style="display:inline;" method="POST" action="index.php">
      Archive <b><i>'.$this->file.'</i></b> wirklich entpacken?<br>
      <input type="hidden" name="folder" value="'.$_REQUEST['folder'].'">
      <input type="hidden" name="'.$type_key.'" value="'.$_REQUEST['folder']."/".$this->file.'">
      <input type="submit" name="unpack" value="Ja"><input type="submit" onclick="Effect.toggle(\'unpack_file_'.$this->file.'\',\'appear\',{duration:0.5}); return false;" value="Nein"></form>&nbsp;
    </span>
    <script type="text/javascript">
      new Draggable(\'unpack_file_'.$this->file.'\',{revert:false});
    </script>
    ';
    return $icon;
  }
}

// #########################################################################
class edit_txt {
  var $file;
  function edit_txt($file) {
    $this->file = $file;
  }
  
  function getIconHTML() {
    $icon['icon'] = '<a href="'.exp_file.'?view=edit&file='.urlencode($_REQUEST['folder']."/".$this->file).'" target="myiframe_target" onclick="Effect.toggle(\'my_iframe\',\'appear\',{duration:0.5});"><img border=\"0\" src="'.exp_url.'/img/16/edit.gif" border="0" alt="edit"></a>';
    $type_key = "unpack_file";
    $icon['div'] = '';
    return $icon;
  }
}

// #########################################################################

class mp3play {
  var $file;
  function mp3play($file) {
    $this->file = $file;
  }
  
  function getIconHTML() {
    $flv = basename($this->file);
    $icon['icon'] = '<a href="'.exp_file.'?view=mp3&file='.urlencode($_REQUEST['folder']."/".$this->file).'" target="myiframe_target" onclick="Effect.toggle(\'my_iframe\',\'appear\',{duration:0.5});"><img border=\"0\" src="'.exp_url.'/img/16/play.gif" border="0" alt="edit"></a>';
    $icon['div'] = '';
    return $icon;
  }
}

class flvplay {
  var $file;
  function flvplay($file) {
    $this->file = $file;
  }
  
  function getIconHTML() {
    $flv = basename($this->file);
    $icon['icon'] = '<a href="'.exp_file.'?view=flv&file='.urlencode($_REQUEST['folder']."/".$this->file).'" target="myiframe_target" onclick="Effect.toggle(\'my_iframe\',\'appear\',{duration:0.5});"><img border=\"0\" src="'.exp_url.'/img/16/play.gif" border="0" alt="edit"></a>';
    $icon['div'] = '';
    return $icon;
  }
}

function IconDiv_wrapper() {
    

    $icon[0]['icon'] = '<a href="#" onclick="sndReq(\'reload\',\''.$_REQUEST['folder'].'\'); return false;"><img border=\"0\" src="'.exp_url.'/img/16/reload.gif" border="0" alt="login"></a>';
    $icon[0]['div']  = "";
    $std = new standard();
    $icon = $std->getIconHTML(); 
    
    $upload_file = new upload_file($_REQUEST['folder']);
    $icon[] = $upload_file->getIconHTML();
    $mkdir = new mkdir($_REQUEST['folder']);
    $icon[] = $mkdir->getIconHTML(); 
    $viewmode = new listview();
    $icon[] = $viewmode->getIconHTML();
    
    foreach($icon as $i)    {
      $all['icons'].= $i['icon'];
      $all['divs'].= $i['div'];
    }
    
    return $all;
}


function CB_setFileOptions($value,$row) {

  $login =  new login();

  if ($login->loginCheck()) {
     if(!empty($value)) {
       if ($value=="folder") {
         $delFile = new delFile($row['filename'],true);
         $icon = $delFile->getIconHTML();
         return $icon['icon'].$icon['div'];
       } else {
         $delFile = new delFile($row['filename'],false);
         $delFilei = $delFile->getIconHTML();
         $contenti.=$delFilei['icon'];
         $contentd.=$delFilei['div'];
         if(strtolower($value)=="zip") {
           $unpackFile = new unpack($row['filename']);
           $unpackFilei = $unpackFile->getIconHTML();
           $contenti.=$unpackFilei['icon'];
           $contentd.=$unpackFilei['div'];
         }
         if(strtolower($value)=="flv") {
           $flvplay = new flvplay($row['filename']);
           $flvplayi = $flvplay->getIconHTML();
           $contenti.=$flvplayi['icon'];
           $contentd.=$flvplayi['div'];
         }
         $songs = array_flip(explode(",","mp3"));
         if(isset($songs[strtolower($value)])) {
           $mp3File = new mp3play($row['filename']);
           $mp3Filei = $mp3File->getIconHTML();
           $contenti.=$mp3Filei['icon'];
           $contentd.=$mp3Filei['div'];
         }
         
         $editable = array_flip(explode(",","xml,sql,js,css,txt,html,htm"));
         if(isset($editable[strtolower($value)])) {
           $edFile = new edit_txt($row['filename']);
           $edFilei = $edFile->getIconHTML();
           $contenti.=$edFilei['icon'];
           $contentd.=$edFilei['div'];
         }
         return "<nobr>".$contenti.$contentd."</nobr>";
       }
    }
  } 
  return "&nbsp;";
}


?>
