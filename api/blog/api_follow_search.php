<?php
/* Function: 用户对关注人进行查询
 * Com.:
 * Author: yangyang
 * Date: 2011-9-14
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
require_once libfile("function/follow");
$searchkey=$_G['gp_searchkey'];
$uid=$_G['gp_uid'];
$code=$_G['gp_code'];
$start=$_G['gp_start'];
$perpage=$_G['gp_perpage'];
if($code==md5('esn'.$searchkey.$uid.$start.$perpage)){
	if($searchkey) {
		$wheresql = "AND profile.realname LIKE '%".$searchkey."%'";
	}
	$countsql="SELECT COUNT(*) FROM ".DB::table('home_friend')." main  INNER JOIN ".DB::table('common_member_profile')." profile ON main.fuid=profile.uid WHERE main.uid='".$uid."' and (type='1' or type='3') $wheresql";
	$count = DB::result(DB::query($countsql), 0);
	if($count){
		$query = DB::query("SELECT main.fuid AS uid, main.gids, main.num, main.note,profile.realname FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON profile.uid=main.fuid  
			WHERE main.uid='".$uid."' and (type='1' or type='3') $wheresql
			ORDER BY main.num DESC, main.dateline DESC
			LIMIT $start,$perpage");
			while($value=DB::fetch($query)){
				$res[]=$value;
			}
	}
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}
echo json_encode($res);
?>