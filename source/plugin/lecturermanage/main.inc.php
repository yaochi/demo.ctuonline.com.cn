<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

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

    if ($img["attachment"]) {
    	$imgurl = "data/attachment/home/".$img["attachment"];
    }

    require_once libfile('function/org');
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    $userinfo = getmember($_POST["firstman_names_uids"]);
	$org=get_org_by_user($userinfo["username"]);
	$orgid=$org[id];
	$orgname =$org[name];
	$orgname_all=getAllOrgName($orgid);
	$gender=getGender($userinfo["username"]);

    //获取所在机构信息
    //$orginfo = function code;

    DB::insert("lecturer", array("name"=>$_POST["firstman_names"],//姓名
    		"lecid"=>$_POST["firstman_names_uids"],//用户id
    		"tempusername"=>$userinfo["username"],
    		"orgid"=>$orgid,//机构id
			"orgname"=>$orgname,//机构名称
    		"orgname_all"=>$orgname_all,
    		"gender"=>$gender,
    		"imgurl"=>$imgurl,    //照片
    		"trainingtrait"=>$_POST["trainingtrait"],
			"trainingexperience"=>$_POST["trainingexperience"],
			"teachdirection"=>$_POST["teachdirection"],//授课方向
    		"rank"=>5,//级别
    		"rank_name"=>'其他',
    		"tel"=>$_POST["tel"],//电话
    		"email"=>$_POST["email"],//email
    		"isinnerlec"=>1,//是否内部讲师:1-是,2-否
    		"fid"=>$_G['fid'],//专区id
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
    		"updateline"=>time(),
			"uid"=>$_G['uid']));

    	$lecturerid = DB::insert_id();
		$courses=$_POST["addedcourses"];
    	$courseids_arr = explode(",",$courses);
    	$courseid_arr=array_unique($courseids_arr);

    	$traincourse[lecid]=$lecturerid;
    	$traincourse[source]=1;
    	$traincourse[isgroup]=0;
    	$traincourse[belong]=0;
    	$traincourse[power]=5;
    	$traincourse[update_time]=time();
    	foreach($courseid_arr as $id)
    	{
    		$elementname="addcourseName_".$id;
    		$traincourse[coursename]=$_POST[$elementname];
    		op_course('insert',$traincourse,1,$lecturerid);
   		}

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
		group_add_user_to_group($uid, $userinfo[username], $_G[fid]);
