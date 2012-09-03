<?php
/*
 * Created on 2012-2-28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  require_once (dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");

/**
 *跳转到上海师资管理平台
 */
  function index(){
  	global $_G;
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM ".DB :: table('forum_groupuser')." where fid=536 AND uid=".$_G[uid]),0);
	if($count>0){
		$regname=$_G[username];
		$name=user_get_user_name($_G[uid]);
		$user=$name."-".$regname;
		$tuser=base64_encode($user);
		$url="http://210.73.207.67:7100/Home/Index?f=".$tuser;
		header("Location:".$url);
	}else{
		$url="http://home.myctu.cn/sh";
		header("Location:".$url);
	}

  }

?>
