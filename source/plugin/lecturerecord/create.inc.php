<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

if(!$_G["gp_select"]){
    $_G["gp_select"] = "index";
}
//$navs = array(
//    array(url=>join_plugin_action2('index', array(select=>"index")), name=>"授课申报", select=>"index"),
//    array(url=>join_plugin_action2('index_dev', array(select=>"index_dev")), name=>"课程开发", select=>"index_dev"),
//    array(url=>join_plugin_action2('index_research', array(select=>"index_research")), name=>"课程研究", select=>"index_research"),
//    array(url=>join_plugin_action2('index_tu', array(select=>"index_tu")), name=>"在线课程辅导", select=>"index_tu"),
//);
function index(){
	global $_G;
	$query = DB::query("SELECT id FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$result = DB::fetch($query);

	if(!$result && $result["id"]<=0 && !$_G['forum']['ismoderator']){
		showmessage('您不是讲师', 'forum.php?mod=group&action=plugin&fid='.$_G["fid"].'&plugin_name=lecturerecord&plugin_op=groupmenu');
	}
	$action = $_G["gp_ac"];
	if(!$action){
		$levels = array(array(1, "集团级"), array(2, "省级"), array(3, "地市级"));
		$type= empty($_GET['type']) ? 1 : $_GET['type'];
		return array("levels"=>$levels,"type"=>$type);
	}
}

function index_dev(){
	global $_G;
	$query = DB::query("SELECT id FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$result = DB::fetch($query);
	if(!$result && $result["id"]<=0 && !$_G['forum']['ismoderator']){
		showmessage('您不是讲师', 'forum.php?mod=group&action=plugin&fid='.$_G["fid"].'&plugin_name=lecturerecord&plugin_op=groupmenu');
	}

	$levels = array(array(1, "集团级"), array(2, "省级"), array(3, "地市级"));
	require_once libfile('function/home_attachment');
	$attachlist = getattach($lecturer['id'],'lecid');
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];

	return array("levels"=>$levels, "attachlist"=>$attachlist, "attachs"=>$attachs, "attachlist"=>$attachlist);
}

function index_research(){
	global $_G;
	$query = DB::query("SELECT id FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$result = DB::fetch($query);
	if(!$result && $result["id"]<=0 && !$_G['forum']['ismoderator']){
		showmessage('您不是讲师', 'forum.php?mod=group&action=plugin&fid='.$_G["fid"].'&plugin_name=lecturerecord&plugin_op=groupmenu');
	}
}

function index_tu(){
	global $_G;
	$query = DB::query("SELECT id FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$result = DB::fetch($query);
if(!$result && $result["id"]<=0 && !$_G['forum']['ismoderator']){
		showmessage('您不是讲师', 'forum.php?mod=group&action=plugin&fid='.$_G["fid"].'&plugin_name=lecturerecord&plugin_op=groupmenu');
	}
}

function getuser($uid) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid=".$uid);
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function getlecturer($lecid) {
	$lecturerinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('lecturer')." WHERE lecid=".$lecid);
	$lecturerinfo = DB::fetch($query);
	return $lecturerinfo;
}

function getlecturerbyid($id) {
	$lecturerinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('lecturer')." WHERE id=".$id);
	$lecturerinfo = DB::fetch($query);
	return $lecturerinfo;
}

function save(){
	global $_G;

	$query = DB::query("SELECT id FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$result = DB::fetch($query);
	if(!$result && $result["id"]<=0 && !$_G['forum']['ismoderator']){
		showmessage('您不是讲师', 'forum.php?mod=group&action=plugin&fid='.$_G["fid"].'&plugin_name=lecturerecord&plugin_op=groupmenu');
	}

	if ($_POST["teacherids"]) {
		$teacherid = $_POST["teacherids"];
		$teacherids = explode(",",$teacherid);
		$teacherid = $teacherids[0];
		$teachernames = explode(",", $_POST["teacher"]);
		$teachername = $teachernames[0];
		$lecturerinfo = getlecturerbyid($teacherid);
		$lecuid = $lecturerinfo[lecid];
	} else {
		$lecturerinfo = getlecturer($_G["uid"]);
		$teacherid = $lecturerinfo["id"];
		//获取用户信息
		$userinfo = getuser($_G["uid"]);
		if (!$userinfo['realname']) {
			$teachername = $_G["member"]["username"];
		} else {
			$teachername = $userinfo['realname'];
		}
		$lecuid = $_G['uid'];
	}
	$rule=array();
	$data["type"] = $_POST["type"];
	$data["name"] = $_POST["name"];
	$data["org_id"] = $_POST["orgname_input_id"];
	$data["org_name"] = $_POST["orgname_input"];
	$data["teacher_id"] = $teacherid;
	$data["teacher_name"] = $teachername;
	$data["tempcreatetime"]=date('Y-m-d H:i:s',time());

	$type=$_POST["type_lr"];
	$inf[lecid]=$teacherid;
	if($type=='apply'){
	$inf[type]=1;
	$inf[year]=date("Y",strtotime($_POST["starttime"]));
	$data["class_name"] = $_POST["class_name"];
	$data["class_level"] = $rule["coefficient"]=$_POST["class_level"];
	$data["starttime"] = strtotime($_POST["starttime"]);
	$data["endtime"] = $year=strtotime($_POST["endtime"]);
	$data["address"] = $_POST["jgname_input"];
	$data["class_stud_num"] = $_POST["class_stud_num"];
	$data["class_time"] = $rule["num"]=$_POST["class_time"];
	$data["class_result"] = $rule["degree"]=$_POST["class_result"];
	}else if($type=='dev'){
	$inf[type]=2;
	$inf[year]=date("Y",strtotime($_POST["starttime"]));
	$data["class_level"] = $rule["coefficient"]=$_POST["class_level"];
	$data["starttime"] = strtotime($_POST["starttime"]);
	$data["endtime"] = $year=strtotime($_POST["endtime"]);
	$data["address"] = $_POST["jgname_input"];
	$data["class_result"] = $rule["degree"]=$_POST["class_result1"];
	}else if($type=='research'){
	$inf[type]=3;
	$inf[year]=date("Y",time());
	$data["starttime"] = time();
	$data["join_num"] = $rule["num"]=$_POST["join_num"];
	}else{
	$inf[type]=4;
	$inf[year]=date("Y",time());
	$data["starttime"] = time();
	$data["tu_num"] = $rule["num"]=$_POST["tu_num"];
	}
	DB::insert("lecture_record", $data);
	$lecrecordid = DB::insert_id();

	require_once libfile('function/home_attachment');
    updateattach('', $lecrecordid, 'recordid', $_G['gp_attachnew'], $_G['gp_attachdel']);

//发送动态
	require_once libfile('function/feed');
	$tite_data = array('lecname' => '<a href="forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=lecturermanage&plugin_op=groupmenu&diy=&lecid='.$lecrecordid.'&lecturermanage_action=view">'.$userinfo['realname'].'</a>');
	feed_add('lecturerecord', 'feed_lecturerecord', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $_G['username'],$_G['fid']);

	require_once (dirname(__FILE__)."/function/function_lecturerecord.php");
	add_lecrecord_credit($inf,$rule);
	showmessage('创建成功', join_plugin_action('index'));
}

/*
function deal_20110803()
{
	global $_G;
	set_time_limit(0);
	require_once (dirname(__FILE__)."/function/function_lecturerecord.php");
	$sql="SELECT * FROM `pre_lecture_record` where starttime>1293840000;";
	$query=DB::query($sql);
	while($value=DB::fetch($query))
	{
		if($value[type]==1)
		{
			$rule["coefficient"]=$value["class_level"];
			$rule["num"]=$value["class_time"];
			$rule["degree"]=$value["class_result"];
		}
		else if($value[type]==2)
		{
			$rule["coefficient"]=$value["class_level"];
			$rule["degree"]=$value["class_result1"];
		}
		else if($value[type]==3)
		{
			$rule["num"]=$value["join_num"];
		}
		else
		{
			$rule["num"]=$value["tu_num"];
		}
	$inf[lecid]=$value[teacher_id];
	$inf[type]=$value[type];
	$inf[year]=date("Y",$value[starttime]);
	add_lecrecord_credit($inf,$rule);
	}
	echo "SUCCESS!";
	exit();
}
*/
?>
