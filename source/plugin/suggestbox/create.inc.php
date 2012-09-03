<?php

/*
 * Created on 2011-11-29
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once (dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");


function index() {
	global $_G;
	$sql = "select pro.realname as realname,mem.username as regname from pre_common_member mem, pre_common_member_profile pro where mem.uid=" . $_G['uid'] . " and  pro.uid=" . $_G['uid'];
	$info = DB :: query($sql);
	$value = DB :: fetch($info);

	return array (
		"info" => $value
	);
}

function save() {
	global $_G;

	DB :: insert("suggestbox", array (
		"uid" => $_G["uid"],
		"name" => $_POST["name"],
		"regname" => $_POST["regname"],
		"deptname" => $_POST["deptname"],
		"corpname" => $_POST["corpname"],
		"telephone" => $_POST["telephone"],
		"duty" => $_POST["duty"],
		"suggest" => $_POST["suggest"],
		"fid" => $_G["fid"],
		 "status" => 1,
	"createdate" => time()));

	$url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=suggestbox&plugin_op=groupmenu";
	showmessage('创建成功', $url);
}
function saveRespond() {
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_suggestbox.php");
	$suggest = getSuggest($_GET['suggestid']);
	$authorer = getRegname();
	$sql = "update pre_suggestbox set respond=respond+1 where suggestId=" . $suggest['suggestId'];

	DB :: query($sql);
	DB :: insert("opinion_reply", array (
		"uid" => $suggest['uid'],
		"username" => $suggest['regname'],
		"realname" => $suggest['name'],"isadmin"=>$_G["gp_isadmin"],
	"replydateline" => time(), "authoreruid" => $_G['uid'], "authorer" => $authorer, "replmessage" => $_G["gp_replmessage"], "type" => 1, "optionid" => $suggest['suggestId'], "fid" => $_G["fid"], "tap" => 1));
	$callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
	$data = array (
		"data" => 1,
		"suggestid" => $suggest['suggestId']
	);
	$ss = json_encode($data);
	echo "$callback($ss)";
	dexit();

}
function saveCommonRespond() {
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_suggestbox.php");
	$suggest = getSuggest($_G['gp_suggestid']);
	$authorer = getRegname();
	 $corp=getcompanyinfo($suggest['regname']);
	$sql = "update pre_suggestbox set respond=respond+1 where suggestId=" . $suggest['suggestId'];

	DB :: query($sql);
	DB :: insert("opinion_reply", array (
		"uid" => $suggest['uid'],
		"username" => $suggest['regname'],
		"realname" => $suggest['name'],
	"replydateline" => time(), "authoreruid" => $_G['uid'], "authorer" => $authorer, "replmessage" => trim($_POST["replmessage"]), "type" => 1, "optionid" => $suggest['suggestId'], "fid" => $_G["fid"], "tap" => 2));
	$url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu&suggestbox_action=view&suggestid=" . $suggest['suggestId'];
	showmessage('提交回复成功', $url);
}
function passSuggest() {
	global $_G;

	$select = "select * from pre_suggestbox where suggestId=" . $_G['gp_suggestid'];
	$sug = DB :: query($select);
	$sugvalue = DB :: fetch($sug);
	$updateReview = "update  pre_suggestbox set review='" . $_G['gp_replymessage'] . "' where  suggestId=" . $_G['gp_suggestid'];
	DB :: query($updateReview);


	if ($sugvalue['status'] ==1) {
		$sql = "update  pre_suggestbox set status=2, views=0,respond=0, passdate=" . time() . " where  suggestId=" . $_G['gp_suggestid'];
		DB :: query($sql);
		require_once (dirname(__FILE__) . "/function/function_suggestbox.php");
		$suggest = getSuggest($_G['gp_suggestid']);
		 $corp=getcompanyinfo($suggest['regname']);
          op_learncredit($suggest['uid'],$suggest['regname'],$suggest['name'],2,2,$suggest['suggestId'],100,$corp);

		$callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
		$data = array (
			"data" => 1
		);
		$ss = json_encode($data);
		echo "$callback($ss)";
		dexit();
	  } else {

		$callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
		$data = array (
			"data" => 2
		);
		$ss = json_encode($data);
		echo "$callback($ss)";
		dexit();
	}

}
function saveUpdate() {
	global $_G;
	$isupdate = "";

	if ($_POST["corpname"]) {
		$isupdate .= ", corpname='" . $_POST["corpname"] . "'";
	}
	if ($_POST["deptname"]) {
		$isupdate .= ", deptname='" . $_POST["deptname"] . "'";
	}
	if ($_POST["duty"]) {
		$isupdate .= ", duty='" . $_POST["duty"] . "'";
	}
	if ($_POST["telephone"]) {
		$isupdate .= ", telephone='" . $_POST["telephone"] . "'";
	}
	if ($_POST["suggest"]) {
		$isupdate .= ", suggest='" . $_POST["suggest"] . "'";
	}
	if($_G['forum']['ismoderator']==1){
		$isupdate .= ", status=1 ";
	}
	$suggestid = $_POST["suggestid"];
	$updatetime = time();
	DB :: query("UPDATE " . DB :: table("suggestbox") . " SET createdate=" . $updatetime. $isupdate . " WHERE suggestId=" . $suggestid);
		require_once (dirname(__FILE__) . "/function/function_suggestbox.php");
	$suggest = getSuggest($_POST['suggestid']);
	$authorer = getRegname();
	$reply=$_POST["replmessage"];
	if($reply){
		if($_G['forum']['ismoderator']==1){
			$isadmin=0;
		}else{
			$isadmin=1;
		}
		$uid=$suggest['uid'];
	DB :: insert("opinion_reply", array (
		"uid" =>$uid ,
		"username" => $suggest['regname'],
		"realname" => $suggest['name'],"isadmin"=>$isadmin,
	"replydateline" => time(), "authoreruid" => $_G['uid'], "authorer" => $authorer, "replmessage" => $reply, "type" => 1, "optionid" => $suggest['suggestId'], "fid" => $_G["fid"], "tap" => 1));
	}


	$url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu";
	showmessage('修改成功', $url);
}
function savePut() {
	global $_G;
	$isupdate = "";

	if ($_POST["corpname"]) {
		$isupdate .= ", corpname='" . $_POST["corpname"] . "'";
	}
	if ($_POST["deptname"]) {
		$isupdate .= ", deptname='" . $_POST["deptname"] . "'";
	}
	if ($_POST["duty"]) {
		$isupdate .= ", duty='" . $_POST["duty"] . "'";
	}
	if ($_POST["telephone"]) {
		$isupdate .= ", telephone='" . $_POST["telephone"] . "'";
	}
	if ($_POST["suggest"]) {
		$isupdate .= ", suggest='" . $_POST["suggest"] . "'";
	}
	$suggestid = $_POST["suggestid"];
	$updatetime = time();
	DB :: query("UPDATE " . DB :: table("suggestbox") . " SET createdate=" . $updatetime." , status=1 ". $isupdate . " WHERE suggestId=" . $suggestid);
		require_once (dirname(__FILE__) . "/function/function_suggestbox.php");
	$suggest = getSuggest($_POST['suggestid']);
	$authorer = getRegname();
	$reply=$_POST["replmessage"];
	if($reply){
		if($_G['forum']['ismoderator']==1){
			$isadmin=0;
		}else{
			$isadmin=1;
		}
		$uid=$suggest['uid'];
	DB :: insert("opinion_reply", array (
		"uid" =>$uid ,
		"username" => $suggest['regname'],
		"realname" => $suggest['name'],"isadmin"=>$isadmin,
	"replydateline" => time(), "authoreruid" => $_G['uid'], "authorer" => $authorer, "replmessage" => $reply, "type" => 1, "optionid" => $suggest['suggestId'], "fid" => $_G["fid"], "tap" => 1));
	}


	$url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu";
	showmessage('提交成功', $url);
}
function review() {
	$suggestid = $_GET['suggestid'];
	return array (
		"suggestid" => $suggestid
	);

}
?>
