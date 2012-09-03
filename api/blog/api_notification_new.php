<?php
/* Function: 是否有我的通知
 * Com.:
 * Author: caimingmao
 * Date: 2012-02-23
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_group.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
if(!$uid){
	$username=$_G['gp_username'];
	$uid=user_get_uid_by_username($username);
}
if($uid){
	$sql="select count(*) from ".DB::table("home_notification")." where uid=".$uid." and new=1 and type='zq_at'";
	$info=DB::query($sql);
	$value=DB::result($info);
	$res['atNum']=$value;
	$sql="select count(*) from ".DB::table("home_notification")." where uid=".$uid." and new=1 and type='zq_comment'";
	$info=DB::query($sql);
	$value=DB::result($info);
	$res['commentNum']=$value;
	$sql="select count(*) from ".DB::table("home_notification")." where uid=".$uid." and new=1 and type not in('zq_at','zq_comment')";
	$info=DB::query($sql);
	$value=DB::result($info);
	$res['otherNum']=$value;
}
echo json_encode($res);
?>
