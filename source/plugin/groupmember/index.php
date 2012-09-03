<?php
header("location:forum.php?mod=group&action=memberlist&fid=".$_G[fid]);
exit;

define('APPTYPEID', 2);
define('CURSCRIPT', 'forum');


$discuz = & discuz_core::instance();

$modarray = array('ajax','announcement','attachment','forumdisplay',
'group','image','index','medal','misc','modcp','notice','post','redirect',
'relatekw','relatethread','rss','search','topicadmin','trade','viewthread'
);

$modcachelist = array(
'index'		=> array('announcements', 'onlinelist', 'forumlinks', 'advs_index',
'heats', 'historyposts', 'onlinerecord', 'blockclass', 'userstats'),
'forumdisplay'	=> array('smilies', 'announcements_forum', 'globalstick', 'forums',
'icons', 'onlinelist', 'forumstick', 'blockclass',
'threadtable_info', 'threadtableids'),
'viewthread'	=> array('smilies', 'smileytypes', 'forums', 'usergroups', 'ranks',
'stamps', 'bbcodes', 'smilies',
'custominfo', 'groupicon', 'stamps', 'threadtableids', 'threadtable_info'),
'post'		=> array('bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes',
'icons', 'domainwhitelist'),
'space'		=> array('fields_required', 'fields_optional', 'custominfo'),
'group'		=> array('grouptype'),
);

$mod = !in_array($discuz->var['mod'], $modarray) ? 'index' : $discuz->var['mod'];

define('CURMODULE', $mod != 'redirect' ? $mod : 'viewthread');
$cachelist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}

$discuz->cachelist = $cachelist;
$discuz->init();

loadforum();
set_rssauth();
runhooks();






if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}

require_once libfile('function/group');
$_G['action']['action'] = 3;
$_G['action']['fid'] = $_G['fid'];
$_G['basescript'] = 'group';
$_G[forum][my_empirical]=group_get_empirical($_G['uid'],$_G['fid']);//added by fumz,2010-8-26 9:52:22