//		$modmember = 4;
//		$showmessage = 'group_join_succeed';
//		$confirmjoin = TRUE;
//		$inviteuid = DB::result_first("SELECT uid FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$uid'");
//
//		if($_G['forum']['jointype'] == 3) {
//			if(!$inviteuid) {
//				$confirmjoin = FALSE;
//				$showmessage = 'group_join_forbid_anybody';
//			}
//		}
//		if($_G['forum']['jointype'] == 1) {
//			if(!$inviteuid) {
//				$confirmjoin = FALSE;
//				$showmessage = 'group_join_need_invite';
//			}
//		} elseif($_G['forum']['jointype'] == 2) {
//			$modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
//			!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
//		}
//
//		if($confirmjoin) {
//			DB::query("INSERT INTO ".DB::table('forum_groupuser')." (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$uid', '$userinfo[username]', '$modmember', '".TIMESTAMP."', '".TIMESTAMP."')", 'UNBUFFERED');
//			if($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
//				foreach($groupmanagers as $manage) {
//					notification_add($manage['uid'], 'group', 'group_member_join', array('url' => $_G['siteurl'].'forum.php?mod=group&action=manage&op=checkuser&fid='.$_G['fid']), 1);
//				}
//			} else {
//				update_usergroups($uid);
//			}
//			if($inviteuid) {
//				DB::query("DELETE FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$uid'");
//			}
//			if($modmember == 4) {
//				DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+1 WHERE fid='$_G[fid]'");
//			}
//			updateactivity($_G['fid'], 0);
//		}
//		include_once libfile('function/stat');
//		updatestat('groupjoin');
//		delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
	}

    //被推荐通知
    //获取推荐人信息
    $usr = getuser($_G['uid']);
    if (!$usr['realname']) {
    	$usr['realname'] = $_G['member']['username'];
    }
    //notification_add($_POST["firstman_names_uids"], 'zq_teacher', '[讲师]您被“{username}“推荐为内部培训师，快去看看吧', array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$usr['realname'].'</a>'), 1);

    //评价内容
    //$message1 = $_POST["evaluation1"];
    $message2 = $_POST["evaluation2"];
    //$message = "参加了 ".$message1." 评论道：<br/>".$message2;

    DB::insert("home_comment", array("uid"=>$_G['uid'],
    		"id"=>$lecturerid,
    		"idtype"=>"lecturerid",
			"authorid"=>$_G['uid'],
    		"author"=>$_G['member']['username'],    //照片
			"ip"=>$_G['clientip'],//获取IP
    		"dateline"=>time(),
    		"message"=>$message2,
			"uid"=>$_G['uid']));
    $commentid = DB::insert_id();


    //推荐经验值
    group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'teacher_commend');

    //评价经验值
    //group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'teacher_comment');


    //被评价通知
    //notification_add($_POST["firstman_names_uids"], 'zq_teacher', '[讲师]“{username}”对您进行了讲师评价，赶快去看看吧', array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$usr['realname'].'</a>'), 1);

	showmessage('推荐成功。', join_plugin_action("commend"));
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

	if ($img["attachment"]) {
    	$imgurl = "data/attachment/home/".$img["attachment"];
    }
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    DB::insert("lecturer", array("name"=>$_POST["firstman_names"],//姓名
			"orgname"=>$_POST["orgname"],//机构名称
    		"orgname_all"=>$_POST["orgname"],
    		"gender"=>$_POST["gender"],//性别
    		"imgurl"=>$imgurl,    //照片
    		"rank"=>$_POST["rank"],//级别
			"trainingexperience"=>$_POST["trainingexperience"],
			"trainingtrait"=>$_POST["trainingtrait"],
		  	"rank_name"=>getOutterRankname($_POST["rank"]),
    		"tel"=>$_POST["tel"],//电话
    		"email"=>$_POST["email"],//email
    		"isinnerlec"=>2,//是否内部讲师:1-是,2-否
    		"fid"=>$_G['fid'],//专区id
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
    		"updateline"=>time(),
			"uid"=>$_G['uid']));
    $lecturerid = DB::insert_id();

		$courses=$_POST["addedcourses"];
    	$courseids_arr = explode(",",$courses);
    	$courseid_arr=array_unique($courseids_arr);

    	$traincourse[lecid]=$lecturerid;
    	$traincourse[source]=1;
    	$traincourse[isgroup]=0;
    	$traincourse[belong]=0;
    	$traincourse[power]=5;
    	$traincourse[update_time]=time();
    	foreach($courseid_arr as $id)
    	{
    		$elementname="addcourseName_".$id;
    		$traincourse[coursename]=$_POST[$elementname];
    		op_course('insert',$traincourse,1,$lecturerid);
   		}

    //评价内容
    //$message1 = $_POST["evaluation1"];
    $message2 = $_POST["evaluation2"];
    //$message = "参加了 ".$message1." 评论道：<br/>".$message2;

    DB::insert("home_comment", array("uid"=>$_G['uid'],
    		"id"=>$lecturerid,
    		"idtype"=>"lecturerid",
			"authorid"=>$_G['uid'],
    		"author"=>$_G['member']['username'],    //照片
			"ip"=>$_G['clientip'],//获取IP
    		"dateline"=>time(),
    		"message"=>$message2,
			"uid"=>$_G['uid']));

    //推荐经验值
    group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'teacher_commend');

    //评价经验值
    //group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'teacher_comment');
    $url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid=".$lecturerid;
	showmessage('推荐成功。', $url);
}

