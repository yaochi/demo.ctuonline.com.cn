<?php
	require_once dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
	require_once dirname(dirname(dirname(__FILE__)))."/source/api/lt_org/user.php";
	require_once dirname(dirname(dirname(__FILE__)))."/source/function/function_group.php";
	$discuz = & discuz_core::instance();
    $discuz->init();
	$orgId = $_POST["orgId"];
    $currentPage = $_POST["currentPage"];
    $fid = $_POST["fid"];
	$groupIds = $_POST["groupIds"];
	//echo ($orgId."<br/>");
	//echo ($currentPage."<br/>");
	//echo ($fid."<br/>");
	//echo ($groupIds."<br/>");
	
	//$orgId = '41023';
	//$currentPage = '0';
	//$fid = '617';
	//$groupIds = '2885126';
	$userMgr = new User();
	$data = $userMgr->getUserByGroupIdAndPageAndGroupIds($orgId, $currentPage,500,$groupIds);
	foreach($data as $item){
		group_add_user_to_group($item["id"], $item["regname"], $fid);
	}
	unset($userMgr);
?>
