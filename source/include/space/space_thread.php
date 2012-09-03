<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_thread.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

if(empty($_G['gp_view'])) $_G['gp_view'] = 'we';
$_GET['order'] = empty($_GET['order']) ? 'dateline' : $_GET['order'];

$allowviewuserthread = $_G['setting']['allowviewuserthread'];

$perpage = 20;
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$hiddennum = $count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'thread',
	'view' => $_G['gp_view'],
	'type' => $_GET['type'],
	'order' => $_GET['order'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey'],
	'from' => $_GET['from']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

require_once libfile('function/misc');
require_once libfile('function/forum');
require_once libfile('function/feed');
loadcache(array('forums'));
$fids = $comma = '';

$wheresql = $space['uid'] == $_G['uid'] || !$allowviewuserthread ? '1' : " t.fid IN(".$allowviewuserthread.")";
$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;

if($_G['gp_view'] == 'all') {
	
	//过滤条件，显示公开专区和自己参加专区的
	// 我参与的专区活动ids
	$join_fids = join_groups($space['uid']);
	//开放的专区或活动（包括浏览权限为继承专区的）
	$open_fids = open_groups();
	//合并
	$fids = array_merge($join_fids, $open_fids); 
	if($fids){
		$wheresql.= " AND t.fid IN (".dimplode($fids).")";
	}else{
		$wheresql.= " AND 0 ";
	}
	if($_GET['order'] == 'hot') {
		$wheresql .= " AND t.replies>='$minhot'";
	}
	$orderactives = array($_GET['order'] => ' class="a"');

} elseif($_G['gp_view'] == 'me') {

	if($_GET['from'] == 'space') $diymode = 1;

	$viewtype = in_array($_G['gp_type'], array('reply', 'thread')) ? $_G['gp_type'] : 'thread';
	$filter = in_array($_G['gp_filter'], array('recyclebin', 'aduit', 'close', 'common')) ? $_G['gp_filter'] : '';
	if($viewtype == 'thread') {
		$wheresql .= " AND t.authorid = '$space[uid]'";
		if($filter == 'recyclebin') {
			$wheresql .= " AND t.displayorder='-1'";
		} elseif($filter == 'aduit') {
			$wheresql .= " AND t.displayorder='-2'";
		} elseif($filter == 'close') {
			$wheresql .= " AND t.closed='1'";
		} elseif($filter == 'common') {
			$wheresql .= " AND t.displayorder>='0' AND t.closed='0'";
		} else {
			$wheresql .= " AND t.displayorder>='0' ";
		}
		$ordersql = 't.lastpost DESC';
	} else {
		$postsql = $threadsql = '';
		if($filter == 'recyclebin') {
			$postsql .= " AND p.invisible='-1'";
		} elseif($filter == 'aduit') {
			$postsql .= " AND p.invisible='-2'";
		} elseif($filter == 'close') {
			$threadsql .= " AND t.closed='1'";
		} elseif($filter == 'common') {
			$postsql .= " AND p.invisible='0'";
			$threadsql .= " AND t.displayorder>='0' AND t.closed='0'";
		} else {
			$threadsql .= " AND t.displayorder>='0' ";
		}
		$postsql .= " AND p.first='0'";
		$posttable = getposttable('p');

		require_once libfile('function/post');
		$query = DB::query("SELECT p.authorid, p.tid, p.pid, p.fid, p.invisible, p.dateline, p.message, t.special, t.status, t.subject, t.digest,t.attachment, t.replies, t.views, t.lastposter, t.lastpost FROM ".DB::table($posttable)." p
		INNER JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid $threadsql
		WHERE p.authorid='$space[uid]' $postsql ORDER BY p.dateline DESC LIMIT $start,$perpage");

		$list = array();
		while($value = DB::fetch($query)) {
			$value['message'] = messagecutstr($value['message'], 100);
			$list[] = procthread($value) ;
			$tids[$value['tid']] = $value['tid'];
		}
		$multi = simplepage(count($list), $perpage, $page, $theurl);

		$need_count = false;
	}
	$orderactives = array($viewtype => ' class="a"');

} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql .= " AND t.authorid='$fuid'";
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql .= " AND t.authorid IN ($space[feedfriend])";
			$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we";
		}

		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' ORDER BY num DESC LIMIT 0,100");
		while ($value = DB::fetch($query)) {
			$userlist[] = $value;
		}
	} else {
		$need_count = false;
	}
}

$actives = array($_G['gp_view'] =>' class="a"');

if($need_count) {

	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= " AND t.subject LIKE '%$searchkey%'";
	}
	$wheresql .= " AND t.isgroup='1' AND t.special='0'";
	$query = DB::query("SELECT t.* FROM ".DB::table('forum_thread')." t WHERE $wheresql ORDER BY $ordersql LIMIT $start,$perpage");

	while ($value = DB::fetch($query)) {
		if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
			$hiddennum++;
			continue;
		}else{
			$value['author']=user_get_user_name_by_username($value['author']);
		}
		$value['lastposter']=user_get_user_name_by_username($value['lastposter']);
		$list[] = procthread($value);
	}
	$multi = simplepage(count($list)+$hiddennum, $perpage, $page, $theurl);
}

dsetcookie('home_diymode', $diymode);

include_once template("diy:home/space_thread");

?>