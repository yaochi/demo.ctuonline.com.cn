<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_notice2 {

function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        $myscript = "'".$_GET['script']."'";
        $groupid = $_GET["fid"];
//        $one = '<select class="ps" name="parameter[select_type][]" onchange="javascript:location.href=portal.php?mod=portalcp&ac=block&op=setting&classname=notice&script='.$myscript.'&inajax=1&fid='.$_GET['fid'].'&select_type=this.value">';
		$myname = "'notice'";
        $one = '<select class="ps" name="parameter[select_type][]" onchange="block_get_setting_array('.$myname.', '.$myscript.', '.$_GET['fid'].', this.value)">';
//        $one .= '<option value=""></option>';
    	
 //		分类
		require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = 'notice';
	    if(common_category_is_enable($groupid, $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $pluginid);
	    }
		$defaulttype = '';
		foreach ($categorys as $value) {
			if(!$defaulttype) {
				$defaulttype = $value['id'];
			}
			$check = '';
			if($_GET['select_type'] == $value['id']){
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
		$query = DB::query("SELECT * FROM ".DB::table('notice')." WHERE status=1 AND group_id=".$groupid.$mytype);
		while($value=DB::fetch($query)) {
			$two .= '<option value="'.$value['id'].'" >'.$value['title'].'</option>';
		}
        $two .='</select>';

		$result[] = array("title"=>"选择分类", "html"=>$one);
		$result[] = array("title"=>"选择通知公告", "html"=>$two);
		$result[] = array("html"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>');
        return $result;
    }
    
	function getstylesetting($style) {
    	$categorys_setting = array();
    	//纯列表
        $categorys_setting["general"] = array(
            'title_len' => array(
                'title' => '通知公告标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'scroll_check' => array(
            	'title'=> '是否滚动',
            	'type' => 'radio',
            	'value' => 0, 
            )
        );
        //独立样式
        $categorys_setting["selfstyle"] = array(
            'title_len' => array(
                'title' => '通知公告标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '通知公告摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '通知公告图片宽度',
                'type' => 'text',
                'value' => 140
            ),
            'top_pic_height' => array(
                'title' => '通知公告图片高度',
                'type' => 'text',
                'value' => 140
            ),
        );
        //全文滚动
        $categorys_setting["full_text_scroll"] = array(
            'title_len' => array(
                'title' => '通知公告标题字数',
                'type' => 'text',
                'value' => 50
            ),
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	$groupid = $_GET["fid"];
	    $noticeid = '';
	    foreach($parameter['select_notice'] as $value) {
			$noticeid .= $value.',';
		}
		$noticeid = substr($noticeid, 0, strlen($noticeid)-1);
		if ($noticeid) {
			$query = DB::query("SELECT * FROM ".DB::table('notice')." WHERE id IN (".$noticeid.") AND moderated!=-1 ORDER BY create_time DESC ");
	        $notices = array();
	    	if ($style[key] == "selfstyle") {
	            //图片尺寸  摘要长度  标题字数
	            while ($notice = DB::fetch($query)) {
	                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
	                $notice["name"] = cutstr($notice["title"], $parameter["title_len"]);
	                if ($notice["description"]) {
	                	require_once libfile("function/post");
						$notice['content'] = messagecutstr($notice['content'], 10000);
	                    $notice["content"] = cutstr($notice["content"], $parameter[top_desc_len]) . "...";
	                }
	                $notice["create_time"] = dgmdate($notice["create_time"]);
					$notice['contenttype']='notice';
	                $notices[$notice[id]] = $notice;
	            }
	        } elseif ($style[key] == "general") {
	        	//纯列表 记录标题字数
	            while ($notice = DB::fetch($query)) {
	                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
	                $notice["name"] = cutstr($notice["title"], $parameter["title_len"]);
					$notice['contenttype']='notice';
					
	                $notices[$notice[id]] = $notice;
	            }
	        }elseif ($style[key] == "full_text_scroll") {
	        	//纯列表 记录标题字数
	            while ($notice = DB::fetch($query)) {
	                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
	                $notice["name"] = cutstr($notice["title"], $parameter["title_len"]);
					$notice['contenttype']='notice';
	                $notices[$notice[id]] = $notice;
	            }
	        }
	        
		}
        $result["parameter"] = $parameter;
	    $result["listdata"] = $notices;
	    
        return array('data' => $result);
    }

}
?>
