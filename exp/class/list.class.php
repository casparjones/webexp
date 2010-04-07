<?php
// defines
define("pd_list_tblwidth", "100%");

class pDlist
{

var $data;
var $meta;
var $_tbl_config;
var $_header;
var $__footer;
var $_id;
var $_DB;
var $_sql;
var $_config;
var $_maxTextSize;

function pDlist($id=false) {
  if ($id==false) {
    $this->_id = time();
  } else {
    $this->_id = $id;
  }
  
  if ($_GET['list'][$this->_id])  
  {
    $order_row = $_GET['list'][$this->_id]['order'];
    if ($_GET['list'][$this->_id]['dir']) {
      $this->_header[$order_row]['dir'] = $_GET['list'][$this->_id]['dir'];
    } else {
      $this->_header[$order_row]['dir'] = "DESC";
    }
    
    $this->_sql['order'] = $order_row." ".$this->_header[$order_row]['dir'];
  }
  
  $this->_config['max_rows'] = 30;
  $this->setPageParam();
  $this->setTableWidth();
}

function setPageParam($page=false) {
  if ($page>0) {
    $this->_config['page'] = intval($page);
  } else {
    $this->_config['page'] = intval($_GET['page']);
  }
  $this->_sql['limit']['start'] = $this->_config['page']*$this->_config['max_rows'];
  $this->_sql['limit']['max'] = $this->_config['max_rows']; 
}

function setMaxRows($max) {
  if ($max>0) {
    $this->_config['max_rows'] = intval($max);
    $this->setPageParam();
  }
}

function setData($data) {
  if ($data['version']=="V1.1") {
    // new format
    $this->data = $data['data'];
    $this->meta = $data['meta'];  
  } else {
    $this->data = $data;
  }
  
  if(!is_array($this->data)) $this->data = array();
  
  reset($this->data);
  $this->_config['cols'] = count(current($this->data));
  $this->_config['rows'] = count($this->data);
  $first_pos = intval(key($this->data));
  if ($first_pos>0) {
    $page = $this->_config['max_rows']/$first_pos;
  } else {
    $page = 0;
  }
  $this->setPageParam(intval($page));
}

function getHTML_PicView() {
    $objs = $this->getObjectsInTmpl();
    $html = implode(" ",$objs);
    
    return $html;
}

function getObjectsInTmpl($tpl=false) {
    if ($tpl==false || empty($tpl)) {
      $tpl = getDefaultObjectTpl();
    }
    foreach ($this->data as $row_id=>$row_data) {
      $obj[$row_id] = $this->parseTpl($tpl,$row_data);
    }
    
  return $obj;
}

function parseTpl($tpl,$data) {
  foreach ($data as $search=>$replace) {
    if ($this->_maxTextSize[$search]>0 && strlen($replace)>$this->_maxTextSize[$search]) {
      $kurz_replace = substr($replace,0,$this->_maxTextSize[$search]);
      $kurz_replace = strtr($kurz_replace, array(" "=>"&nbsp;")); 
      $replace = "<span title=\"".$replace."\">".$kurz_replace."...</span>";
    }
    $tpl = preg_replace("|\{".$search."\}|", $replace, $tpl);
  }
  
  return $tpl;
}

function getHTML() {
  $tbl_header = $this->getHeader();
  $tbl_body = $this->getBody();
  if(isset($this->_footer)) {
    $tbl_footer = $this->getFooter();
  } else {
    $tbl_footer = "";
  }
  
  if ($this->_config['noPageControl']==true) {
    $page_control = "";
  } else {
    $page_control = $this->getPageControl();
  }
  $result = "<table id=\"".$this->_id."\" width=\"".$this->_tbl_config['width']."\">\n".$tbl_header."\n".$tbl_body."\n".$tbl_footer."\n$page_control</table>";
  return $result;
}

function getPageControl() {
  $res_str="<tr><td align=\"right\" colspan=\"".$this->_config['cols']."\">[&nbsp;";
  if($_GET['list']) {
    $opt = '&list['.$this->_id.'][order]='.$_GET['list'][$this->_id]['order'].'&list['.$this->_id.'][dir]='.$_GET['list'][$this->_id]['dir'];
  }
    
  if ($this->_config['page']>0) {
    $res_str.= "<a href=\"?page=".($this->_config['page']-1).$opt."\">&larr;&nbsp;zurück</a>&nbsp;|";
  } 
  $res_str.= "&nbsp;Page:&nbsp;".($this->_config['page']+1);
  if ($this->meta['FOUND_ROWS']>0) {
    $max_page = ceil($this->meta['FOUND_ROWS']/$this->_config['max_rows']);
    $res_str.= "/".$max_page."&nbsp;";
    $allow_forward = $this->_config['page']+1<$max_page;
  } else {
    $allow_forward = $this->_config['rows']==$this->_config['max_rows'];
    $res_str.= "&nbsp;";
  }
  if ($allow_forward) {
    $res_str.= "|&nbsp;<a href=\"?page=".($this->_config['page']+1).$opt."\">vor&nbsp;&rarr;</a>";
  }
  $res_str.= "&nbsp;]</td></tr>";
  return $res_str;
}

function getSQL() {
  return $this->_sql;
}

function getFooter() {
  $row = '<tr>';
  reset($this->data);
  $oneDatas = current($this->data);
 
  foreach ($oneDatas as $key=>$data) {
    $value = "";
    $td_option = "";
     if ($this->_header[$key]['invisible']==true) {
       // unsichtbar
     } else {
        if ($this->_footer['sum'][$key]) {
          $value = $this->_footer['sum'][$key];
          $td_option.= " style='border-top: 1px solid black; border-bottom: 1px double black;'";
        } 
        
        if (isset($this->_header[$key]['callBack'])) {
            $callBackparams = $this->_header[$key]['callBack_params'];
            $callBackparams['key'] = $key;
            $new_value = call_user_func($this->_header[$key]['callBack'],$value,$data,$callBackparams);
        }
      	 
      	if (empty($value)) {
            $value = "&nbsp;";
        }
          
      	if (empty($new_value)) {
            $new_value = $value;
        }
        
         if (!empty($this->_header[$key]['valign'])) {
            $td_option.= " valign=\"".$this->_header[$key]['valign']."\"";
         } else {
            $td_option.= " valign=\"top\"";
         }
         
         if (!empty($this->_header[$key]['align'])) {
            $td_option.= " align=\"".$this->_header[$key]['align']."\"";
         } else {
            $td_option.= " align=\"left\"";
         }
         
         if ($this->_header[$key]['width']) {
           $td_option.= " width='".$this->_header[$key]['width']."'";
         }
        
        if (!empty($td_option)) {
         $row.= '   <td '.$td_option.'>'.$new_value.'</td>'."\n";
        } else {
         $row.= '   <td>'.$new_value.'</td>'."\n";
        }
    }
  }
  $row .= "</tr>";
  
  // echo "<pre>".htmlentities($row)."</pre>";
  
  return $row;
}

function getHeader() {

    $oneDatas = current($this->data);
    if(is_array($oneDatas)) {
      $row = '<thead>
      <tr bgcolor="#8B8BFF">';
      foreach ($oneDatas as $key=>$data) {
        if ($this->_header[$key]['invisible']==true) {
          // unsichtbar
        } else {
          $th_options = "";
          if ($this->_header[$key]['name']) {
            $row_name = $this->_header[$key]['name'];
          } else {
            $row_name = $key;
          }
        
          if ($this->_header[$key]['width']) {
            $th_options = " width='".$this->_header[$key]['width']."'";
          }
        
          if ($this->_header[$key]['dir']=="ASC") {
            $order_direction = "DESC";
            $pic_dir = "<img src='".cvs_url_img."/up.gif'>";
          } elseif ($this->_header[$key]['dir']=="DESC") {
            $order_direction = "ASC";
            $pic_dir = "<img src='".cvs_url_img."/down.gif'>";
          } else {
            $order_direction = "ASC";
            $pic_dir = "<img src='".cvs_url_img."/trans.gif' width='11px' height='7px'>";
          }
          
          if ($this->_header[$key]['noOrder']==true) {
            $row .= '<th id="'.$key.'"'.$th_options.'>'.$row_name.'</th>';
          } else {
            $row .= '<th id="'.$key.'"'.$th_options.'>'.$pic_dir.'<a href="?list['.$this->_id.'][order]='.$key.'&list['.$this->_id.'][dir]='.$order_direction.'">'.$row_name.'</a></th>';
          }
        }
      }
      $row .= '</tr></thead>';
    }
  return $row;
}

function getBody() {
    $row = '<tbody>';
    $back[]="bgcolor=\"#E4EEFF\"";
    $back[]="bgcolor=\"#C1D8FF\"";
  foreach ($this->data as $row_id=>$row_data) {
    $row.= '<tr '.$back[bcmod($row_id,2)].'>'."\n";
    foreach ($row_data as $key=>$value) {
       if ($this->_header[$key]['invisible']==true) {
         // unsichtbar
       } else {
         unset($td_option);
         unset($new_value);
         if (isset($this->_header[$key]['callBack'])) {
            $callBackparams = $this->_header[$key]['callBack_params'];
            $callBackparams['key'] = $key;
            $new_value = call_user_func($this->_header[$key]['callBack'],$row_data[$key],$row_data,$callBackparams);
         }
    	   
    	   if (empty($new_value)) {
            $new_value = $value;
         }
         
         if (!empty($this->_header[$key]['valign'])) {
            $td_option = " valign=\"".$this->_header[$key]['valign']."\"";
         } else {
            $td_option = " valign=\"top\"";
         }
         
         if (!empty($this->_header[$key]['align'])) {
            $td_option.= " align=\"".$this->_header[$key]['align']."\"";
         } else {
            $td_option.= " align=\"left\"";
         }
         
         if ($this->_header[$key]['width']) {
           $td_option.= " width='".$this->_header[$key]['width']."'";
         }
   
         if ($this->_header[$key]['sumEnable']) {
            $this->_footer['sum'][$key]=$this->_footer['sum'][$key]+$value;
         }
   
         if (!empty($td_option)) {
          $row.= '   <td '.$td_option.'>'.$new_value.'</td>'."\n";
         } else {
          $row.= '   <td>'.$new_value.'</td>'."\n";
         }
    	 }
    }
    $row.= '</tr>'."\n";
  }
  $row .= '</thead>';
  return $row;
}

function setColMaxSize($colLabel, $maxSize) {
  $this->_maxTextSize[$colLabel] = $maxSize;
}

function setColName($colLabel, $colName) {
  $this->_header[$colLabel]['name'] = $colName;
}

function setColInvisible($colLabel) {
  $this->_header[$colLabel]['invisible'] = true;
}

function setColVisible($colLabel) {
  $this->_header[$colLabel]['invisible'] = false;
}

function setColNoOrder($colLabel) {
  $this->_header[$colLabel]['noOrder'] = true;
}

function setCallBackFunktion($colLabel,$function,$params=false) {
  $this->_header[$colLabel]['callBack'] = $function;
  $this->_header[$colLabel]['callBack_params'] = $params;
}

function setTableWidth($width=false) {
  if ($widht==false) {
    $this->_tbl_config['width'] = pd_list_tblwidth;
  } else {
    $this->_tbl_config['width'] = $width;
  }
}

function setColValign($colLabel,$valign=false) {
   $this->_header[$colLabel]['valign'] = $valign;
}

function setColAlign($colLabel,$align=false) {
   $this->_header[$colLabel]['align'] = $align;
}

function setColWidth($colLabel, $size="") {
   $this->_header[$colLabel]['width'] = $size;
}

function setConfigParam($was,$value) {
  $this->_config[$was] = $value;
}

function setColSum($colLabel) {
    $this->_header[$colLabel]['sumEnable'] = true;
}


} // end Class

