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
$num=empty($_G[gp_shownum])?0:$_G[gp_shownum];
$fid=$_G['gp_fid'];
$pagetype=$_G['gp_pagetype'];

if($uid){
	if(!$fid){
		$pagetype='up';
	}
	if($pagetype=='up'){
		$grouplist=mygrouplist($uid, 'fup', array('f.name', 'ff.icon'), $num,0,0,0,$fid);
	}elseif($pagetype=='down'){
		$grouplist=mygrouplist($uid, 'fdown', array('f.name', 'ff.icon'), $num,0,0,0,$fid);
	}
	foreach($grouplist as $fid => $group) {
		$newgroup[fid]=$group[fid];
		$newgroup[name]=$group[name];
		$newgroup[gviewperm]=$group[gviewperm];
		$newgroup[description]=$group[description];
		$newgroup[membernum]=$group[membernum];
		$newgroup[icon]=$group[icon];
		$newgrouplist[]=$newgroup;
		$forum[fid]=$newgroup[fid];
		$forum[gviewperm]=$newgroup[gviewperm];
		$forum[fname]=$newgroup[name];
		$forum[ficonImg]=$newgroup[icon];
		$forums[]=$forum;
	}
	$res[forums]=$forums;
}

echo json_encode($res);
?>