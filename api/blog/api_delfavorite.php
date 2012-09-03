
<?php
/* Function: 取消收藏接口
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_home.php';

$discuz = & discuz_core::instance();

$discuz->init();

$id=$_G['gp_id'];
$feedid=$_G['gp_feedid'];
$idtype=$_G['gp_idtype'];
$uid=empty($_G['gp_uid'])?$_G['uid']:$_G['gp_uid'];
$code=$_G['gp_code'];

if($code==md5('esn'.$id.$idtype.$uid.$feedid)){
	if($feedid){
		$result=DB::query("delete from ".DB::TABLE("home_favorite")." where uid=".$uid." and feedid=".$feedid);
		DB::query("update ".DB::TABLE("home_feed")." set favorites=favorites+1  where feedid=".$feedid);
	}else{
		$result=DB::query("delete from ".DB::TABLE("home_favorite")." where uid=".$uid." and id=".$id." and idtype=".$idtype);
	}
		
		if($result){
			$res[success]='Y';
			$res[message]='成功！';
		}else{
			$res[success]='N';
			$res[message]='删除失败！';
		}
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}
echo json_encode($res);
?>