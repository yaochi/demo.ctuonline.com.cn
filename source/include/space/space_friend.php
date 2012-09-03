<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_friend.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 24;
$perpage = mob_perpage($perpage);

$list = $ols = $fuids = array();
require_once libfile("function/home");

$officialbloguid =   getOfficialBlogUid(); //官方博客uid

if(!$_GET['view']&&!$_GET['searchsubmit']){
	$value = home_user_add_blog();
	$value['username'] = $value['realname'];
}
if($value!=false){
    $list[] = $value;
}
$count = 0;
$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

if(empty($_GET['view']) || $_GET['view'] == 'all') $_GET['view'] = 'me';

ckstart($start, $perpage);

if($_GET['view'] == 'online') {
	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online";
	$actives = array('me'=>' class="a"');

	space_merge($space, 'field_home');
	$wheresql = '';
	if($_GET['type']=='near') {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=near";
		$ip = explode('.', $_G['clientip']);
		$wheresql = " WHERE ip1='$ip[0]' AND ip2='$ip[1]' AND ip3='$ip[2]'";
	} elseif($_GET['type']=='friend' && $space['feedfriend']) {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=friend";
		$wheresql = " WHERE uid IN ($space[feedfriend])";
	} else {
		$_GET['type']=='all';
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=all";
		$wheresql = ' WHERE 1';
	}

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_session')." $wheresql"), 0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table("common_session")." $wheresql ORDER BY lastactivity DESC LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {

			if($value['magichidden']) {
				$count = $count - 1;
				continue;
			}
			if($_GET['type']=='near') {
				if($value['uid'] == $space['uid']) {
					$count = $count-1;
					continue;
				}
			}

			$ols[$value['uid']] = $value['lastactivity'];
			$list[$value['uid']] = $value;
			$fuids[$value['uid']] = $value['uid'];
		}

		if($fuids) {
			require_once libfile('function/friend');
			friend_check($space['uid'], $fuids);

			$query = DB::query("SELECT cm.*, cmfh.* FROM ".DB::table("common_member").' cm
				LEFT JOIN '.DB::table("common_member_field_home")." cmfh ON cmfh.uid=cm.uid
				WHERE cm.uid IN(".dimplode($fuids).")");
			while($value = DB::fetch($query)) {
				$value['isfriend'] = $value['uid']==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$value['uid']] ? 1 : 0;
				$value['note'] = getstr($value['recentnote'], 35, 0, 0, 0, 0, -1);
				$list[$value['uid']] = array_merge($list[$value['uid']], $value);
			}
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'visitor' || $_GET['view'] == 'trace') {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="a"');

	if($_GET['view'] == 'visitor') {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_visitor')." main WHERE main.uid='$space[uid]'"), 0);
		$query = DB::query("SELECT main.vuid AS uid, main.vusername AS username, main.dateline
			FROM ".DB::table('home_visitor')." main
			WHERE main.uid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	} else {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_visitor')." main WHERE main.vuid='$space[uid]'"), 0);
		$query = DB::query("SELECT main.uid AS uid, main.dateline
			FROM ".DB::table('home_visitor')." main
			WHERE main.vuid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	}
	if($count) {
		while ($value = DB::fetch($query)) {
			$fuids[] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'blacklist') {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="a"');

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_blacklist')." main WHERE main.uid='$space[uid]'"), 0);
	if($count) {
		$query = DB::query("SELECT s.username, s.groupid, main.dateline, main.buid AS uid
			FROM ".DB::table('home_blacklist')." main
			LEFT JOIN ".DB::table('common_member')." s ON s.uid=main.buid
			WHERE main.uid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['isfriend'] = 0;
			$fuids[] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} else {
/**
 * modified by fumz ，2010-9-20 13:03:11
 * 按工号查找好友改为按真实姓名查找
 */
	//begin
/*	$theurl = "home.php?mod=space&uid=$space[uid]&do=$do";
	$actives = array('me'=>' class="a"');

	$_GET['view'] = 'me';

	$wheresql = '';
	if($space['self']) {
		require_once libfile('function/friend');
		$groups = friend_group_list();
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}
	}
	if($_GET['searchkey']) {
		$wheresql = "AND main.fusername LIKE '%$_GET[searchkey]%'";
		$theurl .= "&searchkey=$_GET[searchkey]";
	}
	
	//获取官方博客好友信息
	$obuid = getOfficialBlogUid();
	$obfinfo = getOfficialBlogAsFriend();
	$isHasobf = false;
	$obfriend = array();
	$ftop = array();
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_friend')." main WHERE main.uid='$space[uid]' $wheresql"), 0);
	if($count) {

		$query = DB::query("SELECT main.fuid AS uid, main.gid, main.num, main.note FROM ".DB::table('home_friend')." main
			WHERE main.uid='$space[uid]' $wheresql
			ORDER BY main.num DESC, main.dateline DESC
			LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$_G["home_friend_".$space['uid'].'_'.$value['uid']] = $value['isfriend'] = 1;
			$fuids[$value['uid']] = $value['uid'];
//			判断是否有官方博客好友
			if ($obuid == $value['uid']) {
				$isHasobf = true;
				$obfriend = $value;
			} else {
				$list[$value['uid']] = $value;
			}
		}
	}*/
	
	$theurl = "home.php?mod=space&uid=$space[uid]&do=$do";
	$actives = array('me'=>' class="a"');

	$_GET['view'] = 'me';

	$wheresql = '';
	if($space['self']) {
		require_once libfile('function/friend');
		$groups = friend_group_list();
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}
	}
	if($_GET['searchkey']) {
		//$wheresql = "AND main.fusername LIKE '%$_GET[searchkey]%'";
		$wheresql = "AND profile.realname LIKE '%$_GET[searchkey]%'";
		$theurl .= "&searchkey=$_GET[searchkey]";
	}
	
	//获取官方博客好友信息
//	$obuid = getOfficialBlogUid();
//	$obfinfo = getOfficialBlogAsFriend();
	require_once libfile("function/blog");
	$obfinfo = blog_offical_user($_G['uid']);
	$isHasobf = false;
	$obfriend = array();
	$ftop = array();
	$countsql="SELECT COUNT(*) FROM ".DB::table('home_friend')." main  INNER JOIN ".DB::table('common_member_profile')." profile ON main.fuid=profile.uid WHERE main.uid='$space[uid]' $wheresql";
//exit("sql:$countsql");
	$count = DB::result(DB::query($countsql), 0);

	if($count) {

		$query = DB::query("SELECT main.fuid AS uid, main.gid, main.num, main.note FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON profile.uid=main.fuid  
			WHERE main.uid='$space[uid]' $wheresql and (type=2 or type=3)
			ORDER BY main.num DESC, main.dateline DESC
			LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$_G["home_friend_".$space['uid'].'_'.$value['uid']] = $value['isfriend'] = 1;
			$fuids[$value['uid']] = $value['uid'];
//			判断是否有官方博客好友
			if ($obfinfo['uid'] == $value['uid']) {
				$isHasobf = true;
				$obfriend = $value;
			} else {
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

	$diymode = 1;
	if($space['self'] && $_GET['from'] != 'space') $diymode = 0;
	if($diymode) {
		$theurl .= "&from=space";
	}

	$multi = multi($count, $perpage, $page, $theurl);

	if($space['self']) {
		$groupselect = array($group => ' class="current"');

		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum) {
			$maxfriendnum = checkperm('maxfriendnum') + $space['addfriend'];
		}
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
		if(empty($list[$value['uid']])) $list[$value['uid']] = array();
		$list[$value['uid']] = array_merge($list[$value['uid']], $value);
	}
}

$listrealuids = array();
foreach($list as $user){
    $listrealuids[] = $user[uid];
}
$listrealname =  user_get_user_realname($listrealuids);

$a_actives = array($_GET['view'].$_GET['type'] => ' class="a"');
include_once template("diy:home/space_friend");

?>