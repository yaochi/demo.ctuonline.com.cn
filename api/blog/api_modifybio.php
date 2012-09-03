<?php
/* Function: 社区中修改个人简介
 * Com.:
 * Author: yangyang
 * Date: 2011-9-13
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$message=$_G['gp_message'];
$code=$_G['gp_code'];
$message=urldecode($message);
if($code==md5('esn'.$uid)){
	if($uid){
		if($message){
			$message=dhtmlspecialchars(trim(addslashes($message)));
			$flag=DB::query("update ".DB::TABLE("common_member_profile")." set bio='".$message."' where uid=".$uid);
		}else{
			$flag=DB::query("update ".DB::TABLE("common_member_profile")." set bio='' where uid=".$uid);
		}
		if($flag){
			$res[success]="Y";
			$res[message]="成功！";
		}else{
			$res[success]="N";
			$res[message]="更新失败！";
		}
	}else{
		$res[success]="N";
		$res[message]="参数错误！";
	}
}else{
	$res[success]="N";
	$res[message]="code错误！";
}
echo json_encode($res);
?>