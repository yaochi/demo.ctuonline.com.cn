<?php
/* Function: 评论删除
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$icon=$_G["gp_icon"];
$cid=$_G["gp_cid"];
$uid=$_G["gp_uid"];
$id=$_G['gp_id'];
$feedid=$_G["gp_feedid"];
$code=$_G["gp_code"];
if(md5("esn".$icon.$cid.$uid.$id.$feedid)==$code){
	if($icon=='thread'||$icon=='poll'||$icon=='reward'){
		DB::query("delete from ".DB::TABLE("forum_post")." where pid=".$cid);
		if($id){
			DB::query("update ".DB::TABLE("forum_thread")." set replies = replies-1 where tid=".$id);
		}
	}else{
		DB::query("delete from ".DB::TABLE("home_comment")." where cid=".$cid);
	}
	if($icon=='resourcelist'){
		DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes-1 where id=".id." and icon='$resourcelist'");
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