$actionarray = array('join', 'out', 'create', 'viewmember', 'manage', 'index', 'memberlist', 'plugin');
$action = getgpc('action') && in_array($_G['gp_action'], $actionarray) ? $_G['gp_action'] : 'index';
if(in_array($action, array('join', 'out', 'create', 'manage'))) {
	if(empty($_G['uid'])) {
		showmessage('not_loggedin', '', '', array('login' => 1));
	}
}
if(empty($_G['fid']) && $action != 'create') {
	showmessage('group_rediret_now', 'group.php');
}
$first = &$_G['cache']['grouptype']['first'];
$second = &$_G['cache']['grouptype']['second'];
$rssauth = $_G['rssauth'];
$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="'.$_G['setting']['bbname'].' - '.$navtitle.'" href="'.$_G['siteurl'].'forum.php?mod=rss&fid='.$_G['fid'].'&amp;auth='.$rssauth."\" />\n") : '';
$navtitle = lang('group/template', 'group');
if($_G['fid']) {
	$navtitle .= ' - '.$_G['forum']['name'];
	$metakeywords = $_G['forum']['metakeywords'];
	$metadescription = $_G['forum']['metadescription'];
	if($_G['forum']['status'] != 3) {
		showmessage('forum_not_group', 'group.php');
	} elseif($_G['forum']['jointype'] < 0 && !$_G['forum']['ismoderator']) {
		showmessage('forum_group_status_off', 'group.php');
	}
	$groupcache = getgroupcache($_G['fid'], array('replies', 'views', 'digest', 'lastpost', 'ranking', 'activityuser', 'newuserlist'), 604800);

	$_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
	$_G['forum']['banner'] = get_groupimg($_G['forum']['banner']);
	$_G['forum']['dateline'] = dgmdate($_G['forum']['dateline'], 'd');
	$_G['forum']['posts'] = intval($_G['forum']['posts']);
	$_G['grouptypeid'] = $_G['forum']['fup'];
	$groupuser = DB::fetch_first("SELECT * FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
	$onlinemember = grouponline($_G['fid'], 1);
	$groupmanagers = $_G['forum']['moderators'];
    
    $groupuser = DB::fetch_first("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
    $_G["forum"]["my_empirical"] = $groupuser["empirical_value"];
    
    if ($result["group_type"] >= 1 && $result["group_type"] < 20) {
        $_G['forum']['type_icn_id'] = 'brzone_l';
    } elseif ($result["group_type"] >= 20 && $result["group_type"] < 60) {
        $_G['forum']['type_icn_id'] = 'bizone_l';
    }
}
if($action=='plugin'){//added by fumz
	$action="memberlist";
}
//exit($action."早安，中国！groupmember index.php");

if(in_array($action, array('out', 'viewmember', 'manage', 'index', 'memberlist'))) {
	
	$status = groupperm($_G['forum'], $_G['uid'], $action, $groupuser);
	if($status == -1) {
		showmessage('forum_not_group', 'group.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	}
	if($action != 'index') {
		if($status == 2) {
			showmessage('forum_group_noallowed', "forum.php?mod=group&fid=$_G[fid]");
		} elseif($status == 3) {
			showmessage('forum_group_moderated', "forum.php?mod=group&fid=$_G[fid]");
		}
	}
}

if(in_array($action, array('index')) && $status != 2) {

	$newuserlist = $activityuserlist = array();
	foreach($groupcache['newuserlist']['data'] as $user) {
		$newuserlist[$user['uid']] = $user;
		$newuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
	}

	$activityuser = array_slice($groupcache['activityuser']['data'], 0, 8);
	foreach($activityuser as $user) {
		$activityuserlist[$user['uid']] = $user;
		$activityuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
	}

	$groupviewed_list = get_viewedgroup();

}

$groupnav = get_groupnav($_G['forum']);

$showpoll = $showtrade = $showreward = $showactivity = $showdebate = 0;
if($_G['forum']['allowpostspecial']) {
	$showpoll = $_G['forum']['allowpostspecial'] & 1;
	$showtrade = $_G['forum']['allowpostspecial'] & 2;
	$showreward = isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($_G['forum']['allowpostspecial'] & 4);
	$showactivity = $_G['forum']['allowpostspecial'] & 8;
	$showdebate = $_G['forum']['allowpostspecial'] & 16;
}

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && $showpoll;
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && $showtrade;
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && $showreward;
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && $showactivity;
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && $showdebate;
}
if($_G["fid"]){
    group_load_plugin($_G["fid"]);
}
if($action == 'index') {

	$newthreadlist = array();
	if($status != 2) {
		require_once libfile('function/feed');
		$newthreadlist = getgroupcache($_G['fid'], array('dateline'), 0, 10, 0, 1);
		foreach($newthreadlist['dateline']['data'] as $tid => $thread) {
			if($thread['closed']) {
				$newthreadlist['dateline']['data'][$tid]['folder'] = 'lock';
			} elseif(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$tid.'D') === FALSE) {
				$newthreadlist['dateline']['data'][$tid]['folder'] = 'new';
			} else {
				$newthreadlist['dateline']['data'][$tid]['folder'] = 'common';
			}
		}

       $groupfeedlist = feed_activity_user(array_keys($groupcache['activityuser']['data']));
	} else {
		$newuserlist = $activityuserlist = array();
		$newuserlist = array_slice($groupcache['newuserlist']['data'], 0, 4);
		foreach($newuserlist as $user) {
			$newuserlist[$user['uid']] = $user;
			$newuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
		}
	}

	write_groupviewed($_G['fid']);
	//include template('diy:group/group:'.$_G['fid']);
} elseif($action=="plugin"){
    include join_group_pluginmodule($_G['gp_plugin_name'], $_G['gp_plugin_op']);
    //include template('diy:group/group:'.$_G['fid']);
} elseif($action == 'memberlist') {

	$oparray = array('card', 'address', 'alluser');
	$op = getgpc('op') && in_array($_G['gp_op'], $oparray) ?  $_G['gp_op'] : 'alluser';
	$page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
	$perpage = 48;
	$start = ($page - 1) * $perpage;

	$alluserlist = $adminuserlist = array();
	$staruserlist = $page < 2 ? groupuserlist($_G['fid'], 'lastupdate', 0, 0, array('level' => '3'), array('uid', 'username', 'level', 'joindateline', 'lastupdate')) : '';
	$adminlist = $groupmanagers && $page < 2 ? $groupmanagers : array();

	if($op == 'alluser') {
		$alluserlist = groupuserlist($_G['fid'], 'lastupdate', $perpage, $start, "AND level='4'", '', $onlinemember['list']);
		$multipage = multi($_G['forum']['membernum'], $perpage, $page, 'forum.php?mod=group&action=memberlist&op=alluser&fid='.$_G['fid']);

		if($adminlist) {
			foreach($adminlist as $user) {
				//获取真实姓名
				$user['realname'] = user_get_user_name($user['uid']);
				$adminuserlist[$user['uid']] = $user;
				$adminuserlist[$user['uid']]['online'] = $onlinemember['list'] && is_array($onlinemember['list']) && $onlinemember['list'][$user['uid']] ? 1 : 0;
			}
		}
	}

	//include template('diy:group/group:'.$_G['fid']);

} elseif($action == 'join') {
	$inviteuid = 0;
	$membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
	if(!empty($membermaximum)) {
		$curnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]'");
		if($curnum >= $membermaximum) {
			showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
		}
	}
	if($groupuser['uid']) {
		showmessage('group_has_joined', "forum.php?mod=group&fid=$_G[fid]");
	} else {
		$modmember = 4;
		$showmessage = 'group_join_succeed';
		$confirmjoin = TRUE;
		$inviteuid = DB::result_first("SELECT uid FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
		
		if($_G['forum']['jointype'] == 3) {
			if(!$inviteuid) {
				$confirmjoin = FALSE;
				$showmessage = 'group_join_forbid_anybody';
			}
		}
		if($_G['forum']['jointype'] == 1) {
			if(!$inviteuid) {
				$confirmjoin = FALSE;
				$showmessage = 'group_join_need_invite';
			}
		} elseif($_G['forum']['jointype'] == 2) {
			$modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
			!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
		}

		if($confirmjoin) {
			DB::query("INSERT INTO ".DB::table('forum_groupuser')." (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$_G[uid]', '$_G[username]', '$modmember', '".TIMESTAMP."', '".TIMESTAMP."')", 'UNBUFFERED');
			if($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
				foreach($groupmanagers as $manage) {
					notification_add($manage['uid'], 'group', 'group_member_join', array('url' => $_G['siteurl'].'forum.php?mod=group&action=manage&op=checkuser&fid='.$_G['fid']), 1);
				}
			} else {
				update_usergroups($_G['uid']);
			}
			if($inviteuid) {
				DB::query("DELETE FROM ".DB::table('forum_groupinvite')." WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
			}
			if($modmember == 4) {
				DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+1 WHERE fid='$_G[fid]'");
			}
			updateactivity($_G['fid'], 0);
		}
		include_once libfile('function/stat');
		updatestat('groupjoin');
		delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
		showmessage($showmessage, "forum.php?mod=group&fid=$_G[fid]");
	}

} elseif($action == 'out') {

	if($_G['uid'] == $_G['forum']['founderuid']) {
		showmessage('group_exit_founder');
	}
	$showmessage = 'group_exit_succeed';
		DB::query("DELETE FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+'-1' WHERE fid='$_G[fid]'");
	update_usergroups($_G['uid']);
	delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
	showmessage($showmessage, "forum.php?mod=forumdisplay&fid=$_G[fid]");

} elseif($action == 'create') {

	if(!$_G['group']['allowbuildgroup']) {
		showmessage('group_create_usergroup_failed', "group.php");
	}
	$groupnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_groupuser')." WHERE uid='$_G[uid]' AND level='1'");
	$allowbuildgroup = $_G['group']['allowbuildgroup'] - $groupnum;
	if($allowbuildgroup < 1) {
		showmessage('group_create_max_failed');
	}

	if(!submitcheck('createsubmit')) {
		$groupselect = get_groupselect(getgpc('fupid'), getgpc('groupid'));
	} else {
		$_G['gp_name'] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($_G['gp_name'])), 20, ''))));
		if(empty($_G['gp_name'])) {
			showmessage('group_name_empty');
		} elseif(empty($_G['gp_fup'])) {
			showmessage('group_category_empty');
		}
		if(empty($_G['cache']['grouptype']['second'][$_G['gp_fup']])) {
			showmessage('group_category_error');
		}
		if(DB::result(DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE name='$_G[gp_name]' AND type='sub' "), 0)) {
			showmessage('group_name_exist');
		}

		$levelid = DB::result_first("SELECT levelid FROM ".DB::table('forum_grouplevel')." WHERE creditshigher<='0' AND '0'<creditslower LIMIT 1");

		DB::query("INSERT INTO ".DB::table('forum_forum')."(fup, type, name, status, level) VALUES ('$_G[gp_fup]', 'sub', '$_G[gp_name]', '3', '$levelid')");
		$newfid = DB::insert_id();
		if($newfid) {
			$jointype = intval($_G['gp_jointype']);
			$gviewperm = intval($_G['gp_gviewperm']);
			$descriptionnew = dhtmlspecialchars(censor(trim($_G['gp_descriptionnew'])));
			$tagnew = dhtmlspecialchars(censor(trim($_G['gp_tagnew'])));
			DB::query("INSERT INTO ".DB::table('forum_forumfield')."(fid, description, jointype, gviewperm, dateline, founderuid, foundername, membernum, tag) VALUES ('$newfid', '$descriptionnew', '$jointype', '$gviewperm', '".TIMESTAMP."', '$_G[uid]', '$_G[username]', '1', '$tagnew')");
			DB::query("UPDATE ".DB::table('forum_forumfield')." SET groupnum=groupnum+1 WHERE fid='$_G[gp_fup]'");
			DB::query("INSERT INTO ".DB::table('forum_groupuser')."(fid, uid, username, level, joindateline) VALUES ('$newfid', '$_G[uid]', '$_G[username]', '1', '".TIMESTAMP."')");
			group_create_empirical($newfid);
            require_once libfile('function/cache');
			updatecache('grouptype');
		}
		include_once libfile('function/stat');
		updatestat('group');
		showmessage('group_create_succeed', "forum.php?mod=group&action=manage&fid=$newfid", array(), array('msgtype' => 3));
	}

	//include template('diy:group/group:'.$_G['fid']);

} elseif($action == 'manage'){
	if(!$_G['forum']['ismoderator']) {
		showmessage('group_admin_noallowed');
	}
	$specialswitch = $_G['current_grouplevel']['specialswitch'];

	$oparray = array('group', 'checkuser', 'manageuser', 'threadtype', 'demise', 'manageplugin', 'manageempirical');
	$_G['gp_op'] = getgpc('op') && in_array($_G['gp_op'], $oparray) ?  $_G['gp_op'] : 'group';
	if(empty($groupmanagers[$_G[uid]]) && $_G['gp_op'] != 'group') {
		showmessage('group_admin_noallowed');
	}
	$page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
	$perpage = 48;
	$start = ($page - 1) * $perpage;
	$url = 'forum.php?mod=group&action=manage&op='.$_G['gp_op'].'&fid='.$_G['fid'];
	if($_G['gp_op'] == 'group') {
		if(submitcheck('groupmanage')) {
			if(($_G['gp_name'] && !empty($specialswitch['allowchangename'])) || ($_G['gp_fup'] && !empty($specialswitch['allowchangetype']))) {
				if($_G['uid'] != $_G['forum']['founderuid']) {
					showmessage('group_edit_only_founder');
				}

				if(isset($_G['gp_name'])) {
					$_G['gp_name'] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($_G['gp_name'])), 20, ''))));
					if(empty($_G['gp_name'])) showmessage('group_name_empty');
				} elseif(isset($_G['gp_fup']) && empty($_G['gp_fup'])) {
					showmessage('group_category_empty');
				}

				$addsql  = '';
				if(!empty($_G['gp_name']) && $_G['gp_name'] != $_G['forum']['name']) {
					if(DB::result(DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE name='$_G[gp_name]' AND type='sub' "), 0)) {
						showmessage('group_name_exist', $url);
					}
					$addsql = "name = '$_G[gp_name]'";
				}

				if(intval($_G['gp_fup']) != $_G['forum']['fup']) {
					$addsql .= $addsql ? ', ' : '';
					$addsql .= "fup = '".intval($_G['gp_fup'])."'";
				}
				if($addsql) {
					DB::query("UPDATE ".DB::table('forum_forum')." SET $addsql WHERE fid='$_G[fid]'");
				}
			}
			if(in_array($_G['adminid'], array(1, 2)) && $_G['gp_recommend'] != $_G['forum']['recommend']) {
				DB::query("UPDATE ".DB::table('forum_forum')." SET recommend='".intval($_G['gp_recommend'])."' WHERE fid='$_G[fid]'");
				require_once libfile('function/cache');
				updatecache('forumrecommend');
			}

			$iconsql = '';
			$deletebanner = $_G['gp_deletebanner'];
			$iconnew = upload_icon_banner($_G['forum'], $_FILES['iconnew'], 'icon');
			$bannernew = upload_icon_banner($_G['forum'], $_FILES['bannernew'], 'banner');
			if($iconnew) {
				$iconsql .= ", icon='$iconnew'";
			}
			if($bannernew && empty($deletebanner)) {
				$iconsql .= ", banner='$bannernew'";
			} elseif($deletebanner) {
				$iconsql .= ", banner=''";
				@unlink($_G['forum']['banner']);
			}
			$_G['gp_descriptionnew'] = nl2br(dhtmlspecialchars(censor(trim($_G['gp_descriptionnew']))));
			$_G['gp_tagnew'] = nl2br(dhtmlspecialchars(censor(trim($_G['gp_tagnew']))));
			$_G['gp_jointypenew'] = intval($_G['gp_jointypenew']);
			if($_G['gp_jointypenew'] == '-1' && $_G['uid'] != $_G['forum']['founderuid']) {
				showmessage('group_close_only_founder');
			}
			$_G['gp_gviewpermnew'] = intval($_G['gp_gviewpermnew']);
			DB::query("UPDATE ".DB::table('forum_forumfield')." SET description='$_G[gp_descriptionnew]', tag='$_G[gp_tagnew]', jointype='$_G[gp_jointypenew]', gviewperm='$_G[gp_gviewpermnew]'$iconsql WHERE fid='$_G[fid]'");
			showmessage('group_setup_succeed', $url);
		} else {
			$firstgid = $_G['cache']['grouptype']['second'][$_G['forum']['fup']]['fup'];
			$groupselect = get_groupselect($firstgid, $_G['forum']['fup']);
			$gviewpermselect = $jointypeselect = array('','','');
			$_G['forum']['descriptionnew'] = str_replace("<br />", '', $_G['forum']['description']);
			$_G['forum']['tagnew'] = str_replace("<br />", '', $_G['forum']['tag']);
			$jointypeselect[$_G['forum']['jointype']] = 'checked="checked"';
			$gviewpermselect[$_G['forum']['gviewperm']] = 'checked="checked"';
			require_once libfile('function/forumlist');
			$forumselect = forumselect(FALSE, 0, $_G['forum']['recommend']);
		}
	} elseif($_G['gp_op'] == 'checkuser') {
		$checktype = 0;
		$checkusers = array();
		if(!empty($_G['gp_uid'])) {
			$checkusers = array($_G['gp_uid']);
			$checktype = intval($_G['gp_checktype']);
		} elseif(getgpc('checkall') == 1 || getgpc('checkall') == 2) {
			$checktype = $_G['gp_checkall'];
			$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND level='0'");
			while($row = DB::fetch($query)) {
				$checkusers[] = $row['uid'];
			}
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				notification_add($uid, 'group', 'group_member_check', array('groupname' => $_G['forum']['name'], 'url' => $_G['siteurl'].'forum.php?mod=group&fid='.$_G['fid']), 1);
			}
			if($checktype == 1) {
				DB::query("UPDATE ".DB::table('forum_groupuser')." SET level='4' WHERE uid IN(".dimplode($checkusers).") AND fid='$_G[fid]'");
				update_usergroups($checkusers);
				DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+".count($checkusers)." WHERE fid='$_G[fid]'");
			} elseif($checktype == 2) {
				DB::query("DELETE FROM ".DB::table('forum_groupuser')." WHERE uid IN(".dimplode($checkusers).") AND fid='$_G[fid]'");
			}
			if($checktype == 1) {
				showmessage('group_moderate_succeed', $url);
			} else {
				showmessage('group_moderate_failed', $url);
			}
		} else {
			$checkusers = array();
			$userlist = groupuserlist($_G['fid'], 'joindateline', $perpage, $start, array('level' => 0));
			$checknum = DB::result(DB::query("SELECT count(*) FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND level='0'"), 0);
			$multipage = multi($checknum, $perpage, $page, $url);
			foreach($userlist as $user) {
				$user['joindateline'] = date('Y-m-d H:i', $user['joindateline']);
				$checkusers[$user['uid']] = $user;
			}
		}
	} elseif($_G['gp_op'] == 'manageplugin'){
        if($_G["gp_m"]=="disable"){
            //屏蔽
            $data["fid"] = $_G["gp_fid"];
            if($_G["gp_plugin_id"]){
                $data["plugin_id"] = $_G["gp_plugin_id"];
                DB::insert('common_group_plugin', $data);
                require_once libfile('function/cache');
                //updatecache("group_plugins_".$_G["fid"]);
                showmessage("关闭成功", $url);
            }
        }elseif($_G["gp_m"]=="enable"){
            //激活
            DB::delete("common_group_plugin", array(fid=>$_G["gp_fid"], plugin_id=>$_G["gp_plugin_id"]));
            require_once libfile('function/cache');
            //updatecache("group_plugins_".$_G["fid"]);
            showmessage("开启成功", $url);
        }
    } elseif($_G['gp_op'] == 'manageempirical'){
        $t_ac = $_G["gp_m"]?$_G["gp_m"]:"index";
        if($t_ac=="index"){
            group_create_empirical($_G["gp_fid"]);
            $query = DB::query("SELECT gev.id,gev.value,ge.name,ge.ename FROM ".DB::table("group_empirical")."  ge,".DB::table("group_empirical_values")." gev WHERE ge.id=gev.eid AND gev.fid=".$_G["gp_fid"]);
            $group_empirical_op_settings = array();
            while($row=DB::fetch($query)){
                $group_empirical_op_settings[$row["id"]] = $row;
            }
        }elseif($t_ac=="save"){
            foreach($_G["gp_values"] as $key=>$values){
                DB::query("UPDATE ".DB::table("group_empirical_values")." SET value=".$values." WHERE id=".$key." AND fid=".$_G["gp_fid"]);
            }
            showmessage("更新经验值列表成功", $url);
        }
    } elseif($_G['gp_op'] == 'manageuser') {
		$mtype = array(1 => lang('group/template', 'group_moderator'), 3 => lang('group/template', 'group_star_member'), 4 => lang('group/template', 'group_normal_member'), 5 => lang('group/template', 'group_goaway'));
		if(!submitcheck('manageuser')) {
			$userlist = array();
			$staruserlist = $page < 2 ? groupuserlist($_G['fid'], '', 0, 0, array('level' => '3'), array('uid', 'username', 'level', 'joindateline', 'lastupdate')) : '';
			$adminuserlist = $groupmanagers && $page < 2 ? $groupmanagers : array();
			$userlist = groupuserlist($_G['fid'], '', $perpage, $start, "AND level='4'");
			$multipage = multi($_G['forum']['membernum'], $perpage, $page, $url);
		} else {
			if(count($groupuser) == 1) {
				showmessage('group_admin_only_one_member', $url);
			}
			$muser = getgpc('muid');
			$targetlevel = $_G['gp_targetlevel'];
			if($muser && is_array($muser)) {
				foreach($muser as $muid => $mlevel) {
					if($_G['forum']['founderuid'] != $_G['uid'] && $groupmanagers[$muid] && $groupmanagers[$muid]['level'] <= $groupuser['level']) {
						showmessage('group_member_level_admin_noallowed.', $url);
					}
					if($muid != $_G['uid'] && ($_G['forum']['founderuid'] == $_G['uid'] || !$groupmanagers[$muid] || $groupmanagers[$muid]['level'] > $groupuser['level'])) {
						if($targetlevel != 5) {
							DB::query("UPDATE ".DB::table('forum_groupuser')." SET level='$targetlevel' WHERE uid='$muid' AND fid='$_G[fid]'");
						} else {
							if(!$groupmanagers[$muid] || count($groupmanagers) > 1) {
								DB::query("DELETE FROM ".DB::table('forum_groupuser')." WHERE uid='$muid' AND fid='$_G[fid]'");
								DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum=membernum+'-1' WHERE fid='$_G[fid]'");
								update_usergroups($muid);
							} else {
								showmessage('group_only_one_moderator', $url);
							}
						}
					}
				}
				update_groupmoderators($_G['fid']);
				showmessage('group_setup_succeed', $url.'&page='.$page);
			} else {
				showmessage('group_choose_member', $url);
			}
		}
	} elseif($_G['gp_op'] == 'threadtype') {
		if(empty($specialswitch['allowthreadtype'])) {
			showmessage('group_level_cannot_do');
		}
		if($_G['forum']['founderuid'] != $_G['uid']) {
			showmessage('group_threadtype_only_founder');
		}
		$typenumlimit = 20;
		if(!submitcheck('groupthreadtype')) {
			$threadtypes = $checkeds = array();
			if(empty($_G['forum']['threadtypes'])) {
				$checkeds['status'][0] = 'checked';
				$display = 'none';
			} else {
				$display = '';
				$_G['forum']['threadtypes']['status'] = 1;
				foreach($_G['forum']['threadtypes'] as $key => $val) {
					$val = intval($val);
					$checkeds[$key][$val] = 'checked';
				}
			}

			$query = DB::query("SELECT * FROM ".DB::table('forum_threadclass')." WHERE fid='{$_G['fid']}' ORDER BY displayorder");
			while($type = DB::fetch($query)) {
				$type['enablechecked'] = isset($_G['forum']['threadtypes']['types'][$type['typeid']]) ? ' checked="checked"' : '';
				$type['name'] = dhtmlspecialchars($type['name']);
				$threadtypes[] = $type;
			}
		} else {
			$threadtypesnew = $_G['gp_threadtypesnew'];
			$threadtypesnew['types'] = $threadtypes['special'] = $threadtypes['show'] = array();
			if(is_array($_G['gp_newname']) && $_G['gp_newname']) {
				$newname = array_unique($_G['gp_newname']);
				if($newname) {
					foreach($newname as $key => $val) {
						$val = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($val)), 16, ''))));
						if($_G['gp_newenable'][$key] && $val) {
							$newtypeid = DB::result_first("SELECT typeid FROM ".DB::table('forum_threadclass')." WHERE fid='{$_G['fid']}' AND name='$val'");
							if(!$newtypeid) {
								$typenum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_threadclass')." WHERE fid='{$_G['fid']}'");
								if($typenum < $typenumlimit) {
									$threadtypes_newdisplayorder = intval($_G['gp_newdisplayorder'][$key]);
									$newtypeid = DB::insert('forum_threadclass', array('fid' => $_G['fid'], 'name' => $val, 'displayorder' => $threadtypes_newdisplayorder), 1);
								}
							}
							if($newtypeid) {
								$threadtypesnew['types'][$newtypeid] = $val;
							}
						}
					}
				}
				$threadtypesnew['status'] = 1;
			} else {
				$newname = array();
			}
			if($threadtypesnew['status']) {
				if(is_array($threadtypesnew['options']) && $threadtypesnew['options']) {

					if(!empty($threadtypesnew['options']['enable'])) {
						$typeids = implodeids(array_keys($threadtypesnew['options']['enable']));
					} else {
						$typeids = '0';
					}
					if(!empty($threadtypesnew['options']['delete'])) {
						$threadtypes_deleteids = implodeids($threadtypesnew['options']['delete']);
						DB::query("DELETE FROM ".DB::table('forum_threadclass')." WHERE `typeid` IN ($threadtypes_deleteids) AND fid='{$_G['fid']}'");
					}
					$query = DB::query("SELECT * FROM ".DB::table('forum_threadclass')." WHERE typeid IN ($typeids) AND fid='{$_G['fid']}' ORDER BY displayorder");
					while($type = DB::fetch($query)) {
						if($threadtypesnew['options']['enable'][$type['typeid']]) {
							$threadtypesnew['types'][$type['typeid']] = $threadtypesnew['options']['name'][$type['typeid']];
						}
						if($threadtypesnew['options']['name'][$type['typeid']] != $type['name'] || $threadtypesnew['options']['displayorder'][$type['typeid']] != $type['displayorder']) {
							$threadtypesnew['options']['name'][$type['typeid']] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($threadtypesnew['options']['name'][$type['typeid']])), 16, ''))));
							$threadtypesnew['options']['displayorder'][$type['typeid']] = intval($threadtypesnew['options']['displayorder'][$type['typeid']]);
							DB::update('forum_threadclass', array(
								'name' => $threadtypesnew['options']['name'][$type['typeid']],
								'displayorder' => $threadtypesnew['options']['displayorder'][$type['typeid']],
							), array('typeid' => "{$type['typeid']}", 'fid' => "{$_G['fid']}"));
						}
						$threadtypesnew['icons'][$type['typeid']] = trim($threadtypesnew['options']['icon'][$type['typeid']]);
					}
				}
				$threadtypesnew = $threadtypesnew['types'] ? addslashes(serialize(array
					(
					'required' => (bool)$threadtypesnew['required'],
					'listable' => (bool)$threadtypesnew['listable'],
					'prefix' => $threadtypesnew['prefix'],
					'types' => $threadtypesnew['types']
					))) : '';
			} else {
				$threadtypesnew = '';
			}
			DB::update('forum_forumfield', array('threadtypes' => $threadtypesnew), "fid='{$_G['fid']}'");
			showmessage('group_threadtype_edit_succeed', $url);
		}
	} elseif($_G['gp_op'] == 'demise') {
		if(!empty($_G['forum']['founderuid']) && $_G['forum']['founderuid'] == $_G['uid']) {
			$ucresult = $allowbuildgroup = $groupnum = 0;
			unset($groupmanagers[$_G['forum']['founderuid']]);
			if(empty($groupmanagers)) {
				showmessage('group_cannot_demise');
			}

			if(submitcheck('groupdemise')) {
				$suid = intval($_G['gp_suid']);
				if(empty($suid)) {
					showmessage('group_demise_choose_receiver');
				}
				if(empty($_G['gp_grouppwd'])) {
					showmessage('group_demise_password');
				}
				loaducenter();
				$ucresult = uc_user_login($_G['uid'], $_G['gp_grouppwd'], 1);
				if(!is_array($ucresult) || $ucresult[0] < 1) {
					showmessage('group_demise_password_error');
				}
				$user = getuserbyuid($suid);
				loadcache('usergroup_'.$user['groupid']);
				$allowbuildgroup = $_G['cache']['usergroup_'.$user['groupid']]['allowbuildgroup'];
				if($allowbuildgroup > 0) {
					$groupnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_forumfield')." WHERE founderuid='$suid'");

				}
				if(empty($allowbuildgroup) || $allowbuildgroup - $groupnum < 1) {
					showmessage('group_demise_receiver_cannot_do');
				}
				DB::query("UPDATE ".DB::table('forum_forumfield')." SET founderuid='$suid', foundername='{$user['username']}' WHERE fid='$_G[fid]'");

				sendpm($suid, lang('group/template', lang('group/template', 'group_demise_message_title', array('forum' => $_G['forum']['name'])), lang('group/template', 'group_demise_message_body', array('forum' => $_G['forum']['name'], 'siteurl' => $_G['siteurl'], 'fid' => $_G['fid'])), $_G['uid']));
				showmessage('group_demise_succeed', 'forum.php?mod=group&action=manage&fid='.$_G['fid']);
			}
		} else {
			showmessage('group_demise_founder_only');
		}

	} else {
		showmessage('undefined_action');
	}
	
	//include template('diy:group/group:'.$_G['fid']);

}

$action='plugin';//added by fumz 2010-7-23 15:45:39

?>