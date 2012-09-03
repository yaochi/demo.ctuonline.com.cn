<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_notice3 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        $myscript = "'".$_GET['script']."'";
//        $one = '<select class="ps" name="parameter[select_type][]" onchange="javascript:location.href=portal.php?mod=portalcp&ac=block&op=setting&classname=notice&script='.$myscript.'&inajax=1&fid='.$_GET['fid'].'&select_type=this.value">';
		$myname = "'notice'";
        $one = '<select class="ps" name="parameter[select_type][]" onchange="block_get_setting_array('.$myname.', '.$myscript.', '.$_GET['fid'].', this.value)">';
        $one .= '<option value="">请选择</option>';
        
    	require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = $_GET["plugin_name"];
	    $groupid = $_GET["fid"];
	    if(common_category_is_enable($groupid, $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $pluginid);
	    }
		$defaulttype = '';
		while($value=$categorys) {
			if($defaulttype) {
				$defaulttype = $value['id'];
			}
			$check = '';
			if($_GET['select_type'] == $value['name']){
                $check = 'selected="selected"';
            }
			$one .= '<option value="'.$value['id'].'" '.$check.'>'.$value['name'].'</option>';
		}
        $one .='</select>';
        
        $mytype = "";
        if(isset($_GET['select_type'])) {
        	$mytype .= " AND category_id=".$_GET['select_type'];
        } else {
        	$mytype .= "";
        }
        
        $two = '<select class="ps" multiple="multiple" name="parameter[select_notice][]">';
		$query = DB::query("SELECT * FROM ".DB::table('notice')." WHERE group_id=".$groupid.$mytype);
		while($value=DB::fetch($query)) {
			$two .= '<option value="'.$value['id'].'" >'.$value['title'].'</option>';
		}
        $two .='</select>';

		$result[] = array("title"=>"请选择分类", "html"=>$one);
		$result[] = array("title"=>"请选择标题", "html"=>$two);
		$result[] = array("html"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>');
        return $result;
    }

    function getdata($style, $parameter) {
    	$groupid = $_GET["fid"];
    	$noticeid = '';
	    foreach($parameter['select_notice'] as $value) {
			$noticeid .= $value.',';
		}
		$noticeid = substr($noticeid, 0, strlen($noticeid)-1);
        $query = DB::query("SELECT * FROM ".DB::table('notice')." WHERE id IN (".$noticeid.") ORDER BY create_time DESC ");
        $html = "<ul>";
        while($data = DB::fetch($query)) {
            $url = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid=${data['id']}&notice_action=view";
//            显示标题和内容
            $html .= sprintf('<li><a href="%s">%s</a></li>', $url, $data["title"]);
            $html .= sprintf('<li>%s</li>', $data["content"]);
        }
        $html .= "</ul>";
        return array('html' => $html, 'data' =>null);
    }

}
?>
