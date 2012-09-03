<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");


function index(){
	global $_G;
	$addsql = "";
	$addurl = "";
	
	$isinnerlec = $_POST["isinnerlec"] ? $_POST["isinnerlec"] : $_GET["isinnerlec"];
	$name = $_POST["name"] ? $_POST["name"] : $_GET["name"];
	$rank_inner = $_POST["rank_inner"] ? $_POST["rank_inner"] : $_GET["rank_inner"];
	$rank_outter = $_POST["rank_outter"] ? $_POST["rank_outter"] : $_GET["rank_outter"];
	$teachdirection = $_POST["teachdirection"] ? $_POST["teachdirection"] : $_GET["teachdirection"];
	$course=$_POST["course"] ? $_POST["course"] : $_GET["course"];
	//内外部
	if ($isinnerlec==1) {
		$isinnerlecsql = " AND isinnerlec=".$isinnerlec;
		$isinnerlecurl = "&isinnerlec=".$isinnerlec;
		//级别
		if ($rank_inner) {
			$ranksql = " AND rank=".$rank_inner;
			$rankurl = "&rank_inner=".$rank_inner;
	
		}
	} elseif ($isinnerlec==2) {
		$isinnerlecsql = " AND isinnerlec=".$isinnerlec;
		$isinnerlecurl = "&isinnerlec=".$isinnerlec;
		//级别
		if ($rank_outter) {
			$ranksql = " AND rank=".$rank_outter;
			$rankurl = "&rank_outter=".$rank_outter;
		}
	} elseif (!$isinnerlec) {
		//$isinnerlecsql = " AND lec.isinnerlec=1";
		//$isinnerlecurl = "&isinnerlec=1";
	}
	//姓名
	if ($name) {
		$namesql = " AND name like'%".$name.trim()."%'";
		$nameurl = "&name=".$name.trim();
	}
	//授课方向
	if ($teachdirection) {
		$teachdirectionsql = " AND teachdirection=".$teachdirection;
		$teachdirectionurl = "&teachdirection=".$teachdirection;
	}
	if ($course) {
		$coursesql = " AND courses like '%".$course.trim()."%' ";
		$courseurl = "&course=".$course;
	}
	
	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
	if($teachdirection||$course){
		$query=DB::query("SELECT distinct(lecid) FROM ".DB::TABLE("shlecture_direct")." where 1=1 ".$teachdirectionsql.$coursesql);
		while($row=DB::fetch($query)){
			$lecid[]=$row[lecid];
		}
	}
	$lecid=implode(',',$lecid);
	if($lecid){
		$lecidsql= " AND id in (".$lecid.")";
	}
	$query = DB::query("SELECT * FROM ".DB::table("shlecture")." where 1=1 ".$addsql.$isinnerlecsql.$ranksql.$namesql.$lecidsql." ORDER BY dateline DESC LIMIT $start,$perpage");
	$addsql=" FROM ".DB::table("shlecture")." where 1=1 ".$addsql.$isinnerlecsql.$ranksql.$namesql.$lecidsql;
	
    $lecturers = array();
    while ($lecturer = DB::fetch($query)) {
		$lecturers[] = $lecturer;
    }
    $addurl = $isinnerlecurl.$nameurl.$rankurl.$teachdirectionurl.$courseurl;
	$getcount = getlecturercount($groupid, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=shlecturer&plugin_op=groupmenu".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}

	return array("multipage"=>$multipage,"lecturers"=>$lecturers, "isinnerlec"=>$isinnerlec, "course"=>$course, "name"=>$name, "rank_inner"=>$rank_inner, "rank_outter"=>$rank_outter, "teachdirection"=>$teachdirection);
}



function delete() {
	global $_G;

    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
    DB::query("DELETE FROM ".DB::table("shlecture")." WHERE id=".$_GET['lecid']);
	DB::query("DELETE FROM ".DB::table("shlecture_direct")." WHERE lecid=".$_GET['lecid']);
    showmessage('删除成功', join_plugin_action('index'));
}

function getlecturercount($groupid, $addsql) {
	$query = DB::query("SELECT count(*) ".$addsql);
	return DB::result($query, 0);
}


?>
