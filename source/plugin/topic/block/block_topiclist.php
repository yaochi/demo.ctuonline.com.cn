<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_topiclist {
    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        $return = array(
            'select_type' => array(
				'title' => '选择分类',
				'type' => 'select',
				'value' => array(),
			),
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );

        require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = "topic";
	    $groupid = $_GET["fid"];
	    if(common_category_is_enable($groupid, $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $pluginid);
	    } else {
	    	array_shift($settings);
	    }
        $return['select_type']['value'][] = array("0", "全部");
	    foreach ($categorys as $value) {
	    	$return['select_type']['value'][] = array($value['id'], $value['name']);
	    }
        
        return $return;
    }

    function getstylesetting($style) {
        $categorys_setting = array();
        $categorys_setting["general"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        $categorys_setting["focus"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '首条话题摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '首条话题的图片宽度',
                'type' => 'text',
                'value' => 140
            ),
            'top_pic_height' => array(
                'title' => '首条话题的图片高度',
                'type' => 'text',
                'value' => 140
            ),
        );
        $categorys_setting["hot"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'summary_len' => array(
                'title' => '话题摘要字数',
                'type' => 'text',
                'value' => 200
            )
        );
        $categorys_setting["out"] = array(
            'summary_len' => array(
                'title' => '话题摘要字数',
                'type' => 'text',
                'value' => 100
            )
        );
        $categorys_setting["cate_title_author"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
        );
        $categorys_setting["cate_title_author_desc"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
             'summary_len' => array(
                'title' => '话题摘要字数',
                'type' => 'text',
                'value' => 200
            )
        );
        $categorys_setting["cate_title_author_desc_photo"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
             'summary_len' => array(
                'title' => '话题摘要字数',
                'type' => 'text',
                'value' => 200
            )
        );
        $categorys_setting["cate_title_posttime"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
        );
        $categorys_setting["cate_title_lastpost"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
        );
        $categorys_setting["cate_title_viewnum"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
        );
        $categorys_setting["cate_title_replynum"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
        );
        $categorys_setting["title_author_reply_lastpost"] = array(
            'title_len' => array(
                'title' => '话题标题字数',
                'type' => 'text',
                'value' => 50
            ),
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
        if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        if(!empty($parameter[select_type]) && $parameter[select_type]!=0){
            $where = " AND t.category_id=".$parameter[select_type];
        }
        $result = array();
        $sql = "SELECT t.*, cmp.realname realname FROM ".DB::table("forum_thread")." t, ".DB::table("common_member_profile")." cmp 
			WHERE t.special=0 AND t.displayorder IN (0, 1, 2, 3, 4) AND cmp.uid=t.authorid AND t.fid=$_GET[fid] $where 
			ORDER BY t.displayorder DESC, t.lastpost DESC
			LIMIT $parameter[items]";
        $query = DB::query($sql);
        $threads = array();
         
        if ($style[key] == "general") {
            //纯列表 记录标题字数
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        } else if ($style[key] == "focus") {
            //首条记录的图片尺寸 首条记录的摘要长度 记录标题字数
            include_once libfile('block/thread', 'class');
            $bt = new block_thread();
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
               // $thread["description"] = $bt->getthread($thread['tid'], $parameter[top_desc_len]);
               //把对象传过去，不查询数据库
                $thread["description"] = $bt->getthreadNew($thread['tid'], $thread);
                
                $thread["create_time"] = dgmdate($thread["dateline"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                if (!$top) {
                	$top = $thread;
					$top['id']=$thread[tid];
					$top['contenttype']='topic';
                	$top["pic"] = 'data/attachment/'.($data['attachment'] ? 'forum/'.$data['attachment'] : STATICURL.'image/common/nophoto.gif');
                } else {
                	$threads[$thread[tid]] = $thread;
                }
            }
            $data = $bt->getpic($top['tid']);
            $result["top"] = $top;
        } else if ($style[key] == "hot") {
            //记录标题字数  记录摘要字数
            include_once libfile('block/thread', 'class');
            $bt = new block_thread();
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
                $thread["description"] = $bt->getthread($thread['tid'], $parameter[summary_len]);
                $thread['author'] = $thread['realname'];
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        } else if($style[key]=='out'){
            include_once libfile('block/thread', 'class');
            $bt = new block_thread();
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["description"] = $bt->getthread($thread['tid'], $parameter[summary_len]);
                $thread["dateline"] = dgmdate($thread[dateline]);
				$thread["name"] = $thread["subject"];
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        } else if($style[key]=="cate_title_author"){
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
                $threads[$thread[tid]] = $thread;
            }
        } else if($style[key]=="cate_title_author_desc"){
            include_once libfile('block/thread', 'class');
            $bt = new block_thread();
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
                $thread["description"] = $bt->getthread($thread['tid'], $parameter[summary_len]);                
                $thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        } else if($style[key]=="cate_title_author_desc_photo"){
            include_once libfile('block/thread', 'class');
            $bt = new block_thread();
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
                $thread["description"] = $bt->getthread($thread['tid'], $parameter[summary_len]);
				  $thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        } else if($style[key]=="cate_title_posttime"){
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
                $thread["posttime"] = dgmdate($thread[dateline]);
				$thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        }  else if($style[key]=="cate_title_lastpost"){
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
                $thread["lastpost"] = dgmdate($thread[lastpost]);
				$thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        }   else if($style[key]=="cate_title_viewnum"){
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
				$thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        } else if($style[key]=="cate_title_replynum"){
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
				$thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        } else if($style[key]=="title_author_reply_lastpost"){
            require_once libfile("function/category");
            $categorys = common_category_get_category($_GET["fid"], "topic");
            while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&per=10&ordertype=desc&tid=" . $thread["tid"];
                $thread["subject"] = cutstr($thread["subject"], $parameter["title_len"]);
                if($thread[category_id]>0 && $categorys[$thread[category_id]]){
                    $thread["category"] = $categorys[$thread[category_id]][name];
                }
                $thread["dateline"] = dgmdate($thread[dateline]);
                $thread["lastpost"] = dgmdate($thread[lastpost]);
                $thread["author"] = user_get_user_name_by_username($thread["author"]);
                $thread["lastposteruid"] = user_get_uid_by_username($thread["lastposter"]);
                $thread["lastposter"] = user_get_user_name_by_username($thread["lastposter"]);
				$thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
        }
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        return array('data' => $result);
    }

}

?>
