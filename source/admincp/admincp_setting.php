<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_setting.php 11748 2010-06-12 05:40:23Z monkey $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$setting = array();
$query = DB::query("SELECT * FROM ".DB::table('common_setting'));
while($row = DB::fetch($query)) {
	$setting[$row['skey']] = $row['svalue'];
}

if(!$isfounder) {
	unset($setting['ftp']);
}

$extbutton = '';
$operation = $operation ? $operation : 'basic';

if($operation == 'styles') {
	$floatwinkeys = array('login', 'register', 'sendpm', 'newthread', 'reply', 'viewratings', 'viewwarning', 'viewthreadmod', 'viewvote', 'tradeorder', 'activity', 'debate', 'nav', 'usergroups', 'task');
	$floatwinarray = array();
	foreach($floatwinkeys as $k) {
		$floatwinarray[] = array($k, $lang['setting_styles_global_allowfloatwin_'.$k]);
	}
}

if(!submitcheck('settingsubmit')) {

	if($operation == 'ec') {
		shownav('extended', 'nav_ec', 'nav_ec_config');
	} elseif(in_array($operation, array('seo', 'cachethread', 'serveropti'))) {
		shownav('global', 'setting_optimize', 'setting_'.$operation);
	} elseif($operation == 'styles') {
		shownav('style', 'setting_styles');
	} elseif($operation == 'editor') {
		shownav('style', 'setting_editor');
	} elseif(in_array($operation, array('mail', 'uc', 'manyou'))) {
		shownav('founder', 'setting_'.$operation);
	} else {
		shownav('global', 'setting_'.$operation);
	}

	if(in_array($operation, array('seo', 'memory', 'cachethread', 'serveropti'))) {
		$current = array($operation => 1);
		showsubmenu('setting_optimize', array(
			array('setting_seo', 'setting&operation=seo', $current['seo']),
			array('setting_memory', 'setting&operation=memory', $current['memory']),
			array('setting_cachethread', 'setting&operation=cachethread', $current['cachethread']),
			array('setting_serveropti', 'setting&operation=serveropti', $current['serveropti'])
		));
	} elseif($operation == 'ec') {
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'setting&operation=ec', 1),
			array('nav_ec_tenpay', 'ec&operation=tenpay', 0),
			array('nav_ec_alipay', 'ec&operation=alipay', 0),
			array('nav_ec_credit', 'ec&operation=credit', 0),
			array('nav_ec_orders', 'ec&operation=orders', 0),
			array('nav_ec_tradelog', 'tradelog&mod=forum', 0)
		));
	} elseif($operation == 'access') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('register', 'access')) ? $_G['gp_anchor'] : 'register';
		showsubmenuanchors('setting_access', array(
			array('setting_access_register', 'register', $_G['gp_anchor'] == 'register'),
			array('setting_access_access', 'access', $_G['gp_anchor'] == 'access')
		));
	} elseif($operation == 'home') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('base', 'realname', 'videophoto', 'privacy')) ? $_G['gp_anchor'] : 'base';
		showsubmenuanchors('setting_home', array(
			array('setting_home_base', 'base', $_G['gp_anchor'] == 'base'),
			array('setting_home_realname', 'realname', $_G['gp_anchor'] == 'realname'),
			array('setting_home_videophoto', 'videophoto', $_G['gp_anchor'] == 'videophoto'),
			array('setting_home_privacy', 'privacy', $_G['gp_anchor'] == 'privacy')
		));
	} elseif($operation == 'manyou') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('base', 'manage', 'search')) ? $_G['gp_anchor'] : 'base';
		$current = array($_G['gp_anchor'] => 1);

		$manyounav[0] = array('setting_manyou_base', 'setting&operation=manyou&anchor=base', $current['base']);
		if($setting['my_app_status']) {
			$manyounav[1] = array('setting_manyou_manage', 'setting&operation=manyou&anchor=manage', $current['manage']);
		}
		if($setting['my_search_status']) {
			$manyounav[2] = array('setting_manyou_search_manage', 'setting&operation=manyou&anchor=search', $current['search']);
		}
		showsubmenu('setting_manyou', $manyounav);
	} elseif($operation == 'mail') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('setting', 'check')) ? $_G['gp_anchor'] : 'setting';
		showsubmenuanchors('setting_mail', array(
			array('setting_mail_setting', 'mailsetting', $_G['gp_anchor'] == 'setting'),
			array('setting_mail_check', 'mailcheck', $_G['gp_anchor'] == 'check')
		));
	} elseif($operation == 'sec') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('base', 'seccode', 'secqaa')) ? $_G['gp_anchor'] : 'base';
		showsubmenuanchors('setting_sec', array(
			array('setting_sec_base', 'base', $_G['gp_anchor'] == 'base'),
			array('setting_sec_seccode', 'seccode', $_G['gp_anchor'] == 'seccode'),
			array('setting_sec_secqaa', 'secqaa', $_G['gp_anchor'] == 'secqaa')
		));
	} elseif($operation == 'attach') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('basic', 'forumattach', 'remote', 'albumattach')) ? $_G['gp_anchor'] : 'basic';
		showsubmenuanchors('setting_attach', array(
			array('setting_attach_basic', 'basic', $_G['gp_anchor'] == 'basic'),
			$isfounder ? array('setting_attach_remote', 'remote', $_G['gp_anchor'] == 'remote') : '',
			array('setting_attach_forumattach', 'forumattach', $_G['gp_anchor'] == 'forumattach'),
			array('setting_attach_album', 'albumattach', $_G['gp_anchor'] == 'albumattach'),
		));
	} elseif($operation == 'styles') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('global', 'index', 'forumdisplay', 'viewthread', 'refresh', 'sitemessage')) ? $_G['gp_anchor'] : 'global';
		$current = array($_G['gp_anchor'] => 1);
		showsubmenu('setting_styles', array(
			array('setting_styles_global', 'setting&operation=styles&anchor=global', $current['global']),
			array('setting_styles_index', 'setting&operation=styles&anchor=index', $current['index']),
			array('setting_styles_forumdisplay', 'setting&operation=styles&anchor=forumdisplay', $current['forumdisplay']),
			array('setting_styles_viewthread', 'setting&operation=styles&anchor=viewthread', $current['viewthread']),
			array('setting_styles_refresh', 'setting&operation=styles&anchor=refresh', $current['refresh']),
			array('setting_styles_sitemessage', 'setting&operation=styles&anchor=sitemessage', $current['sitemessage'])
		));
	} elseif($operation == 'functions') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('mod', 'heatthread', 'recommend', 'comment', 'other')) ? $_G['gp_anchor'] : 'mod';
		showsubmenuanchors('setting_functions', array(
			array('setting_functions_mod', 'mod', $_G['gp_anchor'] == 'mod'),
			array('setting_functions_heatthread', 'heatthread', $_G['gp_anchor'] == 'heatthread'),
			array('setting_functions_recommend', 'recommend', $_G['gp_anchor'] == 'recommend'),
			array('setting_functions_comment', 'comment', $_G['gp_anchor'] == 'comment'),
			array('setting_functions_other', 'other', $_G['gp_anchor'] == 'other'),
		));
	} elseif($operation == 'credits') {
		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('base', 'policytable')) ? $_G['gp_anchor'] : 'base';
		$current = array($_G['gp_anchor'] => 1);
		showsubmenu('setting_credits', array(
			array('setting_credits_base', 'setting&operation=credits&anchor=base', $current['base']),
			array('setting_credits_policy', 'credits&operation=list&anchor=policytable', $current['policytable']),
		));
	} elseif($operation == 'editor') {
		showsubmenu('setting_editor', array(
			array('setting_editor_global', 'setting&operation=editor', 1),
			array('setting_editor_code', 'misc&operation=bbcode', 0),
		));
	} else {
		showsubmenu('setting_'.$operation);
	}
	showformheader('setting&edit=yes');
	showhiddenfields(array('operation' => $operation));

	if($operation == 'basic') {

		showtableheader();
		showsetting('setting_basic_bbname', 'settingnew[bbname]', $setting['bbname'], 'text');
		showsetting('setting_basic_sitename', 'settingnew[sitename]', $setting['sitename'], 'text');
		showsetting('setting_basic_siteurl', 'settingnew[siteurl]', $setting['siteurl'], 'text');
		showsetting('setting_basic_adminemail', 'settingnew[adminemail]', $setting['adminemail'], 'text');
		showsetting('setting_basic_icp', 'settingnew[icp]', $setting['icp'], 'text');
		showsetting('setting_basic_boardlicensed', 'settingnew[boardlicensed]', $setting['boardlicensed'], 'radio');
		showsetting('setting_basic_bbclosed', 'settingnew[bbclosed]', $setting['bbclosed'], 'radio');
		showsetting('setting_basic_closedreason', 'settingnew[closedreason]', $setting['closedreason'], 'textarea');
		showsetting('setting_basic_stat', 'settingnew[statcode]', $setting['statcode'], 'textarea');

	} elseif($operation == 'home') {

		require_once libfile('function/forumlist');

		showtableheader('', 'nobottom', 'id="base"'.($_G['gp_anchor'] != 'base' ? ' style="display: none"' : ''));
		showsetting('setting_home_base_feedday', 'settingnew[feedday]', $setting['feedday'], 'text');
		showsetting('setting_home_base_feedmaxnum', 'settingnew[feedmaxnum]', $setting['feedmaxnum'], 'text');
		showsetting('setting_home_base_feedhotday', 'settingnew[feedhotday]', $setting['feedhotday'], 'text');
		showsetting('setting_home_base_feedhotmin', 'settingnew[feedhotmin]', $setting['feedhotmin'], 'text');
		showsetting('setting_home_base_feedtargetblank', 'settingnew[feedtargetblank]', $setting['feedtargetblank'], 'radio');
		showsetting('setting_home_base_showallfriendnum', 'settingnew[showallfriendnum]', $setting['showallfriendnum'], 'text');
		showsetting('setting_home_base_feedhotnum', 'settingnew[feedhotnum]', $setting['feedhotnum'], 'text');
		showsetting('setting_home_base_maxpage', 'settingnew[maxpage]', $setting['maxpage'], 'text');
		showsetting('setting_home_base_sendmailday', 'settingnew[sendmailday]', $setting['sendmailday'], 'text');
		if($setting['realname']) {
		}
		if($setting['videophoto']) {
		}
		showtagfooter('tbody');

		showsetting('setting_home_base_topcachetime', 'settingnew[topcachetime]', $setting['topcachetime'], 'text');
		showsetting('setting_home_base_groupnum', 'settingnew[friendgroupnum]', $setting['friendgroupnum'], 'text');
		$threadtype = array('1' => 'poll', '2' => 'trade', '3' => 'reward', '4' => 'activity', '5' => 'debate');
		foreach($threadtype as $special => $key) {
			$forumselect = "<select name=\"%s\">\n<option value=\"\">&nbsp;&nbsp;> ".cplang('select')."</option>".str_replace('%', '%%', forumselect(FALSE, 0, $setting[$key.'forumid'], TRUE, FALSE, $special)).'</select>';
			showsetting('setting_home_base_default_'.$key.'_forum', "settingnew[{$key}forumid]", $setting[$key.'forumid'], sprintf($forumselect, "settingnew[{$key}forumid]"));
		}
		showsetting('setting_home_base_allowhomemode', 'settingnew[allowhomemode]', $setting['allowhomemode'], 'radio');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="realname"'.($_G['gp_anchor'] != 'realname' ? ' style="display: none"' : ''));
		showsetting('setting_home_realname_switch', 'settingnew[realname]', $setting['realname'], 'radio', '', 1);
		showsetting('setting_home_realname_setting', '', '', cplang('setting_home_realname_setting_detail'));
		showtitle('setting_home_realname_authority');
		showsetting('setting_home_realname_allowviewspace', 'settingnew[name_allowviewspace]', $setting['name_allowviewspace'], 'radio');
		showsetting('setting_home_realname_allowfriend', 'settingnew[name_allowfriend]', $setting['name_allowfriend'], 'radio');
		showsetting('setting_home_realname_allowpoke', 'settingnew[name_allowpoke]', $setting['name_allowpoke'], 'radio');
		showsetting('setting_home_realname_allowdoing', 'settingnew[name_allowdoing]', $setting['name_allowdoing'], 'radio');
		showsetting('setting_home_realname_allowblog', 'settingnew[name_allowblog]', $setting['name_allowblog'], 'radio');
		showsetting('setting_home_realname_allowalbum', 'settingnew[name_allowalbum]', $setting['name_allowalbum'], 'radio');
		showsetting('setting_home_realname_allowpoll', 'settingnew[name_allowpoll]', $setting['name_allowpoll'], 'radio');
		showsetting('setting_home_realname_allowshare', 'settingnew[name_allowshare]', $setting['name_allowshare'], 'radio');
		showsetting('setting_home_realname_allowfavorite', 'settingnew[name_allowfavorite]', $setting['name_allowfavorite'], 'radio');
		showsetting('setting_home_realname_allowcomment', 'settingnew[name_allowcomment]', $setting['name_allowcomment'], 'radio');
		showsetting('setting_home_realname_allowuserapp', 'settingnew[name_allowuserapp]', $setting['name_allowuserapp'], 'radio');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="videophoto"'.($_G['gp_anchor'] != 'videophoto' ? ' style="display: none"' : ''));
		showsetting('setting_home_videophoto_switch', 'settingnew[videophoto]', $setting['videophoto'], 'radio', '', 1);
		showtitle('setting_home_videophoto_authority');
		showsetting('setting_home_videophoto_allowviewphoto', 'settingnew[video_allowviewspace]', $setting['video_allowviewspace'], 'radio');
		showsetting('setting_home_videophoto_allowfriend', 'settingnew[video_allowfriend]', $setting['video_allowfriend'], 'radio');
		showsetting('setting_home_videophoto_allowpoke', 'settingnew[video_allowpoke]', $setting['video_allowpoke'], 'radio');
		showsetting('setting_home_videophoto_allowdoing', 'settingnew[video_allowdoing]', $setting['video_allowdoing'], 'radio');
		showsetting('setting_home_videophoto_allowblog', 'settingnew[video_allowblog]', $setting['video_allowblog'], 'radio');
		showsetting('setting_home_videophoto_allowalbum', 'settingnew[video_allowalbum]', $setting['video_allowalbum'], 'radio');
		showsetting('setting_home_videophoto_allowshare', 'settingnew[video_allowshare]', $setting['video_allowshare'], 'radio');
		showsetting('setting_home_videophoto_allowfavorite', 'settingnew[video_allowfavorite]', $setting['video_allowfavorite'], 'radio');
		showsetting('setting_home_videophoto_allowcomment', 'settingnew[video_allowcomment]', $setting['video_allowcomment'], 'radio');
		showsetting('setting_home_videophoto_allowuserapp', 'settingnew[video_allowuserapp]', $setting['video_allowuserapp'], 'radio');
		showtablefooter();

		if(isset($setting['privacy'])) {
			$setting['privacy'] = unserialize($setting['privacy']);
		}
		showtableheader('', 'nobottom', 'id="privacy"'.($_G['gp_anchor'] != 'privacy' ? ' style="display: none"' : ''));
		showtitle('setting_home_privacy_new_user');
		showsetting('setting_home_privacy_view_index', array('settingnew[privacy][view][index]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(2, $lang['setting_home_privacy_self'])
		)), $setting['privacy']['view']['index'], 'select');
		showsetting('setting_home_privacy_view_friend', array('settingnew[privacy][view][friend]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(2, $lang['setting_home_privacy_self'])
		)), $setting['privacy']['view']['friend'], 'select');
		showsetting('setting_home_privacy_view_wall', array('settingnew[privacy][view][wall]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(2, $lang['setting_home_privacy_self'])
		)), $setting['privacy']['view']['wall'], 'select');
		showsetting('setting_home_privacy_view_feed', array('settingnew[privacy][view][feed]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend'])
		)), $setting['privacy']['view']['feed'], 'select');
		showsetting('setting_home_privacy_view_doing', array('settingnew[privacy][view][doing]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend'])
		)), $setting['privacy']['view']['doing'], 'select');
		showsetting('setting_home_privacy_view_blog', array('settingnew[privacy][view][blog]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend'])
		)), $setting['privacy']['view']['blog'], 'select');
		showsetting('setting_home_privacy_view_album', array('settingnew[privacy][view][album]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend'])
		)), $setting['privacy']['view']['album'], 'select');
		showsetting('setting_home_privacy_view_share', array('settingnew[privacy][view][share]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend'])
		)), $setting['privacy']['view']['share'], 'select');
		showsetting('setting_home_privacy_view_poll', array('settingnew[privacy][view][poll]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend'])
		)), $setting['privacy']['view']['poll'], 'select');

		showsetting('setting_home_privacy_default_feed', array('settingnew[privacy][feed]', array(
			array('doing', $lang['setting_home_privacy_default_feed_doing'], '1'),
			array('blog', $lang['setting_home_privacy_default_feed_blog'], '1'),
			array('upload', $lang['setting_home_privacy_default_feed_upload'], '1'),
			array('share', $lang['setting_home_privacy_default_feed_share'], '1'),
			array('poll', $lang['setting_home_privacy_default_feed_poll'], '1'),
			array('joinpoll', $lang['setting_home_privacy_default_feed_joinpoll'], '1'),
			array('friend', $lang['setting_home_privacy_default_feed_friend'], '1'),
			array('comment', $lang['setting_home_privacy_default_feed_comments'], '1'),
			array('show', $lang['setting_home_privacy_default_feed_show'], '1'),
			array('credit', $lang['setting_home_privacy_default_feed_credit'], '1'),
			array('spaceopen', $lang['setting_home_privacy_default_feed_spaceopen'], '1'),
			array('invite', $lang['setting_home_privacy_default_feed_invite'], '1'),
			array('task', $lang['setting_home_privacy_default_feed_task'], '1'),
			array('profile', $lang['setting_home_privacy_default_feed_profile'], '1'),
			array('click', $lang['setting_home_privacy_default_feed_click'], '1'),
			array('newthread', $lang['setting_home_privacy_default_feed_newthread'], '1'),
			array('newreply', $lang['setting_home_privacy_default_feed_newreply'], '1'),
			)), $setting['privacy']['feed'], 'omcheckbox');
		showtablefooter();

	} elseif($operation == 'manyou') {

		require_once DISCUZ_ROOT.'./source/admincp/admincp_manyou.php';

	} elseif($operation == 'access') {

		$wmsgcheck = array($setting['welcomemsg'] =>'checked');
		$setting['inviteconfig'] = unserialize($setting['inviteconfig']);
		$setting['extcredits'] = unserialize($setting['extcredits']);

		$buycredits = $rewardcredits = '';
		for($i = 0; $i <= 8; $i++) {
			if($setting['extcredits'][$i]['available']) {
				$extcredit = 'extcredits'.$i.' ('.$setting['extcredits'][$i]['title'].')';
				$buycredits .= '<option value="'.$i.'" '.($i == intval($setting['inviteconfig']['invitecredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
				$rewardcredits .= '<option value="'.$i.'" '.($i == intval($setting['inviteconfig']['inviterewardcredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
			}
		}

		$groupselect = '';
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." WHERE type='special'");
		while($group = DB::fetch($query)) {
			$groupselect .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $setting['inviteconfig']['invitegroupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		$taskarray = array(array('', cplang('select')));
		$query = DB::query("SELECT taskid, name FROM ".DB::table('common_task')." WHERE available='2'");
		while($task = DB::fetch($query)) {
			$taskarray[] = array($task['taskid'], $task['name']);
		}

		showtableheader('', 'nobottom', 'id="register"'.($_G['gp_anchor'] != 'register' ? ' style="display: none"' : ''));
		showsetting('setting_access_register_status', array('settingnew[regstatus]', array(
			array(0, $lang['setting_access_register_close'], array('rcmessage' => '', 'showinvite' => 'none')),
			array(1, $lang['setting_access_register_open'], array('rcmessage' => 'none', 'showinvite' => 'none')),
			array(2, $lang['setting_access_register_invite'], array('rcmessage' => 'none', 'showinvite' => '')),
			array(3, $lang['setting_access_register_open_invite'], array('rcmessage' => 'none', 'showinvite' => ''))
		)), $setting['regstatus'], 'mradio');

		showtagheader('tbody', 'rcmessage', $setting['regstatus'] == 0, 'sub');
		showsetting('setting_access_register_regclosemessage', 'settingnew[regclosemessage]', $setting['regclosemessage'], 'textarea');
		showtagfooter('tbody');

		showtagheader('tbody', 'showinvite', $setting['regstatus'] > 1, 'sub');
		showsetting('setting_access_register_invite_credit', '', '', '<select name="settingnew[inviteconfig][inviterewardcredit]">'.$rewardcredits.'</select>');
		showsetting('setting_access_register_invite_addcredit', 'settingnew[inviteconfig][inviteaddcredit]', $setting['inviteconfig']['inviteaddcredit'], 'text');
		showsetting('setting_access_register_invite_invitedcredit', 'settingnew[inviteconfig][invitedaddcredit]', $setting['inviteconfig']['invitedaddcredit'], 'text');
		showsetting('setting_access_register_invite_group', '', '', '<select name="settingnew[inviteconfig][invitegroupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$groupselect.'</select>');
		showtagfooter('tbody');
		showsetting('setting_access_register_link_name', 'settingnew[reglinkname]', $setting['reglinkname'], 'text');
		showsetting('setting_access_register_censoruser', 'settingnew[censoruser]', $setting['censoruser'], 'textarea');
		showsetting('setting_access_register_verify', array('settingnew[regverify]', array(
			array(0, $lang['none']),
			array(1, $lang['setting_access_register_verify_email']),
			array(2, $lang['setting_access_register_verify_manual'])
		)), $setting['regverify'], 'select');
		showsetting('setting_access_register_verify_ipwhite', 'settingnew[ipverifywhite]', $setting['ipverifywhite'], 'textarea');
		showsetting('setting_access_register_ctrl', 'settingnew[regctrl]', $setting['regctrl'], 'text');
		showsetting('setting_access_register_floodctrl', 'settingnew[regfloodctrl]', $setting['regfloodctrl'], 'text');
		showsetting('setting_access_register_ipctrl', 'settingnew[ipregctrl]', $setting['ipregctrl'], 'textarea');
		showsetting('setting_access_register_welcomemsg', array('settingnew[welcomemsg]', array(
			array(0, $lang['setting_access_register_welcomemsg_nosend'], array('welcomemsgext' => 'none')),
			array(1, $lang['setting_access_register_welcomemsg_pm'], array('welcomemsgext' => '')),
			array(2, $lang['setting_access_register_welcomemsg_email'], array('welcomemsgext' => ''))
		)), $setting['welcomemsg'], 'mradio');
		showtagheader('tbody', 'welcomemsgext', $setting['welcomemsg'], 'sub');
		showsetting('setting_access_register_welcomemsgtitle', 'settingnew[welcomemsgtitle]', $setting['welcomemsgtitle'], 'text');
		showsetting('setting_access_register_welcomemsgtxt', 'settingnew[welcomemsgtxt]', $setting['welcomemsgtxt'], 'textarea');
		showtagfooter('tbody');
		showsetting('setting_access_register_bbrules', 'settingnew[bbrules]', $setting['bbrules'], 'radio', '', 1);
		showsetting('setting_access_register_bbrulestxt', 'settingnew[bbrulestxt]', $setting['bbrulestxt'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="access"'.($_G['gp_anchor'] != 'access' ? ' style="display: none"' : ''));
		showsetting('setting_access_access_newbiespan', 'settingnew[newbiespan]', $setting['newbiespan'], 'text');
		showsetting('setting_access_access_ipaccess', 'settingnew[ipaccess]', $setting['ipaccess'], 'textarea');
		showsetting('setting_access_access_adminipaccess', 'settingnew[adminipaccess]', $setting['adminipaccess'], 'textarea');
		showsetting('setting_access_access_domainwhitelist', 'settingnew[domainwhitelist]', $setting['domainwhitelist'], 'textarea');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'styles') {

		$_G['setting']['showsettings'] = str_pad(decbin($setting['showsettings']), 3, 0, STR_PAD_LEFT);
		$setting['showsignatures'] = $_G['setting']['showsettings']{0};
		$setting['showavatars'] = $_G['setting']['showsettings']{1};
		$setting['showimages'] = $_G['setting']['showsettings']{2};
		$setting['postnocustom'] = implode("\n", (array)unserialize($setting['postnocustom']));
		$setting['sitemessage'] = unserialize($setting['sitemessage']);
		$setting['disallowfloat'] = $setting['disallowfloat'] ? unserialize($setting['disallowfloat']) : array();
		$setting['allowfloatwin'] = array_diff($floatwinkeys, $setting['disallowfloat']);
		$setting['indexhot'] = unserialize($setting['indexhot']);

		$setting['customauthorinfo'] = unserialize($setting['customauthorinfo']);
		$setting['customauthorinfo'] = $setting['customauthorinfo'][0];
		list($setting['zoomstatus'], $setting['imagemaxwidth']) = explode("\t", $setting['zoomstatus']);
		$setting['imagemaxwidth'] = !empty($setting['imagemaxwidth']) ? $setting['imagemaxwidth'] : 600;

		$stylelist = "<select name=\"settingnew[styleid]\">\n";
		$query = DB::query("SELECT styleid, name FROM ".DB::table('common_style')."");
		while($style = DB::fetch($query)) {
			$selected = $style['styleid'] == $setting['styleid'] ? 'selected="selected"' : NULL;
			$stylelist .= "<option value=\"$style[styleid]\" $selected>$style[name]</option>\n";
		}
		$stylelist .= '</select>';

		showtips('setting_tips', 'global_tips', $_G['gp_anchor'] == 'global');
		showtips('setting_tips', 'index_tips', $_G['gp_anchor'] == 'index');
		showtips('setting_tips', 'forumdisplay_tips', $_G['gp_anchor'] == 'forumdisplay');

		showtableheader('', 'nobottom', 'id="global"'.($_G['gp_anchor'] != 'global' ? ' style="display: none"' : ''));
		showsetting('setting_styles_global_styleid', '', '', $stylelist);
		showsetting('setting_styles_global_jsmenu', 'settingnew[forumjump]', $setting['forumjump'], 'radio');
		showsetting('setting_styles_global_allowfloatwin', array('settingnew[allowfloatwin]', $floatwinarray), $setting['allowfloatwin'], 'mcheckbox');
		showsetting('setting_styles_global_creditnotice', 'settingnew[creditnotice]', $setting['creditnotice'], 'radio');

		showtableheader('', 'nobottom', 'id="index"'.($_G['gp_anchor'] != 'index' ? ' style="display: none"' : ''));

		showsetting('setting_styles_index_indexhot_status', 'settingnew[indexhot][status]', $setting['indexhot']['status'], 'radio', 0, 1);
		showsetting('setting_styles_index_indexhot_limit', 'settingnew[indexhot][limit]', $setting['indexhot']['limit'], 'text');
		showsetting('setting_styles_index_indexhot_days', 'settingnew[indexhot][days]', $setting['indexhot']['days'], 'text');
		showsetting('setting_styles_index_indexhot_expiration', 'settingnew[indexhot][expiration]', $setting['indexhot']['expiration'], 'text');
		showsetting('setting_styles_index_indexhot_messagecut', 'settingnew[indexhot][messagecut]', $setting['indexhot']['messagecut'], 'text');
		showtagfooter('tbody');
		showsetting('setting_styles_index_subforumsindex', 'settingnew[subforumsindex]', $setting['subforumsindex'], 'radio');
		showsetting('setting_styles_index_forumlinkstatus', 'settingnew[forumlinkstatus]', $setting['forumlinkstatus'], 'radio');
		showsetting('setting_styles_index_forumallowside', 'settingnew[forumallowside]', $setting['forumallowside'], 'radio');
		showsetting('setting_styles_index_members', 'settingnew[maxbdays]', $setting['maxbdays'], 'text');
		showsetting('setting_styles_index_whosonline', array('settingnew[whosonlinestatus]', array(
			array(0, $lang['setting_styles_index_display_none']),
			array(1, $lang['setting_styles_index_whosonline_index']),
			array(2, $lang['setting_styles_index_whosonline_forum']),
			array(3, $lang['setting_styles_index_whosonline_both'])
		)), $setting['whosonlinestatus'], 'select');
		showsetting('setting_styles_index_whosonline_contract', 'settingnew[whosonline_contract]', $setting['whosonline_contract'], 'radio');
		showsetting('setting_styles_index_online_more_members', 'settingnew[maxonlinelist]', $setting['maxonlinelist'], 'text');
		showsetting('setting_styles_index_hideprivate', 'settingnew[hideprivate]', $setting['hideprivate'], 'radio');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="forumdisplay"'.($_G['gp_anchor'] != 'forumdisplay' ? ' style="display: none"' : ''));

		showsetting('setting_styles_forumdisplay_tpp', 'settingnew[topicperpage]', $setting['topicperpage'], 'text');
		showsetting('setting_styles_forumdisplay_threadmaxpages', 'settingnew[threadmaxpages]', $setting['threadmaxpages'], 'text');
		showsetting('setting_styles_forumdisplay_globalstick', 'settingnew[globalstick]', $setting['globalstick'], 'radio');
		showsetting('setting_styles_forumdisplay_stick', 'settingnew[threadsticky]', $setting['threadsticky'], 'text');
		showsetting('setting_styles_forumdisplay_part', 'settingnew[forumseparator]', $setting['forumseparator'], 'radio');
		showsetting('setting_styles_forumdisplay_visitedforums', 'settingnew[visitedforums]', $setting['visitedforums'], 'text');
		showtablefooter();

		showtagheader('div', 'viewthread', $_G['gp_anchor'] == 'viewthread');
		showtableheader('nav_setting_viewthread', 'nobottom');
		showsetting('setting_styles_viewthread_ppp', 'settingnew[postperpage]', $setting['postperpage'], 'text');
		showsetting('setting_styles_viewthread_starthreshold', 'settingnew[starthreshold]', $setting['starthreshold'], 'text');
		showsetting('setting_styles_viewthread_maxsigrows', 'settingnew[maxsigrows]', $setting['maxsigrows'], 'text');
		showsetting('setting_styles_viewthread_sigviewcond', 'settingnew[sigviewcond]', $setting['sigviewcond'], 'text');
		showsetting('setting_styles_viewthread_rate_on', 'settingnew[ratelogon]', $setting['ratelogon'], 'radio');
		showsetting('setting_styles_viewthread_rate_number', 'settingnew[ratelogrecord]', $setting['ratelogrecord'], 'text');
		showsetting('setting_styles_viewthread_show_signature', 'settingnew[showsignatures]', $setting['showsignatures'], 'radio');
		showsetting('setting_styles_viewthread_show_face', 'settingnew[showavatars]', $setting['showavatars'], 'radio');
		showsetting('setting_styles_viewthread_show_images', 'settingnew[showimages]', $setting['showimages'], 'radio');
		showsetting('setting_styles_viewthread_imagemaxwidth', 'settingnew[imagemaxwidth]', $setting['imagemaxwidth'], 'text');
		showsetting('setting_styles_viewthread_zoomstatus', 'settingnew[zoomstatus]', $setting['zoomstatus'], 'radio');
		showsetting('setting_styles_viewthread_fastpost', 'settingnew[fastpost]', $setting['fastpost'], 'radio', 0, 1);
		showsetting('setting_styles_viewthread_fastsmilies', 'settingnew[fastsmilies]', $setting['fastsmilies'], 'radio');
		showtagfooter('tbody');
		showsetting('setting_styles_viewthread_vtonlinestatus', array('settingnew[vtonlinestatus]', array(
			array(0, $lang['setting_styles_viewthread_display_none']),
			array(1, $lang['setting_styles_viewthread_online_easy']),
			array(2, $lang['setting_styles_viewthread_online_exactitude'])
		)), $setting['vtonlinestatus'], 'select');
		showsetting('setting_styles_viewthread_userstatusby', 'settingnew[userstatusby]', $setting['userstatusby'], 'radio');
		showsetting('setting_styles_viewthread_postno', 'settingnew[postno]', $setting['postno'], 'text');
		showsetting('setting_styles_viewthread_postnocustom', 'settingnew[postnocustom]', $setting['postnocustom'], 'textarea');
		showsetting('setting_styles_viewthread_maxsmilies', 'settingnew[maxsmilies]', $setting['maxsmilies'], 'text');

		showsetting('setting_styles_viewthread_author_onleft', array('settingnew[authoronleft]', array(
			array(1, cplang('setting_styles_viewthread_author_onleft_yes')),
			array(0, cplang('setting_styles_viewthread_author_onleft_no')))), $setting['authoronleft'], 'mradio');

		showtableheader('setting_styles_viewthread_customauthorinfo', 'fixpadding');
		$authorinfoitems = array(
			'uid' => 'UID',
			'posts' => $lang['setting_styles_viewthread_userinfo_posts'],
			'threads' => $lang['setting_styles_viewthread_userinfo_threads'],
			'digest' => $lang['setting_styles_viewthread_userinfo_digest'],
			'credits' => $lang['setting_styles_viewthread_userinfo_credits'],
			'readperm' => $lang['setting_styles_viewthread_userinfo_readperm'],
			'regtime' => $lang['setting_styles_viewthread_userinfo_regtime'],
			'lastdate' => $lang['setting_styles_viewthread_userinfo_lastdate'],
		);
		if(!empty($_G['setting']['extcredits'])) {
			foreach($_G['setting']['extcredits'] as $key => $value) {
				$authorinfoitems['extcredits'.$key] = $value['title'];
			}
		}
		$query = DB::query("SELECT fieldid,title FROM ".DB::table('common_member_profile_setting')." WHERE available='1' AND showinthread='1' ORDER BY displayorder");
		while($profilefields = DB::fetch($query)) {
			$authorinfoitems['field_'.$profilefields['fieldid']] = $profilefields['title'];
		}

		showsubtitle(array('', 'display_order', 'setting_styles_viewthread_userinfo_left', 'setting_styles_viewthread_userinfo_menu'));

		$authorinfoitemsetting = '';
		foreach($authorinfoitems as $key => $value) {
			$authorinfoitemsetting .= '<tr><td>'.$value.
				'</td><td><input name="settingnew[customauthorinfo]['.$key.'][order]" type="text" class="txt" value="'.$setting['customauthorinfo'][$key]['order'].'">'.
				'</td><td><input name="settingnew[customauthorinfo]['.$key.'][left]" type="checkbox" class="checkbox" value="1" '.($setting['customauthorinfo'][$key]['left'] ? 'checked' : '').'>'.
				'</td><td><input name="settingnew[customauthorinfo]['.$key.'][menu]" type="checkbox" class="checkbox" value="1" '.($setting['customauthorinfo'][$key]['menu'] ? 'checked' : '').'>'.
				'</td></tr>';
		}

		echo $authorinfoitemsetting;
		showtablefooter();
		showtagfooter('div');

		$setting['msgforward'] = !empty($setting['msgforward']) ? unserialize($setting['msgforward']) : array();
		$setting['msgforward']['messages'] = !empty($setting['msgforward']['messages']) ? implode("\n", $setting['msgforward']['messages']) : '';
		showtablefooter();

		showtableheader('', 'nobottom', 'id="refresh"'.($_G['gp_anchor'] != 'refresh' ? ' style="display: none"' : ''));
		showsetting('setting_styles_refresh_refreshtime', 'settingnew[msgforward][refreshtime]', $setting['msgforward']['refreshtime'], 'text');
		showsetting('setting_styles_refresh_quick', 'settingnew[msgforward][quick]', $setting['msgforward']['quick'], 'radio', '', 1);
		showsetting('setting_styles_refresh_messages', 'settingnew[msgforward][messages]', $setting['msgforward']['messages'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="sitemessage"'.($_G['gp_anchor'] != 'sitemessage' ? ' style="display: none"' : ''));
		showsetting('setting_styles_sitemessage_time', 'settingnew[sitemessage][time]', $setting['sitemessage']['time'], 'text');
		showsetting('setting_styles_sitemessage_register', 'settingnew[sitemessage][register]', $setting['sitemessage']['register'], 'textarea');
		showsetting('setting_styles_sitemessage_login', 'settingnew[sitemessage][login]', $setting['sitemessage']['login'], 'textarea');
		showsetting('setting_styles_sitemessage_newthread', 'settingnew[sitemessage][newthread]', $setting['sitemessage']['newthread'], 'textarea');
		showsetting('setting_styles_sitemessage_reply', 'settingnew[sitemessage][reply]', $setting['sitemessage']['reply'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'seo') {

		$rewritedata = rewritedata();
		$setting['rewritestatus'] = isset($setting['rewritestatus']) ? unserialize($setting['rewritestatus']) : '';
		$setting['rewriterule'] = isset($setting['rewriterule']) ? unserialize($setting['rewriterule']) : '';
		$rewritearray = array();
		foreach($rewritedata['rulesearch'] as $k => $v) {
			$v = !$setting['rewriterule'][$k] ? $v : $setting['rewriterule'][$k];
			$rewritearray[] = array($k, cplang('setting_seo_rewritestatus_'.$k).'<br />'.cplang('setting_seo_rewriterule').'<input onclick="doane(event)" name="settingnew[rewriterule]['.$k.']" class="txt" value="'.htmlspecialchars($v).'"/>');
		}
		showtips('setting_tips');
		showtableheader();
		showtitle('setting_seo');
		showsetting('setting_seo_rewritestatus', array('settingnew[rewritestatus]', $rewritearray), $setting['rewritestatus'], 'mcheckbox2');
		showsetting('setting_seo_rewritecompatible', 'settingnew[rewritecompatible]', $setting['rewritecompatible'], 'radio');
		showsetting('setting_seo_seotitle', 'settingnew[seotitle]', $setting['seotitle'], 'text');
		showsetting('setting_seo_seokeywords', 'settingnew[seokeywords]', $setting['seokeywords'], 'text');
		showsetting('setting_seo_seodescription', 'settingnew[seodescription]', $setting['seodescription'], 'text');
		showsetting('setting_seo_seohead', 'settingnew[seohead]', $setting['seohead'], 'textarea');

		showtagfooter('tbody');

	} elseif($operation == 'cachethread') {

		include_once libfile('function/forumlist');
		$forumselect = '<select name="fids[]" multiple="multiple" size="10"><option value="all">'.$lang['all'].'</option><option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
		showtableheader();
		showtitle('setting_cachethread');
		showsetting('setting_cachethread_indexlife', 'settingnew[cacheindexlife]', $setting['cacheindexlife'], 'text');
		showsetting('setting_cachethread_life', 'settingnew[cachethreadlife]', $setting['cachethreadlife'], 'text');
		showsetting('setting_cachethread_dir', 'settingnew[cachethreaddir]', $setting['cachethreaddir'], 'text');

		showtitle('setting_cachethread_coefficient_set');
		showsetting('setting_cachethread_coefficient', 'settingnew[threadcaches]', '', "<input type=\"text\" class=\"txt\" size=\"30\" name=\"settingnew[threadcaches]\" value=\"$setting[threadcaches]\">");
		showsetting('setting_cachethread_coefficient_forum', '', '', $forumselect);

	} elseif($operation == 'serveropti') {

		$checkgzipfunc = !function_exists('ob_gzhandler') ? 1 : 0;
		if($setting['jspath'] == 'static/js/') {
			$tjspath['default'] = 'checked="checked"';
			$setting['jspath'] = '';
		} elseif($setting['jspath'] == 'data/cache/') {
			$tjspath['cache'] =  'checked="checked"';
			$setting['jspath'] = '';
		} else {
			$tjspath['custom'] =  'checked="checked"';
		}

		showtips('setting_tips');
		showtableheader();
		showtitle('setting_serveropti');
		showsetting('setting_serveropti_delayviewcount', array('settingnew[delayviewcount]', array(
			$lang['setting_serveropti_delayviewcount_thread'],
			$lang['setting_serveropti_delayviewcount_attach'],
		)), $setting['delayviewcount'], 'binmcheckbox');
		showsetting('setting_serveropti_nocacheheaders', 'settingnew[nocacheheaders]', $setting['nocacheheaders'], 'radio');
		showsetting('setting_serveropti_maxonlines', 'settingnew[maxonlines]', $setting['maxonlines'], 'text');
		showsetting('setting_serveropti_onlinehold', 'settingnew[onlinehold]', $setting['onlinehold'], 'text');
		showsetting('setting_serveropti_loadctrl', 'settingnew[loadctrl]', $setting['loadctrl'], 'text');
		showsetting('setting_serveropti_jspath', '', '', '<ul class="nofloat" onmouseover="altStyle(this);">
			<li'.($tjspath['default'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[jspath]" value="static/js/" '.$tjspath['default'].'> '.$lang['setting_serveropti_jspath_default'].'</li>
			<li'.($tjspath['cache'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[jspath]" value="data/cache/" '.$tjspath['cache'].'> '.$lang['setting_serveropti_jspath_cache'].'</li>
			<li'.($tjspath['custom'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[jspath]" value="" '.$tjspath['custom'].'> '.$lang['setting_serveropti_jspath_custom'].' <input type="text" class="txt" style="width: 100px" name="settingnew[jspathcustom]" value="'.$setting['jspath'].'" size="6"></li></ul>'
		);

	} elseif($operation == 'editor') {

		$_G['setting']['editoroptions'] = str_pad(decbin($setting['editoroptions']), 2, 0, STR_PAD_LEFT);
		$setting['defaulteditormode'] = $_G['setting']['editoroptions']{0};
		$setting['allowswitcheditor'] = $_G['setting']['editoroptions']{1};

		showtableheader();
		showsetting('setting_editor_mode_default', array('settingnew[defaulteditormode]', array(
			array(0, $lang['setting_editor_mode_discuzcode']),
			array(1, $lang['setting_editor_mode_wysiwyg']))), $setting['defaulteditormode'], 'mradio');
		showsetting('setting_editor_swtich_enable', 'settingnew[allowswitcheditor]', $setting['allowswitcheditor'], 'radio');
		showsetting('setting_editor_smthumb', 'settingnew[smthumb]', $setting['smthumb'], 'text');
		showsetting('setting_editor_smcols', 'settingnew[smcols]', $setting['smcols'], 'text');
		showsetting('setting_editor_smrows', 'settingnew[smrows]', $setting['smrows'], 'text');
		showtablefooter();

	} elseif($operation == 'functions') {

		showtips('setting_tips', 'mod_tips', $_G['gp_anchor'] == 'mod');
		showtips('setting_tips', 'other_tips', $_G['gp_anchor'] == 'other');
		showtips('setting_functions_recommend_tips', 'recommend_tips', $_G['gp_anchor'] == 'recommend');

		showtableheader('', 'nobottom', 'id="mod"'.($_G['gp_anchor'] != 'mod' ? ' style="display: none"' : ''));
		showsetting('setting_functions_mod_updatestat', 'settingnew[updatestat]', $setting['updatestat'], 'radio');
		showsetting('setting_functions_mod_status', 'settingnew[modworkstatus]', $setting['modworkstatus'], 'radio');
		showsetting('setting_functions_mod_maxmodworksmonths', 'settingnew[maxmodworksmonths]', $setting['maxmodworksmonths'], 'text');
		showsetting('setting_functions_mod_losslessdel', 'settingnew[losslessdel]', $setting['losslessdel'], 'text');
		showsetting('setting_functions_mod_reasons', 'settingnew[modreasons]', $setting['modreasons'], 'textarea');
		showsetting('setting_functions_mod_bannedmessages', array('settingnew[bannedmessages]', array(
			$lang['setting_functions_mod_bannedmessages_thread'],
			$lang['setting_functions_mod_bannedmessages_avatar'],
			$lang['setting_functions_mod_bannedmessages_signature'])), $setting['bannedmessages'], 'binmcheckbox');
		showsetting('setting_functions_mod_warninglimit', 'settingnew[warninglimit]', $setting['warninglimit'], 'text');
		showsetting('setting_functions_mod_warningexpiration', 'settingnew[warningexpiration]', $setting['warningexpiration'], 'text');
		showtablefooter();

		$setting['heatthread'] = unserialize($setting['heatthread']);
		$setting['recommendthread'] = unserialize($setting['recommendthread']);
		$count = count(explode(',', $setting['heatthread']['iconlevels']));
		$heatthreadicons = '';
		for($i = 0;$i < $count;$i++) {
			$heatthreadicons .= '<img src="static/image/common/hot_'.($i + 1).'.gif" /> ';
		}
		$count = count(explode(',', $setting['recommendthread']['iconlevels']));
		$recommendicons = '';
		for($i = 0;$i < $count;$i++) {
			$recommendicons .= '<img src="static/image/common/recommend_'.($i + 1).'.gif" /> ';
		}

		$setting['commentitem'] = explode("\t", $setting['commentitem']);

		showtableheader('', 'nobottom', 'id="heatthread"'.($_G['gp_anchor'] != 'heatthread' ? ' style="display: none"' : ''));
		showsetting('setting_functions_heatthread_reply', 'settingnew[heatthread][reply]', $setting['heatthread']['reply'], 'text');
		showsetting('setting_functions_heatthread_recommend', 'settingnew[heatthread][recommend]', $setting['heatthread']['recommend'], 'text');
		showsetting('setting_functions_heatthread_iconlevels', '', '', '<input name="settingnew[heatthread][iconlevels]" class="txt" type="text" value="'.$setting['heatthread']['iconlevels'].'" /><br />'.$heatthreadicons);
		showtablefooter();

		showtableheader('', 'nobottom', 'id="recommend"'.($_G['gp_anchor'] != 'recommend' ? ' style="display: none"' : ''));
		showsetting('setting_functions_recommend_status', 'settingnew[recommendthread][status]', $setting['recommendthread']['status'], 'radio', 0, 1);
		showsetting('setting_functions_recommend_addtext', 'settingnew[recommendthread][addtext]', $setting['recommendthread']['addtext'], 'text');
		showsetting('setting_functions_recommend_subtracttext', 'settingnew[recommendthread][subtracttext]', $setting['recommendthread']['subtracttext'], 'text');
		showsetting('setting_functions_recommend_defaultshow', array('settingnew[recommendthread][defaultshow]', array(
			array(0, $lang['setting_functions_recommend_defaultshow_0']),
			array(1, $lang['setting_functions_recommend_defaultshow_1']))), $setting['recommendthread']['defaultshow'], 'mradio');
		showsetting('setting_functions_recommend_daycount', 'settingnew[recommendthread][daycount]', intval($setting['recommendthread']['daycount']), 'text');
		showsetting('setting_functions_recommend_ownthread', 'settingnew[recommendthread][ownthread]', $setting['recommendthread']['ownthread'], 'radio');
		showsetting('setting_functions_recommend_iconlevels', '', '', '<input name="settingnew[recommendthread][iconlevels]" class="txt" type="text" value="'.$setting['recommendthread']['iconlevels'].'" /><br />'.$recommendicons);
		showtablefooter();

		showtableheader('', 'nobottom', 'id="comment"'.($_G['gp_anchor'] != 'comment' ? ' style="display: none"' : ''));
		showsetting('setting_functions_comment_number', 'settingnew[commentnumber]', $setting['commentnumber'], 'text');
		showsetting('setting_functions_comment_firstpost', 'settingnew[commentfirstpost]', $setting['commentfirstpost'], 'radio');
		showsetting('setting_functions_comment_postself', 'settingnew[commentpostself]', $setting['commentpostself'], 'radio');
		showsetting('setting_functions_comment_commentitem_0', 'settingnew[commentitem][0]', $setting['commentitem'][0], 'textarea');
		showsetting('setting_functions_comment_commentitem_1', 'settingnew[commentitem][1]', $setting['commentitem'][1], 'textarea');
		showsetting('setting_functions_comment_commentitem_2', 'settingnew[commentitem][2]', $setting['commentitem'][2], 'textarea');
		showsetting('setting_functions_comment_commentitem_3', 'settingnew[commentitem][3]', $setting['commentitem'][3], 'textarea');
		showsetting('setting_functions_comment_commentitem_4', 'settingnew[commentitem][4]', $setting['commentitem'][4], 'textarea');
		showsetting('setting_functions_comment_commentitem_5', 'settingnew[commentitem][5]', $setting['commentitem'][5], 'textarea');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="other"'.($_G['gp_anchor'] != 'other' ? ' style="display: none"' : ''));
		showsetting('setting_functions_other_pwdsafety', 'settingnew[pwdsafety]', $setting['pwdsafety'], 'radio');
		showsetting('setting_functions_other_autoidselect', 'settingnew[autoidselect]', $setting['autoidselect'], 'radio');
		showsetting('setting_functions_other_rssstatus', 'settingnew[rssstatus]', $setting['rssstatus'], 'radio');
		showsetting('setting_functions_other_rssttl', 'settingnew[rssttl]', $setting['rssttl'], 'text');
		showsetting('setting_functions_other_debug', 'settingnew[debug]', $setting['debug'], 'radio');
		showsetting('setting_functions_other_activity_type', 'settingnew[activitytype]', $setting['activitytype'], 'textarea');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'permissions') {

		include_once libfile('function/forumlist');
		$forumselect = '<select name="settingnew[allowviewuserthread][fids][]" multiple="multiple" size="10">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
		$setting['allowviewuserthread'] = unserialize($setting['allowviewuserthread']);
		if($setting['allowviewuserthread']['fids']) {
			foreach($setting['allowviewuserthread']['fids'] as $v) {
				$forumselect = str_replace('<option value="'.$v.'">', '<option value="'.$v.'" selected>', $forumselect);
			}
		}

		showtableheader();
		showsetting('setting_permissions_allowviewuserthread', 'settingnew[allowviewuserthread][allow]', $setting['allowviewuserthread']['allow'], 'radio', 0, 1);
		showsetting('setting_permissions_allowviewuserthread_fids', '', '', $forumselect);
		showtagfooter('tbody');
		showsetting('setting_permissions_memliststatus', 'settingnew[memliststatus]', $setting['memliststatus'], 'radio');
		showsetting('setting_permissions_reportpost', 'settingnew[reportpost]', $setting['reportpost'], 'radio');
		showsetting('setting_permissions_minpostsize', 'settingnew[minpostsize]', $setting['minpostsize'], 'text');
		showsetting('setting_permissions_maxpostsize', 'settingnew[maxpostsize]', $setting['maxpostsize'], 'text');
		showsetting('setting_permissions_alloweditpost', array('settingnew[alloweditpost]', array(
			cplang('thread_general'),
			cplang('thread_poll'),
			cplang('thread_trade'),
			cplang('thread_reward'),
			cplang('thread_activity'),
			cplang('thread_debate')
		)), $setting['alloweditpost'], 'binmcheckbox');
		showsetting('setting_permissions_maxpolloptions', 'settingnew[maxpolloptions]', $setting['maxpolloptions'], 'text');
		showsetting('setting_permissions_editby', 'settingnew[editedby]', $setting['editedby'], 'radio');

		showtitle('nav_setting_rate');
		showsetting('setting_permissions_karmaratelimit', 'settingnew[karmaratelimit]', $setting['karmaratelimit'], 'text');
		showsetting('setting_permissions_modratelimit', 'settingnew[modratelimit]', $setting['modratelimit'], 'radio');
		showsetting('setting_permissions_dupkarmarate', 'settingnew[dupkarmarate]', $setting['dupkarmarate'], 'radio');

	} elseif($operation == 'credits') {

		$rules = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule'));
		while($value = DB::fetch($query)) {
			$rules[$value['rid']] = $value;
		}

		echo '<div id="base"'.($_G['gp_anchor'] != 'base' ? ' style="display: none"' : '').'>';

		showtableheader('setting_credits_extended', 'fixpadding');
		showsubtitle(array('setting_credits_available', 'credits_id', 'credits_img', 'credits_title', 'credits_unit', 'setting_credits_ratio', 'setting_credits_init', 'credits_inport', 'credits_import'), '');

		$setting['extcredits'] = unserialize($setting['extcredits']);
		$setting['initcredits'] = explode(',', $setting['initcredits']);

		for($i = 1; $i <= 8; $i++) {
			showtablerow('', array('width="40"', 'class="td22"', 'class="td22"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[extcredits][$i][available]\" value=\"1\" ".($setting['extcredits'][$i]['available'] ? 'checked' : '')." />",
				'extcredits'.$i,
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[extcredits][$i][img]\" value=\"{$setting['extcredits'][$i]['img']}\">",
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[extcredits][$i][title]\" value=\"{$setting['extcredits'][$i]['title']}\">",
				"<input type=\"text\" class=\"txt\" size=\"5\" name=\"settingnew[extcredits][$i][unit]\" value=\"{$setting['extcredits'][$i]['unit']}\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"settingnew[extcredits][$i][ratio]\" value=\"".(float)$setting['extcredits'][$i]['ratio']."\" onkeyup=\"if(this.value != '0' && \$('allowexchangeout$i').checked == false && \$('allowexchangein$i').checked == false) {\$('allowexchangeout$i').checked = true;\$('allowexchangein$i').checked = true;} else if(this.value == '0') {\$('allowexchangeout$i').checked = false;\$('allowexchangein$i').checked = false;}\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"settingnew[initcredits][$i]\" value=\"".intval($setting['initcredits'][$i])."\">",
				"<input class=\"checkbox\" type=\"checkbox\" size=\"3\" name=\"settingnew[extcredits][$i][allowexchangeout]\" value=\"1\" ".($setting['extcredits'][$i]['allowexchangeout'] ? 'checked' : '')." id=\"allowexchangeout$i\">",
				"<input class=\"checkbox\" type=\"checkbox\" size=\"3\" name=\"settingnew[extcredits][$i][allowexchangein]\" value=\"1\" ".($setting['extcredits'][$i]['allowexchangein'] ? 'checked' : '')." id=\"allowexchangein$i\">"
			));
		}
		showtablerow('', 'colspan="10" class="lineheight"', $lang['setting_credits_extended_comment']);

		showtableheader('setting_credits');
		showsetting('setting_credits_formula', 'settingnew[creditsformula]', $setting['creditsformula'], 'textarea');

		$setting['creditstrans'] = explode(',', $setting['creditstrans']);
		$_G['setting']['creditstrans'] = array();
		for($si = 0; $si < 9; $si++) {
			$_G['setting']['creditstrans'][$si] = '';
			for($i = 0; $i <= 8; $i++) {
				$_G['setting']['creditstrans'][$si] .= '<option value="'.$i.'" '.($i == $setting['creditstrans'][$si] ? 'selected' : '').'>'.($i ? 'extcredits'.$i.($setting['extcredits'][$i]['title'] ? '('.$setting['extcredits'][$i]['title'].')' : '') : ($si > 0 ? $lang['setting_credits_trans_used'] : $lang['none'])).'</option>';
			}
		}
		showsetting('setting_credits_trans', '', '', '<select onchange="if(this.value > 0) {$(\'creditstransextra\').style.display = \'\';} else {$(\'creditstransextra\').style.display = \'none\';}" name="settingnew[creditstrans][0]">'.$_G['setting']['creditstrans'][0].'</select>');
		showtagheader('tbody', 'creditstransextra', $setting['creditstrans'][0], 'sub');
		showsetting('setting_credits_trans1', '', '' ,'<select name="settingnew[creditstrans][1]">'.$_G['setting']['creditstrans'][1].'</select>');
		showsetting('setting_credits_trans2', '', '' ,'<select name="settingnew[creditstrans][2]">'.$_G['setting']['creditstrans'][2].'</select>');
		showsetting('setting_credits_trans3', '', '' ,'<select name="settingnew[creditstrans][3]">'.$_G['setting']['creditstrans'][3].'</select>');
		showhiddenfields(array('settingnew[creditstrans][4]' => 0));
		showsetting('setting_credits_trans5', '', '' ,'<select name="settingnew[creditstrans][5]"><option value="-1">'.$lang['setting_credits_trans5_none'].'</option>'.$_G['setting']['creditstrans'][5].'</select>');
		showsetting('setting_credits_trans6', '', '' ,'<select name="settingnew[creditstrans][6]">'.$_G['setting']['creditstrans'][6].'</select>');
		showsetting('setting_credits_trans7', '', '' ,'<select name="settingnew[creditstrans][7]">'.$_G['setting']['creditstrans'][7].'</select>');
		showsetting('setting_credits_trans8', '', '' ,'<select name="settingnew[creditstrans][8]">'.$_G['setting']['creditstrans'][8].'</select>');
		showtagfooter('tbody');
		showsetting('setting_credits_tax', 'settingnew[creditstax]', $setting['creditstax'], 'text');
		showsetting('setting_credits_mintransfer', 'settingnew[transfermincredits]', $setting['transfermincredits'], 'text');
		showsetting('setting_credits_minexchange', 'settingnew[exchangemincredits]', $setting['exchangemincredits'], 'text');
		showsetting('setting_credits_maxincperthread', 'settingnew[maxincperthread]', $setting['maxincperthread'], 'text');
		showsetting('setting_credits_maxchargespan', 'settingnew[maxchargespan]', $setting['maxchargespan'], 'text');
		showtablefooter();
		echo '</div>';

	} elseif($operation == 'mail' && $isfounder) {

		$setting['mail'] = unserialize($setting['mail']);
		$passwordmask = $setting['mail']['auth_password'] ? $setting['mail']['auth_password']{0}.'********'.substr($setting['mail']['auth_password'], -2) : '';

		showtableheader('', '', 'id="mailsetting"'.($_G['gp_anchor'] != 'setting' ? ' style="display: none"' : ''));
		showsetting('setting_mail_setting_send', array('settingnew[mail][mailsend]', array(
			array(1, $lang['setting_mail_setting_send_1'], array('hidden1' => 'none', 'hidden2' => 'none')),
			array(2, $lang['setting_mail_setting_send_2'], array('hidden1' => '', 'hidden2' => '')),
			array(3, $lang['setting_mail_setting_send_3'], array('hidden1' => '', 'hidden2' => 'none'))
		)), $setting['mail']['mailsend'], 'mradio');
		showtagheader('tbody', 'hidden1', $setting['mail']['mailsend'] != 1, 'sub');
		showsetting('setting_mail_setting_server', 'settingnew[mail][server]', $setting['mail']['server'], 'text');
		showsetting('setting_mail_setting_port', 'settingnew[mail][port]', $setting['mail']['port'], 'text');
		showtagfooter('tbody');
		showtagheader('tbody', 'hidden2', $setting['mail']['mailsend'] == 2, 'sub');
		showsetting('setting_mail_setting_auth', 'settingnew[mail][auth]', $setting['mail']['auth'], 'radio');
		showsetting('setting_mail_setting_from', 'settingnew[mail][from]', $setting['mail']['from'], 'text');
		showsetting('setting_mail_setting_username', 'settingnew[mail][auth_username]', $setting['mail']['auth_username'], 'text');
		showsetting('setting_mail_setting_password', 'settingnew[mail][auth_password]', $passwordmask, 'text');
		showtagfooter('tbody');
		showsetting('setting_mail_setting_delimiter', array('settingnew[mail][maildelimiter]', array(
			array(1, $lang['setting_mail_setting_delimiter_crlf']),
			array(0, $lang['setting_mail_setting_delimiter_lf']),
			array(2, $lang['setting_mail_setting_delimiter_cr']))),  $setting['mail']['maildelimiter'], 'mradio');
		showsetting('setting_mail_setting_includeuser', 'settingnew[mail][mailusername]', $setting['mail']['mailusername'], 'radio');
		showsetting('setting_mail_setting_silent', 'settingnew[mail][sendmail_silent]', $setting['mail']['sendmail_silent'], 'radio');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="mailcheck"'.($_G['gp_anchor'] != 'check' ? ' style="display: none"' : ''));
		showsetting('setting_mail_check_test_from', 'test_from', '', 'text');
		showsetting('setting_mail_check_test_to', 'test_to', '', 'textarea');
		showsubmit('', '', '<input type="submit" class="btn" name="mailcheck" value="'.cplang('setting_mail_check_submit').'" onclick="this.form.action=\''.ADMINSCRIPT.'?action=checktools&operation=mailcheck&frame=no\';this.form.target=\'mailcheckiframe\'">', '<iframe name="mailcheckiframe" style="display: none"></iframe>');
		showtablefooter();

		showformfooter();
		exit;

	} elseif($operation == 'sec') {

		$seccodecheck = $secreturn = 1;
		$sectpl = '<br /><sec>: <sec><sec>';
		include template('common/seccheck');

		$checksc = array();
		$setting['seccodedata'] = unserialize($setting['seccodedata']);

		$seccodetypearray = array(
			array(0, cplang('setting_sec_seccode_type_image'), array('seccodeimageext' => '', 'seccodeimagewh' => '')),
			array(1, cplang('setting_sec_seccode_type_chnfont'), array('seccodeimageext' => '', 'seccodeimagewh' => '')),
			array(2, cplang('setting_sec_seccode_type_flash'), array('seccodeimageext' => 'none', 'seccodeimagewh' => '')),
			array(3, cplang('setting_sec_seccode_type_wav'), array('seccodeimageext' => 'none', 'seccodeimagewh' => 'none')),
			array(99, cplang('setting_sec_seccode_type_bitmap'), array('seccodeimageext' => 'none', 'seccodeimagewh' => 'none')),
		);

		showtips('setting_sec_code_tips', 'seccode_tips', $_G['gp_anchor'] == 'seccode');
		showtips('setting_sec_qaa_tips', 'secqaa_tips', $_G['gp_anchor'] == 'secqaa');

		showtableheader('', '', 'id="base"'.($_G['gp_anchor'] != 'base' ? ' style="display: none"' : ''));
		showsetting('setting_serveropti_floodctrl', 'settingnew[floodctrl]', $setting['floodctrl'], 'text');
		showsetting('setting_sec_base_need_email', 'settingnew[need_email]', $setting['need_email'], 'radio');
		showsetting('setting_sec_base_need_avatar', 'settingnew[need_avatar]', $setting['need_avatar'], 'radio');
		showsetting('setting_sec_base_need_friendnum', 'settingnew[need_friendnum]', $setting['need_friendnum'], 'text');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="seccode"'.($_G['gp_anchor'] != 'seccode' ? ' style="display: none"' : ''));
		showsetting('setting_sec_seccode_status', array('settingnew[seccodestatus]', array(
			cplang('setting_sec_seccode_status_register'),
			cplang('setting_sec_seccode_status_login'),
			cplang('setting_sec_seccode_status_post'),
			cplang('setting_sec_seccode_status_password')
		)), $setting['seccodestatus'], 'binmcheckbox');
		showsetting('setting_sec_seccode_minposts', 'settingnew[seccodedata][minposts]', $setting['seccodedata']['minposts'], 'text');
		showsetting('setting_sec_seccode_type', array('settingnew[seccodedata][type]', $seccodetypearray), $setting['seccodedata']['type'], 'mradio', '', 0, cplang('setting_sec_seccode_type_comment').$seccheckhtml);
		showtagheader('tbody', 'seccodeimagewh', $setting['seccodedata']['type'] != 3 && $setting['seccodedata']['type'] != 99, 'sub');
		showsetting('setting_sec_seccode_width', 'settingnew[seccodedata][width]', $setting['seccodedata']['width'], 'text');
		showsetting('setting_sec_seccode_height', 'settingnew[seccodedata][height]', $setting['seccodedata']['height'], 'text');
		showtagfooter('tbody');
		showtagheader('tbody', 'seccodeimageext', $setting['seccodedata']['type'] != 2 && $setting['seccodedata']['type'] != 3 && $setting['seccodedata']['type'] != 99, 'sub');
		showsetting('setting_sec_seccode_scatter', 'settingnew[seccodedata][scatter]', $setting['seccodedata']['scatter'], 'text');
		showsetting('setting_sec_seccode_background', 'settingnew[seccodedata][background]', $setting['seccodedata']['background'], 'radio');
		showsetting('setting_sec_seccode_adulterate', 'settingnew[seccodedata][adulterate]', $setting['seccodedata']['adulterate'], 'radio');
		showsetting('setting_sec_seccode_ttf', 'settingnew[seccodedata][ttf]', $setting['seccodedata']['ttf'], 'radio', !function_exists('imagettftext'));
		showsetting('setting_sec_seccode_angle', 'settingnew[seccodedata][angle]', $setting['seccodedata']['angle'], 'radio');
		showsetting('setting_sec_seccode_warping', 'settingnew[seccodedata][warping]', $setting['seccodedata']['warping'], 'radio');
		showsetting('setting_sec_seccode_color', 'settingnew[seccodedata][color]', $setting['seccodedata']['color'], 'radio');
		showsetting('setting_sec_seccode_size', 'settingnew[seccodedata][size]', $setting['seccodedata']['size'], 'radio');
		showsetting('setting_sec_seccode_shadow', 'settingnew[seccodedata][shadow]', $setting['seccodedata']['shadow'], 'radio');
		showsetting('setting_sec_seccode_animator', 'settingnew[seccodedata][animator]', $setting['seccodedata']['animator'], 'radio', !function_exists('imagegif'));
		showtagfooter('tbody');
		showsubmit('settingsubmit');
		showtablefooter();

		$setting['secqaa'] = unserialize($setting['secqaa']);
		$start_limit = ($page - 1) * 10;
		$secqaanums = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_secquestion')."");
		$multipage = multi($secqaanums, 10, $page, ADMINSCRIPT.'?action=setting&operation=sec&anchor=secqaa');

		$query = DB::query("SELECT * FROM ".DB::table('common_secquestion')." LIMIT $start_limit, 10");

		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1,''], [1,'<input name="newquestion[]" type="text" class="txt">','td26'], [1, '<input name="newanswer[]" type="text" class="txt">']],
	];
	</script>
EOT;
		showtagheader('div', 'secqaa', $_G['gp_anchor'] == 'secqaa');
		showtableheader('setting_sec_secqaa', 'nobottom');
		showsetting('setting_sec_secqaa_status', array('settingnew[secqaa][status]', array(
			cplang('setting_sec_seccode_status_register'),
			cplang('setting_sec_seccode_status_post'),
			cplang('setting_sec_seccode_status_password')
		)), $setting['secqaa']['status'], 'binmcheckbox');
		showsetting('setting_sec_secqaa_minposts', 'settingnew[secqaa][minposts]', $setting['secqaa']['minposts'], 'text');
		showtablefooter();

		showtableheader('setting_sec_secqaa_qaa', 'noborder fixpadding');
		showsubtitle(array('', 'setting_sec_secqaa_question', 'setting_sec_secqaa_answer'));

		$qaaext = array();
		while($item = DB::fetch($query)) {
			if(!$item['type']) {
				showtablerow('', array('', 'class="td26"'), array(
					'<input class="checkbox" type="checkbox" name="delete[]" value="'.$item['id'].'">',
					'<input type="text" class="txt" name="question['.$item['id'].']" value="'.dhtmlspecialchars($item['question']).'" class="txtnobd" onblur="this.className=\'txtnobd\'" onfocus="this.className=\'txt\'">',
					'<input type="text" class="txt" name="answer['.$item['id'].']" value="'.$item['answer'].'" class="txtnobd" onblur="this.className=\'txtnobd\'" onfocus="this.className=\'txt\'">'
				));
			} else {
				$qaaext[] = $item['question'];
			}
		}
		echo '<tr><td></td><td class="td26"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['setting_sec_secqaa_add'].'</a></div></td><td></td></tr>';

		$dir = DISCUZ_ROOT.'./source/class/secqaa';
		$qaadir = dir($dir);
		$secqaaext = array();
		while($entry = $qaadir->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^secqaa\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
				@include_once $dir.'/'.$entry;
				$qaaclass = substr($entry, 0, -4);
				if(class_exists($qaaclass)) {
					$qaa = new $qaaclass();
					$script = substr($qaaclass, 7);
					showtablerow('', array('', 'class="td26"'), array(
						'',
						'<label>'.($qaa->copyright ? '<div class="right">'.lang('secqaa/'.$script, $qaa->copyright).'</div>' : '').'<label><input class="checkbox" class="checkbox" type="checkbox" name="secqaaext[]" value="'.$script.'"'.(in_array($script, $qaaext) ? ' checked="checked"' : '').'> '.lang('secqaa/'.$script, $qaa->name).(@filemtime($dir.'/'.$entry) > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : '').($qaa->description ? '<div class="lightfont" style="margin-left:30px">'.lang('secqaa/'.$script, $qaa->description).'</div>' : '').'</label>'
					));
				}
			}
		}

		showsubmit('settingsubmit', 'submit', 'del', '', $multipage);
		showtablefooter();
		showtagfooter('div');

		showformfooter();
		exit;


	} elseif($operation == 'datetime') {

		$checktimeformat = array($setting['timeformat'] == 'H:i' ? 24 : 12 => 'checked');

		$setting['userdateformat'] = dateformat($setting['userdateformat']);
		$setting['dateformat'] = dateformat($setting['dateformat']);

		showtableheader();
		showtitle('setting_datetime_format');
		showsetting('setting_datetime_dateformat', 'settingnew[dateformat]', $setting['dateformat'], 'text');
		showsetting('setting_datetime_timeformat', '', '', '<input class="radio" type="radio" name="settingnew[timeformat]" value="24" '.$checktimeformat[24].'> 24 '.$lang['hour'].' <input class="radio" type="radio" name="settingnew[timeformat]" value="12" '.$checktimeformat[12].'> 12 '.$lang['hour'].'');
		showsetting('setting_datetime_dateconvert', 'settingnew[dateconvert]', $setting['dateconvert'], 'radio');
		showsetting('setting_datetime_timeoffset', 'settingnew[timeoffset]', $setting['timeoffset'], 'text');

		showtitle('setting_datetime_periods');
		showsetting('setting_datetime_visitbanperiods', 'settingnew[visitbanperiods]', $setting['visitbanperiods'], 'textarea');
		showsetting('setting_datetime_postbanperiods', 'settingnew[postbanperiods]', $setting['postbanperiods'], 'textarea');
		showsetting('setting_datetime_postmodperiods', 'settingnew[postmodperiods]', $setting['postmodperiods'], 'textarea');
		showsetting('setting_datetime_ban_downtime', 'settingnew[attachbanperiods]', $setting['attachbanperiods'], 'textarea');
		showsetting('setting_datetime_searchbanperiods', 'settingnew[searchbanperiods]', $setting['searchbanperiods'], 'textarea');

	} elseif($operation == 'attach') {

		$checkwm = array($setting['watermarkstatus'] => 'checked');
		$checkmkdirfunc = !function_exists('mkdir') ? 'disabled' : '';
		$setting['watermarktext'] = unserialize($setting['watermarktext']);
		$setting['watermarktext']['fontpath'] = str_replace(array('ch/', 'en/'), '', $setting['watermarktext']['fontpath']);

		$fontlist = '<select name="settingnew[watermarktext][fontpath]">';
		$dir = opendir(DISCUZ_ROOT.'./static/image/seccode/font/en');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$dir = opendir(DISCUZ_ROOT.'./static/image/seccode/font/ch');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$fontlist .= '</select>';

		showtips('setting_attach_tips');

		showtableheader('', '', 'id="basic"'.($_G['gp_anchor'] != 'basic' ? ' style="display: none"' : ''));
		showsetting('setting_attach_basic_dir', 'settingnew[attachdir]', $setting['attachdir'], 'text');
		showsetting('setting_attach_basic_url', 'settingnew[attachurl]', $setting['attachurl'], 'text');
		showsetting('setting_attach_image_lib', array('settingnew[imagelib]', array(
			array(0, $lang['setting_attach_image_watermarktype_GD'], array('imagelibext' => 'none')),
			array(1, $lang['setting_attach_image_watermarktype_IM'], array('imagelibext' => ''))
		)), $setting['imagelib'], 'mradio');
		showtagheader('tbody', 'imagelibext', $setting['imagelib'], 'sub');
		showsetting('setting_attach_image_impath', 'settingnew[imageimpath]', $setting['imageimpath'], 'text');
		showtagfooter('tbody');
		showsetting('setting_attach_image_thumbquality', 'settingnew[thumbquality]', $setting['thumbquality'], 'text');
		showsetting('setting_attach_image_watermarkstatus', '', '', '<table style="margin-bottom: 3px; margin-top:3px;"><tr><td colspan="3"><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="0" '.$checkwm[0].'>'.$lang['setting_attach_image_watermarkstatus_none'].'</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="1" '.$checkwm[1].'> #1</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="2" '.$checkwm[2].'> #2</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="3" '.$checkwm[3].'> #3</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="4" '.$checkwm[4].'> #4</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="5" '.$checkwm[5].'> #5</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="6" '.$checkwm[6].'> #6</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="7" '.$checkwm[7].'> #7</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="8" '.$checkwm[8].'> #8</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus]" value="9" '.$checkwm[9].'> #9</td></tr></table>');
		showsetting('setting_attach_image_watermarkminwidthheight', array('settingnew[watermarkminwidth]', 'settingnew[watermarkminheight]'), array(intval($setting['watermarkminwidth']), intval($setting['watermarkminheight'])), 'multiply');
		showsetting('setting_attach_image_watermarktype', array('settingnew[watermarktype]', array(
			array('gif', $lang['setting_attach_image_watermarktype_gif'], array('watermarktypeext' => 'none')),
			array('png', $lang['setting_attach_image_watermarktype_png'], array('watermarktypeext' => 'none')),
			array('text', $lang['setting_attach_image_watermarktype_text'], array('watermarktypeext' => ''))
		)), $setting['watermarktype'], 'mradio');
		showsetting('setting_attach_image_watermarktrans', 'settingnew[watermarktrans]', $setting['watermarktrans'], 'text');
		showsetting('setting_attach_image_watermarkquality', 'settingnew[watermarkquality]', $setting['watermarkquality'], 'text');
		showtagheader('tbody', 'watermarktypeext', $setting['watermarktype'] == 'text', 'sub');
		showsetting('setting_attach_image_watermarktext_text', 'settingnew[watermarktext][text]', $setting['watermarktext']['text'], 'textarea');
		showsetting('setting_attach_image_watermarktext_fontpath', '', '', $fontlist);
		showsetting('setting_attach_image_watermarktext_size', 'settingnew[watermarktext][size]', $setting['watermarktext']['size'], 'text');
		showsetting('setting_attach_image_watermarktext_angle', 'settingnew[watermarktext][angle]', $setting['watermarktext']['angle'], 'text');
		showsetting('setting_attach_image_watermarktext_color', 'settingnew[watermarktext][color]', $setting['watermarktext']['color'], 'color');
		showsetting('setting_attach_image_watermarktext_shadowx', 'settingnew[watermarktext][shadowx]', $setting['watermarktext']['shadowx'], 'text');
		showsetting('setting_attach_image_watermarktext_shadowy', 'settingnew[watermarktext][shadowy]', $setting['watermarktext']['shadowy'], 'text');
		showsetting('setting_attach_image_watermarktext_shadowcolor', 'settingnew[watermarktext][shadowcolor]', $setting['watermarktext']['shadowcolor'], 'color');
		showsetting('setting_attach_image_watermarktext_imtranslatex', 'settingnew[watermarktext][translatex]', $setting['watermarktext']['translatex'], 'text');
		showsetting('setting_attach_image_watermarktext_imtranslatey', 'settingnew[watermarktext][translatey]', $setting['watermarktext']['translatey'], 'text');
		showsetting('setting_attach_image_watermarktext_imskewx', 'settingnew[watermarktext][skewx]', $setting['watermarktext']['skewx'], 'text');
		showsetting('setting_attach_image_watermarktext_imskewy', 'settingnew[watermarktext][skewy]', $setting['watermarktext']['skewy'], 'text');
		showtagfooter('tbody');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="forumattach"'.($_G['gp_anchor'] != 'forumattach' ? ' style="display: none"' : ''));
		showsetting('setting_attach_basic_imgpost', 'settingnew[attachimgpost]', $setting['attachimgpost'], 'radio');
		$setting['swfupload'] = $setting['swfupload'] == 2 ? array(0, 1) : array($setting['swfupload']);
		showsetting('setting_attach_basic_swfupload', array('settingnew[swfupload]', array(array(0, $lang['setting_attach_basic_simple']), array(1, $lang['setting_attach_basic_multi']))), $setting['swfupload'], 'mcheckbox');
		showsetting('setting_attach_basic_allowattachurl', 'settingnew[allowattachurl]', $setting['allowattachurl'], 'radio');
		showsetting('setting_attach_image_thumbstatus', array('settingnew[thumbstatus]', array(
			array('', $lang['setting_attach_image_thumbstatus_none'], array('thumbext' => 'none')),
			array('fixnone', $lang['setting_attach_image_thumbstatus_fixnone'], array('thumbext' => '')),
			array('fixwr', $lang['setting_attach_image_thumbstatus_fixwr'], array('thumbext' => '')),
		)), $setting['thumbstatus'], 'mradio');
		showsetting('setting_attach_basic_thumbsource', 'settingnew[thumbsource]', $setting['thumbsource'], 'radio');
		showtagheader('tbody', 'thumbext', $setting['thumbstatus'], 'sub');
		showsetting('setting_attach_image_thumbwidthheight', array('settingnew[thumbwidth]', 'settingnew[thumbheight]'), array(intval($setting['thumbwidth']), intval($setting['thumbheight'])), 'multiply');
		showtagfooter('tbody');
		showsetting('setting_attach_antileech_expire', 'settingnew[attachexpire]', $setting['attachexpire'], 'text');
		showsetting('setting_attach_antileech_refcheck', 'settingnew[attachrefcheck]', $setting['attachrefcheck'], 'radio');
		showtagfooter('tbody');

		showsubmit('settingsubmit');
		showtablefooter();

		if($isfounder) {

			$setting['ftp'] = unserialize($setting['ftp']);
			$setting['ftp'] = is_array($setting['ftp']) ? $setting['ftp'] : array();
			$setting['ftp']['password'] = authcode($setting['ftp']['password'], 'DECODE', md5($_G['config']['security']['authkey']));
			$setting['ftp']['password'] = $setting['ftp']['password'] ? $setting['ftp']['password']{0}.'********'.$setting['ftp']['password']{strlen($setting['ftp']['password']) - 1} : '';

			showtableheader('', '', 'id="remote"'.($_G['gp_anchor'] != 'remote' ? ' style="display: none"' : ''));
			showsetting('setting_attach_remote_enabled', array('settingnew[ftp][on]', array(
				array(1, $lang['yes'], array('ftpext' => '', 'ftpcheckbutton' => '')),
				array(0, $lang['no'], array('ftpext' => 'none', 'ftpcheckbutton' => 'none'))
			), TRUE), $setting['ftp']['on'], 'mradio');
			showtagheader('tbody', 'ftpext', $setting['ftp']['on'], 'sub');
			showsetting('setting_attach_remote_enabled_ssl', 'settingnew[ftp][ssl]', $setting['ftp']['ssl'], 'radio');
			showsetting('setting_attach_remote_ftp_host', 'settingnew[ftp][host]', $setting['ftp']['host'], 'text');
			showsetting('setting_attach_remote_ftp_port', 'settingnew[ftp][port]', $setting['ftp']['port'], 'text');
			showsetting('setting_attach_remote_ftp_user', 'settingnew[ftp][username]', $setting['ftp']['username'], 'text');
			showsetting('setting_attach_remote_ftp_pass', 'settingnew[ftp][password]', $setting['ftp']['password'], 'text');
			showsetting('setting_attach_remote_ftp_pasv', 'settingnew[ftp][pasv]', $setting['ftp']['pasv'], 'radio');
			showsetting('setting_attach_remote_dir', 'settingnew[ftp][attachdir]', $setting['ftp']['attachdir'], 'text');
			showsetting('setting_attach_remote_url', 'settingnew[ftp][attachurl]', $setting['ftp']['attachurl'], 'text');
			showsetting('setting_attach_remote_timeout', 'settingnew[ftp][timeout]', $setting['ftp']['timeout'], 'text');
			showtagfooter('tbody');

			showsetting('setting_attach_remote_allowedexts', 'settingnew[ftp][allowedexts]', $setting['ftp']['allowedexts'], 'textarea');
			showsetting('setting_attach_remote_disallowedexts', 'settingnew[ftp][disallowedexts]', $setting['ftp']['disallowedexts'], 'textarea');
			showsetting('setting_attach_remote_minsize', 'settingnew[ftp][minsize]', $setting['ftp']['minsize'], 'text');
			showsetting('setting_attach_antileech_remote_hide_dir', 'settingnew[ftp][hideurl]', $setting['ftp']['hideurl'], 'radio');

			showsubmit('settingsubmit');
			showtablefooter();
		}

		showtableheader('', '', 'id="albumattach"'.($_G['gp_anchor'] != 'albumattach' ? ' style="display: none"' : ''));
		showsetting('setting_attach_album_maxtimage', array('settingnew[maxthumbwidth]', 'settingnew[maxthumbheight]'), array(intval($setting['maxthumbwidth']), intval($setting['maxthumbheight'])), 'multiply');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'search') {

		$setting['search'] = unserialize($setting['search']);

		showtableheader('setting_search_status', 'fixpadding');
		showsubtitle(array('setting_search_onoff', 'search_item_name', 'setting_serveropti_searchctrl', 'setting_serveropti_maxspm', 'setting_serveropti_maxsearchresults'));
		$search_portal = array(
				$setting['search']['portal']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][portal][status]" value="1" checked="checked" />' : '<input type="checkbox" name="settingnew[search][portal][status]" value="1" />',
				cplang('setting_search_status_portal'),
				'<input type="text" class="txt" name="settingnew[search][portal][searchctrl]" value="'.$setting['search']['portal']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][portal][maxspm]" value="'.$setting['search']['portal']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][portal][maxsearchresults]" value="'.$setting['search']['portal']['maxsearchresults'].'" />',
			);
		$search_forum = array(
				$setting['search']['forum']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][forum][status]" value="1" checked="checked" />' : '<input type="checkbox" name="settingnew[search][forum][status]" value="1" />',
				cplang('setting_search_status_forum'),
				'<input type="text" class="txt" name="settingnew[search][forum][searchctrl]" value="'.$setting['search']['forum']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][forum][maxspm]" value="'.$setting['search']['forum']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][forum][maxsearchresults]" value="'.$setting['search']['forum']['maxsearchresults'].'" />',
			);
		$search_blog = array(
				$setting['search']['blog']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][blog][status]" value="1" checked="checked" />' : '<input type="checkbox" name="settingnew[search][blog][status]" value="1" />',
				cplang('setting_search_status_blog'),
				'<input type="text" class="txt" name="settingnew[search][blog][searchctrl]" value="'.$setting['search']['blog']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][blog][maxspm]" value="'.$setting['search']['blog']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][blog][maxsearchresults]" value="'.$setting['search']['blog']['maxsearchresults'].'" />',
			);
		$search_album = array(
				$setting['search']['album']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][album][status]" value="1" checked="checked" />' : '<input type="checkbox" name="settingnew[search][album][status]" value="1" />',
				cplang('setting_search_status_album'),
				'<input type="text" class="txt" name="settingnew[search][album][searchctrl]" value="'.$setting['search']['album']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][album][maxspm]" value="'.$setting['search']['album']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][album][maxsearchresults]" value="'.$setting['search']['album']['maxsearchresults'].'" />',
			);
		$search_group = array(
				$setting['search']['group']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][group][status]" value="1" checked="checked" />' : '<input type="checkbox" name="settingnew[search][group][status]" value="1" />',
				cplang('setting_search_status_group'),
				'<input type="text" class="txt" name="settingnew[search][group][searchctrl]" value="'.$setting['search']['group']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][group][maxspm]" value="'.$setting['search']['group']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][group][maxsearchresults]" value="'.$setting['search']['group']['maxsearchresults'].'" />',
			);
		showtablerow('', array('width="100"', 'width="120"', 'width="120"', 'width="120"'), $search_portal);
		showtablerow('', '', $search_forum);
		showtablerow('', '', $search_blog);
		showtablerow('', '', $search_album);
		showtablerow('', '', $search_group);
		showtablefooter();

		showtableheader('settings_sphinx', 'fixpadding');
        showsetting('settings_sphinx_sphinxon', 'settingnew[sphinxon]', $setting['sphinxon'], 'radio');
        showsetting('settings_sphinx_sphinxhost', 'settingnew[sphinxhost]', $setting['sphinxhost'], 'text');
        showsetting('settings_sphinx_sphinxport', 'settingnew[sphinxport]', $setting['sphinxport'], 'text');
        showsetting('settings_sphinx_sphinxsubindex', 'settingnew[sphinxsubindex]', $setting['sphinxsubindex'], 'text');
        showsetting('settings_sphinx_sphinxmsgindex', 'settingnew[sphinxmsgindex]', $setting['sphinxmsgindex'], 'text');
        showsetting('settings_sphinx_sphinxmaxquerytime', 'settingnew[sphinxmaxquerytime]', $setting['sphinxmaxquerytime'], 'text');
        showsetting('settings_sphinx_sphinxlimit', 'settingnew[sphinxlimit]', $setting['sphinxlimit'], 'text');
        $spx_ranks = array('SPH_RANK_PROXIMITY_BM25', 'SPH_RANK_BM25', 'SPH_RANK_NONE');
        $selectspxrank = '';
        $selectspxrank = '<select name="settingnew[sphinxrank]">';
        foreach($spx_ranks as $spx_rank) {
            $selectspxrank.= '<option value="'.$spx_rank.'"'.($spx_rank == $setting['sphinxrank'] ? 'selected="selected"' : '').'>'.$spx_rank.'</option>';
        }
        $selectspxrank .='</select>';
        showsetting('settings_sphinx_sphinxrank', '', '', $selectspxrank);
        showtablefooter();

	} elseif($operation == 'uc' && $isfounder) {

		$disable = !is_writeable(DISCUZ_ROOT . './config/config_ucenter.php');
		include DISCUZ_ROOT.'./config/config_ucenter.php';

		showtips('setting_uc_tips');
		showtableheader();
		showsetting('setting_uc_appid', 'settingnew[uc][appid]', UC_APPID, 'text', $disable);
		showsetting('setting_uc_key', 'settingnew[uc][key]', UC_KEY, 'text', $disable);
		showsetting('setting_uc_api', 'settingnew[uc][api]', UC_API, 'text', $disable);
		showsetting('setting_uc_ip', 'settingnew[uc][ip]', UC_IP, 'text', $disable);
		showsetting('setting_uc_connect', array('settingnew[uc][connect]', array(
			array('mysql', $lang['setting_uc_connect_mysql'], array('ucmysql' => '')),
			array('', $lang['setting_uc_connect_api'], array('ucmysql' => 'none')))), UC_CONNECT, 'mradio', $disable);
		list($ucdbname, $uctablepre) = strpos(UC_DBTABLEPRE, '.') ? explode('.', str_replace('`', '', UC_DBTABLEPRE)) : array(UC_DBNAME, UC_DBTABLEPRE);
		showtagheader('tbody', 'ucmysql', UC_CONNECT, 'sub');
		showsetting('setting_uc_dbhost', 'settingnew[uc][dbhost]', UC_DBHOST, 'text', $disable);
		showsetting('setting_uc_dbuser', 'settingnew[uc][dbuser]', UC_DBUSER, 'text', $disable);
		showsetting('setting_uc_dbpass', 'settingnew[uc][dbpass]', '********', 'text', $disable);
		showsetting('setting_uc_dbname', 'settingnew[uc][dbname]', $ucdbname, 'text', $disable);
		showsetting('setting_uc_dbtablepre', 'settingnew[uc][dbtablepre]', $uctablepre, 'text', $disable);
		showtagfooter('tbody');
		showsetting('setting_uc_activation', 'settingnew[ucactivation]', $setting['ucactivation'], 'radio');
		showsetting('setting_uc_avatarmethod', array('settingnew[avatarmethod]', array(
			array(0, $lang['setting_uc_avatarmethod_0']),
			array(1, $lang['setting_uc_avatarmethod_1']),
			)), $setting['avatarmethod'], 'mradio');

	} elseif($operation == 'ec') {

		showtableheader();
		showtitle('setting_ec_credittrade');
		showsetting('setting_ec_ratio', 'settingnew[ec_ratio]', $setting['ec_ratio'], 'text');
		showsetting('setting_ec_mincredits', 'settingnew[ec_mincredits]', $setting['ec_mincredits'], 'text');
		showsetting('setting_ec_maxcredits', 'settingnew[ec_maxcredits]', $setting['ec_maxcredits'], 'text');
		showsetting('setting_ec_maxcreditspermonth', 'settingnew[ec_maxcreditspermonth]', $setting['ec_maxcreditspermonth'], 'text');

	} elseif($operation == 'memory') {

		showtips('setting_memory_tips');
		showtableheader('setting_memory_status', 'fixpadding');
		showsubtitle(array('setting_memory_state_interface', 'setting_memory_state_extension', 'setting_memory_state_config', 'setting_memory_clear', ''));

		$do_clear_ok = $do == 'clear' ? cplang('setting_memory_do_clear') : '';
		$do_clear_link = '<a href="'.ADMINSCRIPT.'?action=setting&operation=memory&do=clear">'.cplang('setting_memory_clear').'</a>'.$do_clear_ok;

		$ea = array('eAccelerator',
			$discuz->mem->extension['eaccelerator'] ? cplang('setting_memory_php_enable') : cplang('setting_memory_php_disable'),
			$discuz->mem->config['eaccelerator'] ? cplang('open') : cplang('closed'),
			$discuz->mem->type == 'eaccelerator' ? $do_clear_link : '--'
			);
		$memcache = array('memcache',
			$discuz->mem->extension['memcache'] ? cplang('setting_memory_php_enable') : cplang('setting_memory_php_disable'),
			$discuz->mem->config['memcache']['server'] ? cplang('open') : cplang('closed'),
			$discuz->mem->type == 'memcache' ? $do_clear_link : '--'
			);
		$xcache = array('Xcache',
			$discuz->mem->extension['xcache'] ? cplang('setting_memory_php_enable') : cplang('setting_memory_php_disable'),
			$discuz->mem->config['xcache'] ? cplang('open') : cplang('closed'),
			$discuz->mem->type == 'xcache' ? $do_clear_link : '--'
			);

		showtablerow('', array('width="100"', 'width="120"', 'width="120"'), $memcache);
		showtablerow('', '', $ea);
		showtablerow('', '', $xcache);
		showtablefooter();

		if(!isset($setting['memory'])) {
			DB::insert('common_setting', array('skey' => 'memory', 'svalue' =>''), false, true);
			$setting['memory'] = '';
		}

		if($do == 'clear') {
			$discuz->mem->clear();
		}

		$setting['memory'] = unserialize($setting['memory']);
		showtableheader('setting_memory_function', 'fixpadding');
		showsubtitle(array('setting_memory_func', 'setting_memory_func_enable', 'setting_memory_func_ttl', ''));

		$func_array = array('forumindex', 'diyblock', 'diyblockoutput');

		foreach ($func_array as $skey) {
			showtablerow('', array('width="100"', 'width="120"', 'width="120"'), array(
					cplang('setting_memory_func_'.$skey),
					'<input type="checkbox" class="checkbox" name="settingnew[memory]['.$skey.'][enable]" '.($setting['memory'][$skey]['enable'] ? 'checked' : '').' value="1">',
					'<input type="text" class="txt" name="settingnew[memory]['.$skey.'][ttl]" value="'.$setting['memory'][$skey]['ttl'].'">',
					''
					));
		}

	} else {

		cpmsg('undefined_action');

	}

	showsubmit('settingsubmit', 'submit', '', $extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
	showtablefooter();
	showformfooter();

} else {

	$settingnew = $_G['gp_settingnew'];

	if($operation == 'credits') {
		$extcredits_exists = 0;
		foreach($settingnew['extcredits'] as $val) {
			if(isset($val['available']) && $val['available'] == 1) {
				$extcredits_exists = 1;
				break;
			}
		}
		if(!$extcredits_exists) {
			cpmsg('setting_extcredits_must_available');
		}
	}

	if($operation == 'uc' && is_writeable('./config/config_ucenter.php') && $isfounder) {
		require_once './config/config_ucenter.php';
		$ucdbpassnew = $settingnew['uc']['dbpass'] == '********' ? UC_DBPW : $settingnew['uc']['dbpass'];
		if($settingnew['uc']['connect']) {
			$uc_dblink = @mysql_connect($settingnew['uc']['dbhost'], $settingnew['uc']['dbuser'], $ucdbpassnew, 1);
			if(!$uc_dblink) {
				cpmsg('uc_database_connect_error', '', 'error');
			} else {
				mysql_close($uc_dblink);
			}
		}

		$fp = fopen('./config/config_ucenter.php', 'r');
		$configfile = fread($fp, filesize('./config/config_ucenter.php'));
		$configfile = trim($configfile);
		$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
		fclose($fp);

		$connect = '';
		if($settingnew['uc']['connect']) {
			$connect = 'mysql';
			$samelink = ($dbhost == $settingnew['uc']['dbhost'] && $dbuser == $settingnew['uc']['dbuser'] && $dbpw == $ucdbpassnew);
			$samecharset = !($dbcharset == 'gbk' && UC_DBCHARSET == 'latin1' || $dbcharset == 'latin1' && UC_DBCHARSET == 'gbk');
			$configfile = str_replace("define('UC_DBHOST', '".addslashes(UC_DBHOST)."')", "define('UC_DBHOST', '".$settingnew['uc']['dbhost']."')", $configfile);
			$configfile = str_replace("define('UC_DBUSER', '".addslashes(UC_DBUSER)."')", "define('UC_DBUSER', '".$settingnew['uc']['dbuser']."')", $configfile);
			$configfile = str_replace("define('UC_DBPW', '".addslashes(UC_DBPW)."')", "define('UC_DBPW', '".$ucdbpassnew."')", $configfile);
			$configfile = str_replace("define('UC_DBNAME', '".addslashes(UC_DBNAME)."')", "define('UC_DBNAME', '".$settingnew['uc']['dbname']."')", $configfile);
			$configfile = str_replace("define('UC_DBTABLEPRE', '".addslashes(UC_DBTABLEPRE)."')", "define('UC_DBTABLEPRE', '`".$settingnew['uc']['dbname'].'`.'.$settingnew['uc']['dbtablepre']."')", $configfile);
		}
		$configfile = str_replace("define('UC_CONNECT', '".addslashes(UC_CONNECT)."')", "define('UC_CONNECT', '".$connect."')", $configfile);
		$configfile = str_replace("define('UC_KEY', '".addslashes(UC_KEY)."')", "define('UC_KEY', '".$settingnew['uc']['key']."')", $configfile);
		$configfile = str_replace("define('UC_API', '".addslashes(UC_API)."')", "define('UC_API', '".$settingnew['uc']['api']."')", $configfile);
		$configfile = str_replace("define('UC_IP', '".addslashes(UC_IP)."')", "define('UC_IP', '".$settingnew['uc']['ip']."')", $configfile);
		$configfile = str_replace("define('UC_APPID', '".addslashes(UC_APPID)."')", "define('UC_APPID', '".$settingnew['uc']['appid']."')", $configfile);

		$fp = fopen('./config/config_ucenter.php', 'w');
		if(!($fp = @fopen('./config/config_ucenter.php', 'w'))) {
			cpmsg('uc_config_write_error', '', 'error');
		}
		@fwrite($fp, trim($configfile));
		@fclose($fp);
	}

	if($operation == 'manyou' && ($settingnew['my_app_status'] != $setting['my_app_status'] || $settingnew['my_search_status'] != $setting['my_search_status'] || $settingnew['my_refresh'])) {

		$my_url = 'http://api.manyou.com/uchome.php';
		$my_register_url = 'http://api.manyou.com/uchome.php';

		$mySiteId = empty($_G['setting']['my_siteid'])?'':$_G['setting']['my_siteid'];
		$siteName = $_G['setting']['bbname'];
		$siteUrl = $_G['siteurl'];
		$ucUrl = $_G['setting']['ucenterurl'];
		$siteCharset = $_G['charset'];
		$siteTimeZone = $_G['setting']['timeoffset'];
		$mySiteKey = empty($_G['setting']['my_sitekey'])?'':$_G['setting']['my_sitekey'];
		$siteKey = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
		$siteLanguage = $_G['config']['output']['language'];
		$siteVersion = $_G['setting']['version'];
		$myVersion = '0.0';
		$productType = 'DISCUZX';
		$siteRealNameEnable = '';
		$siteRealAvatarEnable = '';
		$siteEnableSearch = intval($settingnew['my_search_status']);
		$siteSearchInvitationCode = $settingnew['my_search_invite'];
		$siteEnableApp = intval($settingnew['my_app_status']);
		loadcache('max_post_id');
		$maxPostId = intval($_G['cache']['max_post_id']);
		$oldSiteId = empty($_G['setting']['my_siteid_old'])?'':$_G['setting']['my_siteid_old'];
		$oldSitekeySign = empty($_G['setting']['my_sitekey_sign_old'])?'':$_G['setting']['my_sitekey_sign_old'];

		$key = $mySiteId . $siteName . $siteUrl . $ucUrl . $siteCharset . $siteTimeZone . $siteRealNameEnable . $mySiteKey . $siteKey;
		$key = md5($key);

		$siteName = urlencode($siteName);

		$register = true;
		if($mySiteId) {
			$register = false;
			$postString = sprintf('action=%s&productType=%s&key=%s&mySiteId=%d&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteEnableRealName=%s&siteEnableRealAvatar=%s&siteKey=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s&siteEnableSearch=%s&siteSearchInvitationCode=%s&siteEnableApp=%s', 'siteRefresh', $productType, $key, $mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteKey, $siteLanguage, $siteVersion, $myVersion, $siteEnableSearch, $siteSearchInvitationCode, $siteEnableApp);
			if(!empty($oldSiteId) && !empty($oldSitekeySign)) {
				$postString .= '&oldSiteId='.$oldSiteId.'&oldSitekeySign='.$oldSitekeySign;
			}
		} else {
			$postString = sprintf('action=%s&productType=%s&siteKey=%s&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteRealNameEnable=%s&siteRealAvatarEnable=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s&siteEnableSearch=%s&siteSearchInvitationCode=%s&siteEnableApp=%s', 'siteRegister', $productType, $siteKey, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteLanguage, $siteVersion, $myVersion, $siteEnableSearch, $siteSearchInvitationCode, $siteEnableApp);
		}

		if($settingnew['my_search_status']) {
			$postString .= '&maxPostId='.$maxPostId;
		}

		$response = dfsockopen($my_register_url, 0, $postString, '', false, $settingnew['my_ip']);
		$res = unserialize($response);
		if (!$response) {
			$res['errCode'] = 111;
			$res['errMessage'] = 'Empty Response';
			$res['result'] = $response;
		} elseif(!$res) {
			$res['errCode'] = 110;
			$res['errMessage'] = 'Error Response';
			$res['result'] = $response;
		}
		if($res['errCode']) {
			cpmsg('my_register_error', '', 'error', array('errCode'=>$res['errCode'], 'errMessage'=>$res['errMessage']));
		} elseif($register) {
			$settingnew['my_siteid'] = $res['result']['mySiteId'];
			$settingnew['my_sitekey'] = $res['result']['mySiteKey'];
		}

	}
	if($operation == 'manyou' && !$settingnew['my_search_status'] && $setting['my_search_status']) {
		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ('my_search_closetime', '".time()."')");
	}

	$nohtmlarray = array('bbname', 'regname', 'reglinkname', 'icp', 'sitemessage');
	foreach($nohtmlarray as $k) {
		if(isset($settingnew[$k])) {
			$settingnew[$k] = dhtmlspecialchars($settingnew[$k]);
		}
	}

	if(isset($settingnew['censoruser'])) {
		$settingnew['censoruser'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingnew['censoruser']));
	}

	if(isset($settingnew['ipregctrl'])) {
		$settingnew['ipregctrl'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingnew['ipregctrl']));
	}

	if(isset($settingnew['ipaccess'])) {
		if($settingnew['ipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingnew['ipaccess']))) {
			if(!ipaccess($_G['clientip'], $settingnew['ipaccess'])) {
				cpmsg('setting_ipaccess_invalid', '', 'error');
			}
		}
	}

	if(isset($settingnew['commentitem'])) {
		$settingnew['commentitem'] = implode("\t" , $settingnew['commentitem']);
	}

	if(isset($settingnew['adminipaccess'])) {
		if($settingnew['adminipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingnew['adminipaccess']))) {
			if(!ipaccess($_G['clientip'], $settingnew['adminipaccess'])) {
				cpmsg('setting_adminipaccess_invalid', '', 'error');
			}
		}
	}

	if(isset($settingnew['welcomemsgtitle'])) {
		$settingnew['welcomemsgtitle'] = cutstr(trim(dhtmlspecialchars($settingnew['welcomemsgtitle'])), 75);
	}

	if(isset($settingnew['showsignatures']) && isset($settingnew['showavatars']) && isset($settingnew['showimages'])) {
		$settingnew['showsettings'] = bindec($settingnew['showsignatures'].$settingnew['showavatars'].$settingnew['showimages']);
	}

	if(!empty($settingnew['globalstick'])) {
		updatecache('globalstick');
	}

	if(isset($settingnew['inviteconfig'])) {
		$settingnew['inviteconfig'] = addslashes(serialize($settingnew['inviteconfig']));
	}

	if(isset($settingnew['sitemessage'])) {
		$settingnew['sitemessage'] = addslashes(serialize($settingnew['sitemessage']));
	}

	if(isset($settingnew['smthumb'])) {
		$settingnew['smthumb'] = intval($settingnew['smthumb']) >= 20 && intval($settingnew['smthumb']) <= 40 ? intval($settingnew['smthumb']) : 20;
	}

	if(isset($settingnew['indexhot'])) {
		$settingnew['indexhot']['limit'] = intval($settingnew['indexhot']['limit']) ? $settingnew['indexhot']['limit'] : 10;
		$settingnew['indexhot']['days'] = intval($settingnew['indexhot']['days']) ? $settingnew['indexhot']['days'] : 7;
		$settingnew['indexhot']['expiration'] = intval($settingnew['indexhot']['expiration']) ? $settingnew['indexhot']['expiration'] : 900;
		$settingnew['indexhot']['width'] = intval($settingnew['indexhot']['width']) ? $settingnew['indexhot']['width'] : 100;
		$settingnew['indexhot']['height'] = intval($settingnew['indexhot']['height']) ? $settingnew['indexhot']['height'] : 70;
		$settingnew['indexhot']['messagecut'] = intval($settingnew['indexhot']['messagecut']) ? $settingnew['indexhot']['messagecut'] : 200;
		$_G['setting']['indexhot'] = $settingnew['indexhot'];
		$settingnew['indexhot'] = addslashes(serialize($settingnew['indexhot']));
		updatecache('heats');
	}

	if(isset($settingnew['defaulteditormode']) && isset($settingnew['allowswitcheditor'])) {
		$settingnew['editoroptions'] = bindec($settingnew['defaulteditormode'].$settingnew['allowswitcheditor']);
	}

	if(isset($settingnew['myrecorddays'])) {
		$settingnew['myrecorddays'] = intval($settingnew['myrecorddays']) > 0 ? intval($settingnew['myrecorddays']) : 30;
	}

	if(!empty($settingnew['thumbstatus']) && !function_exists('imagejpeg')) {
		$settingnew['thumbstatus'] = 0;
	}

	if(!empty($settingnew['memory'])) {
		foreach($settingnew['memory'] as $k => $v) {
			$settingnew['memory'][$k] = array(
				'enable' => !empty($settingnew['memory'][$k]['enable']) ? 1 : 0,
				'ttl' => min(3600 * 24, max(30, intval($settingnew['memory'][$k]['ttl'])))
				);
		}
		$settingnew['memory'] = addslashes(serialize($settingnew['memory']));
	}

	if(isset($settingnew['creditsformula']) && isset($settingnew['extcredits']) && isset($settingnew['initcredits']) && isset($settingnew['creditstrans']) && isset($settingnew['creditstax'])) {
		if(!checkformulacredits($settingnew['creditsformula'])) {
			cpmsg('setting_creditsformula_invalid', '', 'error');
		}

		$extcreditsarray = array();
		if(is_array($settingnew['extcredits'])) {
			foreach($settingnew['extcredits'] as $key => $value) {
				if($value['available'] && !$value['title']) {
					cpmsg('setting_credits_title_invalid', '', 'error');
				}
				$extcreditsarray[$key] = array
					(
					'img' => dhtmlspecialchars(dstripslashes($value['img'])),
					'title'	=> dhtmlspecialchars(dstripslashes($value['title'])),
					'unit' => dhtmlspecialchars(dstripslashes($value['unit'])),
					'ratio' => ($value['ratio'] > 0 ? (float)$value['ratio'] : 0),
					'available' => $value['available'],
					'showinthread' => $value['showinthread'],
					'allowexchangein' => $value['allowexchangein'],
					'allowexchangeout' => $value['allowexchangeout']
					);
				$settingnew['initcredits'][$key] = intval($settingnew['initcredits'][$key]);
			}
		}

		for($si = 0; $si < 9; $si++) {
			$creditstransi = $si > 0 && !$settingnew['creditstrans'][$si] ? $settingnew['creditstrans'][0] : $settingnew['creditstrans'][$si];
			if($creditstransi && empty($settingnew['extcredits'][$creditstransi]['available']) && $settingnew['creditstrans'][$si] != -1) {
				cpmsg('setting_creditstrans_invalid', '', 'error');
			}
		}

		$settingnew['creditsformulaexp'] = $settingnew['creditsformula'];
		foreach(array('digestposts', 'posts', 'threads', 'friends', 'doings', 'blogs', 'albums', 'polls', 'sharings', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8') as $var) {
			if($extcreditsarray[$creditsid = preg_replace("/^extcredits(\d{1})$/", "\\1", $var)]['available']) {
				$replacement = $extcreditsarray[$creditsid]['title'];
			} else {
				$replacement = $lang['setting_credits_formula_'.$var];
			}
			$settingnew['creditsformulaexp'] = str_replace($var, '<u>'.$replacement.'</u>', $settingnew['creditsformulaexp']);
		}
		$settingnew['creditsformulaexp'] = addslashes('<u>'.$lang['setting_credits_formula_credits'].'</u>='.$settingnew['creditsformulaexp']);

		$initformula = str_replace('posts', '0', $settingnew['creditsformula']);
		for($i = 1; $i <= 8; $i++) {
			$initformula = str_replace('extcredits'.$i, $settingnew['initcredits'][$i], $initformula);
		}
		eval("\$_G['setting']['initcredits'] = round($initformula);");

		$settingnew['extcredits'] = addslashes(serialize($extcreditsarray));
		$settingnew['initcredits'] = $_G['setting']['initcredits'].','.implode(',', $settingnew['initcredits']);
		if($settingnew['creditstax'] < 0 || $settingnew['creditstax'] >= 1) {
			$settingnew['creditstax'] = 0;
		}

		$settingnew['creditstrans'] = implode(',', $settingnew['creditstrans']);
	}

	if(isset($settingnew['maxonlines'])) {
		if($settingnew['maxonlines'] > 65535 || !is_numeric($settingnew['maxonlines'])) {
			cpmsg('setting_maxonlines_invalid', '', 'error');
		}

		DB::query("ALTER TABLE ".DB::table('common_session')." MAX_ROWS=$settingnew[maxonlines]");
		if($settingnew['maxonlines'] < $setting['maxonlines']) {
			DB::query("DELETE FROM ".DB::table('common_session')."");
		}
	}

	if(isset($settingnew['seccodedata'])) {
		$settingnew['seccodedata']['width'] = intval($settingnew['seccodedata']['width']);
		$settingnew['seccodedata']['height'] = intval($settingnew['seccodedata']['height']);
		if($settingnew['seccodedata']['type'] != 3) {
			$settingnew['seccodedata']['width'] = $settingnew['seccodedata']['width'] < 100 ? 100 : ($settingnew['seccodedata']['width'] > 200 ? 200 : $settingnew['seccodedata']['width']);
			$settingnew['seccodedata']['height'] = $settingnew['seccodedata']['height'] < 50 ? 50 : ($settingnew['seccodedata']['height'] > 80 ? 80 : $settingnew['seccodedata']['height']);
		} else {
			$settingnew['seccodedata']['width'] = 85;
			$settingnew['seccodedata']['height'] = 25;
		}
		$settingnew['seccodedata'] = addslashes(serialize($settingnew['seccodedata']));
	}

	if(isset($settingnew['allowviewuserthread'])) {
		$settingnew['allowviewuserthread'] = addslashes(serialize($settingnew['allowviewuserthread']));
	}

	if($operation == 'serveropti') {
		$settingnew['delayviewcount'] = bindec(intval($settingnew['delayviewcount'][2]).intval($settingnew['delayviewcount'][1]));
	}

	if($operation == 'sec') {
		$settingnew['seccodestatus'] = bindec(intval($settingnew['seccodestatus'][5]).intval($settingnew['seccodestatus'][4]).intval($settingnew['seccodestatus'][3]).intval($settingnew['seccodestatus'][2]).intval($settingnew['seccodestatus'][1]));
		if(is_array($_G['gp_delete'])) {
			DB::query("DELETE FROM	".DB::table('common_secquestion')." WHERE id IN (".dimplode($_G['gp_delete']).")");
		}

		if(is_array($_G['gp_question'])) {
			foreach($_G['gp_question'] as $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($_G['gp_answer'][$key])), 50);
				if($q !== '' && $a !== '') {
					DB::query("UPDATE ".DB::table('common_secquestion')." SET question='$q', answer='$a' WHERE id='$key'");
				}
			}
		}
		DB::query("DELETE FROM	".DB::table('common_secquestion')." WHERE type='1'");
		if(is_array($_G['gp_secqaaext'])) {
			foreach($_G['gp_secqaaext'] as $ext) {
				DB::insert('common_secquestion', array('type' => '1', 'question' => $ext));
			}
		}

		if(is_array($_G['gp_newquestion']) && is_array($_G['gp_newanswer'])) {
			foreach($_G['gp_newquestion'] as $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($_G['gp_newanswer'][$key])), 50);
				if($q !== '' && $a !== '') {
					DB::insert('common_secquestion', array('question' => $q, 'answer' => $a));
				}
			}
		}

		updatecache('secqaa');

		$settingnew['secqaa']['status'] = bindec(intval($settingnew['secqaa']['status'][4]).intval($settingnew['secqaa']['status'][3]).intval($settingnew['secqaa']['status'][2]).intval($settingnew['secqaa']['status'][1]));
		$settingnew['secqaa'] = serialize($settingnew['secqaa']);
	}

	if($operation == 'seo') {
		$settingnew['rewritestatus'] = addslashes(serialize($settingnew['rewritestatus']));
		$settingnew['baidusitemap_life'] = max(1, min(24, intval($settingnew['baidusitemap_life'])));
		$rewritedata = rewritedata();
		foreach($settingnew['rewriterule'] as $k => $v) {
			if(!$v) {
				$settingnew['rewriterule'][$k] = $rewritedata['rulesearch'][$k];
			}
		}
		$settingnew['rewriterule'] = addslashes(serialize($settingnew['rewriterule']));
	}

	if($operation == 'functions') {
		$settingnew['bannedmessages'] = bindec(intval($settingnew['bannedmessages'][3]).intval($settingnew['bannedmessages'][2]).intval($settingnew['bannedmessages'][1]));
	}
	if($operation == 'permissions') {
		$settingnew['alloweditpost'] = bindec(intval($settingnew['alloweditpost'][6]).intval($settingnew['alloweditpost'][5]).intval($settingnew['alloweditpost'][4]).intval($settingnew['alloweditpost'][3]).intval($settingnew['alloweditpost'][2]).intval($settingnew['alloweditpost'][1]));
	}
	if($operation == 'ec') {
		if($settingnew['ec_ratio']) {
			if($settingnew['ec_ratio'] < 0) {
				cpmsg('alipay_ratio_invalid', '', 'error');
			}
		} else {
			$settingnew['ec_mincredits'] = $settingnew['ec_maxcredits'] = 0;
		}
		foreach(array('ec_ratio', 'ec_mincredits', 'ec_maxcredits', 'ec_maxcreditspermonth', 'tradeimagewidth', 'tradeimageheight') as $key) {
			$settingnew[$key] = intval($settingnew[$key]);
		}
	}

	if(isset($settingnew['visitbanperiods']) && isset($settingnew['postbanperiods']) && isset($settingnew['postmodperiods']) && isset($settingnew['searchbanperiods'])) {
		foreach(array('visitbanperiods', 'postbanperiods', 'postmodperiods', 'searchbanperiods') as $periods) {
			$periodarray = array();
			foreach(explode("\n", $settingnew[$periods]) as $period) {
				if(preg_match("/^\d{1,2}\:\d{2}\-\d{1,2}\:\d{2}$/", $period = trim($period))) {
					$periodarray[] = $period;
				}
			}
			$settingnew[$periods] = implode("\r\n", $periodarray);
		}
	}

	if(isset($settingnew['infosidestatus'])) {
		$settingnew['infosidestatus'] = addslashes(serialize($settingnew['infosidestatus']));
	}

	if(isset($settingnew['heatthread'])) {
		$settingnew['heatthread']['reply'] = $settingnew['heatthread']['reply'] > 0 ? intval($settingnew['heatthread']['reply']) : 5;
		$settingnew['heatthread']['recommend'] = $settingnew['heatthread']['recommend'] > 0 ? intval($settingnew['heatthread']['recommend']) : 3;
		$settingnew['heatthread']['hottopic'] = !empty($settingnew['heatthread']['hottopic']) ? $settingnew['heatthread']['hottopic'] : '50,100,200';
		$settingnew['heatthread'] = addslashes(serialize($settingnew['heatthread']));
	}

	if(isset($settingnew['recommendthread'])) {
		$settingnew['recommendthread'] = addslashes(serialize($settingnew['recommendthread']));
	}

	if(isset($settingnew['timeformat'])) {
		$settingnew['timeformat'] = $settingnew['timeformat'] == '24' ? 'H:i' : 'h:i A';
	}

	if(isset($settingnew['dateformat'])) {
		$settingnew['dateformat'] = dateformat($settingnew['dateformat'], 'format');
	}

	if($isfounder && isset($settingnew['ftp'])) {
		$setting['ftp'] = unserialize($setting['ftp']);
		$setting['ftp']['password'] = authcode($setting['ftp']['password'], 'DECODE', md5($_G['config']['security']['authkey']));
		if(!empty($settingnew['ftp']['password'])) {
			$pwlen = strlen($settingnew['ftp']['password']);
			if($pwlen < 3) {
				cpmsg('ftp_password_short', '', 'error');
			}
			if($settingnew['ftp']['password']{0} == $setting['ftp']['password']{0} && $settingnew['ftp']['password']{$pwlen - 1} == $setting['ftp']['password']{strlen($setting['ftp']['password']) - 1} && substr($settingnew['ftp']['password'], 1, $pwlen - 2) == '********') {
				$settingnew['ftp']['password'] = $setting['ftp']['password'];
			}
			$settingnew['ftp']['password'] = authcode($settingnew['ftp']['password'], 'ENCODE', md5($_G['config']['security']['authkey']));
		}
		$settingnew['ftp'] = serialize($settingnew['ftp']);
	}

	if($isfounder && isset($settingnew['mail'])) {
		$setting['mail'] = unserialize($setting['mail']);
		$passwordmask = $setting['mail']['auth_password'] ? $setting['mail']['auth_password']{0}.'********'.substr($setting['mail']['auth_password'], -2) : '';
		$settingnew['mail']['auth_password'] = $settingnew['mail']['auth_password'] == $passwordmask ? $setting['mail']['auth_password'] : $settingnew['mail']['auth_password'];
		$settingnew['mail'] = serialize($settingnew['mail']);
	}

	if(isset($settingnew['jsrefdomains'])) {
		$settingnew['jsrefdomains'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingnew['jsrefdomains']));
	}

	if(isset($settingnew['jsdateformat'])) {
		$settingnew['jsdateformat'] = dateformat($settingnew['jsdateformat'], 'format');
	}

	if(isset($settingnew['cachethreaddir']) && isset($settingnew['threadcaches'])) {
		if($settingnew['cachethreaddir'] && !is_writable(DISCUZ_ROOT.'./'.$settingnew['cachethreaddir'])) {
			cpmsg('cachethread_dir_noexists', '', 'error', array('cachethreaddir' => $settingnew['cachethreaddir']));
		}
		if(!empty($_G['gp_fids'])) {
			$sqladd = in_array('all', $_G['gp_fids']) ? '' :  " WHERE fid IN ('".implode("', '", $_G['gp_fids'])."')";
			DB::query("UPDATE ".DB::table('forum_forum')." SET threadcaches='$settingnew[threadcaches]'$sqladd");
		}
	}

	if($operation == 'attach') {
		$settingnew['thumbwidth'] = intval($settingnew['thumbwidth']) > 0 ? intval($settingnew['thumbwidth']) : 200;
		$settingnew['thumbheight'] = intval($settingnew['thumbheight']) > 0 ? intval($settingnew['thumbheight']) : 300;
		$settingnew['swfupload'] = isset($settingnew['swfupload'][1]) ? 2 : (isset($settingnew['swfupload'][0]) ? $settingnew['swfupload'][0] : 0);
		$settingnew['maxthumbwidth'] = intval($settingnew['maxthumbwidth']);
		$settingnew['maxthumbheight'] = intval($settingnew['maxthumbheight']);
		if($settingnew['maxthumbwidth'] < 300 || $settingnew['maxthumbheight'] < 300) {
			$settingnew['maxthumbwidth'] = 0;
			$settingnew['maxthumbheight'] = 0;
		}
	}

	if(isset($settingnew['watermarktext'])) {
		$settingnew['watermarktext']['size'] = intval($settingnew['watermarktext']['size']);
		$settingnew['watermarktext']['angle'] = intval($settingnew['watermarktext']['angle']);
		$settingnew['watermarktext']['shadowx'] = intval($settingnew['watermarktext']['shadowx']);
		$settingnew['watermarktext']['shadowy'] = intval($settingnew['watermarktext']['shadowy']);
		$settingnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $settingnew['watermarktext']['fontpath']);
		if($settingnew['watermarktype'] == 'text' && $settingnew['watermarktext']['fontpath']) {
			$fontpath = $settingnew['watermarktext']['fontpath'];
			$fontpathnew = 'ch/'.$fontpath;
			$settingnew['watermarktext']['fontpath'] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
			if(!$settingnew['watermarktext']['fontpath']) {
				$fontpathnew = 'en/'.$fontpath;
				$settingnew['watermarktext']['fontpath'] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
			}
			if(!$settingnew['watermarktext']['fontpath']) {
				cpmsg('watermarkpreview_fontpath_error', '', 'error');
			}
		}
		$settingnew['watermarktext'] = addslashes(serialize($settingnew['watermarktext']));
	}

	if(isset($settingnew['msgforward'])) {
		if(!empty($settingnew['msgforward']['messages'])) {
			$tempmsg = explode("\n", $settingnew['msgforward']['messages']);
			$settingnew['msgforward']['messages'] = array();
			foreach($tempmsg as $msg) {
				if($msg = strip_tags(trim($msg))) {
					$settingnew['msgforward']['messages'][] = $msg;
				}
			}
		} else {
			$settingnew['msgforward']['messages'] = array();
		}

		$tmparray = array(
			'refreshtime' => intval($settingnew['msgforward']['refreshtime']),
			'quick' => $settingnew['msgforward']['quick'] ? 1 : 0,
			'messages' => $settingnew['msgforward']['messages']
		);
		$settingnew['msgforward'] = addslashes(serialize($tmparray));
	}

	if(isset($settingnew['onlinehold'])) {
		$settingnew['onlinehold'] = intval($settingnew['onlinehold']) > 0 ? intval($settingnew['onlinehold']) : 15;
	}

	if(isset($settingnew['postno'])) {
		$settingnew['postno'] = trim($settingnew['postno']);
	}
	if(isset($settingnew['postnocustom'])) {
		$settingnew['postnocustom'] = addslashes(serialize(explode("\n", $settingnew['postnocustom'])));
	}

	if($operation == 'styles') {
		$settingnew['disallowfloat'] = array_diff($floatwinkeys, isset($settingnew['allowfloatwin']) ? $settingnew['allowfloatwin'] : array());
		$settingnew['disallowfloat'] = addslashes(serialize($settingnew['disallowfloat']));
		$settingnew['customauthorinfo'] = addslashes(serialize(array($settingnew['customauthorinfo'])));
		list(, $_G['setting']['imagemaxwidth']) = explode("\t", $setting['zoomstatus']);
		$settingnew['zoomstatus'] = $settingnew['zoomstatus']."\t".$settingnew['imagemaxwidth'];
		unset($settingnew['allowfloatwin']);
	}

	if($operation == 'search') {
		foreach($settingnew['search'] as $key => $val) {
			foreach($val as $k => $v) {
				$settingnew['search'][$key][$k] = max(0, intval($v));
			}
		}
		$settingnew['search'] = addslashes(serialize($settingnew['search']));
	}

	if(isset($settingnew['smcols'])) {
		$settingnew['smcols'] = $settingnew['smcols'] >= 8 && $settingnew['smcols'] <= 12 ? $settingnew['smcols'] : 8;
	}

	if(isset($settingnew['jspath'])) {
		if(!$settingnew['jspath']) {
			$settingnew['jspath'] = $settingnew['jspathcustom'];
		}
	}

	if(isset($settingnew['domainwhitelist'])) {
		$settingnew['domainwhitelist'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingnew['domainwhitelist']));
	}

	if(isset($settingnew['shownewuser']) && !$settingnew['shownewuser']) {
		$settingnew['newspacenum'] = 0;
	}

	if(!$setting['my_app_status']) {
		$settingnew['videophoto'] = 0;
	}

	$updatecache = FALSE;
	$settings = array();
	foreach($settingnew as $key => $val) {
		if($setting[$key] != $val) {
			$$key = $val;
			$updatecache = TRUE;
			if(in_array($key, array('newbiespan', 'topicperpage', 'postperpage', 'hottopic', 'starthreshold', 'delayviewcount', 'attachexpire',
				'visitedforums', 'maxsigrows', 'timeoffset', 'statscachelife', 'pvfrequence', 'oltimespan', 'seccodestatus',
				'maxprice', 'rssttl', 'maxonlines', 'loadctrl', 'floodctrl', 'regctrl', 'regfloodctrl',
				'searchctrl', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6',
				'extcredits7', 'extcredits8', 'transfermincredits', 'exchangemincredits', 'maxincperthread', 'maxchargespan',
				'maxspm', 'maxsearchresults', 'maxsmilies', 'threadmaxpages', 'maxpostsize', 'minpostsize', 'sendmailday',
				'maxpolloptions', 'karmaratelimit', 'losslessdel', 'smcols', 'allowdomain', 'feedday', 'feedmaxnum', 'feedhotday', 'feedhotmin',
				'feedtargetblank', 'updatestat', 'namechange', 'namecheck', 'realname', 'networkpage', 'maxreward', 'groupnum', 'starlevelnum', 'friendgroupnum',
				'pollforumid', 'tradeforumid', 'rewardforumid', 'activityforumid', 'debateforumid', 'maxpage',
				'starcredit', 'topcachetime', 'newspacevideophoto', 'newspacerealname', 'newspaceavatar', 'newspacenum', 'shownewuser',
				'feedhotnum', 'showallfriendnum', 'feedread', 'name_allowuserapp', 'name_allowcomment', 'name_allowshare', 'name_allowfavorite',
				'name_allowpoll', 'name_allowalbum', 'name_allowblog', 'name_allowdoing', 'name_allowpoke', 'name_allowfriend',
				'name_allowviewspace', 'video_allowuserapp', 'video_allowshare', 'video_allowfavorite', 'video_allowpoll', 'video_allowalbum', 'video_allowblog',
				'video_allowdoing', 'video_allowcomment', 'video_allowwall', 'video_allowpoke', 'video_allowfriend', 'video_allowviewphoto', 'videophoto',
				'need_friendnum', 'need_avatar', 'uniqueemail', 'need_email',
				'watermarktrans', 'watermarkquality', 'jscachelife', 'maxmodworksmonths', 'maxonlinelist'))) {
				$val = (float)$val;
			}

			if($key == 'privacy') {
				foreach($val['view'] as $var => $value) {
					$val['view'][$var] = intval($value);
				}
				if(!isset($val['feed']) || !is_array($val['feed'])) {
					$val['feed'] = array();
				}
				foreach($val['feed'] as $var => $value) {
					$val['feed'][$var] = 1;
				}
				$val = addslashes(serialize($val));
			}

			$settings[] = "('$key', '$val')";
		}
	}

	if($settings) {
		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ".implode(',', $settings));
	}
	if($updatecache) {
		updatecache('setting');
		if(isset($settingnew['forumlinkstatus']) && $settingnew['forumlinkstatus'] != $setting['forumlinkstatus']) {
			updatecache('forumlinks');
		}
		if(isset($settingnew['userstatusby']) && $settingnew['userstatusby'] != $setting['userstatusby']) {
			updatecache('usergroups');
		}
		if((isset($settingnew['tagstatus']) && $settingnew['tagstatus'] != $setting['tagstatus']) || (isset($settingnew['viewthreadtags']) && $settingnew['viewthreadtags'] != $setting['viewthreadtags'])) {
			updatecache(array('tags_index', 'tags_viewthread'));
		}
		if((isset($settingnew['smthumb']) && $settingnew['smthumb'] != $setting['smthumb']) || (isset($settingnew['smcols']) && $settingnew['smcols'] != $setting['smcols']) || (isset($settingnew['smrows']) && $settingnew['smrows'] != $setting['smrows'])) {
			updatecache('smilies_js');
		}
		if(isset($settingnew['customauthorinfo']) && $settingnew['customauthorinfo'] != $setting['customauthorinfo']) {
			updatecache('custominfo');
		}
		if($operation == 'credits') {
			updatecache('custominfo');
		}
		if($operation == 'access') {
			updatecache('ipctrl');
		}
		if($operation == 'styles') {
			updatecache('styles');
		}
		if(isset($settingnew['domainwhitelist'])) {
			updatecache('domainwhitelist');
		}
	}

	cpmsg('setting_update_succeed', 'action=setting&operation='.$operation.(!empty($_G['gp_anchor']) ? '&anchor='.$_G['gp_anchor'] : '').(!empty($from) ? '&from='.$from : ''), 'succeed');
}

function dateformat($string, $operation = 'formalise') {
	$string = htmlspecialchars(trim($string));
	$replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
	return str_replace($replace[0], $replace[1], $string);
}

function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}

?>