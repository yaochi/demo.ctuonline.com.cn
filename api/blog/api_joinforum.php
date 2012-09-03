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
		$count=DB::result_first("select count(*) from ".DB::TABLE("forum_groupuser")." where uid=".$uid." and fid=".$fid);
		if($count){
			$res[success]='N';
			$res[message]='您已加入专区';
		}else{
			$userarr=getuserbyuid($uid);
			$username=$userarr[username];
			$jointype=DB::result_first("select jointype from ".DB::TABLE("forum_forum")." ff left join ".DB::TABLE("forum_forumfield")." fff on ff.fid=fff.fid where (ff.type='sub' or ff.type='activity') and ff.fid=".$fid);
			if($jointype=='0'){
				DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate) VALUES ('".$fid."', '".$uid."', '".$username."', '4', '" . time() . "', '" . time() . "')", 'UNBUFFERED');
				$res[success]='Y';
				$res[message]='专区加入成功';
			}elseif($jointype=='2'){
				DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate) VALUES ('".$fid."', '".$uid."', '".$username."', '0', '" . time() . "', '" . time() . "')", 'UNBUFFERED');
				$res[success]='Y';
				$res[message]='已提交申请，请等待审核';
			}else{
				$res[success]='N';
				$res[message]='专区有加入权限限制';
			}
			if($jointype=='0'||$jointype=='2'){
				include_once libfile('function/stat');
				updatestat('groupjoin');
				include_once libfile('function/group');
				group_update_forum_hot($fid);
				delgroupcache($fid, array('activityuser', 'newuserlist'));
				require_once libfile("function/credit");
				credit_create_credit_log($uid, "joingroup", $fid);
				group_add_empirical_by_setting($uid, $fid, "group_join_group", $fid);
				if ($jointype == 0) {
					DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='".$fid."'");
				}
			}
		}
	}
}else{
	$res[success]='N';
	$res[message]='code错误！';
}

echo json_encode($res);
?>