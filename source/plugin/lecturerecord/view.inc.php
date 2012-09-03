<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
	global $_G;
	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$lecturer = DB::fetch($query);
	/*if(!$result || $result["id"]<=0){
		echo "您不是讲师";
	}*/
	$action = $_G["gp_ac"];
	if(!$action){
		$recordid = $_GET["recordid"];
		$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
		$record = DB::fetch($query);

		if (!$record) {
			showmessage('授课记录不存在');
		}

		$record["starttime"] = dgmdate($record["starttime"]);
		$record["endtime"] = dgmdate($record["endtime"]);

		$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));
		$type = $_GET["type"]?$_GET["type"]:"apply";
		return array("levels"=>$levels, "type"=>$type,"record"=>$record, "lecturer"=>$lecturer);
	}
}

function index_dev(){
	global $_G;
	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$lecturer = DB::fetch($query);

	$recordid = $_GET["recordid"];
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);

	$record["starttime"] = dgmdate($record["starttime"]);
	$record["endtime"] = dgmdate($record["endtime"]);

	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));

	require_once libfile('function/home_attachment');
	$attachments = getattachs($record['id'], 'recordid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];

	return array("levels"=>$levels, "record"=>$record, "attachlist"=>$attachlist, "lecturer"=>$lecturer);
}

function index_research(){
	global $_G;
	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$lecturer = DB::fetch($query);

	$recordid = $_GET["recordid"];

	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);

	$record["starttime"] = dgmdate($record["starttime"]);
	$record["endtime"] = dgmdate($record["endtime"]);

	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));

	require_once libfile('function/home_attachment');
	$attachments = getattachs($record['id'], 'recordid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];

	return array("levels"=>$levels, "record"=>$record, "attachlist"=>$attachlist, "lecturer"=>$lecturer);
}

function index_tu(){
	global $_G;
	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE lecid=".$_G["uid"]);
	$lecturer = DB::fetch($query);

	$recordid = $_GET["recordid"];
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);

	$record["starttime"] = dgmdate($record["starttime"]);
	$record["endtime"] = dgmdate($record["endtime"]);

	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));

	require_once libfile('function/home_attachment');
	$attachments = getattachs($record['id'], 'recordid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];

	return array("levels"=>$levels, "record"=>$record, "attachlist"=>$attachlist, "lecturer"=>$lecturer);
}

function getlecturer($lecid) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('lecturer')." WHERE lecid=".$lecid);
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function getlecturerbyid($id) {
	if (!$id) {
		showmessage('操作失败');
	}
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('lecturer')." WHERE id=".$id);
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function edit(){
	global $_G;
	$recordid = $_GET["recordid"];
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);

	$record["starttime"] = dgmdate($record["starttime"]);
	$record["endtime"] = dgmdate($record["endtime"]);
	$type = $_GET["type"]?$_GET["type"]:"apply";
	$levels = array(array(1, "集团级"), array(2, "省级"), array(3, "地市级"));
	return array("levels"=>$levels, "type"=>$type,"record"=>$record);
}

function edit_dev(){
	$recordid = $_GET["recordid"];
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);

	$record["starttime"] = dgmdate($record["starttime"]);
	$record["endtime"] = dgmdate($record["endtime"]);

	$levels = array(array(1, "集团级"), array(2, "省级"), array(3, "地市级"));

	require_once libfile('function/home_attachment');
	$attachlist = getattach($record['id'],'recordid');
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];

	return array("levels"=>$levels, "record"=>$record, "attachlist"=>$attachlist, "attachs"=>$attachs, "imgattachs"=>$imgattachs);
}

function edit_research(){
	$recordid = $_GET["recordid"];
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);

	$record["starttime"] = dgmdate($record["starttime"]);
	$record["endtime"] = dgmdate($record["endtime"]);

	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));

	require_once libfile('function/home_attachment');
	$attachlist = getattach($record['id'],'recordid');
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];

	return array("levels"=>$levels, "record"=>$record, "attachlist"=>$attachlist, "attachs"=>$attachs, "imgattachs"=>$imgattachs);
}

