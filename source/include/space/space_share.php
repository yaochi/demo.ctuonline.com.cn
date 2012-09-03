<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_share.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

//查看分享详情
if($id) {

	$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid='$id' AND uid='$space[uid]'");
	$share = DB::fetch($query);
	if(empty($share)) {
		showmessage('share_does_not_exist');
	}

	require_once libfile('function/share');
	$share = mkshare($share);

	$perpage = 50;
	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	$list = array();
	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$csql = $cid?"cid='$cid' AND":'';

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='sid'"),0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='sid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, "home.php?mod=space&uid=$share[uid]&do=share&id=$id", '', 'comment_ul');

	$diymode = intval($_G['cookie']['home_diymode']);

	include_once template("diy:home/space_share_view");

} else {
//查看分享列表
	$perpage = 20;

	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$gets = array(
		'mod' => 'space',
		'uid' => $space['uid'],
		'do' => 'share',
		'view' => $_GET['view'],
		'type' => $_GET['type'],
		'from' => $_GET['from']
	);
	$theurl = 'home.php?'.url_implode($gets);

	$f_index = '';
	$need_count = true;

	if(empty($_GET['view'])) $_GET['view'] = 'me';

	if($_GET['view'] == 'all') {
		
		require_once libfile('function/feed');
		
		//过滤条件，显示公开专区和自己参加专区的
		// 我参与的专区活动ids
		$join_fids = join_groups($space['uid']);
		//开放的专区或活动（包括浏览权限为继承专区的）
		$open_fids = open_groups();
		//合并
		$fids = array_merge($join_fids, $open_fids); 
		
		$sub_wheresql = "select sl.sid from ".DB::table('home_sharelog')." sl where (sl.idtype!='group') ";
		if($fids){
			$sub_wheresql .= " or (sl.idtype='group' and sl.id in ('".implode("','", $fids)."') )";
			
		}
		
		$wheresql = "sid in(".$sub_wheresql.")";

		
		

	} elseif($_GET['view'] == 'we') {

		space_merge($space, 'field_home');

		if($space['feedfriend']) {
			$wheresql = "uid IN ($space[feedfriend])";
			$f_index = 'USE INDEX(dateline)';
		} else {
			$need_count = false;
		}

	}
	//专区的分享
	elseif($_GET['view'] == 'group') {

		require_once libfile('function/group');
		$groups = group_get_by_user($space[uid]);
		if($groups){
			$groupIds=array();
			foreach ($groups as $group){
				$groupIds[]=$group['fid'];
			}
			require_once libfile('function/share');
			$sids=listShareIds($groupIds,"group");
			if($sids){
				$wheresql = "sid in('".implode("','", $sids)."')";
			}else{
				$need_count = false;
			}
		}else{
			showmessage('share_not_exist_group');
		}
		
	} 
	//我的分享
	else {
		
		if($_GET['from'] == 'space') $diymode = 1;
		
		//好友分享给我的
		if($_GET['type'] == 'friendshare'){
			$userIds=array($space[uid]);
			require_once libfile('function/share');
			$sids=listShareIds($userIds,"user");
			if($sids){
				$wheresql = "sid in('".implode("','", $sids)."')";
			}else{
				showmessage('share_does_not_exist');
			}
		}
		//我自己分享出去的
		else{
			$wheresql = "uid='$space[uid]'";
		}
		
	}
	$actives = array($_GET['view'] => ' class="a"');

	if($_GET['type'] && $_GET['type'] != 'all' && $_GET['type'] != 'friendshare') {
		$sub_actives = array('type_'.$_GET['type'] => ' class="a"');
		if($_GET[type]=="galbum"){
			$_GET[type]="album";
		}
		if($_GET[type]=="gpic"){
			$_GET[type]="pic";
		}
		$wheresql .= " AND type='$_GET[type]'";
	} else {
		if($_GET['type'] &&  $_GET['type'] == 'friendshare'){
			$sub_actives = array('type_'.$_GET['type'] => ' class="a"');
		}else{
			$sub_actives = array('type_all' => ' class="a"');
		}
	}
	$wheresql .= " AND status=1 ";
	$list = array();

	$sid = empty($_GET['sid'])?0:intval($_GET['sid']);
	$sharesql = $sid?"sid='$sid' AND":'';
    
	if($need_count) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_share')." WHERE $sharesql $wheresql"),0);
		if($count) {
			require_once libfile('function/share');
			$query = DB::query("SELECT * FROM ".DB::table('home_share')." $f_index
				WHERE $sharesql $wheresql
				ORDER BY dateline DESC
				LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$value = mkshare($value);
				$list[] = $value;
			}
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);

	dsetcookie('home_diymode', $diymode);

	include_once template("diy:home/space_share_list");
}

?>