<?php
/* Function:我管理的专区
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_group.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G[gp_uid];
$num=empty($_G[gp_size])?0:$_G[gp_size];
if($uid){
	$grouplist=mygrouplist($uid, 'lastupdate', array('f.name', 'ff.icon'), $num,0,1);
	foreach($grouplist as $fid => $group) {
		$fids[]=$group[fid];
		$newgroup[fid]=$group[fid];
		$newgroup[name]=$group[name];
		$newgroup[icon]=$group[icon];
		$newgroup[type]=$group[group_type];
		$newgrouplist[$newgroup[fid]]=$newgroup;
	}
	$query=DB::query("select fid,jointype from ".DB::TABLE("forum_forumfield")." where fid in (".implode(',',$fids).")");
	while($value=DB::fetch($query)){
		$newgrouplist[$value[fid]][jointype]=$value[jointype];
	}
	$res[data]=$newgrouplist;
}

echo json_encode($res);
?>