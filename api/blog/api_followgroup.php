<?php
/* Function: 根据用户的uid用户的好友分组
 * Com.:
 * Author: yangyang
 * Date: 2011-9-14
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_follow.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
if($uid){
	$groups = follow_group_list_new($uid);
	$res[groups]=$groups;
}

echo json_encode($res);
?>