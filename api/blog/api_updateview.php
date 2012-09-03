<?php
/* Function: 修改首页显示
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$viewindex=$_G[gp_viewindex];
$uid=$_G[gp_uid];
$code=$_G[gp_code];
if($code==md5('esn'.$viewindex.$uid)){
	$result=DB::query("update ".DB::TABLE("common_member_field_home")." set viewindex='".$viewindex."' where uid=".$uid);
}

if($result){
	$res[success]="Y";
}else{
	$res[success]="N";
}


echo json_encode($res);
?>