function getDefaultObjectTpl() {
$tpl = '<a href="{url}"{aoptions}><table style="display:inline" width="110px" height="110px">
<tr><td width="110px" height="95px" valign="center" align="center"><a href="{url}"{aoptions}>{ext}</a></td></tr>
<tr><td width="110px" valign="center" align="center" style="font-size:9px"><a href="{url}"{aoptions}>{filename}</a></td></tr>
</table></a>';
$tpl = '
    <table style="display:inline" width="110px" height="110px">
      <tr>
        <td width="110px" height="95px" valign="center" align="center">
          <a href="{url}"{aoptions}>{ext}</a>
        </td>
      </tr>
      <tr>
        <td width="110px" valign="center" align="center" style="font-size:9px">
          <a href="{url}">{filename}</a>
        </td>
      </tr>
    </table>
';
$tpl = '
<a href="{url}"{aoptions}>
  <div id="galerie">
   	<div id="thumb">
      {ext}
    </div>
  	<div id="name">{filename}</div>
  </div>
</a>';
return $tpl;
}

// Callbackfunktions

function cb_setDate($value, $row, $params=false) {
  if (empty($params['format'])) {
    $res = date("d.m.Y H:i:s",$value);
  } else {
    $res = date($params['format'],$value);
  }
  
  return $res;
}



?>
