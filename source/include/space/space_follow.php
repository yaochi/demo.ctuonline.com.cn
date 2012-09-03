<?php

/**
 *     $Id: function_follow.php 2011-9-5 15:34:35 yangy$
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$op = empty($_GET['op'])?'':$_GET['op'];
require_once libfile("function/follow");
require_once libfile("function/feed");
require_once libfile("function/org");
$perpage = 20;
$perpage = mob_perpage($perpage);

$count = 0;
$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
$group=$_GET['group'];
$uid=$_GET['uid'];
$diymode=$_GET['diymode'];
$wheresql = '';
if($_G[uid]==$uid || !$uid){
	$myself=1;
}
if($_GET['searchkey']) {
	$wheresql = "AND profile.realname LIKE '%$_GET[searchkey]%'";
	$theurl .= "&searchkey=$_GET[searchkey]";
}
if($op=='fans'){
	$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&op=fans";
	$countsql="SELECT COUNT(*) FROM ".DB::table('home_friend')." main  INNER JOIN ".DB::table('common_member_status')." profile ON main.fuid=profile.uid WHERE main.uid='$space[uid]' and (type='2' or type='3') $wheresql";
	$count = DB::result(DB::query($countsql), 0);
	if($count) {
	
			$query = DB::query("SELECT main.fuid AS uid,main.type,main.gids, main.gid, main.num, main.note,profile.fans FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_status')." profile ON profile.uid=main.fuid  
				WHERE main.uid='$space[uid]' and (type='2' or type='3') $wheresql
				ORDER BY main.dateline DESC
				LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$feed=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where (icon='thread' or icon='doing' or icon='blog') and uid=".$value['uid']." order by dateline desc limit 1");
					$feed=mkfeed($feed);
					if($feed[icon]=='doing'){
						$value['feedmessage']=$feed['title_data']['message'];
					}elseif($feed[icon]=='thread'){
						$value['feedmessage']=$feed['body_data']['message'];
					}elseif($feed[icon]=='blog'){
						$value['feedmessage']=$feed['body_data']['summary'];
					}
					$value['feeddate']=dgmdate($feed[dateline],'m月d日 H:i');
					$orgname=getUserGroupByuserId($value[uid]);
					//if($orgname){
						$value[orgname]=$orgname;
					//}else{
						//$value[orgname]="所在公司";
					//}
				$fanslist[$value['uid']] = $value;
				$fuids[$value['uid']] = $value['uid'];
			}
			//print_r($fanslist);
		}
	if($fuids) {
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($fuids).")");
		while ($value = DB::fetch($query)) {
			if(!$value['magichidden']) {
				$ols[$value['uid']] = $value['lastactivity'];
			} elseif($fanslist[$value['uid']] && !in_array($_GET['view'], array('me', 'trace', 'blacklist'))) {
				unset($fanslist[$value['uid']]);
				$count = $count - 1;
			}
		}
		$query = DB::query("SELECT cm.*, cmfh.* FROM ".DB::table("common_member").' cm LEFT JOIN '.DB::table("common_member_field_home")." cmfh ON cmfh.uid=cm.uid WHERE cm.uid IN(".dimplode($fuids).")");
		while($value = DB::fetch($query)) {
			
			if(empty($fanslist[$value['uid']])) $fanslist[$value['uid']] = array();
			$fanslist[$value['uid']] = array_merge($fanslist[$value['uid']], $value);
			//print_r($list[$value['uid']]);echo('<br/>');
		}
	}
	if($fanslist){
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where uid=".$_G[uid]." and fuid in (".implode(',',array_keys($fanslist)).")");
	}
	while($value=DB::fetch($query)){
		$fanslist[$value[fuid]]['cntype']=$value[type];
	}
	if($diymode) {
		$theurl .= "&diymode=$diymode";
	}
	$fanslistrealuids = array();
	foreach($fanslist as $fansuser){
		$fanslistrealuids[] = $fansuser[uid];
	}
	$fanslistrealname = user_get_user_realname($fanslistrealuids);
	
	$multi = multi($count, $perpage, $page, $theurl);
}else{
	
	//关注分组
	$groups = follow_group_list_new();
		$theurl = "home.php?mod=space&uid=$space[uid]&do=$do";
		$actives = array('me'=>' class="a"');
	
		$_GET['view'] = 'me';
	
		
		if($space['self']) {
			//require_once libfile('function/friend');
			//$groups = friend_group_list();
			$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
			if($group > -1) {
				$wheresql = " AND main.gids like '%,".$group."%'";
				$theurl .= "&group=$group";
			}
			$followtype=$_GET['followtype'];
			if($followtype=='none'){
				$wheresql = " AND main.gids ='' ";
				$theurl .= "&followtype=$followtype";
			}elseif($followtype=='friend'){
				$wheresql = " AND type='3' ";
				$theurl .= "&followtype=$followtype";
			}else{
				
			}
		}else{
			$followtype=$_GET['followtype'];
			if($followtype=='none'){
				$wheresql = " AND main.gids ='' ";
				$theurl .= "&followtype=$followtype";
			}elseif($followtype=='friend'){
				$wheresql = " AND type='3' ";
				$theurl .= "&followtype=$followtype";
			}else{
				
			}
		}
		
		
		//获取官方博客好友信息
	//	$obuid = getOfficialBlogUid();
	//	$obfinfo = getOfficialBlogAsFriend();
		require_once libfile("function/blog");
		$obfinfo = blog_offical_user($_G['uid']);
		$isHasobf = false;
		$obfriend = array();
		$ftop = array();
		$countsql="SELECT COUNT(*) FROM ".DB::table('home_friend')." main  INNER JOIN ".DB::table('common_member_profile')." profile ON main.fuid=profile.uid WHERE main.uid='$space[uid]' and (type='1' or type='3') $wheresql";
	//exit("sql:$countsql");
		$count = DB::result(DB::query($countsql), 0);
	
		if($count) {
	
			$query = DB::query("SELECT main.fuid AS uid,main.type,main.gids, main.gid, main.num, main.note FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON profile.uid=main.fuid  
				WHERE main.uid='$space[uid]' and (type='1' or type='3') $wheresql
				ORDER BY main.dateline DESC
				LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				if($value[type]=='3'){
					$_G["home_friend_".$space['uid'].'_'.$value['uid']] = $value['isfriend'] = 1;
					$_G["home_follow_".$space['uid'].'_'.$value['uid']] = $value['isfollow'] = 1;
				}else{
					$_G["home_follow_".$space['uid'].'_'.$value['uid']] = $value['isfollow'] = 1;
				}
				$fuids[$value['uid']] = $value['uid'];
	//			判断是否有官方博客好友
				if ($obfinfo['uid'] == $value['uid']) {
					$isHasobf = true;
					$obfriend = $value;
				} else {
					$groupname=array();
					$gids=explode(',',$value[gids]);
					for($i=1;$i<count($gids)-1;$i++){
						$groupname[]=$groups[$gids[$i]];
						$value[grouparray][]=$gids[$i];
					}
					$value[groupname]=implode(',',$groupname);
					$feed=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where (icon='thread' or icon='doing' or icon='blog') and uid=".$value['uid']." order by dateline desc limit 1");
					$feed=mkfeed($feed);
					if($feed[icon]=='doing'){
						$value['feedmessage']=$feed['title_data']['message'];
					}elseif($feed[icon]=='thread'){
						$value['feedmessage']=$feed['body_data']['message'];
					}elseif($feed[icon]=='blog'){
						$value['feedmessage']=$feed['body_data']['summary'];
					}
					$value['feeddate']=dgmdate($feed[dateline],'m月d日 H:i');
					$list[$value['uid']] = $value;
				}
			}
		}
		//end,fumz，2010-9-20 13:03:58
		if (!$isHasobf) {
			$ftop[$obfinfo['uid']] = $obfinfo;
		} else {
			$ftop[$obfriend['uid']] = $obfriend;
		}
	
		
		if($diymode) {
			$theurl .= "&diymode=$diymode";
		}
		
		$multi = multi($count, $perpage, $page, $theurl);
	
		if($space['self']) {
			$groupselect = array($group => ' class="current"');
	
			$maxfriendnum = checkperm('maxfriendnum');
			if($maxfriendnum) {
				$maxfriendnum = checkperm('maxfriendnum') + $space['addfriend'];
			}
		}
	
	
	if($fuids) {
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($fuids).")");
		while ($value = DB::fetch($query)) {
			if(!$value['magichidden']) {
				$ols[$value['uid']] = $value['lastactivity'];
			} elseif($list[$value['uid']] && !in_array($_GET['view'], array('me', 'trace', 'blacklist'))) {
				unset($list[$value['uid']]);
				$count = $count - 1;
			}
		}
		if($_GET['view'] != 'me') {
			require_once libfile('function/friend');
			friend_check($fuids);
		}
		$query = DB::query("SELECT cm.*, cmfh.* FROM ".DB::table("common_member").' cm LEFT JOIN '.DB::table("common_member_field_home")." cmfh ON cmfh.uid=cm.uid WHERE cm.uid IN(".dimplode($fuids).")");
		while($value = DB::fetch($query)) {
			$value['isfriend'] = $value['uid']==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$value['uid']] ? 1 : 0;
			$value['isfollow'] = $value['uid']==$space['uid'] || $_G["home_follow_".$space['uid'].'_'.$value['uid']] ? 1 : 0;
			
			if(empty($list[$value['uid']])) $list[$value['uid']] = array();
			$list[$value['uid']] = array_merge($list[$value['uid']], $value);
			//print_r($list[$value['uid']]);echo('<br/>');
		}
	}
	if($list){
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where uid=".$_G[uid]." and fuid in (".implode(',',array_keys($list)).")");
	}
	while($value=DB::fetch($query)){
		$list[$value[fuid]]['cntype']=$value[type];
	}
	$listrealuids = array();
	foreach($list as $user){
		$listrealuids[] = $user[uid];
	}
	$listrealname = user_get_user_realname($listrealuids);
	
	
}
$inviteurl = getinviteurl(0, 0, $appid);
function getinviteurl($inviteid, $invitecode, $appid) {
	global $_G;

	if($inviteid && $invitecode) {
		$inviteurl = getsiteurl()."home.php?mod=invite&amp;id={$inviteid}&amp;c={$invitecode}";
	} else {
		$invite_code = space_key($_G['uid'], $appid);
		$inviteapp = $appid?"&amp;app=$appid":'';
		$inviteurl = getsiteurl()."home.php?mod=invite&amp;u=$_G[uid]&amp;c=$invite_code{$inviteapp}";
	}
	return $inviteurl;
}

$a_actives = array($_GET['view'].$_GET['type'] => ' class="a"');
include_once template("diy:home/space_follow");

?>