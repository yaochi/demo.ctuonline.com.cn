<?php
/* Function: 根据用户设置改变用户的首页上可选项的顺序
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G[gp_uid];
$indexsetting=$_G[gp_indexsetting];
$indexsetting=addslashes(urldecode($indexsetting));
if(!$indexsetting){
	$indexsetting='0';
}
if($uid){
	$result=DB::query("update ".DB::Table("common_member")." set indexsetting='".$indexsetting."' where uid=$uid");
	
	if($result){
		$res['success']='Y';
	}else{
		$res['success']='N';
	}
}


echo json_encode($res);
?>