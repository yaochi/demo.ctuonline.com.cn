<?php
/* Function: 添加通知接口
 * Com.:
 * Author: yy
 * Date: 2012-6-21 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

	
$discuz->init();
$uid=$_G["gp_uid"];
$source=$_G["gp_source"];
$touid=$_G["gp_touid"];
$message=$_G["gp_message"];

$touids=explode(',',$touid);
if(count($touids) && $message){
	if($uid){
		$_G['uid']=$uid;
	}
	$res['message']='';
	foreach($touids as $key=>$value){
		$return=notification_add($value,"outsidenotice",$message);
		if($return){
			$res['message']=$res['message']."uid为".$value."的用户通知发送失败";
		}
	}
	$res["code"]='0';
	$res["errorcode"]="11000";
}else{
	$res["code"]='1';
	$res["errorcode"]="91000";
	$res['message']='';
}

echo json_encode($res);
?>