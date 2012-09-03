<?php

/*
 * Created on 2012-4-20
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require dirname(dirname(dirname(__FILE__))) . '/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_follow.php';
$discuz = & discuz_core :: instance();

$discuz->init();
$uid = $_G['gp_uid'];
if ($uid) {
	$groups = follow_group_list_new($uid);
}
while(list($key, $val) = each($groups)){
	$g[id]=$key;
	$g[name]=$val;
	$group[]=$g;
}
$res[groups]=$group;
echo json_encode($res);
?>
