<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index() {
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

    	if(!$lecturer['id'])
    	{
    		$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=groupmenu";
    		showmessage('讲师不存在', $url);
    	}


    	$query1 = DB::query("SELECT * FROM ".DB::table("forum_forum_lecturer")." WHERE lecid=".$lecturer["id"]);
    	while ($r = DB::fetch($query1)) {
    		$groupabouts[] = $r;
    		$hasothergroup = 1;
    	}
    }
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	if($lecturer['isinnerlec']==1)
	{
		$rank=changeRankName($lecturer['rank'],$lecturer['province_rank']);
		$lecturer['rank']=$rank;
	}
	else
	{
		$lecturer['rank']=getOutterRankname($lecturer['rank']);
	}

		$lecturer['courses']=getTrainCourseByLecid($lecturer['id']);
		$year=substr(date('Y', time()), 0, 4);
		$lecturer['yeartimes']=getlecturerecords($lecturer['id'],$year-1);

	//获得讲师uid
	$lectureruid=DB::result_first("select uid from ".DB::TABLE("common_member")." where username='".$lecturer[tempusername 	]."'");
    //是否有授课记录
	if(DB::result(DB::query("SELECT id FROM ".DB::table('lecture_record')." WHERE teacher_id=".$lecturer[id]), 0)) {
        $hasrecord = 1;
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
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$lecturer[id]' AND idtype='lecturerid' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$commentlist[] = $value;
		}
	}

	require_once libfile('function/home_attachment');
	$attachments = getattachs($lecturer['id'], 'lecid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];
  	$arr=getRole_SZ();
    return array("hasothergroup"=>$hasothergroup, "hasrecord"=>$hasrecord, "attachlist"=>$attachlist, "lecturer"=>$lecturer, "commentreplynum"=>$count,"lectureruid"=>$lectureruid, "groupabouts"=>$groupabouts, "_G"=>$_G, "commentlist"=>$commentlist,"year"=>$year-1,"type"=>$arr[type]);
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
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE id=".$_GET["lecid"]);



    $lecturer = array();
    $groupabouts = array();
    while ($row = DB::fetch($query)) {
    	if(!$row['id'])
    		{
    			$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=groupmenu";
    			showmessage('讲师不存在', $url);
    		}
    	$row['courses']=getTrainCourseByLecid($_GET["lecid"]);
    	$rank=getRank($row['rank'],$row['province_rank'],$row['isinnerlec']);
    	//print_r($rank);
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
	$arr=getRole_SZ();
	if($arr[type]==2)
	{
		if($lecturer[province_rank])
			$levels=getleclevels($lecturer[province_rank]);
		else
			$levels[levels1]=$arr[levels];
	}
	$certificate=empty($_GET['certificate'])?0:$_GET['certificate'];
    return array("attachlist"=>$attachlist, "attachs"=>$attachs, "imgattachs"=>$imgattachs, "lecturer"=>$lecturer, "groupabouts"=>$groupabouts,"type"=>$arr[type],"rank"=>$rank,"levels"=>$levels,"plevel"=>$arr[plevel],"certificate"=>$certificate);

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

	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	if ($_POST["orgname"]) {
    	$orgname = ", orgname='".$_POST["orgname"]."'";
    }

	if ($_POST["name"]) {
    	$name = ", name='".$_POST["name"]."'";
    }

    if ($_POST["bginfo"]) {
    	$bginfo = ", bginfo='".$_POST["bginfo"]."'";
    }
	if ($_POST["trainingtrait"]) {
    	$trainingtrait = ", trainingtrait='".$_POST["trainingtrait"]."'";
    }
	if ($_POST["trainingexperience"]) {
    	$trainingexperience = ", trainingexperience='".$_POST["trainingexperience"]."'";
    }
	if ($_POST["teachdirection"]) {
    	$teachdirection = ", teachdirection=".$_POST["teachdirection"];
    }

    if($_POST['isinnerlec']==1)
    {
		if($_POST[admintype]==2)
		{
			$rank = ", province_rank='".$_POST["lectype"]."'";
			$rank_name=", rank_name='".changeRankName($_POST['rank'],$_POST["lectype"])."'";
		}
	 	else if($_POST[admintype]==1)
    	{

    		$rank = ", rank='".$_POST["lectype"]."'";
    		$rank_name=", rank_name='".changeRankName($_POST['lectype'],$_POST["province_rank"])."'";
    		degrade($_POST["lectype"]);
    	}
    }
    else
	{
		$rank = ", rank='".$_POST["rank"]."'";
		$rank_name=", rank_name='".getOutterRankname($_POST["rank"])."'";
	}

	if ($_POST["tel"]) {
    	$tel = ", tel='".$_POST["tel"]."'";
    }
	if ($_POST["email"]) {
    	$email = ", email='".$_POST["email"]."'";
    }


    require_once libfile('function/home_attachment');

    updateattach('', $_POST['lecid'], 'lecid', $_G['gp_attachnew'], $_G['gp_attachdel']);

    DB::query("UPDATE ".DB::table("lecturer")." SET id=id".$name.$imgurl.$rank.$bginfo.$trainingexperience.$trainingtrait.$teachdirection.$tel.$email.$rank_name.$orgname." WHERE id=".$_POST['lecid']);

		$traincourse[lecid]=$_POST["lecid"];
		$traincourse[source]=1;
		$traincourse[isgroup]=0;
		$traincourse[update_time]=time();
		if($_POST[createtype]==1)
    	{
			if($_POST[admintype]==3)
    			$traincourse[belong]=0;
    		else if($_POST[admintype]==2)
    			$traincourse[belong]=2;
    		else if($_POST[admintype]==1&&$_POST[lectype]=5)
    			$traincourse[belong]=0;
    		else
    			$traincourse[belong]=1;
    	}
    	else
    		$traincourse[belong]=0;

		//新增
		$courses=trim($_POST["addedcourses"]);
    	$courseids_arr = explode(",",$courses);
    	$courseid_arr=array_unique($courseids_arr);
    	foreach($courseid_arr as $id)
    	{
    		$elementname="addcourseName_".$id;
    		$traincourse[coursename]=$_POST[$elementname];
    		$elementquali="addcourseQuali_".$id;
    		$traincourse[power]=$_POST[$elementquali];
    		op_course('insert',$traincourse,1,$traincourse[lecid]);
   		}

		//修改
		$courses=trim($_POST["modifiedcourses"]);
    	$courseids = explode(",",$courses);
    	$courseid_arr=array_unique($courseids);
    	foreach($courseid_arr as $id)
    	{
    		$traincourse[id]=$id;
    		$elementname="courseName_".$id;
    		$traincourse[coursename]=$_POST[$elementname];
    		$elementquali="courseQuali_".$id;
    		$traincourse[power]=$_POST[$elementquali];
    		op_course('update',$traincourse,1,$traincourse[lecid]);
   		}

		//删除
		$courses=trim($_POST["deletedcourses"]);
    	op_course('delete',$courses,1,$traincourse[lecid]);




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

    DB::query("DELETE FROM ".DB::table("lecturer")." WHERE id=".$_GET['lecid']);
    showmessage('删除成功', join_plugin_action('index'));
}

function getlecturercount($groupid, $addsql) {
	$query = DB::query("SELECT count(*) FROM ".DB::table('forum_forum_lecturer')." WHERE fid=".$groupid.$addsql);
	return DB::result($query, 0);
}

function checklecturer($uid, $return_action) {
	if(DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('lecturer')." WHERE lecid=".$uid), 0)) {
        showmessage('讲师已经存在', join_plugin_action($return_action));
	}
}

