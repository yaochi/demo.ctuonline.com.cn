<?php
/* Function: 根据uid判断用户加入了哪些专区
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_group.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G[gp_uid];
$num=empty($_G[gp_num])?0:$_G[gp_num];
if($uid){
	$grouplist=mygrouplist($uid, 'lastupdate', array('f.name', 'ff.icon'), $num);
	foreach($grouplist as $fid => $group) {
		$newgroup[fid]=$group[fid];
		$newgroup[name]=$group[name];
		$newgrouplist[]=$newgroup;
	}
	$res[grouplist]=$newgrouplist;
}

echo json_encode($res);
?>