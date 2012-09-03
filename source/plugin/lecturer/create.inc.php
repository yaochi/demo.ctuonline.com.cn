<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

if(!$_G["gp_select"]){
    $_G["gp_select"] = "index";
}
$navs = array(
    array(url=>join_plugin_action2('index', array(select=>"index")), name=>"创建讲师", select=>"index"),
    array(url=>join_plugin_action2('search', array(select=>"search", lecturer_action=>"search", ac=>"search")), name=>"讲师库选择", select=>"search"),
);
function index(){
	global  $_G;
	$fup = $_GET["fid"];

	return array("_G"=>$_G);
}

function index_outter(){
	global  $_G;
	$fup = $_GET["fid"];

	return array("_G"=>$_G);
}

function search(){
	global  $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("search", array("ac"=>'search')));
	}
	if ($_GET["ac"]==search) {
		return;
	}
	
/*	if ($_POST[name]) {
		$name = $_POST[name];
		$iscollegelec = $_POST[iscollegelec];
		$orgname = $_POST[orgname_input];
		$orgid = $_POST[orgname_input_id];
		$includechild = $_POST[includechild];
		$rank = $_POST[rank];
		$coursexperience = $_POST[coursexperience];
		$teachdirection = $_POST[teachdirection];
	} elseif ($_GET[name]) {
		$name = $_GET[name];
		$iscollegelec = $_GET[iscollegelec];
		$orgname = $_GET[orgname_input];
		$orgid = $_GET[orgname_input_id];
		$includechild = $_GET[includechild];
		$rank = $_GET[rank];
		$coursexperience = $_GET[coursexperience];
		$teachdirection = $_GET[teachdirection];
	}*/
	$name = $_REQUEST[name];
	$iscollegelec = $_REQUEST[iscollegelec];
	$orgname = $_REQUEST[orgname_input];
	$orgid = $_REQUEST[orgname_input_id];
	$includechild = $_REQUEST[includechild];
	$rank = $_REQUEST[rank];
	$coursexperience = $_REQUEST[coursexperience];
	$teachdirection = $_REQUEST[teachdirection];
	//机构
	if ($orgid) {
		$orgidurl = "&orgname_input_id=".$orgid;
		if ($includechild) {
			$includechildurl = "&includechild=".$includechild;
			//获取子机构
			require_once libfile('function/org');
	    	$orgidlist = getSubOrg($orgid);
	    	$orgids = implode(",", $orgidlist);
	    	if ($orgids) {
	    		$orgidssql = " AND orgid in(".$orgids.",".$orgid.")";
	    	} else {
	    		$orgidssql = " AND orgid=".$orgid;
	    	}
		} else {
			$orgidssql = " AND orgid=".$orgid;
		}
	}
	if ($orgname) {
		$orgnameurl = "&orgname_input=".$orgname;
	}
	//是否学院讲师
	if ($iscollegelec) {
		$iscollegelecsql = " AND iscollegelec=1";
		$iscollegelecurl = "&iscollegelec=".$iscollegelec;
	} else {
		$iscollegelecsql = "";
	}
	//讲师级别
	if ($rank) {
		$ranksql = " AND rank=".$rank;
		$rankurl = "&rank=".$rank;
	} else {
		$ranksql = "";
	}
	
	//授课方向
	if ($teachdirection) {
		$teachdirectionsql = " AND teachdirection=".$teachdirection;
		$teachdirectionurl = "&teachdirection=".$teachdirection;
	} else {
		$teachdirectionsql = "";
	}
	//modify by yangyang start
	/*//授课经验
	if ($coursexperience) {
		$coursexperiencesql = " AND coursexperience like '%".$coursexperience."%'";
		$coursexperienceurl = "&coursexperienceurl".$coursexperience;
	} else {
		$coursexperiencesql = "";
	}*/
	//主要培训课程
	if ($courses) {
		$coursessql = " AND courses like '%".$courses."%'";
		$coursesurl = "&coursesurl".$courses;
	} else {
		$coursessql = "";
	}
	//end
	$namesql = " AND name like '%".$name."%'";
	$nameurl = "&name=".$name;
	
	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;

	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." lec WHERE isinnerlec=1 ".$namesql.$orgidssql.$iscollegelecsql.$ranksql.$teachdirectionsql.$coursessql." LIMIT $start,$perpage");

	while($row = DB::fetch($query)){
		//过滤专区讲师
		if (DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('forum_forum_lecturer')." WHERE lecid=".$row["id"]." AND fid=".$_G['fid']), 0)) {
			continue;
		}
		$lecturers[] = $row;
	}
	
	$addsql = $namesql.$iscollegelecsql.$orgidsql.$ranksql.$coursessql.$teachdirectionsql;
	$addurl = $nameurl.$iscollegelecurl.$orgnameurl.$orgidurl.$includechildurl.$rankurl.$coursesurl.$teachdirectionurl;
	$getcount = getlecturercount(1, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=lecturer&plugin_op=createmenu&lecturer_action=search".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}

	return array("select"=>"search", "multipage"=>$multipage, "_G"=>$_G, "lecturers"=>$lecturers, "total"=>$getcount, "name"=>$name, "firstman_names_uids"=>"", "orgname_input"=>$orgname, "orgname_input_id"=>$orgid, "includechild"=>$includechild, "coursexperience"=>$coursexperience, "rank"=>$rank, "teachdirection"=>$teachdirection, "iscollegelec"=>$iscollegelec);
}

