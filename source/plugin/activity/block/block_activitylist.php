<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_activitylist {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

        $settings = array(
			'select_category' => array(
				'title' => '选择活动分类',
				'type' => 'select',
				'value' => array(),
			),
			'select_type' => array(
				'title' => '选择活动类型',
				'type' => 'select',
				'value' => array(),
			),
			'select_order' => array(
				'title' => '选择显示顺序',
				'type' => 'mradio',
				'value' => array(
					array('DESC', "创建时间降序"),
					array('ASC', "创建时间升序"),
				),
			),
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		//活动类型
		$settings['select_type']['value'][] = array(0, "全部");
	    $settings['select_type']['value'][] = array('general', "普通活动");
	    $settings['select_type']['value'][] = array('live', "直播活动");
		
		//活动分类
	    require_once libfile("function/category");
	    $is_enable_category = false;
	    $groupid = $_GET["fid"];
	    if(common_category_is_enable($groupid, $plugin_id)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $plugin_id);
	    } else {
	    	array_shift($settings);
	    }
	    $settings['select_category']['value'][] = array(0, '全部');
	    foreach ($categorys as $value) {
	    	$settings['select_category']['value'][] = array($value['id'], $value['name']);
	    }
	    
		return $settings;
    }

    function getstylesetting($style) {
        $categorys_setting = array();
        $categorys_setting["general"] = array(
            'title_len' => array(
                'title' => '活动标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        $categorys_setting["activityfocus"] = array(
            'title_len' => array(
                'title' => '活动标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '首条活动摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '首条活动的图片宽度',
                'type' => 'text',
                'value' => 140
            ),
            'top_pic_height' => array(
                'title' => '首条活动的图片高度',
                'type' => 'text',
                'value' => 140
            ),
        );
        $categorys_setting["hot"] = array(
            'title_len' => array(
                'title' => '活动标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'summary_len' => array(
                'title' => '活动摘要字数',
                'type' => 'text',
                'value' => 200
            )
        );
        $categorys_setting["bigpic_title"] = array(
            'title_len' => array(
                'title' => '活动标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'pic_width' => array(
                'title' => '活动图片宽度',
                'type' => 'text',
                'value' => 140
            ),
            'pic_height' => array(
                'title' => '活动图片高度',
                'type' => 'text',
                'value' => 140
            ),
        );
        //独立样式
        $categorys_setting["selfstyle"] = array(
            'title_len' => array(
                'title' => '活动标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '活动摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '活动图片宽度',
                'type' => 'text',
                'value' => 140
            ),
            'top_pic_height' => array(
                'title' => '活动图片高度',
                'type' => 'text',
                'value' => 140
            ),
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
        if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
    	$mytype = $parameter["select_type"];
    	$mycategory = $parameter["select_category"];
		$myorder = $parameter["select_order"];
    	if ($mytype) {
    		$mytypesql = " AND ffa.type='".$mytype."'";
    	} else {
    		$mytypesql = "";
    	}
    	if ($mycategory) {
    		$mycategorysql = " AND ff.category_id=".$mycategory;
    	} else {
    		$mycategorysql = "";
    	}
		if($myorder){
			$myordersql = $myorder;
		}else{
			$myordersql = "DESC";
		}
        $result = array();
        $query = DB::query("SELECT f.*, ff.dateline,ff.extra,ff.banner,ff.description,ff.membernum,ffa.type,ff.category_id,ffa.average,ffa.viewnum,ffa.teacher_num,ffa.live_num,ffa.live_id,ffa.teacher_id FROM " . DB::table("forum_forum") . " f," . DB::table("forum_forumfield") . " ff, " . DB::table("forum_forum_activity") . " ffa WHERE ff.fid=ffa.fid AND f.fid=ff.fid AND f.type='activity' AND f.fup=" . $_GET["fid"] . $mycategorysql . $mytypesql . " ORDER BY ff.dateline ".$myordersql." LIMIT " . $parameter['items']);
        $forums = array();
        if ($style[key] == "general") {
            //纯列表 记录标题字数
            while ($forum = DB::fetch($query)) {
                $forum["url"] = "forum.php?mod=activity&fid=" . $forum["fid"];
                $forum["name"] = cutstr($forum["name"], $parameter["title_len"]);
				$forum["contenttype"]='activity';
				$forum['id']=$forum['fid'];
                $forums[$forum[fid]] = $forum;
            }
        } else if ($style[key] == "activityfocus") {
            //首条记录的图片尺寸 首条记录的摘要长度 记录标题字数
            $top = null;
            while ($forum = DB::fetch($query)) {
        		if ($forum["type"]=='general') {
        			$extra = unserialize($forum["extra"]);
			        if($extra["startime"]!=0 && $extra["endtime"]!=0){
			            $forum["str_start_time"] = getdate($extra["startime"]);
			            $forum["str_end_time"] = getdate($extra["endtime"]);
			            $forum["startime"] = dgmdate($extra["startime"]);
			            $forum["endtime"] = dgmdate($extra["endtime"]);
			        }
			        $forum["teacher_img"] = get_groupimg($forum["banner"]);
        		} elseif ($forum["type"]=='live') {
	        		$extra = unserialize($forum["extra"]);
			        if($extra["startime"]!=0 && $extra["endtime"]!=0){
			            $forum["str_start_time"] = getdate($extra["startime"]);
			            $forum["str_end_time"] = getdate($extra["endtime"]);
			            $forum["startime"] = dgmdate($extra["startime"]);
			            $forum["endtime"] = dgmdate($extra["endtime"]);
			        }
	        		$liveid = unserialize($forum["live_id"]);
			        if(count($liveid)!=0){
			            if(empty($liveid[count($liveid)-1])){
			                array_pop($liveid);
			            }
			            $t = implode(",", $liveid);
			            if($t){
			                $livequery = DB::query("SELECT * FROM ".DB::table("group_live")." WHERE liveid IN(".$t.")");
			                $lives = array();
							$whatstatus='0';
			                while($live=DB::fetch($livequery)){
								if($live["starttime"]>time()){
									$live[whatstatus]='0';
								}elseif($live["endtime"]<time()){
									$live[whatstatus]='2';
								}else{
									$live[whatstatus]='1';
									$whatstatus='1';
								}
			                	$live["startime"] = dgmdate($live["starttime"],'Y-m-d H:i');
			            		$live["endtime"] =  dgmdate($live["endtime"],'H:i');
			                    $lives[$forum[fid]][] = $live;
			                }
			                $forum["lives"] = $lives;
							$forum["whatstatus"]=$whatstatus;
			            }
			        }
	        		$teacher_ids = unserialize($forum["teacher_id"]);
					foreach($teacher_ids as $i=>$teacherid){
						if($teacherid){
							$teacher_ids[$i]=$teacherid;
						}else{
							$teacher_ids[$i]='0';
						}
					}
		            $t = implode(",", $teacher_ids);
		            if($t){
		                $teacherquery = DB::query("SELECT * FROM " . DB::table("lecturer") . " WHERE id IN(" . $t .") " );
		                $teachers = array();
		                $teacherimgs = array();
		                $img_activity = null;
		            	while($teacher=DB::fetch($teacherquery)){
		            		$rtnn = check_is_group_teacher($forum['fup'], $teacher[id]);
		                	if ($rtnn) {
		                		$teacher[imgurl] = $rtnn[imgurl];
		                		if (!$img_activity && $teacher[imgurl]) {
		                			$img_activity = $teacher[imgurl];
		                		}
		                	} else {
		                	}
		                    $teachers[] = $teacher[name];
		                    $teacherimgs[] = $teacher[imgurl];
		                }
		                $teacher_names = implode(" ", $teachers);
			            if($teachers){
			                $teacher_img = $teacherimgs[0];
			            }
		                $forum["teacher_names"] = $teacher_names;
		            }
					
					if(empty($forum["banner"])){
						$forum["teacher_img"] = $teacher_img;
			        }else{
						$forum["teacher_img"] = get_groupimg($forum["banner"]);
			        }
        		}
        		$forum["img_activity"] = $img_activity;
        		$forum["pic"] = get_groupimg($forum['banner']);
        		$forum["url"] = "forum.php?mod=activity&fid=" . $forum["fid"];
                $forum["name"] = cutstr($forum["name"], $parameter["title_len"]);
				$forum["contenttype"]='activity';
				$forum['id']=$forum['fid'];
                $forum[pic] = get_groupimg($forum['banner']);
        		if ($forum["description"]) {
                    $forum["description"] = cutstr($forum["description"], $parameter[top_desc_len]);
                }
                
                if (!$top) {
                	$top = $forum;
                } else {
                	$forums[$forum[fid]] = $forum;
                }
            }
            
            $result["top"] = $top;
        } else if ($style[key] == "hot") {
            //记录标题字数  记录摘要字数
            while ($forum = DB::fetch($query)) {
                $forum["url"] = "forum.php?mod=activity&fid=" . $forum["fid"];
                $forum["name"] = cutstr($forum["name"], $parameter["title_len"]);
				$forum["contenttype"]='activity';
				$forum['id']=$forum['fid'];
                if ($forum["description"]) {
                    $forum["description"] = cutstr($forum["description"], $parameter[summary_len]);
                }
                $forums[$forum[fid]] = $forum;
                $forumids[] = $forum[fid];
            }
            //为热议添加作者
            if ($forumids) {
            	require_once libfile('function/group');
                $query = DB::query("SELECT fg.fid, fg.username FROM " . DB::table("forum_groupuser") . " fg WHERE fg.fid IN (" . implode(",", $forumids) . ")");
                while ($user = DB::fetch($query)) {
                	$user["username"] = user_get_user_name_by_username($user["username"]);
                    $forums[$user["fid"]]["author"] = $user["username"];
                }
            }
        } else if($style[key] == "bigpic_title"){
            while ($forum = DB::fetch($query)) {
                $forum["url"] = "forum.php?mod=activity&fid=" . $forum["fid"];
                $forum["name"] = cutstr($forum["name"], $parameter["title_len"]);
                $forum["contenttype"]='activity';
				$forum['id']=$forum['fid'];
            	$teacher_ids = unserialize($forum["teacher_id"]);
				foreach($teacher_ids as $i=>$teacherid){
						if($teacherid){
							$teacher_ids[$i]=$teacherid;
						}else{
							$teacher_ids[$i]='0';
						}
					}
	            $t = implode(",", $teacher_ids);
	            if($t){
	                $teacherquery = DB::query("SELECT * FROM " . DB::table("lecturer") . " WHERE id IN(" . $t .") " );
	                $img_activity = null;
	            	while($teacher=DB::fetch($teacherquery)){
	            		$rtnn = check_is_group_teacher($forum['fup'], $teacher[id]);
	                	if ($rtnn) {
	                		$teacher[imgurl] = $rtnn[imgurl];
	                		if (!$img_activity && $teacher[imgurl]) {
	                			$img_activity = $teacher[imgurl];
	                		}
	                	}
	                }
	            }
	            $forum["img_activity"] = $img_activity;
                $forum[pic] = get_groupimg($forum['banner']);
                $forums[$forum[fid]] = $forum;
            }
        } elseif ($style[key] == "selfstyle") {
        	while ($forum = DB::fetch($query)) {
        		if ($forum["type"]=='general') {
        			$extra = unserialize($forum["extra"]);
			        if($extra["startime"]!=0 && $extra["endtime"]!=0){
			            $forum["str_start_time"] = getdate($extra["startime"]);
			            $forum["str_end_time"] = getdate($extra["endtime"]);
			            $forum["startime"] = dgmdate($extra["startime"]);
			            $forum["endtime"] = dgmdate($extra["endtime"]);
			        }
			        $forum["teacher_img"] = get_groupimg($forum["banner"]);
        		} elseif ($forum["type"]=='live') {
	        		$extra = unserialize($forum["extra"]);
			        if($extra["startime"]!=0 && $extra["endtime"]!=0){
			            $forum["str_start_time"] = getdate($extra["startime"]);
			            $forum["str_end_time"] = getdate($extra["endtime"]);
			            $forum["startime"] = dgmdate($extra["startime"]);
			            $forum["endtime"] = dgmdate($extra["endtime"]);
			        }
	        		$liveid = unserialize($forum["live_id"]);
			        if(count($liveid)!=0){
			            if(empty($liveid[count($liveid)-1])){
			                array_pop($liveid);
			            }
			            $t = implode(",", $liveid);
			            if($t){
			                $livequery = DB::query("SELECT * FROM ".DB::table("group_live")." WHERE liveid IN(".$t.")");
			                $lives = array();
							$whatstatus='0';
			                while($live=DB::fetch($livequery)){
								if($live["starttime"]>time()){
									$live[whatstatus]='0';
								}elseif($live["endtime"]<time()){
									$live[whatstatus]='2';
								}else{
									$live[whatstatus]='1';
									$whatstatus='1';
								}
			                	$live["startime"] = dgmdate($live["starttime"],'Y-m-d H:i');
			            		$live["endtime"] = dgmdate($live["endtime"],'H:i');
			                    $lives[$forum[fid]][] = $live;
			                }
							$forum["lives"] = $lives;
							$forum["whatstatus"]=$whatstatus;
			            }
			        }
	        		$teacher_ids = unserialize($forum["teacher_id"]);
					foreach($teacher_ids as $i=>$teacherid){
						if($teacherid){
							$teacher_ids[$i]=$teacherid;
						}else{
							$teacher_ids[$i]='0';
						}
					}
		            $t = implode(",", $teacher_ids);
		            if($t){
		                $teacherquery = DB::query("SELECT * FROM " . DB::table("lecturer") . " WHERE id IN(" . $t .") " );
		                $teachers = array();
		                $teacherimgs = array();
		                $img_activity = null;
		            	while($teacher=DB::fetch($teacherquery)){
		            		$rtnn = check_is_group_teacher($forum['fup'], $teacher[id]);
		                	if ($rtnn) {
		                		$teacher[imgurl] = $rtnn[imgurl];
		                		if (!$img_activity && $teacher[imgurl]) {
		                			$img_activity = $teacher[imgurl];
		                		}
		                	} else {
		                	}
		                    $teachers[] = $teacher[name];
		                    $teacherimgs[] = $teacher[imgurl];
		                }
		                $teacher_names = implode(" ", $teachers);
			            if($teachers){
			                $teacher_img = $teacherimgs[0];
			            }
		                $forum["teacher_names"] = $teacher_names;
		            }
					
					if(empty($forum["banner"])){
						$forum["teacher_img"] = $teacher_img;
			        }else{
						$forum["teacher_img"] = get_groupimg($forum["banner"]);
			        }
        		}
        		$forum["img_activity"] = $img_activity;
        		$forum["pic"] = get_groupimg($forum['banner']);
        		$forum["url"] = "forum.php?mod=activity&fid=" . $forum["fid"];
                $forum["name"] = cutstr($forum["name"], $parameter["title_len"]);
				$forum["contenttype"]='activity';
				$forum['id']=$forum['fid'];
                $forum[pic] = get_groupimg($forum['banner']);
        		if ($forum["description"]) {
                    $forum["description"] = cutstr($forum["description"], $parameter[top_desc_len]);
                }
                $forums[$forum[fid]] = $forum;
            }
        }
        $result["parameter"] = $parameter;
        $result["listdata"] = $forums;
        return array('data' => $result);
    }

}

?>
