<?php
/* Function: 手机端获取用户设置
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G[gp_uid];
if($uid){
	$usersetting='';
	$usersetting=DB::result_first("select usersetting  from ".DB::Table("common_member")." where uid=".$uid);
	

	$res['usersetting']=$usersetting;
	$res["code"]='0';
	$res["errorcode"]="11000";

}else{
	$res['usersetting']='';
	$res["code"]='1';
	$res["errorcode"]="91000";
}


echo json_encode($res);
?>