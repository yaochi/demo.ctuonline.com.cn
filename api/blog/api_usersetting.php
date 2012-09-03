<?php
/* Function: 手机端根据用户设置改变用户的设置
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G[gp_uid];
$usersetting=$_G[gp_usersetting];
$usersetting=addslashes($usersetting);
if(!$usersetting){
	$usersetting='0';
}
if($uid){
	$result=DB::query("update ".DB::Table("common_member")." set usersetting='".$usersetting."' where uid=$uid");
	
	if($result){
		$res['success']='Y';
	}else{
		$res['success']='N';
	}
}


echo json_encode($res);
?>