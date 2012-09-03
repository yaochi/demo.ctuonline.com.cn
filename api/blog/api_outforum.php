<?php
/* Function: 加入专区
 * Com.:
 * Author: yangyang
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
$fid=$_G['gp_fid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$uid.$fid)){
	if($uid && $fid){
		$founderuid=DB::result_first("select founderuid from ".DB::TABLE("forum_forumfield")." where fid=".$fid);
		if($founderuid==$uid){
			$res[success]='N';
			$res[message]='创建者不能退出专区';
		}else{
			$count=DB::result_first("select count(*) from ".DB::TABLE("forum_groupuser")." where uid=".$uid." and fid=".$fid);
			if($count){
				DB::query("DELETE FROM " . DB::table('forum_groupuser') . " WHERE fid='".$fid."' AND uid='".$uid."'");
				DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+'-1' WHERE fid='".$fid."'");
				update_usergroups($uid);
				delgroupcache($fid, array('activityuser', 'newuserlist'));
				update_groupmoderators($fid);
				include_once libfile('function/group');
				group_update_forum_hot($fid);
				$res[success]='Y';
				$res[message]='成功';
			}else{
				$res[success]='N';
				$res[message]='您没有加入专区';
			}
		}
	}
}else{
	$res[success]='N';
	$res[message]='code错误！';
}

echo json_encode($res);
?>