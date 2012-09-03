<?php
/* Function: 社区中个人简介
 * Com.:
 * Author: yangyang
 * Date: 2011-9-13
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$uid)){
	if($uid){
		$res[info]=DB::result_first("select bio from ".DB::TABLE("common_member_profile")." where uid=".$uid);
	}
}
echo json_encode($res);
?>