function save() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if ($_G["member"]["uid"]) {
    	//判断讲师是否存在
    	checklecturer($_G["member"]["uid"], "index");

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

    DB::insert("lecturer", array("name"=>$userinfo["realname"],//姓名
    		"lecid"=>$_G["uid"],//用户id
    		"tempusername"=>$_G["username"],
    		"orgid"=>$_POST["orgid"],//机构id
			"orgname"=>$_POST["orgname"],//机构名称
    		"orgname_all"=>$_POST["orgname_all"],//机构名称
    		"gender"=>$_POST["gender"],
    		"imgurl"=>$imgurl,    //照片
			"teachdirection"=>$_POST["teachdirection"],//授课方向
    		"bginfo"=>$_POST["bginfo"],
			"trainingtrait"=>$_POST["trainingtrait"],
			"trainingexperience"=>$_POST["trainingexperience"],
    		"rank"=>5,//级别
    		"rank_name"=>'其他',
    		"tel"=>$_POST["tel"],//电话
    		"email"=>$_POST["email"],//email
    		"isinnerlec"=>1,//是否内部讲师:1-是,2-否
    		"fid"=>$_G['fid'],//专区id
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
    		"updateline"=>time(),
			"uid"=>$_G['uid']));

    	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    	$lecturerid = DB::insert_id();
		$courses=$_POST["addedcourses"];
    	$courseids_arr = explode(",",$courses);
    	$courseid_arr=array_unique($courseids_arr);

    	$traincourse[lecid]=$lecturerid;
    	$traincourse[belong]=0;
    	$traincourse[power]=5;
    	$traincourse[source]=1;
    	$traincourse[isgroup]=0;
    	$traincourse[update_time]=time();
    	foreach($courseid_arr as $id)
    	{
    		$elementname="addcourseName_".$id;
    		$coursename=$_POST[$elementname];
    		$traincourse[coursename]=$coursename;
    		op_course('insert',$traincourse,1,$lecturerid);
   		}

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
		group_add_user_to_group($_G['uid'], $userinfo[username], $_G[fid]);
//		$modmember = 4;
//		$showmessage = 'group_join_succeed';
//		$confirmjoin = TRUE;
//		$inviteuid = DB::result_first("SELECT uid FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
//
//		if($_G['forum']['jointype'] == 3) {
//			if(!$inviteuid) {
//				$confirmjoin = FALSE;
//				$showmessage = 'group_join_forbid_anybody';
//			}
//		}
//		if($_G['forum']['jointype'] == 1) {
//			if(!$inviteuid) {
//				$confirmjoin = FALSE;
//				$showmessage = 'group_join_need_invite';
//			}
//		} elseif($_G['forum']['jointype'] == 2) {
//			$modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
//			!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
//		}
//
//		if($confirmjoin) {
//			DB::query("INSERT INTO ".DB::table('forum_groupuser')." (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$_G[uid]', '$_G[username]', '$modmember', '".TIMESTAMP."', '".TIMESTAMP."')", 'UNBUFFERED');
//			if($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
//				foreach($groupmanagers as $manage) {
//					notification_add($manage['uid'], 'group', 'group_member_join', array('url' => $_G['siteurl'].'forum.php?mod=group&action=manage&op=checkuser&fid='.$_G['fid']), 1);
//				}
//			} else {
//				update_usergroups($_G['uid']);
//			}
//			if($inviteuid) {
//				DB::query("DELETE FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
//			}
//			if($modmember == 4) {
//				DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+1 WHERE fid='$_G[fid]'");
//			}
//			updateactivity($_G['fid'], 0);
//		}
//		include_once libfile('function/stat');
//		updatestat('groupjoin');
//		delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
	}
	//经验值
	group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'teacher_add_record');

	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid=".$lecturerid;
	showmessage('申请成功。',$url);
}

function petition() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }


    if ($_G["member"]["uid"]) {
    	//判断讲师是否存在
    	checklecturer($_G["member"]["uid"], "index");

    	//获取用户信息
    	$userinfo = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid=".$_G["member"]["uid"]);
		$userinfo = DB::fetch($query);
		require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		require_once libfile('function/org');
		$org=get_org_by_user($_G["username"]);
		$orgid=$org[id];
		$orgname =$org[name];
		$org[orgname_all]=getAllOrgName($orgid);
    	return array("userinfo"=>$userinfo,"org"=>$org);
    }
}

