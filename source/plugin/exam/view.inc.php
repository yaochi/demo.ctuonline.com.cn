<?php
/*
 * Created on 2012-2-28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once (dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");

/**
 * 答题后学员进入结果页
 */
 function index(){
 	global $_G;
  	require_once (dirname(__FILE__)."/function/function_exam.php");
  	$id=$_G['gp_id'];
  	if($id=='') $id=getmaxid();
  	$info=getexaminfo($id,1);	//0:未开启memcache
  	$myinfo=getmyanswer($id,$_G[uid],1);
  	$user=array("uid"=>$_G[uid],"regname"=>$_G[username],"realname"=>user_get_user_name($_G[uid]));
  	if($myinfo[answ]==$info[answers]) ;
  	return array("my"=>$myinfo[info],"questions"=>$info[questions],"base"=>$info[base],"answers"=>$info[answers],"myanswer"=>$myinfo[answ]);
 }
?>
