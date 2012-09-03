<?php
/* Function:获取马甲信息
 * Com.:
 * Author: yangyang
 * Date: 2012-1-13 
 */

require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

$uid=$_G['gp_uid'];
$repeatsid=$_G['gp_repeatsid'];
$code=$_G['gp_code'];

if($code==md5('esn'.$uid.$repeatsid)){
	include_once libfile('function/repeats','plugin/repeats');
	if($repeatsid){
		$result=getforuminfo($repeatsid);
		if($result['icon']){
			$result['icon']=$_G[config]['image']['url'].'/data/attachment/group/'.$result['icon'];
		}else{
			$result['icon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
		}
	}
}

echo json_encode($result);
?>