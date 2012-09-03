<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/space');
require_once libfile('function/spacecp');

$questid = empty($_GET['questid'])?0:intval($_GET['questid']);
if($questid>0){
		$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid='$questid' AND fid='$_G[fid]' LIMIT 1");
		$questionary = DB :: fetch($query);
		if(submitcheck('questionarysubmit')) {
			$_POST['questdescr']=$_POST['message'];
			$_POST['questname'] = empty($_POST['questname'])?'':getstr($_POST['questname'], 100, 1, 1);
			if(empty($_POST['questname'])){
				showmessage("问卷标题不能为空",join_plugin_action('index'));
			}
			$_POST['questdescr'] = empty($_POST['questdescr'])?'':getstr($_POST['questdescr'], 5000, 1, 0);
			if(empty($_POST['questdescr'])){
				showmessage("简介不能为空",join_plugin_action('index'));
			}
			$pluginid = $_GET["plugin_name"];
			require_once libfile("function/category");
			$other = common_category_is_other($_G["fid"], $pluginid);
			if($other["state"]=='Y' && $other["required"]=='Y' && !isset($_POST["category"])){
				showmessage('请选择类型', join_plugin_action("index"));
			}
			$questname= $_POST['questname'];
			$questdescr = $_POST['questdescr'];
			$visible = $_POST['visible'];
			$classid = $_POST['category'];
			
			DB::query("UPDATE ".db::table('questionary')." SET questname='$questname',questdescr='$questdescr',visible='$visible',classid='$classid' WHERE questid='$questid'");
			showmessage('修改问题成功', 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=questionary&plugin_op=createmenu&diy=&questid='.$questid.'&questionary_action=insert_question');
		}
	}
include template("questionary:create");

dexit();

?>


