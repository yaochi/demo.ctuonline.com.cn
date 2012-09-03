<?php
/* Function:查找人员
 * Com.:
 * Author: yangyang
 * Date: 2011-10-8 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$name=$_G['gp_name'];
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];

if($name){
	$membercount=DB::result_first("SELECT count(*) FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and profile.realname LIKE '%".$name."%'");
	if($membercount){
		$query=DB::query("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and profile.realname LIKE '%".$name."%' ORDER BY cms.uid asc limit $num,$shownum");
		while($uservalue=DB::fetch($query)){
			$uservalue[type]='user';
			$member[]=$uservalue;
		}
	}
	$forumcount=DB::result_first("select count(*) from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.name like '%".$name."%' and ff.type='sub' and ff.fid=fff.fid");
	if($forumcount){
		$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.name like '%".$name."%' and ff.type='sub' and ff.fid=fff.fid order by ff.fid asc limit $num,$shownum");
		while($forumvalue=DB::fetch($query)){
			$forumvalue[type]='forum';
			$forums[]=$forumvalue;
		}
	}
	$res[membercount]=$membercount;
	$res[forumcount]=$forumcount;
	$res[member]=$member;
	$res[forum]=$forums;
}

echo json_encode($res);
?>