function search_outter(){
	global  $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("search_outter", array("ac"=>'search_out')));
	}
	if ($_GET["ac"]=='search_out') {
		return;
	}
	
	if ($_POST[name]) {
		$name = $_POST[name];
		$rank = $_POST[rank];
		$teachdirection = $_POST[teachdirection];
	} elseif ($_GET[name]) {
		$name = $_GET[name];
		$rank = $_GET[rank];
		$teachdirection = $_GET[teachdirection];
	}

	//讲师级别
	if ($rank) {
		$ranksql = " AND rank=".$rank;
		$rankurl = "&rank=".$rank;
	} else {
		$ranksql = "";
	}
	//授课方向
	if ($teachdirection) {
		$teachdirectionsql = " AND teachdirection=".$teachdirection;
		$teachdirectionurl = "&teachdirection=".$teachdirection;
	} else {
		$teachdirectionsql = "";
	}
	
	$namesql = " AND name like '%".$name."%'";
	$nameurl = "&name=".$name;
	
	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
	$sql = "SELECT * FROM ".DB::table("lecturer")." lec WHERE isinnerlec=2 ".$namesql.$ranksql.$teachdirectionsql." LIMIT $start,$perpage";	
	$query = DB::query($sql);
	$lecturers = array();
	while($row = DB::fetch($query)){
		//过滤专区讲师
		if (DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('forum_forum_lecturer')." WHERE lecid=".$row["id"]." AND fid=".$_G['fid']), 0)) {
			continue;
		}
		$lecturers[] = $row;
	}
	
	$addsql = $namesql.$ranksql.$teachdirectionsql;
	$addurl = $nameurl.$rankurl.$teachdirectionurl;
	$getcount = getlecturercount(2, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=lecturer&plugin_op=createmenu&lecturer_action=search_outter".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}

	return array("multipage"=>$multipage, "_G"=>$_G, "lecturers"=>$lecturers, "total"=>$getcount, "name"=>$name, "rank"=>$rank, "teachdirection"=>$teachdirection);
}

function save() {
	global $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("index"));
	}

	//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}

	//判断讲师是否存在
	checklecturer($_POST["firstman_names_uids"], "index");
	
	//获取机构岗位信息
	require_once libfile('function/org');
	$uid = $_POST["firstman_names_uids"];
	$userinfo = getmember($uid);
	$orgname = getOrgNameByUser($userinfo["username"]);
	$station = org_get_stattion($_POST["firstman_names_uids"]); 
	$station = implode(",", $station);
	
	//无图片则为空
	if ($img["attachment"]) {
		$imgurl = "data/attachment/home/".$img["attachment"];
	} else {
		$imgurl = "";
	}

	DB::insert("lecturer", array("name"=>$_POST["firstman_names"],
    		"lecid"=>$_POST["firstman_names_uids"],
			"gender"=>$_POST["gender"],
			"orgname"=>$orgname,
			"position"=>$station,
			"bginfo"=>$_POST["about"],
    		"imgurl"=>$imgurl,    
			"teachdirection"=>$_POST["teachdirection"],
    		"rank"=>4,
    		"tel"=>$_POST["tel"],
    		"email"=>$_POST["email"],
    		"isinnerlec"=>1,
			"courses"=>$_POST["courses"],
			"trainingexperience"=>$_POST["trainingexperience"],
    		"trainingtrait"=>$_POST["trainingtrait"],
			"fid"=>$_G['fid'],
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
			"uid"=>$_G['uid']));

	$lecturerid = DB::insert_id();

	DB::insert("forum_forum_lecturer", array("lecid"=>$lecturerid,
			"lecname"=>$_POST["firstman_names"],
    		"imgurl"=>$imgurl,
    	    "fid"=>$_G['fid'],
    		"fname"=>$_G['forum']['name'],
			"about"=>$_POST["about"],
    		"dateline"=>time()));
	
	//附件
	require_once libfile('function/home_attachment');
    updateattach('', $lecturerid, 'lecid', $_G['gp_attachnew'], $_G['gp_attachdel']);
	
