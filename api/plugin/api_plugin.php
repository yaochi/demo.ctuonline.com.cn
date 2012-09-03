<?php
/* Function: app处理接口
 * Com.:
 * Author: yy
 * Date: 2012-6-21 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
	
$discuz->init();
$app=$_G['gp_app'];
$param=$_G['gp_param'];
if($app && $param){
	echo pluginapi($app , $param);
}else{
	$res[data]=array();
	$res[error]='1';
	echo json_encode($res);
}
?>