function index(){
	$groupid = $_GET["fid"];
	$addsql = "";
	$addurl = "";
	$change = $_POST["change"] ? $_POST["change"] : 1;
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
		$isinnerlecsql = " WHERE lec.isinnerlec=".$isinnerlec;
		$isinnerlecurl = "&isinnerlec=".$isinnerlec;
		//级别
		if ($rank_inner) {
			$ranksql = " AND lec.rank_name like'%".$rank_inner.trim()."%'";
			$rankurl = "&rank_inner=".$rank_inner.trim();
		}
	} elseif ($isinnerlec==2) {
		$isinnerlecsql = " WHERE lec.isinnerlec=".$isinnerlec;
		$isinnerlecurl = "&isinnerlec=".$isinnerlec;
		//级别
		if ($rank_outter) {
			$ranksql = " AND lec.rank=".$rank_outter;
			$rankurl = "&rank_outter=".$rank_outter;
		}
	} elseif (!$isinnerlec) {
		//$isinnerlecsql = " WHERE lec.isinnerlec=1";
		$isinnerlecsql = " WHERE 1";
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
	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	//课程
	if ($course) {
	    $coursesql = " AND tc.coursename like '%".$course.trim()."%' ";
	    $idsql=" AND tc.lecid=lec.id";
		$courseurl = "&course=".$course;
		$query=DB::query("SELECT distinct lec.* FROM ".DB::table("lecturer")." lec,".DB::table("train_course")." tc".$isinnerlecsql.$namesql.$orgidsql.$ranksql.$teachdirectionsql.$coursesql.$idsql." ORDER BY lec.id DESC LIMIT $start,$perpage ");
	    $addsql=" FROM ".DB::table("lecturer")." lec,".DB::table("train_course")." tc".$isinnerlecsql.$namesql.$orgidsql.$ranksql.$teachdirectionsql.$coursesql.$idsql;
	}else{
		$query = DB::query("SELECT * FROM ".DB::table("lecturer")." lec".$isinnerlecsql.$namesql.$orgidsql.$ranksql.$teachdirectionsql." ORDER BY id DESC LIMIT $start,$perpage ");
	    $addsql=" FROM ".DB::table("lecturer")." lec".$isinnerlecsql.$namesql.$orgidsql.$ranksql.$teachdirectionsql;
	}

    if($lecturers) $lecturers = array();
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    while ($lecturer = DB::fetch($query)) {
    	if($lecturer[isinnerlec]==1)
			$rank=changeRankName($lecturer['rank'],$lecturer['province_rank']);
		else
			$rank=getOutterRankname($lecturer['rank']);
		$lecturer['rank']=$rank;
		$lecturers[] = $lecturer;

    }
    $addurl = $isinnerlecurl.$nameurl.$orgidurl.$includechildurl.$rankurl.$teachdirectionurl;
	$getcount = getlecturercounts($addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$groupid."&plugin_name=lecturermanage&plugin_op=groupmenu".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
    return array("multipage"=>$multipage,"lecturers"=>$lecturers, "isinnerlec"=>$isinnerlec, "course"=>$course, "name"=>$name, "rank_inner"=>$rank_inner, "rank_outter"=>$rank_outter, "orgid"=>$orgid, "orgname"=>$orgname, "includechild"=>$includechild, "rank_outter"=>$rank_outter, "teachdirection"=>$teachdirection,"change"=>$change);
}

function view() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }

    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }

    $query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE id=".$_GET["lecid"]);
    $lecturer = array();
    $groupabouts = array();
    while ($row = DB::fetch($query)) {
    	$lecturer = $row;
    	$query1 = DB::query("SELECT * FROM ".DB::table("forum_forum_lecturer")." WHERE lecid=".$lecturer["id"]);
    	while ($r = DB::fetch($query1)) {
    		$groupabouts[] = $r;
    	}
    }

