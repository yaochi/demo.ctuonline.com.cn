<?php
/* Function: 改变马甲
 * Com.:
 * Author: yangyang
 * Date: 2012-1-13 
 */

require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
$repeatsid=$_G['gp_repeatsid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$uid.$repeatsid)){
	include_once libfile('function/repeats','plugin/repeats');
	if($uid && ($repeatsid||$repeatsid=='0')){
		changeviewstatus($uid,$repeatsid);
	}
	$_G[member][repeatsstatus]=$repeatsid;
	$result['success']='Y';
}else{
	$result['success']='N';
}

echo json_encode($result);
?>