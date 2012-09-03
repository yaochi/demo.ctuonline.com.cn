<?php
/* Function: 评论删除
 * Com.:
 * Author: caimm
 * Date: 2012-4-23
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G["gp_uid"];
$cid=$_G["gp_cid"];
$feedid=$_G["gp_feedid"];
$code=$_G["gp_code"];
if(md5("esn".$uid.$cid.$feedid)==$code){
	$icon=DB::result_first("select icon from pre_home_feed where feedid=".$feedid);
	if($icon=='resourcelist'){
		$id=DB::result_first("select id from pre_home_comment where cid=".$cid);
	}
	if($icon=='thread'||$icon=='poll'||$icon=='reward'){
		DB::query("delete from ".DB::TABLE("forum_post")." where pid=".$cid);
		$tid=DB::result_first("select tid from pre_forum_post where pid=".$cid);
		if($tid){
			DB::query("update ".DB::TABLE("forum_thread")." set replies = replies-1 where tid=".$tid);
		}
	}else{
		DB::query("delete from ".DB::TABLE("home_comment")." where cid=".$cid);
	}
	if($icon=='resourcelist'){
		DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes-1 where id=".$id." and icon='$resourcelist'");
	}else{
		DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes-1 where feedid=".$feedid);
	}
	$res['success']="Y";
	$res['message']="成功！";
}else{
	$res['success']="N";
	$res['message']="code不正确！";
}

echo json_encode($res);
?>