<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    $arr=getRole_SZ();
    return $arr;

}

function index_outter() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    $arr=getRole_SZ();
    return $arr;
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
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    $arr=getRole_SZ();
    return array("attachlist"=>$attachlist, "attachs"=>$attachs, "imgattachs"=>$imgattachs, "lecturer"=>$lecturer, "groupabouts"=>$groupabouts,"type"=>$arr[type],"levels"=>$arr[levels]);

}

	function save_outter()
	{
		global $_G;
		$fup = $_G["fid"];
    	if(!$fup)
    	{
        	showmessage('参数不合法', join_plugin_action("index"));
    	}

    	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");


    	//获取图片url
		$img = null;
		if ($_POST["aid"])
		{
			$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
			$img = DB::fetch($query);
		}
		$imgurl = "data/attachment/home/".$img["attachment"];

		DB::insert("lecturer", array(
				"name"=>$_POST["name"],
				"gender"=>$_POST["gender"],
				"orgname"=>$_POST["orgname"],
				"orgname_all"=>$_POST["orgname"],
				"bginfo"=>$_POST["bginfo"],
				"trainingexperience"=>$_POST["trainingexperience"],
				"trainingtrait"=>$_POST["trainingtrait"],
    			"imgurl"=>$imgurl,
				"teachdirection"=>$_POST["teachdirection"],
    			"rank"=>$_POST["rank"],
		  		"rank_name"=>getOutterRankname($_POST["rank"]),
    			"tel"=>$_POST["tel"],
    			"email"=>$_POST["email"],
    			"isinnerlec"=>2,
    			"fid"=>$_G['fid'],
    			"fname"=>$_G['forum']['name'],
    			"dateline"=>time(),
				"updateline"=>time(),
				"uid"=>$_G['uid']
		));

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
		//附件
		require_once libfile('function/home_attachment');
    	updateattach('', $lecturerid, 'lecid', $_G['gp_attachnew'], $_G['gp_attachdel']);

    	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid=".$lecturerid;
    	showmessage('创建成功',$url);
	}

function save() {

	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }

    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}
	$imgurl = "data/attachment/home/".$img["attachment"];

	//判断讲师是否存在
	checklecturer($_POST["firstman_names_uids"], "index");

	//获取机构岗位信息
	require_once libfile('function/org');
	$uid = $_POST["firstman_names_uids"];
	$userinfo = getmember($uid);
	$org=get_org_by_user($userinfo["username"]);
	$orgid=$org[id];
	$orgname =$org[name];
	$orgname_all=getAllOrgName($orgid);
	$gender=getGender($userinfo["username"]);
	//$station = org_get_stattion($_POST["firstman_names_uids"]);
	//$station = implode(",", $station);
	if($_POST[admintype]==1)
	{
		$rank=$_POST["lectype"];
		$province_rank='';
	}
	else
	{
		$rank='';
		$province_rank=$_POST["lectype"];
	}
	$rank_name=changeRankName($rank,$province_rank);
	DB::insert("lecturer", array("name"=>$_POST["firstman_names"],
    		"lecid"=>$_POST["firstman_names_uids"],
			"tempusername"=>$userinfo["username"],
			"gender"=>$gender,
			"orgid"=>$orgid,
			"orgname"=>$orgname,
			"orgname_all"=>$orgname_all,
			//"position"=>$station,
			"bginfo"=>$_POST["bginfo"],
			"trainingtrait"=>$_POST["trainingtrait"],
			"trainingexperience"=>$_POST["trainingexperience"],
    		"imgurl"=>$imgurl,
			"teachdirection"=>$_POST["teachdirection"],
    		"rank"=>$rank,
			"rank_name"=>$rank_name,
			"province_rank"=>$province_rank,
    		"tel"=>$_POST["tel"],
    		"email"=>$_POST["email"],
    		"isinnerlec"=>1,
    		"fid"=>$_G['fid'],
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
    	$traincourse[update_time]=time();
    	foreach($courseid_arr as $id)
    	{
    		$elementname="addcourseName_".$id;
    		$traincourse[coursename]=$_POST[$elementname];
    		$elementquali="courseQuali_".$id;
    		$traincourse[power]=$_POST[$elementquali];
    		if($traincourse[power]<=3)
    			$traincourse[belong]=1;
    		else if($traincourse[power]==4)
    			$traincourse[belong]=2;
    		else
    			$traincourse[belong]=0;
    		op_course('insert',$traincourse,1,$lecturerid);
   		}
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

	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid=".$lecturerid;
    showmessage('创建成功', $url);
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
        showmessage('该用户已经是讲师了!', join_plugin_action($return_action));
	}
}

