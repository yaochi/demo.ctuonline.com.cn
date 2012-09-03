<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_polltitle{
	function getsetting() {
		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
         $settings = array(
                'ad_type' => array(
                    'title' => '选择分类',
                    'type'  => 'select',
                    'value' => array()
                ),
                'title' => array(
                    'title' => '指定标题',
                    'type' => 'text'
                ),
				'content_len' => array(
                     'title' => '投票标题字数',
                     'type' => 'text',
                     'default' => 50
                ),
                array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
        );

        //分类
        require_once libfile("function/category");
        $is_enable_category = false;
        $fid = $_GET["fid"];

        if(common_category_is_enable($fid, $plugin_id)){
            $is_enable_category = true;
            $categorys = common_category_get_category($fid, $plugin_id);
        }

        foreach ($categorys as $value){
                $settings['ad_type']['value'][] = array($value['id'], $value['name']);
        }
         $settings['ad_type']['value'][] = array(0, "未分类");
         $settings['ad_type']['value'][] = array(-1, "所有");

        return $settings;
	}
	 function getstylesetting($style) {
        $categorys_setting = array();
		 $categorys_setting["cate_title_author_desc"] = array(
            'summary_len' => array(
                'title' => '投票摘要字数',
                'type' => 'text',
                'default' => 50
            )
        );
		 $categorys_setting["cate_title_author_desc_photo"] = array(
            'summary_len' => array(
                'title' => '投票摘要字数',
                'type' => 'text',
                'default' => 50
            )
        );
        return $categorys_setting[$style];
    }
	function getdata($style, $parameter) {
	global $_G;
	$plugin_id = array_pop(explode("\\", dirname(dirname(__FILE__))));
		if(!$_GET["fid"]){
			return array('html'=>'请在专区内使用该组件', 'data'=>null);
		}
		 require_once libfile("function/category");
         $categorys = common_category_get_category($_GET["fid"],$plugin_id);
		 
        $ad_type = $parameter["ad_type"];
        $title = $parameter["title"];
	
		$ad_type = intval($ad_type);
        $adtype_sql = "";
        if($ad_type!=-1){
            $adtype_sql = " AND t.category_id = '$ad_type' ";
        }
		$query = DB::query("SELECT p.*,t.* FROM ".DB::table("forum_poll")." p LEFT JOIN ".DB::table("forum_thread")." t ON t.tid=p.tid WHERE t.displayorder!='-1' AND t.fid=".$_GET["fid"].$adtype_sql." AND t.subject LIKE '%$title%' ORDER BY dateline DESC LIMIT ".$parameter['items']);
		$forums = array();
		while($poll=DB::fetch($query)){		
			$poll['url']="forum.php?mod=viewthread&tid=".$poll['tid']."&special=1&plugin_name=poll&plugin_op=groupmenu";
			$poll['name']=cutstr($poll['subject'], $parameter["content_len"]);
			$poll['type'] = $poll['multiple'] ? 'checkbox' : 'radio';
			$poll['id']=$poll['tid'];
			$poll['contenttype']='poll';
			if($poll[category_id]<=0 || !$categorys[$poll[category_id]]){
                    $poll["category"] = "未分类";
                }else{
                    $poll["category"] = $categorys[$poll[category_id]]['name'];
                }
			if($style[key] == "cate_title_author_desc" || $style[key] == "cate_title_author_desc_photo"){
				 include_once libfile('block/thread', 'class');
				 $bt = new block_thread();
				$poll['description']=$bt->getthreadNew($poll['tid'], $parameter['summary_len'],$poll);
			}
			$poll["dateline"] = dgmdate($poll[dateline]);
			$poll["lastpost"] = dgmdate($poll[lastpost]);
			$poll['author']=user_get_user_name_by_username($poll[author]);
			if($poll['allvote']){
			}else{
				$poll['allvote'] = check_user_group($_G['username'],$_G['fid']);
			}
			//独立样式
		 	if($style[key]=="selfstyle"){
           		$pollquery = DB::query("SELECT * FROM ".DB::table('forum_polloption')." WHERE tid=".$poll['tid']." ORDER BY displayorder");
           		while($polloption = DB::fetch($pollquery)) {
				$polloption['polloption'] = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
					"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $polloption['polloption']);
					$poll['options'][] = $polloption;
				}
        	}
			$forums[] = $poll;
		}
		 $result["parameter"] = $parameter; //update by qiaoyongzhi,2011-2-25 EKSN143 模块高度
		 $result["listdata"] = $forums;
        return array('data' => $result);
	}
}
?>