function edit_tu(){
	$recordid = $_GET["recordid"];
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);

	$record["starttime"] = dgmdate($record["starttime"]);
	$record["endtime"] = dgmdate($record["endtime"]);

	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));

	require_once libfile('function/home_attachment');
	$attachlist = getattach($record['id'],'recordid');
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];

	return array("attachlist"=>$attachlist, "attachs"=>$attachs, "imgattachs"=>$imgattachs, "levels"=>$levels, "record"=>$record);
}

function getlecturerecord($recordid) {
	$record = array();
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE id=".$recordid);
	$record = DB::fetch($query);
	return $record;
}

function getoldempirical($record) {
	$empirical = 0;
	if ($record["class_level"]==1) {
		$level = 2;
	} elseif ($record["class_level"]==2) {
		$level = 1;
	} else {
		$level = 0;
	}
	if($record[type]==1){
		if($record["class_result"]>4){
			$one = 75;
		}
		//授课经验值=有效课时数×单位课时经验值×培训班级别系数×(平均授课满意度/5)
		$empirical = $record["class_time"]*$one*$level*($record["class_result"]/5);
	}elseif($record[type]==2){
		//即课程开发经验值=单位课程开发积分×课程级别系数×参与度
		$join_level = $record["join_num"]==1?1:0.8;
		$empirical = 500*$level*$join_level;
	}elseif($record[type]==3){
		//即课题研究经验值=单位课题研讨经验值×研讨次数
		$empirical = 50 * $record["join_num"];
	}elseif($record[type]==4){
		//即在线课题辅导经验值=单位在线课题辅导经验值×参与在线辅导的次数
		$empirical = 500 * $record["tu_num"];
	}

	return $empirical;
}

function update(){
	global $_G;
	$recordid = $_POST["recordid"];
	if (!$recordid) {
		showmessage('操作失败', join_plugin_action('index'));
	}
	$record = getlecturerecord($recordid);
	$inf[recordid]=$recordid;
	$inf[lecid]=$_POST["teacherid"];
	$inf[type]=$record[type];
	require_once (dirname(__FILE__)."/function/function_lecturerecord.php");
	//获取讲师信息
	$lecinfo = getlecturerbyid($_POST["teacherid"]);
	$setsql="";
	if ($_POST["name"]) {
		$setsql.= " name='".$_POST["name"]."'";
	}
	if ($_POST["class_name"]) {
		$setsql .= ", class_name='".$_POST["class_name"]."'";
	}
	if ($_POST["class_level"]) {
		$rule_new[coefficient]=$_POST["class_level"];
		$setsql .= ", class_level=".$_POST["class_level"];
	}
	if ($_POST["orgname_input_id"]) {
		$setsql .= ", org_id=".$_POST["orgname_input_id"];
	}
	if ($_POST["orgname_input"]) {
		$setsql .= ", org_name='".$_POST["orgname_input"]."'";
	}
	if ($_POST["org_person"]) {
		$setsql .= ", org_person='".$_POST["org_person"]."'";
	}
	if ($_POST["par_level"]) {
		$setsql .= ", par_level=".$_POST["par_level"];
	}
	if ($_POST["class_outcome"]) {
		$setsql .= ", class_outcome='".$_POST["class_outcome"]."'";
	}
	if ($_POST["attachment"]) {
		$setsql .= ", attachment='".$_POST["attachment"]."'";
	}
	if ($_POST["starttime"]) {
		$inf[year]=substr($_POST["starttime"],0,4);
		$setsql .= ", starttime=".strtotime($_POST["starttime"]);
	}
	if ($_POST["endtime"]) {
		$setsql .= ", endtime=".strtotime($_POST["endtime"]);
	}
	if ($_POST["jgname_input_id"]) {
		$setsql .= ", address_id=".$_POST["jgname_input_id"];
	}
	if ($_POST["jgname_input"]) {
		$setsql .= ", address='".$_POST["jgname_input"]."'";
	}
	if ($_POST["class_stud_num"]) {
		$setsql .= ", class_stud_num=".$_POST["class_stud_num"];
	}
	if ($_POST["join_num"]) {
		$rule_new[num]=$_POST["join_num"];
		$setsql .= ", join_num=".$_POST["join_num"];
	}
	if ($_POST["tu_num"]) {
		$rule_new[num]=$_POST["tu_num"];
		$setsql .= ", tu_num=".$_POST["tu_num"];
	}
	if ($_POST["class_time"]) {
		$rule_new[num]=$_POST["class_time"];
		$setsql .= ", class_time=".$_POST["class_time"];
	}
	if ($_POST["class_result"]) {
		$rule_new[degree]=$_POST["class_result"];
		$setsql .= ", class_result='".$_POST["class_result"]."'";
	}
	up_lecrecord_credit($inf,$rule_new);
	//附件
	require_once libfile('function/home_attachment');
    updateattach('', $recordid, 'recordid', $_G['gp_attachnew'], $_G['gp_attachdel']);

	DB::query("UPDATE ".DB::table("lecture_record")." SET ".$setsql." WHERE id=".$recordid);

    //通知
	require_once libfile('function/core');
	$query = DB::query("SELECT lr.*, l.lecid lecid FROM ".DB::table("lecture_record")." lr, ".DB::table("lecturer")." l WHERE lr.teacher_id=l.id AND lr.id=".$recordid);
	$record = DB::fetch($query);
	if ($record[type]==1) {
		$addurl = "&lecturerecord_action=index&type=apply";
	} elseif ($record[type]==2) {
		$addurl = "&lecturerecord_action=index&type=dev";
	} elseif ($record[type]==3) {
		$addurl = "&lecturerecord_action=index&type=research";
	} elseif ($record[type]==4) {
		$addurl = "&lecturerecord_action=index&type=tu";
	}
	$url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=lecturerecord&plugin_op=viewmenu&recordid=".$recordid.$addurl;
	notification_add($record[lecid],'zq_teachlog','[授课记录]您的“{title}”的授课记录被“{actor}”修改了，快去看看吧。',array("title"=>'<a href="'.$url.'">'.$record[name].'</a>'), 0);

	showmessage('修改成功', $url);
}

