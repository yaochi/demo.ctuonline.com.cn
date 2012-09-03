<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_notice {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        $settings = array(
        	'select_type' => array(
				'title' => '选择分类',
				'type' => 'select',
				'value' => array(),
			),
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		
	    //分类
	    require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = $_GET["classname"];
	    $groupid = $_GET["fid"];
	    if(common_category_is_enable($groupid, $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $pluginid);
	    } else {
	    	array_shift($settings);
	    }
	    $settings['select_type']['value'][] = array(0, '全部');
	    foreach ($categorys as $value) {
	    	$settings['select_type']['value'][] = array($value['id'], $value['name']);
	    }
		return $settings;
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
        //焦点
        $categorys_setting["focus"] = array(
            'title_len' => array(
                'title' => '通知公告标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '首条通知公告摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '首条通知公告的图片宽度',
                'type' => 'text',
                'value' => 140
            ),
            'top_pic_height' => array(
                'title' => '首条通知公告的图片高度',
                'type' => 'text',
                'value' => 140
            ),
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
        //分类+标题+时间
        $categorys_setting["cate_title_posttime"] = array(
            'title_len' => array(
                'title' => '通知公告标题字数',
                'type' => 'text',
                'value' => 50
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
       	
       	//(分类) + 标题 + 摘要
        $categorys_setting["cate_title_author_desc"] = array(
            'title_len' => array(
                'title' => '通知公告标题字数',
                'type' => 'text',
                'value' => 50
            ),
             'desc_len' => array(
                'title' => '通知公告摘要字数',
                'type' => 'text',
                'value' => 200
            ),
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $result = array();
    	$groupid = $_GET["fid"];
    	if ($groupid) {
    		$groupsql = " group_id=".$groupid;
    	}
    	$mytype = $parameter["select_type"];
    	if ($mytype) {
    		$mytypesql = " AND category_id=".$mytype;
    	}
        $sql = "SELECT * FROM ".DB::table('notice')." WHERE ".$groupsql.$mytypesql." AND moderated!=-1 AND status=1 ORDER BY displayorder DESC, create_time DESC LIMIT ".$parameter['items'];
        $query = DB::query($sql);
        $notices = array();

		if ($style[key] == "general") {
            //纯列表 记录标题字数
            while ($notice = DB::fetch($query)) {
                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
                $notice["name"] = cutstr($notice["title"], $parameter["title_len"]);
				$notice['contenttype']='notice';
                $notices[$notice[id]] = $notice;
            }
        } else if ($style[key] == "focus") {
            //首条记录的图片尺寸 首条记录的摘要长度 记录标题字数
            while ($notice = DB::fetch($query)) {
                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
                $notice["name"] = cutstr($notice["title"], $parameter["title_len"]);
                if ($notice["content"]) {
                	require_once libfile("function/post");
					$notice["content"] = messagecutstr($notice["content"], 10000);
                    $notice["description"] = cutstr($notice["content"], $parameter["top_desc_len"]) . "...";
                }
                $notice["create_time"] = dgmdate($notice["create_time"]);
				$notice['contenttype']='notice';
                if (!$top) {
                	$top = $notice;
                	$top["pic"] = $top["imgurl"];
                } else {
                	$notices[$notice[id]] = $notice;
                }
            }
            $result["top"] = $top;
        } else if ($style[key] == "selfstyle") {
            //图片尺寸 摘要长度 标题字数
            while ($notice = DB::fetch($query)) {
                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
                $notice["name"] = cutstr($notice["title"], $parameter["title_len"]);
                if ($notice["content"]) {
                	require_once libfile("function/post");
					$notice['content'] = messagecutstr($notice['content'], 10000);
                    $notice["content"] = cutstr($notice["content"], $parameter["top_desc_len"]) . "...";
                }
                $notice["create_time"] = dgmdate($notice["create_time"]);
				$notice['contenttype']='notice';
                $notices[$notice[id]] = $notice;
            }
        } elseif ($style[key] == "cate_title_posttime") {
        	require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "notice");
        	while ($notice = DB::fetch($query)) {
                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
                $notice["title"] = cutstr($notice["title"], $parameter["title_len"]);
                if($notice[category_id]<=0 || !$categorys[$notice[category_id]]){
                    $notice["category"] = "未分类";
                }else{
                    $notice["category"] = $categorys[$notice[category_id]][name];
                }
                $notice["posttime"] = dgmdate($notice["create_time"]);
				$notice['contenttype']='notice';
                $notices[$notice[id]] = $notice;
            }
        }elseif ($style[key] == "full_text_scroll") {
        	require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "notice");
        	while ($notice = DB::fetch($query)) {
                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
                $notice["title"] = cutstr($notice["title"], $parameter["title_len"]);
                if($notice[category_id]<=0 || !$categorys[$notice[category_id]]){
                    $notice["category"] = "未分类";
                }else{
                    $notice["category"] = $categorys[$notice[category_id]][name];
                }
                $notice["posttime"] = dgmdate($notice["create_time"]);
				$notice['contenttype']='notice';
                $notices[$notice[id]] = $notice;
            }
        }elseif ($style[key] == "cate_title_author_desc") {
        	require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "notice");
        	while ($notice = DB::fetch($query)) {
                $notice["url"] = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid={$notice['id']}&notice_action=view";
                $notice["title"] = cutstr($notice["title"], $parameter["title_len"]);
                if($notice[category_id]<=0 || !$categorys[$notice[category_id]]){
                    $notice["category"] = "未分类";
                }else{
                    $notice["category"] = $categorys[$notice[category_id]][name];
                }
                if ($notice["content"]) {
                	require_once libfile("function/post");
					$notice['content'] = messagecutstr($notice['content'], 10000);
                    $notice["content"] = cutstr($notice["content"], $parameter["desc_len"]);
                }
                $notice["posttime"] = dgmdate($notice["create_time"]);
				$notice['contenttype']='notice';
                $notices[$notice[id]] = $notice;
            }
        }
        $result["parameter"] = $parameter;
        $result["listdata"] = $notices;
        
        return array('data' => $result);
    }

}
?>