function checklecself($uid, $return_action) {
	if(DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('lecturer')." WHERE lecid=".$uid), 0)) {
        showmessage('该用户已经是讲师了!', join_plugin_action($return_action));
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

function sublevel(){
	$par=$_GET['par'];
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	$levels=getSubLevel($par);

	$arraydata=array("s"=>$levels);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";

	exit();
}

function del_level(){
	$levelid=$_GET['levelid'];
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
    $is=delLevel($levelid);

    $arraydata=array("s"=>$is);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();
}

function change_level(){
	global $_G;
	$levelname=$_G['gp_levelname'];
	$par=$_G['gp_levelid'];
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	$is=0;
    if($levelname) $is=changeLevel($par,$levelname);

    $arraydata=array("s"=>$is);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();

}

function add_level(){
	global $_G;
	$levelname=$_G['gp_levelname'];
	$par=$_G['gp_pid'];
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	$is=0;
    if($levelname) $is=addLevel($par,$levelname);

    $arraydata=array("s"=>$is);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();
}

function operate(){
	global $_G;
	$oper=$_GET[oper];
	$groupcourses=array();
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	$id=0;
	if($oper=='addlevel'){
		$id=$_GET[pid];
	}else if($oper=='delevel'){
		$id=$_GET[id];
	}else if($oper=='changelevel'){
		$id=$_GET[id];
	}else if($oper=='addcourse'){
		$groupcourses=getGroupCourses();
	}
	return array("oper"=>$oper,"courses"=>$groupcourses);
}

function check(){
	global $_G;
	$name=$_G[gp_name];
	$org=$_G[gp_org];
	if(!$name||!$org) $is=0;
	else{
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	$is=lecturerIsIn($name,$org);
	}

	$arraydata=array("s"=>$is);
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();
}

function test(){
		require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		//print_r(getProvinceOrg('E00230172'));
		$arr=array();
		$parentid=1;
		$aa=getsubs($parentid,$arr);
		print_r($aa);
		exit();
}


//级别名称添加
function addrankname()
{
	set_time_limit(0);
	$is=1;
	if(!$is) return;
	require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	$query = DB::query("SELECT id FROM ".DB::table("lecturer"));
	while ($row = DB::fetch($query))
	{
		$lecid = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE id=".$row["id"]);
		$lecturer=DB::fetch($lecid);
		if($lecturer['isinnerlec']==1)
		{
			$rankname=changeRankName($lecturer['rank'],$lecturer['province_rank']);
		}
		else
		{
			$rankname=getOutterRankname($lecturer['rank']);
		}
		DB::query("update ".DB::table("lecturer")." SET rank_name='".$rankname."' WHERE id=".$row["id"]);
	}
	exit();
}

//讲师库合并
function analyze(){
		global $_G;
		set_time_limit(0);
		$is=0;
		if(!$is) return;
		require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
		DB::query("UPDATE `pre_lecturer` SET isinnerlec=2 where  isinnerlec=1 AND tempusername is null AND lecid is null;");
		$sql="SELECT id,lecid  FROM `pre_lecturer` where  isinnerlec=1 AND tempusername is null;";
		$info = DB::query($sql);
	        while ($value = DB::fetch($info))
	        {
	        $query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid=".$value[lecid]);
			$userinfo = DB::fetch($query);
			DB::query("update `pre_lecturer` set  tempusername='".$userinfo[username]."' where id=".$value[id]);
	        }
        require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		$filepath="excel.xls";
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('gbk');
		$data->read($filepath);

		$arr_new=$arr_old=array();


 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
 		{

   			$username=$data->sheets[0]['cells'][$i][4];
   			if(!$username) continue;
            $temp=getLecturer_xf($username);
            if($temp==-1) continue;
            $arr_new[tempusername]=$username;
            $arr_new[isinnerlec]=1;
            $arr_new[lecid]="";
            $query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE username='".$username."'");
			$userinfo = DB::fetch($query);
            $arr_new[lecid]=$userinfo[uid];

            $tel=$data->sheets[0]['cells'][$i][10];
 			if($tel) $arr_new[tel]=$tel;

   			$email=$data->sheets[0]['cells'][$i][11];
   			if($email) $arr_new[email]=$email;

            if($temp==0)
   			{
   			  echo "insert_".$username."<br/>";

   			$name=$data->sheets[0]['cells'][$i][3];
   			$name=mb_convert_encoding($name,'UTF-8','GB2312');
 			if($name) $arr_new[name]=$name;

 			$orgid=$data->sheets[0]['cells'][$i][5];
 			if($orgid) $arr_new[orgid]=$orgid;
 			$arr_new[rank]=5;
   			DB::insert('lecturer', $arr_new);

   			echo DB::insert_id();
            }
            else
            {
                echo "update_".$username."<br/>";
              DB::update('lecturer', $arr_new,array("id"=>$temp));
            }
 		}
		exit();
	}

	//CD阶数据处理
	function deal_history(){
		global $_G;
		set_time_limit(0);
		$is=0;
		if(!$is) return;
		require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
        require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		$filepath="history_pxsjy.xls";
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('gbk');
		$data->read($filepath);

		$arr_new=$arr_old=array();


 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
 		{

   			$username=$data->sheets[0]['cells'][$i][1];
   			if(!$username) continue;
            $temp=getLecturer_xf($username,0);
            if($temp==0) continue;
            else
            {
            	$arr_new[tempusername]=$username;
 				$lecturerid = $temp;
 				$rank=$data->sheets[0]['cells'][$i][3];
    			DB::query("UPDATE `pre_lecturer` SET rank='".$rank."' where id=".$lecturerid);
 				$course=$data->sheets[0]['cells'][$i][5];
   				$course=mb_convert_encoding($course,'UTF-8','GB2312');
    			$traincourse[lecid]=$lecturerid;
    			$traincourse[source]=1;
    			$traincourse[isgroup]=0;
    			$traincourse[update_time]=time();
    			$traincourse[coursename]=$course;
    			$traincourse[power]=$data->sheets[0]['cells'][$i][4];
    			$traincourse[belong]=1;

    			op_course('insert',$traincourse,1,$lecturerid);
 			}
 		}
		exit();
	}

	//讲师级别调整
	function alterLecRank()
	{
		$is=0;
		if(!$is) return;
		set_time_limit(0);
		 require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		/*//外部讲师级别调整
		DB::query("UPDATE `pre_lecturer` SET teachdirection=1 where teachdirection=4;");
		DB::query("UPDATE `pre_lecturer` SET rank='5' where isinnerlec=2 AND rank='1';");
		DB::query("UPDATE `pre_lecturer` SET rank='1' where isinnerlec=2 AND rank='2';");
		DB::query("UPDATE `pre_lecturer` SET rank='2' where isinnerlec=2 AND rank='5';");
		//内部讲师级别调整
		DB::query("UPDATE `pre_lecturer` SET rank='5' where isinnerlec=1 AND rank='4';");
		DB::query("UPDATE `pre_lecturer` SET rank='1' where isinnerlec=1 AND rank='1';");*/


		$query = DB::query("SELECT id,lecid,tempusername FROM `pre_lecturer` where isinnerlec=1 AND (rank='2' OR rank='3');");
       	while($info = DB::fetch($query))
       	{
       		$org_arr=getProvinceOrg($info[tempusername]);
       		if(!$org_arr[is])
       			DB::query("UPDATE `pre_lecturer` SET rank='5' where id=".$info[id]);
       		else
       		{
       			searchProvinceLevel($org_arr[org]);
       			$que=DB::query("SELECT min(id) as id FROM `pre_province_level` where province_id=".$org_arr[org][groupid]." AND parent_id!=0;");
       			$value = DB::fetch($que);
       			DB::query("UPDATE `pre_lecturer` SET rank='',province_rank=".$value[id]." where id=".$info[id]);
       		}
       	}
		exit();
	}

		//
	function orgname_all()
	{
		set_time_limit(0);
		 require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		//外部讲师调整
		//DB::query("UPDATE `pre_lecturer` SET orgname_all=orgname where isinnerlec=2;");
		//内部讲师调整

		$query = DB::query("SELECT id,lecid,tempusername,orgid FROM `pre_lecturer` where isinnerlec=1 AND orgid!='';");
       	while($info = DB::fetch($query))
       	{
       		$org=getAllOrgName($info[orgid]);
       		DB::query("UPDATE `pre_lecturer` SET orgname_all='".$org."' where id=".$info[id]);
       	}
		exit();
	}

		//
	function orgname_alter()
	{
		set_time_limit(0);
		 require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		//内部讲师调整
		require_once libfile('function/org');
		$query = DB::query("SELECT id,lecid,tempusername,orgid FROM `pre_lecturer` where isinnerlec=1;");
       	while($info = DB::fetch($query))
       	{
			$org=get_org_by_user($info["tempusername"]);
			//$org=get_org_by_user("E00230157");
			/*print_r($org);
			exit();*/
			$orgid =$org[id];
			$orgname =$org[name];
			if($orgid) $idsql=",orgid=".$orgid;
			echo $info["tempusername"]."_".$orgid."_".$orgname;
       		DB::query("UPDATE `pre_lecturer` SET orgname='".$orgname."'".$idsql." where id=".$info[id]);
       	}
		exit();
	}

	function changeusername(){
		set_time_limit(0);
		$query = DB::query("SELECT id,lecid FROM `pre_lecturer` where isinnerlec=1;");
       	while($value = DB::fetch($query)){
       		$query1 = DB::query("select realname from pre_common_member_profile where uid=".$value[lecid]);
       		$value1 = DB::fetch($query1);
       		echo $value1[realname]."-".$value[id]."<br>";
       		DB::query("UPDATE `pre_lecturer` SET name='".$value1[realname]."' where id=".$value[id]);
       	}
       	echo "over";
       	exit();
	}

	function changeuid(){
		set_time_limit(0);
		$query = DB::query("SELECT id,tempusername FROM `pre_lecturer` where isinnerlec=1;");
       	while($value = DB::fetch($query)){
       		$query1 = DB::query("select uid from pre_common_member where username='".$value[tempusername]."'");
       		$value1 = DB::fetch($query1);
       		//echo $value1[realname]."-".$value[id]."<br>";
       		DB::query("UPDATE `pre_lecturer` SET lecid='".$value1[uid]."' where id=".$value[id]);
       	}
       	echo "over";
       	exit();
	}

	function deal_0720()
	{
		set_time_limit(0);
		require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
		require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		$filepath="excel.xls";
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('gbk');
		$data->read($filepath);

		$arr_new=$arr_old=array();


 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++){
 			$username=$data->sheets[0]['cells'][$i][4];
   			if(!$username) continue;
   			$arr_new[tel]='';
   			$arr_new[email]='';
   			$tel=$data->sheets[0]['cells'][$i][10];
 			if($tel) $arr_new[tel]=$tel;

   			$email=$data->sheets[0]['cells'][$i][11];
   			if($email) $arr_new[email]=$email;
   			DB::update('lecturer', $arr_new,array("tempusername"=>$username));
 		}
 		echo "over";
       	exit();
	}

	function deal()
	{
		set_time_limit(0);
		require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
		require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
		$realpath=dirname(dirname(dirname(dirname(__FILE__))));
		$filepath="/data/attachment/lecturer.xls";
		$realpath.=$filepath;
		$data = new Spreadsheet_Excel_Reader(); //实例化
		$data->setOutputEncoding('gbk');      //编码
		$data->read($realpath);

 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++){
 			$regname=$data->sheets[0]['cells'][$i][1];
 			$regname=mb_convert_encoding($regname,'UTF-8','GB2312');
 			if(($regname=='无')||($regname==''))		{
 				echo $i."<br/>";
 				continue;
 			}

 			$rank=$data->sheets[0]['cells'][$i][3];
 			$rank=mb_convert_encoding($rank,'UTF-8','GB2312');
 			if($rank=='集团级') $rank=1;
 			else if($rank=='课程认证讲师') $rank=2;
 			else if($rank=='课程授权讲师') $rank=3;
 			else $rank=5;

 			$teachdirection=$data->sheets[0]['cells'][$i][5];
 			$teachdirection=mb_convert_encoding($teachdirection,'UTF-8','GB2312');
 			if($teachdirection=='领导力发展与管理类') $teachdirection=1;
 			else if($teachdirection=='营销类') $teachdirection=2;
 			else if($teachdirection=='技术类') $teachdirection=3;
 			else $teachdirection='';

 			$tel=$data->sheets[0]['cells'][$i][9];
 			$tel=mb_convert_encoding($tel,'UTF-8','GB2312');

 			$email=$data->sheets[0]['cells'][$i][10];
 			$email=mb_convert_encoding($email,'UTF-8','GB2312');
 			$lec=array("username"=>$regname,"rank"=>$rank,"teachdirection"=>$teachdirection,"tel"=>$tel,"email"=>$email);
			$temp=insert_lec($lec);
			if($temp==0) echo $i."exist!<br/>";
			if($temp==1) echo $i."user is not exist!<br/>";

 		}
 		echo "over";
       	exit();
	}


