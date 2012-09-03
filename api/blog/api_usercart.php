<?php
/* Function: 根据用户的uid查询用户关注人数、粉丝数、日志数
 * Com.:
 * Author: yangyang
 * Date: 2011-9-14
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$uid)){
	$value=DB::fetch_first("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and cms.uid=".$uid);
	$res[follows]=$value[follow];
	$res[friends]=$value[friends];
	$res[fans]=$value[fans];
	$res[blogs]=$value[blogs];
	$res[realname]=$value[realname];
	$res[userprovince]=$value[userprovince];
	//$res[nickname]=$value[nickname];
	$res[info]=$value[bio];
	$userarr=getuserbyuid($value[uid]);
	$res[username]=$userarr[username];
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$avatar="./uc_server/data/avatar/".$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_small.jpg";
	if(file_exists(dirname(dirname(dirname(__FILE__))).'/'.$avatar)){
		$res[imageurl]=$avatar;
	}else{
		$res[imageurl]="uc_server/images/noavatar_small.gif";
	}

}
echo json_encode($res);
?>