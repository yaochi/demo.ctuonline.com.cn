<?php
/*
 * Created on 2012-2-28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  require_once (dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");

/**
 *学员进入有奖问答页面
 */
  function index(){
  	global $_G;
  	$perpage=10;
  	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
  	$type = empty ($_GET['type']) ? 0 : intval($_GET['type']);
  	$uid = empty ($_GET['uid']) ? $_G[uid] : intval($_GET['uid']);
	if ($page < 1)	$page = 1;
	$start = ($page -1) * $perpage;
  	require_once (dirname(__FILE__)."/function/function_sharesource.php");
	$list=getlist($start,$perpage,$type,$uid);
	$count =getsharecount($type,$uid);
	$uname=user_get_user_name($uid)."的分享";
	if($uid==$_G[uid]) $uname="我的分享";
	$multi = multi($count,$perpage, $page, "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=sharesource&plugin_op=groupmenu&type=".$type);
  	return array("list"=>$list,"multi"=>$multi,"type"=>$type,"uname"=>$uname);
  }

/**
 * 删除询问
 */
  function delete(){
  	global $_G;
  	return array("id"=>$_G[gp_id]);
  }

?>