function checklecself($uid, $return_action) {
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
function reserve(){
	global $_G;
	$uid=$_G[uid];
	$groupmanagers=$_G['forum']['moderators'];
	$lecid=intval($_G[gp_lecid]);
	if($lecid){
		if($_POST["createsubmit"]){
		$setarr=array();
			if($_POST["coursename"]){
				$setarr['coursename']=getstr($_POST["coursename"],100,1,1);
			}else{
				showmessage('所需课题不能为空','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=reserve&lecid='.$lecid);
			}
			if($_POST["courseobject"]){
				$setarr['courseobject']=getstr($_POST["courseobject"],100,1,1);
			}else{
				showmessage('授课对象不能为空','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=reserve&lecid='.$lecid);
			}
			if($_POST["reserveuser"]){
				$setarr['reserveuser']=getstr($_POST["reserveuser"],50,1,1);
			}else{
				showmessage('预约者不能为空','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=reserve&lecid='.$lecid);
			}
			if($_POST["telephone"]){
				$setarr['telephone']=getstr($_POST["telephone"],50,1,1);
			}else{
				showmessage('联系方式不能为空','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=reserve&lecid='.$lecid);
			}
			if($_POST["starttime"]){
				$setarr['starttime']=strtotime($_POST["starttime"]);
			}
			else{
				showmessage('授课时间不能为空','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=reserve&lecid='.$lecid);
			}if($_POST["endtime"]){
				$setarr['endtime']=strtotime($_POST["endtime"]);
			}else{
				showmessage('授课时间不能为空','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=reserve&lecid='.$lecid);
			}
			$setarr['uid']=$uid;
			$setarr['dateline']= $_G['timestamp'];
			$setarr['lecid']= $lecid;
			DB::insert('lecturer_reserve', $setarr, 1);
			require_once libfile('function/mail');
			$allmail=array();
			$teachermail=0;
			foreach($groupmanagers as $manage){
				$query=DB::query("SELECT * FROM ".DB::TABLE('common_member')." WHERE uid=$manage[uid]");
				 while ($value = DB::fetch($query)) {
					$allmail[]=$value[email];
				}
			}
			$query=DB::query("SELECT * FROM ".DB::TABLE('lecturer')." WHERE id=$lecid");
			while($value=DB::fetch($query)){
				$teachermail=2;
				if($value[lecid]){
					$query=DB::query("SELECT * FROM ".DB::TABLE('common_member')." WHERE uid =$value[lecid]");
					 while ($value = DB::fetch($query)) {
						$allmail[]=$value[email];
						$teachermail=1;
					}
				}
			}
			foreach($allmail as $mail){
				if($mail){
						sendmail($mail,'课程预约','
				----------------------------------------------------------------------<br />
				<strong>课程预约信息</strong><br />
				----------------------------------------------------------------------<br />
				所需课题: '.$setarr[coursename].'<br />
				授课对象: '.$setarr[courseobject].'<br />
				授课时间: '.$_POST[starttime].'~'.$_POST[endtime].'<br />
				预约者: '.$setarr[reserveuser].'<br />
				联系方式: '.$setarr[telephone].'<br />');
				}
			}
			if($teachermail=='2'){
			showmessage('已成功发送预约请求至管理员，请等待回复','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid='.$lecid);
			}elseif($teachermail=='1'){
			showmessage('已成功发送预约请求至管理员及管理员，请等待回复','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid='.$lecid);
			}else{
				showmessage('已成功发送预约请求至管理员<br/>由于该讲师尚未设置邮箱,故将无法收到本次预约,欲了解更多信息,请联系本专区管理员','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid='.$lecid);
			}
		}
		return array("lecid"=>$lecid);
	}else{
		showmessage('请选择要预约的讲师','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=groupmenu');
	}
}

function improve(){
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


?>
