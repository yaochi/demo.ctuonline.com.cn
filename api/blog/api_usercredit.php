<?php
/* Function: 积分接口的封装
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=empty($_G['gp_uid'])?$_G['uid']:$_G['gp_uid'];
if($uid){
	if($_G[config][usercredit][on]){
		$url='http://admin.myctu.cn/esn/credit/totalCredit.do';
		$credit=dfsockopen($url,0,'uid='.$uid);
	}else{
		$credit=0;
	}
	$res['credit']=$credit;
}

echo json_encode($res);
?>