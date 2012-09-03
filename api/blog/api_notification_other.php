<?php
/* Function: 通知详情
 * Com.:
 * Author: caimingmao
 * Date: 2012-02-23
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$nid=$_G["gp_id"];
$pagetype=$_G["gp_pagetype"];
$shownum=empty($_G['gp_shownum'])?20:$_G['gp_shownum'];

if($nid){
	if($pagetype=="up"){
		$wheresql=" and id<".$nid;
	}elseif($pagetype=="down"){
		$wheresql=" and id>".$nid;
	}
}else{
	$pagetype="up";
}

if($uid){
	$ordersql=updownsql($pagetype,'home_notification','id',$nid,$shownum,'id'," uid=".$uid);
	$sql="select id,note,dateline from ".DB::table("home_notification")." where uid=".$uid.$wheresql." and type not in('zq_at','zq_comment') and new=1 ".$ordersql['ordersql'];
	$info=DB::query($sql);
	while($value=DB::fetch($info)){
		$notice['id']=$value['id'];
		$notice['content']=$value['note'];
		$notice['noticeDate']=date("Y-m-d H:i:s",$value['dateline']);
		$notice['url']="";
		$notices[]=$notice;
	}
	$res['refresh']=$ordersql['refresh'];
	$res['notices']=$notices;
	if($pagetype=='down'){
		DB::query("update ".DB::table('home_notification')." set new=0 where uid=".$uid." and new=1 and type not in('zq_at','zq_comment')");
	}
}
echo json_encode($res);
?>
