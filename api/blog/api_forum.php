<?php
/* Function: 专区浏览权限0专区成员，1所有
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$fid=$_G['gp_fid'];
if($fid){
	$gviewperm=DB::result_first("select gviewperm from ".DB::TABLE("forum_forumfield")." where fid=".$fid);
	if($gviewperm){
		$res['message']="1";
	}else{
		$res['message']="0";
	}
}
echo json_encode($res);
?>