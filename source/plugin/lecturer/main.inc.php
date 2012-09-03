<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function getuser($uid) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid=".$uid);
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function getmember($uid) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid=".$uid);
	$userinfo = DB::fetch($query);
	return $userinfo;
}


function index(){
	$groupid = $_GET["fid"];
	$addsql = "";
	$addurl = "";
	
	$isinnerlec = $_POST["isinnerlec"] ? $_POST["isinnerlec"] : $_GET["isinnerlec"];
	$name = $_POST["name"] ? $_POST["name"] : $_GET["name"];
	$rank_inner = $_POST["rank_inner"] ? $_POST["rank_inner"] : $_GET["rank_inner"];
	$orgid = $_POST["orgname_input_id"] ? $_POST["orgname_input_id"] : $_GET["orgid"];
	$orgname = $_POST["orgname_input"] ? $_POST["orgname_input"] : $_GET["orgname"];
	$includechild = $_POST["includechild"] ? $_POST["includechild"] : $_GET["includechild"];
	$rank_outter = $_POST["rank_outter"] ? $_POST["rank_outter"] : $_GET["rank_outter"];
	$teachdirection = $_POST["teachdirection"] ? $_POST["teachdirection"] : $_GET["teachdirection"];
	$course=$_POST["course"] ? $_POST["course"] : $_GET["course"];
	//内外部
	if ($isinnerlec==1) {
		$isinnerlecsql = " AND lec.isinnerlec=".$isinnerlec;
		$isinnerlecurl = "&isinnerlec=".$isinnerlec;
		//级别
		if ($rank_inner) {
			$ranksql = " AND lec.rank=".$rank_inner;
			$rankurl = "&rank_inner=".$rank_inner;
		}
	} elseif ($isinnerlec==2) {
		$isinnerlecsql = " AND lec.isinnerlec=".$isinnerlec;
		$isinnerlecurl = "&isinnerlec=".$isinnerlec;
		//级别
		if ($rank_outter) {
			$ranksql = " AND lec.rank=".$rank_outter;
			$rankurl = "&rank_outter=".$rank_outter;
		}
	} elseif (!$isinnerlec) {
		//$isinnerlecsql = " AND lec.isinnerlec=1";
		//$isinnerlecurl = "&isinnerlec=1";
	}
	//姓名
	if ($name) {
		$namesql = " AND lec.name like'%".$name.trim()."%'";
		$nameurl = "&name=".$name.trim();
	}

	//机构
	if ($orgid) {
		if ($includechild) {
			//获取子机构
			require_once libfile('function/org');
			
			if($orgid != 9002){
				//
				$orgidlist = getSubOrg($orgid);
		    	$orgids = implode(",", $orgidlist);
		    	if ($orgids) {
	                $orgidsql = " AND lec.orgid in(".$orgids.",".$orgid.")";
		    	} else {
		    		$orgidsql = " AND lec.orgid=".$orgid;
		    	}
			}else{
				//如果选择了集团,则不需要判断条件了，因为此时需要查询的是全部的机构
			}
	    	
		} else {
			$orgidsql = " AND lec.orgid=".$orgid;
		}
		$orgidurl = "&orgid=".$orgid;
		$includechildurl = "&includechild=".$includechild;
	}
	//授课方向
	if ($teachdirection) {
		$teachdirectionsql = " AND lec.teachdirection=".$teachdirection;
		$teachdirectionurl = "&teachdirection=".$teachdirection;
	}
	if ($course) {
		$coursesql = " AND lec.courses like '%".$course.trim()."%' ";
		$courseurl = "&course=".$course;
	}
	
	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
    $query = DB::query("SELECT lec.*, flec.id flecid, flec.imgurl fimgurl, flec.about fabout FROM ".DB::table("lecturer")." lec, ".DB::table("forum_forum_lecturer")." flec WHERE lec.id=flec.lecid AND flec.fid=".$groupid.$addsql.$isinnerlecsql.$ranksql.$namesql.$orgidsql.$teachdirectionsql.$coursesql." ORDER BY flec.displayorder DESC, flec.dateline DESC LIMIT $start,$perpage ");
	$addsql=" FROM ".DB::table("lecturer")." lec, ".DB::table("forum_forum_lecturer")." flec WHERE lec.id=flec.lecid AND flec.fid=".$groupid.$addsql.$isinnerlecsql.$ranksql.$namesql.$orgidsql.$teachdirectionsql.$coursesql;
    $lecturers = array();
    $_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
    while ($lecturer = DB::fetch($query)) {
	    if($lecturer['highlight']) {
			$string = sprintf('%02d', $notice['highlight']);
			$stylestr = sprintf('%03b', $string[0]);
			
			$lecturer['highlight'] = ' style="';
			$lecturer['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
			$lecturer['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
			$lecturer['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
			$lecturer['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
			$lecturer['highlight'] .= '"';
		} else {
			$lecturer['highlight'] = '';
		}
		$lecturer['id'] = $lecturer['flecid'];
		
		$lecturers[] = $lecturer;
    }
    $addurl = $isinnerlecurl.$nameurl.$iscollegelecurl.$orgidurl.$includechildurl.$rankurl.$teachdirectionurl.$ischeckurl;
	$getcount = getlecturercount($groupid, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$groupid."&plugin_name=lecturer&plugin_op=groupmenu".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
    
	return array("multipage"=>$multipage,"lecturers"=>$lecturers, "isinnerlec"=>$isinnerlec, "course"=>$course, "name"=>$name, "rank_inner"=>$rank_inner, "rank_outter"=>$rank_outter, "orgid"=>$orgid, "orgname"=>$orgname, "includechild"=>$includechild, "rank_outter"=>$rank_outter, "teachdirection"=>$teachdirection);
}

function view(){
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
    $query = DB::query("SELECT flec.id id, lec.name name, flec.imgurl imgurl, lec.gender gender, lec.orgname orgname, lec.tel tel, lec.email email, lec.isinnerlec isinnerlec, flec.about about, lec.teachdirection teachdirection, lec.rank rank, lec.courses courses,lec.tempusername tempusername FROM ".DB::table("lecturer")." lec, ".DB::table("forum_forum_lecturer")." flec 
    		WHERE lec.id=flec.lecid AND flec.id=".$_GET["lecid"]." AND flec.fid=".$_G["fid"]);
    
    $lecturer = array();
    while ($row = DB::fetch($query)) {
    	$lecturer['id'] = $row['id'];
    	$lecturer['name'] = $row['name'];
    	$lecturer['imgurl'] = $row['imgurl'];
    	$lecturer['gender'] = $row['gender'];
    	$lecturer['orgname'] = $row['orgname'];
    	$lecturer['isinnerlec'] = $row['isinnerlec'];
    	$lecturer['about'] = $row['about'];
    	$lecturer['tel'] = $row['tel'];
    	$lecturer['email'] = $row['email'];
    	$lecturer['teachdirection'] = $row['teachdirection'];
    	$lecturer['rank'] = $row['rank'];
		$lecturer[tempusername]= $row['tempusername'];
    	$lecturer['courses'] = unserialize($row['courses']);
    	$query1 = DB::query("SELECT * FROM ".DB::table("forum_forum_lecturer")." WHERE lecid=".$lecturer["id"]." AND fid=".$_G["fid"]);
    	while ($r = DB::fetch($query1)) {
    		$groupabouts[] = $r;
    	}
    }
     $lectureruid=DB::result_first("select uid from ".DB::TABLE("common_member")." where username='".$lecturer[tempusername]."'");
    require_once libfile('function/home_attachment');
	$attachments = getattachs($lecturer['id'], 'lecid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];
	
	//获得专区信息
	$groupnav = get_groupnav($_G['forum']);
    $query = DB::query("SELECT t.name, tt.icon FROM ".DB::table("forum_forum")." t, ".DB::table("forum_forumfield")." tt 
    		 WHERE t.fid=tt.fid AND t.fid=".$_G['fid']);
    $group = DB::fetch($query);
	$group['icon'] = get_groupimg($group['icon'], 'icon');
	
	include template("lecturer:view");
	dexit();
    
//    return array("attachlist"=>$attachlist, "groupabouts"=>$groupabouts, "lecturer"=>$lecturer);
}

function edit() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
    $query = DB::query("SELECT flec.id id, lec.name name, flec.imgurl imgurl, lec.gender gender, lec.orgname orgname, lec.iscollegelec iscollegelec, lec.isinnerlec isinnerlec, flec.about about, lec.teachdirection teachdirection, lec.rank rank, lec.courses courses FROM ".DB::table("lecturer")." lec, ".DB::table("forum_forum_lecturer")." flec 
    		WHERE lec.id=flec.lecid AND flec.id=".$_GET["lecid"]);
    
    $lecturer = array();
    while ($row = DB::fetch($query)) {
    	$lecturer['id'] = $row['id'];
    	$lecturer['name'] = $row['name'];
    	$lecturer['imgurl'] = $row['imgurl'];
    	$lecturer['gender'] = $row['gender'];
    	$lecturer['orgname'] = $row['orgname'];
    	$lecturer['iscollegelec'] = $row['iscollegelec'];
    	$lecturer['isinnerlec'] = $row['isinnerlec'];
    	$lecturer['about'] = $row['about'];
    	$lecturer['teachdirection'] = $row['teachdirection'];
    	$lecturer['rank'] = $row['rank'];
    	$lecturer['courses'] = unserialize($row['courses']);
    }
    
    //获得专区信息
	$groupnav = get_groupnav($_G['forum']);
    $query = DB::query("SELECT t.name, tt.icon FROM ".DB::table("forum_forum")." t, ".DB::table("forum_forumfield")." tt 
    		 WHERE t.fid=tt.fid AND t.fid=".$_G['fid']);
    $group = DB::fetch($query);
	$group['icon'] = get_groupimg($group['icon'], 'icon');
    
    include template("lecturer:edit");
	dexit();
//    return array("lecturer"=>$lecturer);
    
}

function modify() {
	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_POST["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
//	获取图片url
	if ($_POST["aid"]) {
	    $imgurl = null;
	    
	    $query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
	    $img = DB::fetch($query);
	    $imgurl = ", imgurl='data/attachment/home/".$img["attachment"]."'";
	}
    
    DB::query("UPDATE ".DB::table("forum_forum_lecturer")." SET about='".$_POST["about"]."'".$imgurl." WHERE id=".$_POST["lecid"]." AND fid=".$_G['fid']);
    $params = array (
		'lecid' => $_POST["lecid"],
	);
    showmessage('更新成功', join_plugin_action2('index', $params));
}

function delete() {
	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
    DB::query("DELETE FROM ".DB::table("forum_forum_lecturer")." WHERE id=".$_GET['lecid']." AND fid=".$_G['fid']);
    showmessage('删除成功', join_plugin_action('index'));
}

function getlecturercount($groupid, $addsql) {
	$query = DB::query("SELECT count(*) ".$addsql);
	return DB::result($query, 0);
}

function commend() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    //判断讲师是否存在
    if ($_G["member"]["id"]) {
//    	echo $_G["member"]["id"];
//    	checklecturer($_G["member"]["id"], "index");
    }
}

function commend_outter() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    //判断讲师是否存在
    if ($_G["member"]["id"]) {
//    	checklecturer($_G["member"]["id"], "index");
    }
}

function petition() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    
    if ($_G["member"]["uid"]) {
    	//判断讲师是否存在
//    	checklecturer($_G["member"]["id"], "index");

    	//获取用户信息
    	$userinfo = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid=".$_G["member"]["uid"]);
		$userinfo = DB::fetch($query);
		
    	return array("userinfo"=>$userinfo);
    }
}

//保存申请讲师
function save() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if ($_G["member"]["uid"]) {
    	//判断讲师是否存在
//    	checklecturer($_G["member"]["id"], "index");

    	//获取用户信息
    	$userinfo = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid=".$_G["member"]["uid"]);
		$userinfo = DB::fetch($query);
    }
    
//	获取图片url
    $img = null;
    if ($_POST["aid"]) {
    	$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
    	$img = DB::fetch($query);
    }
    $imgurl = "data/attachment/home/".$img["attachment"];
    //获取所在机构信息
    //$orginfo = function code;
    
    if (!$orginfo) {
    	$orginfo["orgname"] = $userinfo["company"];
    }
    
    DB::insert("lecturer", array("name"=>$userinfo["realname"],//姓名
    		"lecid"=>$userinfo["uid"],//用户id
    		"orgid"=>$orginfo["orgid"],//机构id
			"orgname"=>$orginfo["orgname"],//机构名称
    		"imgurl"=>$imgurl,    //照片
			"teachdirection"=>$_POST["teachdirection"],//授课方向
    		"rank"=>4,//级别
    		"tel"=>$_POST["tel"],//电话
    		"email"=>$_POST["email"],//email
    		"isinnerlec"=>1,//是否学院讲师:1-是,2-否
    		"fid"=>$_G['fid'],//专区id
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
			"uid"=>$_G['uid']));
    
    //自动加入专区
    $membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
	if(!empty($membermaximum)) {
		$curnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]'");
		if($curnum >= $membermaximum) {
			showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
		}
	}
	$groupuser = DB::fetch_first("SELECT * FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
	if($groupuser['uid']) {
//		showmessage('group_has_joined', "forum.php?mod=group&fid=$_G[fid]");
	} else {
		$modmember = 4;
		$showmessage = 'group_join_succeed';
		$confirmjoin = TRUE;
		$inviteuid = DB::result_first("SELECT uid FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
		
		if($_G['forum']['jointype'] == 3) {
			if(!$inviteuid) {
				$confirmjoin = FALSE;
				$showmessage = 'group_join_forbid_anybody';
			}
		}
		if($_G['forum']['jointype'] == 1) {
			if(!$inviteuid) {
				$confirmjoin = FALSE;
				$showmessage = 'group_join_need_invite';
			}
		} elseif($_G['forum']['jointype'] == 2) {
			$modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
			!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
		}

		if($confirmjoin) {
			DB::query("INSERT INTO ".DB::table('forum_groupuser')." (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$_G[uid]', '$_G[username]', '$modmember', '".TIMESTAMP."', '".TIMESTAMP."')", 'UNBUFFERED');
			if($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
				foreach($groupmanagers as $manage) {
					notification_add($manage['uid'], 'group', 'group_member_join', array('url' => $_G['siteurl'].'forum.php?mod=group&action=manage&op=checkuser&fid='.$_G['fid']), 1);
				}
			} else {
				update_usergroups($_G['uid']);
			}
			if($inviteuid) {
				DB::query("DELETE FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
			}
			if($modmember == 4) {
				DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+1 WHERE fid='$_G[fid]'");
			}
			updateactivity($_G['fid'], 0);
		}
		include_once libfile('function/stat');
		updatestat('groupjoin');
		delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
	}
	//发送动态
	
	showmessage('申请成功。', 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=lecturer&plugin_op=groupmenu&lecturer_action=petition');
}

//保存推荐讲师-内部
function savecommend() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if (!$_POST["evaluation1"] && !$_POST["evaluation2"]) {
    	showmessage('评价信息不完整，请重新填写。', join_plugin_action('commend'));
    }
	//获取图片url
    $img = null;
    if ($_POST["aid"]) {
    	$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
    	$img = DB::fetch($query);
    }
    
    //判断讲师是否存在
    checklecturer($_POST["firstman_names_uids"], "commend");
    
    $imgurl = "data/attachment/home/".$img["attachment"];
    
    //获取所在机构信息
    //$orginfo = function code;
    
    DB::insert("lecturer", array("name"=>$_POST["firstman_names"],//姓名
    		"lecid"=>$_POST["firstman_names_uids"],//用户id
    		"orgid"=>$orginfo["orgid"],//机构id
			"orgname"=>$orginfo["orgname"],//机构名称
    		"imgurl"=>$imgurl,    //照片
			"teachdirection"=>$_POST["teachdirection"],//授课方向
    		"rank"=>4,//级别
    		"tel"=>$_POST["tel"],//电话
    		"email"=>$_POST["email"],//email
    		"isinnerlec"=>2,//是否学院讲师:1-是,2-否
    		"fid"=>$_G['fid'],//专区id
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
			"uid"=>$_G['uid']));
    $lecturerid = DB::insert_id();
    
//自动加入专区
	$uid = $_POST["firstman_names_uids"];
	$userinfo = getmember($uid);
    $membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
	if(!empty($membermaximum)) {
		$curnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]'");
		if($curnum >= $membermaximum) {
			showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
		}
	}
	$groupuser = DB::fetch_first("SELECT * FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$uid'");
	if($groupuser['uid']) {
//		showmessage('group_has_joined', "forum.php?mod=group&fid=$_G[fid]");
	} else {
		$modmember = 4;
		$showmessage = 'group_join_succeed';
		$confirmjoin = TRUE;
		$inviteuid = DB::result_first("SELECT uid FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$uid'");
		
		if($_G['forum']['jointype'] == 3) {
			if(!$inviteuid) {
				$confirmjoin = FALSE;
				$showmessage = 'group_join_forbid_anybody';
			}
		}
		if($_G['forum']['jointype'] == 1) {
			if(!$inviteuid) {
				$confirmjoin = FALSE;
				$showmessage = 'group_join_need_invite';
			}
		} elseif($_G['forum']['jointype'] == 2) {
			$modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
			!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
		}

		if($confirmjoin) {
			DB::query("INSERT INTO ".DB::table('forum_groupuser')." (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$uid', '$userinfo[username]', '$modmember', '".TIMESTAMP."', '".TIMESTAMP."')", 'UNBUFFERED');
			if($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
				foreach($groupmanagers as $manage) {
					notification_add($manage['uid'], 'group', 'group_member_join', array('url' => $_G['siteurl'].'forum.php?mod=group&action=manage&op=checkuser&fid='.$_G['fid']), 1);
				}
			} else {
				update_usergroups($uid);
			}
			if($inviteuid) {
				DB::query("DELETE FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$uid'");
			}
			if($modmember == 4) {
				DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+1 WHERE fid='$_G[fid]'");
			}
			updateactivity($_G['fid'], 0);
		}
		include_once libfile('function/stat');
		updatestat('groupjoin');
		delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
	}
    
    //被推荐通知
    //获取推荐人信息
    $usr = getuser($_G['uid']);
    if (!$usr['realname']) {
    	$usr['realname'] = $_G['member']['username'];
    }
    notification_add($_POST["firstman_names_uids"], 'zq_teacher', '[讲师]您被“{username}“推荐为内部培训师，快去看看吧', array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$usr['realname'].'</a>'), 1);
    
    //评价内容
    $message1 = $_POST["evaluation1"];
    $message2 = $_POST["evaluation2"];
    $message = "参加了 ".$message1." 评论道：<br/>".$message2;
    
    DB::insert("home_comment", array("uid"=>$_G['uid'],
    		"id"=>$lecturerid,
    		"idtype"=>"lecturerid",
			"authorid"=>$_G['uid'],
    		"author"=>$_G['member']['username'],    //照片
			"ip"=>$_G['clientip'],//获取IP
    		"dateline"=>time(),
    		"message"=>$message,
			"uid"=>$_G['uid']));
    //被评价通知
    notification_add($_POST["firstman_names_uids"], 'zq_teacher', '[讲师]“{username}”对您进行了讲师评价，赶快去看看吧', array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$usr['realname'].'</a>'), 1);
    
	showmessage('推荐成功。', 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=lecturer&plugin_op=groupmenu&lecturer_action=commend');
}

//保存推荐讲师-外部
function savecommend_outter() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if (!$_POST["evaluation1"] && !$_POST["evaluation2"]) {
    	showmessage('评价信息不完整，请重新填写。', join_plugin_action('commend'));
    }
	//获取图片url
    $img = null;
    if ($_POST["aid"]) {
    	$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
    	$img = DB::fetch($query);
    }
    $imgurl = "data/attachment/home/".$img["attachment"];
    
    DB::insert("lecturer", array("name"=>$_POST["firstman_names"],//姓名
			"orgname"=>$_POST["orgname"],//机构名称
    		"gender"=>$_POST["gender"],//性别
    		"imgurl"=>$imgurl,    //照片
    		"rank"=>$_POST["rank"],//级别
    		"tel"=>$_POST["tel"],//电话
    		"email"=>$_POST["email"],//email
    		"isinnerlec"=>2,//是否学院讲师:1-是,2-否
    		"fid"=>$_G['fid'],//专区id
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
			"uid"=>$_G['uid']));
    $lecturerid = DB::insert_id();
    
    //评价内容
    $message1 = $_POST["evaluation1"];
    $message2 = $_POST["evaluation2"];
    $message = "参加了 ".$message1." 评论道：<br/>".$message2;
    
    DB::insert("home_comment", array("uid"=>$_G['uid'],
    		"id"=>$lecturerid,
    		"idtype"=>"lecturerid",
			"authorid"=>$_G['uid'],
    		"author"=>$_G['member']['username'],    //照片
			"ip"=>$_G['clientip'],//获取IP
    		"dateline"=>time(),
    		"message"=>$message,
			"uid"=>$_G['uid']));
	
	showmessage('推荐成功。', 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=lecturer&plugin_op=groupmenu&lecturer_action=commend_outter');
}

function checklecturer($uid, $return_action) {
	if(DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('lecturer')." WHERE lecid=".$uid), 0)) {
        showmessage('讲师已经存在', join_plugin_action($return_action));
	}
}
?>
