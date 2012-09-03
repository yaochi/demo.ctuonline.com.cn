<?php
/* function:获取通知
 * author:caimm
 * date:2012-1-19
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['uid'];
$type = trim($_GET['type']);
if($type){
	if("gfnotice"==$type){
		$typesql = " and (type='gfblog_notice' or type='zq_notice_org' )";
	}else if("sns" == $type){
		$typesql = " and ptype=0 ";
	}else{
		$typesql = " and type='$type'";
	}
}
$sql = "select * from ".DB::table("home_notification")." where uid=".$uid.$typesql." order by dateline desc";
$info = DB::query($sql);
while($value=DB::fetch($info)){
	$notification[]=$value;
}
echo json_encode($notification);
?>