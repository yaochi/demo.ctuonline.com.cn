<?php
/* Function: 判断两个人的关系
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$fromuid=$_G['gp_fromuid'];
$touid=$_G['gp_touid'];
if($fromuid && $touid){
	$type=DB::result_first("select type from ".DB::TABLE("home_friend")." where uid='$fromuid' and fuid='$touid'");
}
$res[followed]=$type;

echo json_encode($res);
?>