//评论
	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)
		$page = 1;

	require_once libfile('function/home');
	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$cid = empty ($_GET['cid']) ? 0 : intval($_GET['cid']);
	$csql = $cid ? "cid='$cid' AND" : '';
	$commentlist = array ();
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$lecturer[id]' AND idtype='lecturerid'"), 0);
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$lecturer[id]' AND idtype='lecturerid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$commentlist[] = $value;
		}
	}

	require_once libfile('function/home_attachment');
	$attachments = getattachs($lecturer['id'], 'lecid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];

    return array("attachlist"=>$attachlist, "lecturer"=>$lecturer, "commentreplynum"=>$count, "groupabouts"=>$groupabouts, "_G"=>$_G, "commentlist"=>$commentlist);
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

$query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE id=".$_GET["lecid"]);

    $lecturer = array();
    $groupabouts = array();
    while ($row = DB::fetch($query)) {
    	$lecturer = $row;
    	$query1 = DB::query("SELECT * FROM ".DB::table("forum_forum_lecturer")." WHERE lecid=".$lecturer["id"]);
    	while ($r = DB::fetch($query1)) {
    		$groupabouts[] = $r;
    	}
    }

    require_once libfile('function/home_attachment');
	$attachlist = getattach($lecturer['id'],'lecid');
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];

    return array("attachlist"=>$attachlist, "attachs"=>$attachs, "imgattachs"=>$imgattachs, "lecturer"=>$lecturer, "groupabouts"=>$groupabouts);

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

    if ($_POST["bginfo"]) {
    	$bginfo = ", bginfo='".$_POST["bginfo"]."'";
    }
	if ($_POST["teachdirection"]) {
    	$teachdirection = ", teachdirection=".$_POST["teachdirection"];
    }
	if ($_POST["rank"]) {
    	$courses = ", rank='".$_POST["rank"]."'";
    }
	if ($_POST["courses"]) {
    	$rank = ", courses='".$_POST["courses"]."'";
    }
	if ($_POST["tel"]) {
    	$tel = ", tel='".$_POST["tel"]."'";
    }
	if ($_POST["email"]) {
    	$email = ", email='".$_POST["email"]."'";
    }

    require_once libfile('function/home_attachment');

    updateattach('', $_POST['lecid'], 'lecid', $_G['gp_attachnew'], $_G['gp_attachdel']);

    DB::query("UPDATE ".DB::table("lecturer")." SET ".$imgurl.$rank.$bginfo.$teachdirection.$courses.$tel.$email." WHERE id=".$_POST['lecid']);

    showmessage('更新成功', join_plugin_action('index'));
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

    $type=$_GET['type'];
    $lecid=$_GET['lecid'];
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    $query = DB::query("SELECT * FROM ".DB::table('lecturer')." WHERE id=".$lecid);
	$lecturer = DB::fetch($query);

	if($lecturer[isinnerlec]==1)
	{
		if($type==1&&$lecturer[province_rank])
		{
			$rank_name=changeRankName('',$lecturer[province_rank]);
			DB::query("UPDATE ".DB::table("lecturer")." SET rank='',rank_name='".$rank_name."'  WHERE id=".$lecid);
			DB::query("DELETE FROM ".DB::table("train_course")." WHERE lecid=".$lecid." AND belong=1");
		}
    	else if($type==2&&$lecturer[rank])
    	{
    		$rank_name=changeRankName($lecturer[rank],'');
    		DB::query("UPDATE ".DB::table("lecturer")." SET province_rank='',rank_name='".$rank_name."'  WHERE id=".$lecid);
    		DB::query("DELETE FROM ".DB::table("train_course")." WHERE lecid=".$lecid." AND belong=2");
    	}
		else
		{
    		DB::query("DELETE FROM ".DB::table("lecturer")." WHERE id=".$lecid);
   		 	DB::query("DELETE FROM ".DB::table("train_course")." WHERE lecid=".$lecid);
		}
	}
	else
	{
    DB::query("DELETE FROM ".DB::table("lecturer")." WHERE id=".$lecid);
    DB::query("DELETE FROM ".DB::table("train_course")." WHERE lecid=".$lecid);
	}
    showmessage('删除成功', join_plugin_action('index'));
}

function getlecturercounts($addsql) {
	$query = DB::query("SELECT count(distinct lec.id)" .$addsql);
	return DB::result($query, 0);
}

function getlecturercount($groupid, $addsql) {
	$query = DB::query("SELECT count(*) FROM ".DB::table('lecturer').$addsql);
	return DB::result($query, 0);
}

function checklecturer($uid, $return_action) {
	if(DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('lecturer')." WHERE lecid=".$uid), 0)) {
        showmessage('讲师已经存在', join_plugin_action($return_action));
	}
}

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

function getmember_by_username($username) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE username='".$username."'");
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function checklecturerstatus() {
	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }

   /* DB::query("UPDATE ".DB::table("lecturer")." SET ischeck=".$_GET["ischeck"]." WHERE id=".$_GET["lecid"]);
    if ($_GET["ischeck"]==1) {
    	showmessage('审核成功', join_plugin_action("index"));
    } elseif ($_GET["ischeck"]==2) {
    	showmessage('取消审核成功', join_plugin_action("index"));
    }*/
}

function importapprove() {
	global $_G;
	if ($_FILES['approvefile']['name']) {
		$handle=fopen($_FILES['approvefile']['tmp_name'],"r");
		while($data=fgetcsv($handle,10000,",")){
			if (!$row) {
				$row = $data;
				continue;
			}
			$code = approve_by_username($data);
			if (!$code) {
				$errorlist[] = $data;
			}
		}
		fclose($handle);

	}

	return array("errorlist"=>$errorlist);
}

/*function approve_by_username($data) {
	if (count($data)!=2) {
		return false;
	} else {
		$userinfo = getmember_by_username($data[0]);
		if ($userinfo) {
			DB::query("UPDATE ".DB::table("lecturer")." SET isapprove=1, rank=".$data[1]." WHERE lecid=".$userinfo[uid]);
			$row = DB::affected_rows();
			if (!row) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}*/

?>