function get_user($username) {
	$userinfo = array();
	$query = DB::query("select c.*,p.realname from pre_common_member c,pre_common_member_profile p where p.uid=c.uid AND c.username='".$username."'");
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function check_1($uid) {
	if(DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('lecturer')." WHERE lecid=".$uid), 0)) {
       return 0;
	}
	return 1;
}

	function insert_lec($lec) {
	global $_G;
    require_once (dirname(__FILE__)."/function/function_lecturermanage.php");
	//根据网大帐号获取用户uid和真实姓名
	$userinfo = get_user($lec["username"]);
	if($userinfo[uid]=='') return 1;
	$rank=$lec["rank"];
	$rank_name=changeRankName($rank,'');
	//判断讲师是否存在
	if(check_1($userinfo[uid])==0) {
        DB::update("lecturer",array("name"=>$userinfo[realname],
    		"lecid"=>$userinfo[uid],
			"tempusername"=>$lec["username"],
			"teachdirection"=>$lec["teachdirection"],
    		"rank"=>$rank,
			"rank_name"=>$rank_name,
    		"tel"=>$lec["tel"],
    		"email"=>$lec["email"],
			"updateline"=>time()),array("tempusername"=>$lec["username"]));
		return 0;
	}

	//获取机构岗位信息
	require_once libfile('function/org');
	$org=get_org_by_user($lec["username"]);
	$orgid=$org[id];
	$orgname =$org[name];
	$orgname_all=getAllOrgName($orgid);
	$gender=getGender($userinfo["username"]);

	DB::insert("lecturer", array("name"=>$userinfo[realname],
    		"lecid"=>$userinfo[uid],
			"tempusername"=>$lec["username"],
			"gender"=>$gender,
			"orgid"=>$orgid,
			"orgname"=>$orgname,
			"orgname_all"=>$orgname_all,
			"teachdirection"=>$lec["teachdirection"],
    		"rank"=>$rank,
			"rank_name"=>$rank_name,
    		"tel"=>$lec["tel"],
    		"email"=>$lec["email"],
    		"isinnerlec"=>1,
    		"fid"=>197,
    		"fname"=>'培训师家园',
    		"dateline"=>time(),
			"updateline"=>time()));
			$lecturerid = DB::insert_id();

//自动加入专区
    $membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
	if(!empty($membermaximum)) {
		$curnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]'");
		if($curnum >= $membermaximum) {
		}
	}
	$groupuser = DB::fetch_first("SELECT * FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$uid'");
	if($groupuser['uid']) {
	} else {
		group_add_user_to_group($userinfo[uid], $userinfo[username], $_G[fid]);
	}

	return $lecturerid;
}
?>