//自动加入专区
    $membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
	if(!empty($membermaximum)) {
		$curnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]'");
		if($curnum >= $membermaximum) {
//			showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
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
		
	showmessage('创建成功', join_plugin_action('index'));

}

function save_outter() {
	global $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("index"));
	}

	//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}

	//无图片则为空
	if ($img["attachment"]) {
		$imgurl = "data/attachment/home/".$img["attachment"];
	} else {
		$imgurl = "";
	}

	DB::insert("lecturer", array("name"=>$_POST["name"],
			"gender"=>$_POST["gender"],
			"orgname"=>$_POST["orgname"],
			"about"=>$_POST["about"],
    		"imgurl"=>$imgurl,    
			"teachdirection"=>$_POST["teachdirection"],
    		"rank"=>$_POST["rank"],
    		"tel"=>$_POST["tel"],
    		"email"=>$_POST["email"],
    		"isinnerlec"=>2,
    		"fid"=>$_G['fid'],
    		"fname"=>$_G['forum']['name'],
    		"dateline"=>time(),
			"uid"=>$_G['uid']));

	$lecturerid = DB::insert_id();

	DB::insert("forum_forum_lecturer", array("lecid"=>$lecturerid,
			"lecname"=>$_POST["name"],
    		"imgurl"=>$imgurl,
    	    "fid"=>$_G['fid'],
    		"fname"=>$_G['forum']['name'],
			"about"=>$_POST["about"],
    		"dateline"=>time()));
	
	//附件
	require_once libfile('function/home_attachment');
    updateattach('', $lecturerid, 'lecid', $_G['gp_attachnew'], $_G['gp_attachdel']);
		
	showmessage('创建成功', join_plugin_action('index'));

}

function step2() {
	global $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("index"));
	}

	if (!empty($_POST["lecturercheckbox"])) {
		$lecids = "".join(",", $_POST["lecturercheckbox"])."";
		$query = DB::query("SELECT * FROM ".DB::table("lecturer")."
    		WHERE id IN (".$lecids.")");
		while($row = DB::fetch($query)){
			$lecturers[] = $row;
		}
	}

	$total = count($lecturers);

	return array("_G"=>$_G, "lecturers"=>$lecturers, "total"=>$total);
}

function step2_save() {
	global $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("index"));
	}

	if ($_POST["total"]) {
		for ($i=0; $i<$_POST["total"]; $i++) {
			DB::insert("forum_forum_lecturer", array("lecid"=>$_POST["lecid".$i],
			"lecname"=>$_POST["lecname".$i],
    		"imgurl"=>$_POST["lecimgurl".$i],
    	    "fid"=>$_G['fid'],
    		"fname"=>$_G['forum']['name'],
			"about"=>$_POST["about".$i],
    		"dateline"=>time()));
		}
		showmessage('保存专区讲师成功', join_plugin_action('index'));
	} else {
		showmessage('请先选择一个讲师', join_plugin_action('search'));
	}
}

function checklecturer($uid, $return_action) {
	if(DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('lecturer')." WHERE lecid=".$uid), 0)) {
		showmessage('讲师已经存在', join_plugin_action($return_action));
	}
}

function getmember($uid) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid=".$uid);
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function getuser($uid) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid=".$uid);
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function getlecturercount($isIO, $addsql) {
	if ($isIO == 1) {
		$isinnerlecsql = " isinnerlec=1";
	} else {
		$isinnerlecsql = " isinnerlec=2";
	}
	$query = DB::query("SELECT count(*) FROM ".DB::table('lecturer')." WHERE ".$isinnerlecsql.$addsql);
	return DB::result($query, 0);
}
?>
