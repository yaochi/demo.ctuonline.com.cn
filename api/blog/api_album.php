<?php
/* Function: 根据检索参数，获取动态中的所有图片
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$feedid=$_G['gp_feedid'];
if($feedid){
	$target_ids=DB::result_first("select target_ids from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	if($target_ids){
		$query=DB::query("select * from ".DB::TABLE("home_pic")." where picid in (".$target_ids.")");
		while($value=DB::fetch($query)){
			$album[]=$value;
		}
	}
	$res['album']=$album;
}
echo json_encode($res);
?>