<?php
/* Function: 专区成员列表
 * Com.:
 * Author: caimingmao
 * Date: 2012-02-23
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uidnum=$_G['gp_uidnum'];
$gid=$_G['gp_gid'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];
$pagetype=empty($_G['gp_pagetype'])?'up':$_G['gp_pagetype'];
if($uidnum){
	if('down'==$pagetype){
		$wheresql=" and uid >".$uidnum;
	}elseif('up'==$pagetype){
		$wheresql=" and uid <".$uidnum;
	}
}else{
	$pagetype='up';
}
if($gid){
	$ordersql=updownsql($pagetype,'forum_groupuser','uid',$uidnum,$shownum,'uid'," fid=".$gid);
	$res['refresh']=$ordersql['refresh'];
	$sql="select uid from ".DB::table("forum_groupuser")." where fid = ".$gid.$wheresql.$ordersql['ordersql'];
		$info=DB::query($sql);
		while($value=DB::fetch($info)){
			$value['iconImg']=useravatar($value[uid]);
			$value['username']=user_get_user_name($value[uid]);
			$list[]=$value;
		}
	/*if('down'==$pagetype){
		$sql="select uid,username from (select uid,username from ".DB::table("forum_groupuser")." where fid = ".$gid.$wheresql." order by uid asc limit 0,".$shownum.") as t order by t.uid desc";
		$info=DB::query($sql);
		while($value=DB::fetch($info)){
			$value['iconImg']=useravatar($value[uid]);
			$list[]=$value;
		}
	}elseif('up'==$pagetype){
		$sql="select uid,username from ".DB::table("forum_groupuser")." where fid = ".$gid.$wheresql." order by uid desc limit 0,".$shownum;
		$info=DB::query($sql);
		while($value=DB::fetch($info)){
			$value['iconImg']=useravatar($value[uid]);
			$list[]=$value;
		}
	}*/
}
$res['users']=$list;
echo json_encode($res);
?>
