<?php
	require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");
	
	function index(){//删除岗位，对当前岗位是设置station_id=-1，对感兴趣的岗位是删除
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$my_station=getStation($_G[uid],$_G[fid],0);//获得当前的岗位信息
		$interest_station=getStation($_G[uid],$_G[fid],1);//获得感兴趣的岗位信息
		$status=1;//0：未设，1:已设，-1:重设
		if(!$my_station) $status=0;
		if($my_station[station_id]==-1) {$status=-1; //station_id=-1,表示需要重设
			$my_station[station_id]="";
		}
		
		//	分页
    	$perpage = 10;
		$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		$start = ($page - 1) * $perpage;
    	$mystart=$interestart=0;
    	$mypage=$interestpage=1;
    	$type = intval($_GET["type"]) ? intval($_GET["type"]) : 0;
    	if($type==1) {
    		$interestpage=$page;
    		$interestart=$start;
    	}
    	else {
    		$mypage=$page;
    		$mystart=$start;
    	}
		
		if($my_station[station_id]==""||!$my_station) $my_station[station_id]=-1;
		if($my_station[station_id]!=-1)
		$my_courses=getCoursesPageByStation($_G[fid],$my_station[station_id],$mystart,$perpage);
		if($interest_station[station_id]==""||!$interest_station) $interest_station[station_id]=-1;
		if($interest_station[station_id]!=-1)
		$interest_courses=getCoursesPageByStation($_G[fid],$interest_station[station_id],$interestart,$perpage);
		
		$install=getAllStation();
		if($install)$install=1;
		else $install=0;
		
	$mycount = getcoursecount($my_station[station_id]);
	$interestcount = getcoursecount($interest_station[station_id]);
	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=stationcourse&plugin_op=groupmenu&type=";
	if($mycount) {
		$mymulti = multi($mycount, $perpage, $mypage, $url."0");
	}
	if($interestcount) {
		$interestmulti = multi($interestcount, $perpage, $interestpage, $url."1");
	}

		return array("type"=>$type,"mymulti"=>$mymulti,"interestmulti"=>$interestmulti,"install"=>$install,"status"=>$status,"my_station"=>$my_station,"interest_station"=>$interest_station,"my_courses"=>$my_courses,"interest_courses"=>$interest_courses);
	}
	
	function force(){//强制重新设置岗位
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$url="forum.php?mod=group&action=manage&op=manage_stationcourse&fid=".$_G[fid];
    	forceset();
		showmessage("强制重设岗位成功",$url);
		
	}
	
	function confirm(){//强制重新设置岗位
		global $_G;
		$type=$_GET[type];
		return array("type"=>$type);	
	}
	
	function erase(){//清空数据
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$url="forum.php?mod=group&action=manage&op=manage_stationcourse&fid=".$_G[fid];
    	erase_data();
		showmessage("清空数据成功",$url);
		
	}
	
	function select_station(){
		require_once (dirname(__FILE__)."/select_station.php");
	}
	
?>
