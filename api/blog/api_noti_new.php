<?php

/* Function: 新提醒数
 * Com.:
 * Author: caimingmao
 * Date: 2012-05-21
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G[gp_uid];
$type=$_G[gp_type];
if($type){
	if($type=="official"){
		DB::query("update pre_home_notification_visit set dateline=".time()." where uid=".$uid);
	}
	if($type=="follow"){
		DB::query("update pre_home_notification set new=0 where uid=".$uid." and type='follow' and new=1");
	}
	if($type=="comment"){
		DB::query("update pre_home_notification set new=0 where uid=".$uid." and type='zq_comment' and new=1");
	}
	if($type=="at"){
		DB::query("update pre_home_notification set new=0 where uid=".$uid." and type='zq_at' and new=1");
	}
}
if($uid){
	//官方通知
	$date=DB::result_first("select dateline from pre_home_notification_visit where uid=".$uid);
	if($date){
		$official=DB::result_first("select count(*) from pre_home_notification where type='gfblog_notice' and dateline >".$date);
	}else{
		$official=0;
	}
	//粉丝
	$follow=DB::result_first("select count(*) from pre_home_notification where type='follow' and new=1 and uid=".$uid);
	//评论
	$comment=DB::result_first("select count(*) from pre_home_notification where type='zq_comment' and new=1 and uid=".$uid);
	//@我的
	$atme=DB::result_first("select count(*) from pre_home_notification where type='zq_at' and new=1 and uid=".$uid);
	//招呼
	$sayhi=DB::result_first("select count(*) from pre_home_poke where uid=".$uid);
	//表态?

	$res[official]=$official;
	$res[follow]=$follow;
	$res[comment]=$comment;
	$res[atme]=$atme;
	$res[sayhi]=$sayhi;
}else{
	$res=null;
}
if($_G[gp_callback]){
	echo $_G[gp_callback]."(".json_encode($res).")";
}else{
	echo json_encode($res);
}
?>
