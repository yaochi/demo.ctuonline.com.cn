<?php
/* Function: @接口提示
 * Com.:
 * Author: yangyang
 * Date: 2011-10-8 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$name=$_G['gp_name'];
$uid=$_G['gp_uid'];
if($name){
	$query=DB::query("SELECT main.fuid AS uid,profile.realname FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON profile.uid=main.fuid WHERE main.uid='".$uid."' and (type='1' or type='3') AND profile.realname LIKE '%".$name."%' ORDER BY main.num DESC, main.dateline DESC limit 0,10");
	while($uservalue=DB::fetch($query)){
		$user[type]='user';
		//$user[atid]=$uservalue[uid];
		$user[name]=$uservalue[realname];
		$member[]=$user;
	}
	$query = DB::query("SELECT * FROM ".DB::table('common_member_field_home')." WHERE uid='$uid'");
	$tmp = DB::fetch($query);
	$groupnamearr=unserialize($tmp['groupnames']);
	for($i=0;$i<count($groupnamearr);$i++){
		if(stristr($groupnamearr[$i],$name)){
			$groupvalue[type]='group';
			//$groupvalue[atid]=$i;
			$groupvalue[name]=$groupnamearr[$i];
			$group[]=$groupvalue;
		}
	}
	$query=DB::query("select * from ".DB::TABLE('forum_forum')." where name like '%".$name."%' and (type='sub' or type='activity') order by fid asc");
	while($forumvalue=DB::fetch($query)){
		$forum[type]='forum';
		//$forum[atid]=$forumvalue[fid];
		$forum[name]=$forumvalue[name];
		$forums[]=$forum;
	}
	$res[keyword]=$name;
	$res[member]=$member;
	$res[group]=$group;
	$res[forum]=$forums;
}
echo json_encode($res);
?>