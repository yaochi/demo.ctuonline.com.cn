<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function getApplyType(){
	$type = $_GET["type"]?$_GET["type"]:"apply";
	if($type=="apply"){
		return 1;
	}elseif($type=="dev"){
		return 2;
	}elseif($type=="research"){
		return 3;
	}elseif($type=="tu"){
		return 4;
	}
}

function operate(){
	global $_G;
	$oper=$_GET[oper];
	$groupcourses=array();
	require_once (dirname(__FILE__)."/function/function_lecturerecord.php");
	$id=0;
	if($oper=='addlevel'){
		$id=$_GET[pid];
	}else if($oper=='delevel'){
		$id=$_GET[id];
	}else if($oper=='changelevel'){
		$id=$_GET[id];
	}else if($oper=='select'){
		$groupcourses=getGroupCourse();	
	}
	return array("oper"=>$oper,"courses"=>$groupcourses);
}

function recordlist() {
	$lecid = $_GET["lecid"];
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." WHERE teacher_id=".$lecid);
	$result = array();
	while ($row = DB::fetch($query)) {
		$row["starttime"] = dgmdate($row["starttime"]);
		$row["endtime"] = dgmdate($row["endtime"]);
		$result[] = $row;
	}
	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));
	
	return array("result"=>$result, "levels"=>$levels);
}

function index(){
	global $_G;
	if ($_GET[lecid]) {
		$lecid = $_GET[lecid];
		$lecidsql = " AND teacher_id=".$lecid;
		$lecidurl = "&lecid=".$lecid;
	}
	//EKSN-285 授课记录模块增加"查看自己的授课记录"功能
    elseif ($_GET[myrecord]==1) {
    	$username_lec=$_G[username];
    	$sql_find_lecid="SELECT id FROM ".DB::table("lecturer")." WHERE tempusername='".$username_lec."'";
    	$query_lecid = DB::query($sql_find_lecid);
		$lec=DB::fetch($query_lecid);
		if($lec) $lecid=$lec[id];
		else $lecid=-1;//不是讲师
		$lecidsql = " AND teacher_id=".$lecid;
		$lecidurl = "&lecid=".$lecid;
	}
	
	$type = getApplyType();
	$lecidsql== " AND type=".$type;

	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." lr WHERE lr.type=".$type.$lecidsql." ORDER BY starttime DESC LIMIT $start,$perpage");
	while($row=DB::fetch($query)){
		$row["starttime"] = dgmdate($row["starttime"]);
		$row["endtime"] = dgmdate($row["endtime"]);
		$uids[] = $row["teacher_id"];
		$result[] = $row;
	}
	
	$getcount = getrecordcount($lecid, $type);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=lecturerecord&plugin_op=groupmenu&lecturerecord_action=index&type=apply".$lecidurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	
	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));
	return array(results=>$result, levels=>$levels, "multipage"=>$multipage, "type"=>$type,"lecid"=>$lecid);
}

function index_dev(){
	if ($_GET[lecid]) {
		$lecid = $_GET[lecid];
		$lecidsql = " AND teacher_id=".$lecid;
		$lecidurl = "&lecid=".$lecid;
	}

	$type = getApplyType();
	
	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." lr WHERE lr.type=".$type.$lecidsql." ORDER BY starttime DESC LIMIT $start,$perpage");
	while($row=DB::fetch($query)){
		$row["starttime"] = dgmdate($row["starttime"]);
		$row["endtime"] = dgmdate($row["endtime"]);
		$uids[] = $row["teacher_id"];
		$result[] = $row;
	}
	
	$getcount = getrecordcount($lecid, $type);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=lecturerecord&plugin_op=groupmenu&lecturerecord_action=index_dev&type=dev".$lecidurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	
	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));
	return array(results=>$result, levels=>$levels, "multipage"=>$multipage, "lecid"=>$lecid);
}

function index_research(){
	
	if ($_GET[lecid]) {
		$lecid = $_GET[lecid];
		$lecidsql = " AND teacher_id=".$lecid;
		$lecidurl = "&lecid=".$lecid;
	}
	
	$type = getApplyType();
	
	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." lr WHERE lr.type=".$type.$lecidsql." LIMIT $start,$perpage");
	while($row=DB::fetch($query)){
		$row["starttime"] = dgmdate($row["starttime"]);
		$row["endtime"] = dgmdate($row["endtime"]);
		$uids[] = $row["teacher_id"];
		$result[] = $row;
	}
	
	$getcount = getrecordcount($lecid, $type);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=lecturerecord&plugin_op=groupmenu&lecturerecord_action=index&type=research".$lecidurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	
	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));
	return array(results=>$result, levels=>$levels, "multipage"=>$multipage, "lecid"=>$lecid);
}

function index_tu(){
	
	if ($_GET[lecid]) {
		$lecid = $_GET[lecid];
		$lecidsql = " AND teacher_id=".$lecid;
		$lecidurl = "&lecid=".$lecid;
	}
	
	$type = getApplyType();
	
	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
	$query = DB::query("SELECT * FROM ".DB::table("lecture_record")." lr WHERE lr.type=".$type.$lecidsql." LIMIT $start,$perpage");
	while($row=DB::fetch($query)){
		$row["starttime"] = dgmdate($row["starttime"]);
		$row["endtime"] = dgmdate($row["endtime"]);
		$uids[] = $row["teacher_id"];
		$result[] = $row;
	}
	
	$getcount = getrecordcount($lecid, $type);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=lecturerecord&plugin_op=groupmenu&lecturerecord_action=index&type=tu".$lecidurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	
	$levels = array(1=>array(key=>1, name=>"集团级"), 2=>array(key=>2, name=>"省级"), 3=>array(key=>3, name=>"地市级"));
	return array(results=>$result, levels=>$levels, "multipage"=>$multipage, "lecid"=>$lecid);
}

function index_top(){
	$year=time();
	$year=date('Y', $year);
	require_once (dirname(__FILE__)."/function/function_lecturerecord.php");
	$data=getTop($year,50);
	
	return array("year"=>$year,"data"=>$data);
}

function getrecordcount($lecid, $type) {
	if ($lecid) {
		$lecidsql = " AND teacher_id=".$lecid;
	}
	$query = DB::query("SELECT count(*) FROM ".DB::table('lecture_record')." WHERE type=".$type.$lecidsql);
	return DB::result($query, 0);
}

?>
