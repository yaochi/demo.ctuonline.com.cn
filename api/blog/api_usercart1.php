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
$touid=$_G['gp_touid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$uid.$touid)){
	$value=DB::fetch_first("SELECT * FROM ".DB::table("common_member_status")." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and cms.uid=".$touid);
	if($uid&&$touid){
		$type=DB::result_first("select type from ".DB::TABLE("home_friend")." where uid='$uid' and fuid='$touid'");
	}
	if($type=='1'||$type=='3'){
		$res[isAttention]='1';
	}else{
		$res[isAttention]='0';
	}
	$mail=DB::result_first("select email from ".DB::table("common_member")." where uid=".$touid);
	if($mail){
		$res[email]=$mail;
	}else{
		$res[email]="";
	}
	$res[follows]=$value[follow];
	$res[friends]=$value[friends];
	$res[fans]=$value[fans];
	$res[blogs]=$value[blogs];
	$res[realname]=$value[realname];
	//$res[nickname]=$value[nickname];
	$res[info]=$value[bio];
	$res[gender]=$value[gender];
	if($value[birthyear]&&$value[birthmonth]&&$value[birthday]){
		$res[birthday]=$value[birthyear].'-'.$value[birthmonth].'-'.$value[birthday];
	}else{
		$res[birthday]="null";
	}
	$res[mobile]=$value[mobile];
	$userarr=getuserbyuid($value[uid]);
	$res[username]=$userarr[username];
	$uid = abs(intval($touid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$avatar="./uc_server/data/avatar/".$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_small.jpg";
	if(file_exists(dirname(dirname(dirname(__FILE__))).'/'.$avatar)){
		$res[iconImg]=$avatar;
	}else{
		$res[iconImg]="uc_server/images/noavatar_small.gif";
	}

}
echo json_encode($res);
?>