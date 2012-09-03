<?php

/**
 *      $Id: function_follow.php 2011-9-5 15:34:35 yangy$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/follow');
require_once libfile('function/org');

$op = empty($_GET['op'])?'':$_GET['op'];
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

$space['key'] = space_key($space['uid']);

$actives = array($op=>' class="a"');

if($op == 'add') {

	if(!checkperm('allowfriend')) {
		showmessage('no_privilege');
	}

	if($uid == $_G['uid']) {
		showmessage('friend_self_error');
	}

	if(friend_check($uid)) {
		showmessage('you_have_friends');
	}

	$tospace = getspace($uid);
	//真实姓名
	$tospace['realname'] = user_get_user_name($uid);
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}

	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	$groups = friend_group_list();

	space_merge($space, 'count');
	space_merge($space, 'field_home');
	
	// Mod by lujianqing 2010-10-13
	//$maxfriendnum = checkperm('maxfriendnum');
	$maxfriendnum = 300;
	if($maxfriendnum && $space['friends'] >= $maxfriendnum + $space['addfriend']) {
		if($_G['magic']['friendnum']) {
			showmessage('enough_of_the_number_of_friends_with_magic');
		} else {
			showmessage('enough_of_the_number_of_friends');
		}
	}
    require_once libfile("function/userapp");
    if(!my_user_is_allow_friend($uid)){
        showmessage("对方禁止任何人加自己为好友");
    }

	if(friend_request_check($uid)) {

		if(submitcheck('add2submit')) {

			$_POST['gid'] = intval($_POST['gid']);
			friend_add($uid, $_POST['gid']);
			
			//获取真实姓名
			$tospace['username'] = user_get_user_name($tospace['uid']);

			if(ckprivacy('friend', 'feed')) {
				require_once libfile('function/feed');
				feed_add('friend', 'feed_friend_title', array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">$tospace[username]</a>"));
			}

			notification_add($uid, 'friend', 'friend_add');

			showmessage('friends_add', dreferer(), array('username' => $tospace['username'], 'uid'=>$uid), array('showdialog'=>1, 'showmsg' => true, 'closetime' =>  1));
		}
        
		$op = 'add2';
		include template('home/spacecp_friend');
		exit();

	} else {

		if(getcount('home_friend_request', array('uid'=>$uid, 'fuid'=>$_G['uid']))) {
			showmessage('waiting_for_the_other_test');
		}

		if($tospace['videophotostatus']) {
			ckvideophoto('friend', $tospace);
		}

		ckrealname('friend');

		if(submitcheck('addsubmit')) {

			$_POST['gid'] = intval($_POST['gid']);
			$_POST['note'] = censor($_POST['note']);
			friend_add($uid, $_POST['gid'], $_POST['note']);

			require_once libfile('function/mail');
			$values = array(
				'username' => $tospace['username'],
				'url' => getsiteurl().'home.php?mod=spacecp&ac=friend&amp;op=request'
			);
			sendmail_touser($uid, lang('spacecp', 'friend_subject', $values), '', 'friend_add');
			showmessage('request_has_been_sent', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' =>  1));

		} else {
			include_once template('home/spacecp_friend');
			exit();
		}
	}

} elseif($op == 'ignore') {

	if($uid) {
		if(submitcheck('followsubmit')) {

			if(friend_check($uid)) {
				friend_delete($uid);
			} else {
				friend_request_delete($uid);
			}
			showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request', array('uid'=>$uid), array('showdialog'=>1, 'showmsg' => true, 'closetime' =>  1));
		}
	} elseif($_GET['key'] == $space['key']) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_friend_request')." WHERE uid='$_G[uid]'"), 0);
		if($count) {
			DB::delete('home_friend_request', array('uid'=>$_G['uid']));
			space_merge($space, 'count');
			$space['newprompt'] = intval($space['newprompt'] - $count);
			if($space['newprompt'] < 0) {
				$space['newprompt'] = 0;
			}
			DB::query("UPDATE ".DB::table('common_member_status')." SET pendingfriends='0' WHERE uid='$_G[uid]'");
			DB::query("UPDATE ".DB::table('common_member')." SET newprompt='$space[newprompt]' WHERE uid='$_G[uid]'");
		}
		showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request');
	}

} elseif($op == 'addconfirm') {

	if(!checkperm('allowfriend')) {
		showmessage('no_privilege');
	}
	if($_GET['key'] == $space['key']) {

		//$maxfriendnum = checkperm('maxfriendnum');
		$maxfriendnum=300;
		space_merge($space, 'field_home');
		space_merge($space, 'count');

		if($maxfriendnum && $space['friends'] >= $maxfriendnum + $space['addfriend']) {
			if($_G['magic']['friendnum']) {
				showmessage('enough_of_the_number_of_friends_with_magic');
			} else {
				showmessage('enough_of_the_number_of_friends');
			}
		}

		$query = DB::query("SELECT fuid, fusername FROM ".DB::table('home_friend_request')." WHERE uid='$space[uid]' LIMIT 0,1");
		if($value = DB::fetch($query)) {
			friend_add($value['fuid']);
			$value['fusername']=user_get_user_name_by_username($value['fusername']);
			showmessage('friend_addconfirm_next', 'home.php?mod=spacecp&ac=friend&op=addconfirm&key='.$space['key'], array('username' => $value['fusername']), array('showdialog'=>1, 'showmsg' => true, 'closetime' =>  1));
		}
	}

	showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request&quickforward=1');

} elseif($op == 'find') {

	$maxnum = 36;

	$myfuids = $fuids =array();

	$i = 0;
	$query = DB::query("SELECT fuid, fusername FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' ORDER BY num DESC");
	while ($value = DB::fetch($query)) {
		if($i < 100) {
			$fuids[$value['fuid']] = $value['fuid'];
		}
		$myfuids[$value['fuid']] = $value['fuid'];
		$i++;
	}
	$myfuids[$space['uid']] = $space['uid'];

	$i = 0;
	$nearlist = array();
    $nearlistuids = array();
	$myip = explode('.', $_G['clientip']);
	$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE ip1='$myip[0]' AND ip2='$myip[1]' AND ip3='$myip[2]'");
	while($value = DB::fetch($query)) {
		if($value['uid'] && empty($myfuids[$value['uid']])) {
			$nearlist[$value['uid']] = $value;
            $nearlistuids[] = $value[uid];
			$i++;
			if($i>=$maxnum) break;
		}
	}

    $nearlistrealname =  user_get_user_realname($nearlistuids);
    
	$i = 0;
	$friendlist = array();
    $friendlistuids = array();
	if($fuids) {
		$query = DB::query("SELECT fuid AS uid, fusername AS username FROM ".DB::table('home_friend')."
			WHERE uid IN (".dimplode($fuids).") LIMIT 0,200");
		$fuids[$space['uid']] = $space['uid'];
		while ($value = DB::fetch($query)) {
			if(empty($myfuids[$value['uid']])) {
				$friendlist[$value['uid']] = $value;
                $friendlistuids[] = $value[uid];
				$i++;
				if($i>=$maxnum) break;
			}
		}
	}
    $friendlistrealname =  user_get_user_realname($friendlistuids);
    
	$i = 0;
	$onlinelist = array();
    $onlinelistuids = array();
    
	$query = DB::query("SELECT * FROM ".DB::table('common_session'));
	while ($value = DB::fetch($query)) {
		if($value['uid'] && empty($myfuids[$value['uid']]) && !isset($onlinelist[$value['uid']])) {
			$onlinelist[$value['uid']] = $value;
            $onlinelistuids[] = $value[uid];
			$i++;
			if($i>=$maxnum) break;
		}
	}

    $onlinelistrealname = user_get_user_realname($onlinelistuids);
} elseif($op == 'changegroup') {


	$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND fuid='$uid' AND (type=1 or type=3)");
	if(!$friend = DB::fetch($query)) {
		showmessage('specified_user_is_not_your_friend');
	}
	$friends=explode(',',$friend['gids']);
	for($i=1; $i<count($friends); $i++){
		$groupselect[$friends[$i]]='checked';
	}
	$groups = follow_group_list_new();
	for($i=1;$i<count($groups); $i++){
		$newgroups[$i]=$groups[$i];
	}

} elseif($op == 'editnote') {

	if(submitcheck('editnotesubmit')) {
		$note = getstr($_POST['note'], 100);
		DB::update('home_friend', array('note'=>$note), array('uid'=>$_G['uid'], 'fuid'=>$uid));
		showmessage('do_success', dreferer(), array('uid'=>$uid, 'note'=>$note), array('showdialog'=>0, 'showmsg' => true, 'closetime' => 0));
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND fuid='$uid'");
	if(!$friend = DB::fetch($query)) {
		showmessage('specified_user_is_not_your_friend');
	}


} elseif($op == 'changenum') {

	if(submitcheck('changenumsubmit')) {
		$num = abs(intval($_POST['num']));
		if($num > 9999) $num = 9999;
		DB::update('home_friend', array('num'=>$num), array('uid'=>$_G['uid'], 'fuid'=>$uid));
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer(), array('fuid'=>$uid, 'num'=>$num), array('showmsg' => true, 'timeout' => 3, 'return'=>1));
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND fuid='$uid'");
	if(!$friend = DB::fetch($query)) {
		showmessage('specified_user_is_not_your_friend');
	}

} elseif($op == 'group') {

	if(submitcheck('groupsubmin')) {
		if(empty($_POST['fuids'])) {
			showmessage('please_correct_choice_groups_friend', dreferer());
		}
		$ids = dimplode($_POST['fuids']);
		$groupid = intval($_POST['group']);
		DB::update('home_friend', array('gid'=>$groupid), "uid='$_G[uid]' AND fuid IN ($ids)");
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer());
	}

	$perpage = 50;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;

	$list = array();
	$multi = $wheresql = '';

	space_merge($space, 'count');

	if($space['friends']) {

		$groups = friend_group_list();

		$theurl = 'home.php?mod=spacecp&ac=friend&op=group';
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}

		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_friend')." main
			WHERE main.uid='$space[uid]' $wheresql"), 0);
		if($count) {
			$query = DB::query("SELECT main.fuid AS uid,main.fusername AS username, main.gid, main.num FROM ".DB::table('home_friend')." main
				WHERE main.uid='$space[uid]' $wheresql
				ORDER BY main.dateline DESC
				LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$value['group'] = $groups[$value['gid']];
				$value[username]=user_get_user_name_by_username($value[username]);
				$list[] = $value;
			}
		}
		$multi = multi($count, $perpage, $page, $theurl);
	}
	$actives = array('group'=>' class="a"');



} elseif($op == 'request') {
    
	
	//显示用户所在省公司王聪
	//显示用户所在省公司乔永志
	require_once libfile('function/space');
	
	if(submitcheck('requestsubmin')) {
		showmessage('do_success', dreferer());
	}

	$maxfriendnum = checkperm('maxfriendnum');
	if($maxfriendnum) {
		$maxfriendnum = $maxfriendnum + $space['addfriend'];
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;

	$list = array();
    $listuserrealnameuids = array();
    $regnames="";
    $listProvice=array();
	$count = getcount('home_friend_request', array('uid'=>$space['uid']));
	if($count) {
		$fuids = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_friend_request')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
		while ($value = DB::fetch($query)) {
			$fuids[$value['fuid']] = $value['fuid'];
			$list[$value['fuid']] = $value;
            $listuserrealnameuids[] = $value['fuid'];
            $progroup=getprogroup($value['fusername'],false);
			if($progroup[groupname]){
				$listProvice[$value['fuid']] = '['.$progroup[groupname].']';
			}else{
				$regnames.=$value['fusername'];
				$regnames.=",";
			}
            //$listProvice[$value['fuid']]=getUserGroupByuserId($value['fuid']);
		}
		$regnames=substr($regnames,0,-1);
	} else {

		space_merge($space, 'status');

		if($space['pendingfriends'] > 0) {
			DB::query("UPDATE ".DB::table('common_member_status')." SET pendingfriends=0 WHERE uid='$space[uid]'");
			$newprompt = $space['newprompt'] - $space['pendingfriends'];
			if($newprompt<1) $newprompt = 0;
			DB::query("UPDATE ".DB::table('common_member')." SET newprompt='$newprompt' WHERE uid='$space[uid]'");
		}

	}


    $listuserrealname = user_get_user_realname($listuserrealnameuids);
    
	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=friend&op=request");



} elseif($op == 'groupname') {

	$groups = friend_group_list();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}
	space_merge($space, 'field_home');
	if(submitcheck('groupnamesubmit')) {
		$space['privacy']['groupname'][$group] = getstr($_POST['groupname'], 20, 1, 1);
		privacy_update();
		showmessage('do_success', dreferer(), array('gid'=>$group), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
	}
} elseif($op == 'groupignore') {

	$groups = friend_group_list();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}
	space_merge($space, 'field_home');
	if(submitcheck('groupignoresubmit')) {
		if(isset($space['privacy']['filter_gid'][$group])) {
			unset($space['privacy']['filter_gid'][$group]);
			$ignore = false;
		} else {
			$space['privacy']['filter_gid'][$group] = $group;
			$ignore = true;
		}
		privacy_update();
		friend_cache($_G['uid']);

		showmessage('do_success', dreferer(), array('group' => $group, 'ignore' => $ignore), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
	}

} elseif($op == 'blacklist') {

	if($_GET['subop'] == 'delete') {
		$_GET['uid'] = intval($_GET['uid']);
		DB::query("DELETE FROM ".DB::table('home_blacklist')." WHERE uid='$space[uid]' AND buid='$_GET[uid]'");
		showmessage('do_success', "home.php?mod=space&do=friend&view=blacklist&quickforward=1&start=$_GET[start]");
	}

	if(submitcheck('blacklistsubmit')) {
		$_POST['username'] = trim($_POST['username']);
		$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE username='$_POST[username]'");
		if(!$tospace = DB::fetch($query)) {
			showmessage('space_does_not_exist');
		}
		if($tospace['uid'] == $space['uid']) {
			showmessage('unable_to_manage_self');
		}

		friend_delete($tospace['uid']);

		DB::insert('home_blacklist', array('uid'=>$space['uid'], 'buid'=>$tospace['uid'], 'dateline'=>$_G['timestamp']), 0, true);

		showmessage('do_success', "home.php?mod=space&do=friend&view=blacklist&quickforward=1&start=$_GET[start]");
	}

} elseif($op == 'rand') {

	$userlist = $randuids = array();
	space_merge($space, 'count');
	if($space['friends']<5) {
		$query = DB::query("SELECT uid FROM ".DB::table('common_session')." LIMIT 0,100");
	} else {
		$query = DB::query("SELECT fuid as uid FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]'");
	}
	while($value = DB::fetch($query)) {
		if($value['uid'] != $space['uid']) {
			$userlist[] = $value['uid'];
		}
	}
	$randuids = sarray_rand($userlist, 1);
	showmessage('do_success', "home.php?mod=space&quickforward=1&uid=".array_pop($randuids));

} elseif ($op == 'getcfriend') {

	$fuid = empty($_GET['fuid']) ? 0 : intval($_GET['fuid']);

	$list = array();
	if($fuid) {
		$friend = $friendlist = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' OR uid='$fuid'");
		while($value = DB::fetch($query)) {
			$friendlist[$value['uid']][] = $value['fuid'];
			$friend[$value['fuid']] = $value;
		}
		if($friendlist[$_G['uid']] && $friendlist[$fuid]) {
			$cfriend = array_intersect($friendlist[$_G['uid']], $friendlist[$fuid]);
			$i = 0;
			foreach($cfriend as $key => $uid) {
				if(isset($friend[$uid])) {
					$list[] = array('uid' => $friend[$uid]['fuid'], 'username' => $friend[$uid]['fusername']);
					$i++;
					if($i >= 15) break;
				}
			}
		}

	}
} elseif($op == 'getinviteuser') {

	$perpage = 20;

	$page = empty($_G['gp_page'])?0:intval($_G['gp_page']);
	if($page<1) $page = 1;
	$start = ($page-1) * $perpage;
	$json = array();
	$wheresql = '';
	if($_G['gp_gid']) {
		$gid = intval($_G['gp_gid']);
		$wheresql = " AND gid='$gid'";
	}
	$singlenum = 0;
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' $wheresql"), 0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' $wheresql ORDER BY num DESC, dateline DESC LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {
			$value['fusername'] = daddslashes($value['fusername']);
			$value['fusername'] = user_get_user_name($value['fuid']);
			$value['avatar'] = avatar($value['fuid'], 'small', true);
			$singlenum++;
			$json[$value['fuid']] = "$value[fuid]:{'uid':$value[fuid], 'username':'$value[fusername]', 'avatar':'$value[avatar]'}";
		}
	}
	$jsstr = "{'userdata':{".implode(',', $json)."}, 'maxfriendnum':'$count', 'singlenum':'$singlenum'}";

} elseif($op == 'getinvitegroupuser') {

	$perpage = 20;

	$page = empty($_G['gp_page'])?0:intval($_G['gp_page']);
	if($page<1) $page = 1;
	$start = ($page-1) * $perpage;
	$json = array();
	$fid = intval($_G['gp_fid']);
	
	if ($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= " AND cmp.realname like '%$searchkey%'";
	}
	
	$singlenum = 0;
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_groupuser')." fg,".DB::table('common_member_profile')." cmp WHERE fid=$fid and fg.uid!=$_G[uid] and fg.uid=cmp.uid $wheresql"), 0);
	if($count) {
		$query = DB::query("SELECT fg.*,cmp.realname FROM ".DB::table('forum_groupuser')." fg,".DB::table('common_member_profile')." cmp WHERE fid=$fid and fg.uid!=$_G[uid] and fg.uid=cmp.uid $wheresql  ORDER BY joindateline DESC LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {
			$value['username'] = daddslashes($value['username']);
			//$value['username'] = user_get_user_name($value['uid']);
			$value['avatar'] = avatar($value['uid'], 'small', true);
			$singlenum++;
			$json[$value['uid']] = "$value[uid]:{'uid':$value[uid], 'username':'$value[realname]', 'avatar':'$value[avatar]'}";
		}
	}
	$jsstr = "{'userdata':{".implode(',', $json)."}, 'maxfriendnum':'$count', 'singlenum':'$singlenum'}";

} elseif($op == 'search') {
	$searchkey = stripsearchkey($_GET['searchkey']);
	if(strlen($searchkey) < 2) {
		showmessage('username_less_two_chars');
	}

	$list = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile')." LEFT JOIN ".DB::table('common_member')." using(uid) WHERE realname LIKE '%$searchkey%' LIMIT 0,100");
	while ($value = DB::fetch($query)) {
		$value[org]=getOrgNameByUser($value[username]);
		$value[station]=getStationByUser($value[uid]);
		$value[username]=user_get_user_realname($value[username]);
		$list[$value['uid']] = $value;	
	}
	$listrealname = array();
	foreach($list as $item){
		$listrealname[] = $item[uid];
	}
	$listrealname =  user_get_user_realname($listrealname);
}elseif($op=='create'){
	
}

include template('home/spacecp_follow');

?>