function delete() {
	global $_G;
	$recordid = $_GET["recordid"];
	require_once (dirname(__FILE__)."/function/function_lecturerecord.php");
	del_lecrecord_credit($recordid);
	$query = DB::query("SELECT lr.*, l.lecid lecid FROM ".DB::table("lecture_record")." lr, ".DB::table("lecturer")." l WHERE lr.teacher_id=l.id AND lr.id=".$recordid);
	$record = DB::fetch($query);
	require_once libfile('function/core');
	require_once libfile('function/credit');
	$userinfo = getlecturerbyid($record[teacher_id]);

	DB::query("DELETE FROM ".DB::table('lecture_record')." WHERE id=".$recordid);
	//通知
	notification_add($record[lecid],'zq_teachlog','[授课记录]您的“{title}”的授课记录被“{actor}”删除了，快去看看吧。',array("title"=>$record[name]), 0);
	showmessage('删除成功', 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=lecturerecord&plugin_op=groupmenu');
}

/*	if($type=="apply"){
		$type = '';
		$data["type"] = 1;
		if($_POST["class_result"]>4){
			$one = $_POST["class_time"] * 75;
		}
		//授课经验值=有效课时数×单位课时经验值×培训班级别系数×(平均授课满意度/5)
		$empirical = $_POST["class_time"]*$one*$level*($_POST["class_result"]/5);

		$creditaction = "addcourseapply";
	}elseif($type=="dev"){
		$type = '_dev';
		$data["type"] = 2;
		//即课程开发经验值=单位课程开发积分×课程级别系数×参与度
		$join_level = $_POST["join_num"]==1?1:0.8;
		$empirical = 500*$level*$join_level;

		$creditaction = "addcoursedevelop";
	}elseif($type=="research"){
		$type = '_research';
		//即课题研究经验值=单位课题研讨经验值×研讨次数
		$data["type"] = 3;
		$empirical = 50 * $_POST["join_num"];

		$creditaction = "addcourseresearch";
	}elseif($type=="tu"){
		$type = '_tu';
		$data["type"] = 4;
		//即在线课题辅导经验值=单位在线课题辅导经验值×参与在线辅导的次数
		$empirical = 500 * $_POST["tu_num"];

		$creditaction = "addcourseguide";
	}
	$empirical = $empirical - $oldempirical;*/

?>
