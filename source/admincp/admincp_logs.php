<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_logs.php 11710 2010-06-11 07:09:34Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$lpp = empty($_G['gp_lpp']) ? 20 : $_G['gp_lpp'];
$checklpp = array();
$checklpp[$lpp] = 'selected="selected"';

$operation = in_array($operation, array('illegal', 'rate', 'credit', 'mods', 'medal', 'ban', 'cp', 'magic', 'error', 'invite', 'payment')) ? $operation : 'illegal';

$logdir = DISCUZ_ROOT.'./data/log/';
$logfiles = get_log_files($logdir, $operation.'log');
$logs = array();
rsort($logfiles);
if($logfiles) {
	$logs = file(!empty($_G['gp_day']) ? $logdir.$_G['gp_day'].'_'.$operation.'log.php' : $logdir.$logfiles[0]);
}

$start = ($page - 1) * $lpp;
$logs = array_reverse($logs);

if(empty($_G['gp_keyword'])) {
	$num = count($logs);
	$multipage = multi($num, $lpp, $page, ADMINSCRIPT."?action=logs&operation=$operation&lpp=$lpp".(!empty($_G['gp_day']) ? '&day='.$_G['gp_day'] : ''), 0, 3);
	$logs = array_slice($logs, $start, $lpp);

} else {
	foreach($logs as $key => $value) {
		if(strpos($value, $_G['gp_keyword']) === FALSE) {
			unset($logs[$key]);
		}
	}
	$multipage = '';
}

$usergroup = array();

if(in_array($operation, array('rate', 'mods', 'ban', 'cp'))) {
	$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')."");
	while($group = DB::fetch($query)) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
}

shownav('tools', 'nav_logs', 'nav_logs_'.$operation);
if($logfiles) {
	$sel = '<select class="right" onchange="location.href=\''.ADMINSCRIPT.'?action=logs&operation='.$operation.'&day=\'+this.value">';
	foreach($logfiles as $logfile) {
		list($date) = explode('_', $logfile);
		$sel .= '<option value="'.$date.'"'.($date == $_G['gp_day'] ? ' selected="selected"' : '').'>'.$date.'</option>';
	}
	$sel .= '</select>';
} else {
	$sel = '';
}
showsubmenu('nav_logs', array(
	array(array('menu' => 'nav_logs_member', 'submenu' => array(
		array('nav_logs_illegal', 'logs&operation=illegal'),
		array('nav_logs_ban', 'logs&operation=ban'),
		array('nav_logs_mods', 'logs&operation=mods'),
	)), '', in_array($operation, array('illegal', 'ban', 'mods'))),
	array(array('menu' => 'nav_logs_system', 'submenu' => array(
		array('nav_logs_cp', 'logs&operation=cp'),
		array('nav_logs_error', 'logs&operation=error'),
	)), '', in_array($operation, array('cp', 'error'))),
	array(array('menu' => 'nav_logs_extended', 'submenu' => array(
		array('nav_logs_rate', 'logs&operation=rate'),
		array('nav_logs_credit', 'logs&operation=credit'),
		array('nav_logs_magic', 'logs&operation=magic'),
		array('nav_logs_medal', 'logs&operation=medal'),
		array('nav_logs_invite', 'logs&operation=invite'),
		array('nav_logs_payment', 'logs&operation=payment'),
	)), '', in_array($operation, array('rate', 'credit', 'magic', 'medal', 'invite', 'payment')))
), $sel);
if($operation == 'illegal') {
	showtips('logs_tips_illegal');
} elseif($operation == 'ban') {
	showtips('logs_tips_ban');
}
showformheader("logs&operation=$operation");
showtableheader('', 'fixpadding');
$filters = '';
if($operation == 'illegal') {

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td23"','class="td23"'), array(
		cplang('time'),
		cplang('ip'),
		cplang('logs_passwd_username'),
		cplang('logs_passwd_password'),
		cplang('logs_passwd_security')
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		if(strtolower($log[2]) == strtolower($_G['member']['username'])) {
			$log[2] = "<b>$log[2]</b>";
		}
		$log[5] = $_G['group']['allowviewip'] ? $log[5] : '-';

		showtablerow('', array('class="smallefont"', 'class="smallefont"', 'class="bold"', 'class="smallefont"', 'class="smallefont"'), array(
			$log[1],
			$log[5],
			$log[2],
			$log[3],
			$log[4]
		));

	}

} elseif($operation == 'rate') {

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td23"','class="td23"','class="td24"'), array(
		cplang('username'),
		cplang('usergroup'),
		cplang('time'),
		cplang('logs_rating_username'),
		cplang('logs_rating_rating'),
		cplang('subject'),
		cplang('reason'),
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">$log[2]</a>";
		$log[3] = $usergroup[$log[3]];
		if($log[4] == $_G['member']['username']) {
			$log[4] = "<b>$log[4]</b>";
		}
		$log[4] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[4])."\" target=\"_blank\">$log[4]</a>";
		$log[6] = $_G['setting']['extcredits'][$log[5]]['title'].' '.($log[6] < 0 ? "<b>$log[6]</b>" : "+$log[6]").' '.$_G['setting']['extcredits'][$log[5]]['unit'];
		$log[7] = $log[7] ? "<a href=\"./forum.php?mod=viewthread&tid=$log[7]\" target=\"_blank\" title=\"$log[8]\">".cutstr($log[8], 20)."</a>" : "<i>$lang[logs_rating_manual]</i>";

		showtablerow('', array('class="bold"'), array(
			$log[2],
			$log[3],
			$log[1],
			$log[4],
			(trim($log[10]) == 'D' ? $lang['logs_rating_delete'] : '').$log[6],
			$log[7],
			$log[9]
		));
	}

} elseif($operation == 'credit') {

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td24"','class="td24"'), array(
		cplang('username'),
		cplang('logs_credit_fromto'),
		cplang('time'),
		cplang('logs_credits_log_update'),
		cplang('action'),
	));

	$lpp = max(5, empty($_G['gp_lpp']) ? 50 : intval($_G['gp_lpp']));
	$start_limit = ($page - 1) * $lpp;
	$mpurl = ADMINSCRIPT."?action=logs&operation=$operation";

	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." l WHERE l.uid='$_G[uid]' AND l.operation IN('TFR', 'RCV', 'CEC', 'ECU', 'AFD', 'UGP', 'TRC')");

	$multipage = multi($num, $lpp, $page, $mpurl, 0, 3);

	$total['send'] = $total['receive'] = array();
	$query = DB::query("SELECT l.*, m.uid AS mid, m.username, mm.username AS ousername FROM ".DB::table('common_credit_log')." l
		LEFT JOIN ".DB::table('common_member')." m ON m.uid=l.relatedid
		LEFT JOIN ".DB::table('common_member')." mm ON mm.uid=l.uid
		WHERE l.uid='$_G[uid]' AND l.operation IN('TFR', 'RCV', 'CEC', 'ECU', 'AFD', 'UGP', 'TRC')
		ORDER BY dateline DESC LIMIT $start_limit,$lpp");

	while($log = DB::fetch($query)) {
		$log['dateline'] = dgmdate($log['dateline'], 'y-n-j H:i');
		if($log['operation'] == 'AFD') {
			$log['fromto'] = cplang('logs_credits_transfer_bank');
		} elseif ($log['operation'] == 'TRC') {
			$log['fromto'] = cplang('logs_credits_transfer_task');
		} elseif ($log['operation'] == 'UGP') {
			$log['fromto'] = '<a href="home.php?mod=spacecp&ac=credit&op=usergroup&searchgroupid='.$log['relatedid'].'">'.$_G['cache']['usergroups'][$log['relatedid']]['grouptitle'].'"';
		} else {
			$log['fromto'] = '<a href="home.php?mod=space&uid='.$log['mid'].'" target="_blank">'.$log['username'].'</a>';
		}
		$log['update'] = '';
		foreach($_G['setting']['extcredits'] as $id => $credit) {
			if($log['extcredits'.$id]) {
				$log['update'] .= $credit['title'].$log['extcredits'.$id].$credit['unit'].'&nbsp;';
			}
		}
		if($log['operation'] == 'TFR') {
			$log['action'] = cplang('logs_credits_transfer_send');
		} elseif($log['operation'] == 'RCV') {
			$log['action'] = cplang('logs_credits_transfer_receive');
		} elseif($log['operation'] == 'ECU') {
			$log['action'] = cplang('logs_credits_exchange_ucenter');
		} elseif($log['operation'] == 'UGP') {
			$log['action'] = cplang('logs_usergroups_charged');
		} elseif($log['operation'] == 'TRC') {
			$log['action'] = cplang('logs_credits_transfer_task');
		} elseif($log['operation'] == 'CEC') {
			$log['action'] = cplang('logs_credits_exchange');
		} elseif($log['operation'] == 'AFD') {
			$log['action'] = cplang('logs_credits_transfer_bank');
		}
		showtablerow('', array('class="bold"'), array(
			"<a href=\"home.php?mod=space&username=".rawurlencode($log['ousername'])."\" target=\"_blank\">$log[ousername]",
			$log['fromto'],
			$log['dateline'],
			$log['update'],
			$log['action'],
		));
	}

	$result = array('send' => array(), 'receive' => array());
	foreach(array('send', 'receive') as $key) {
		foreach($total[$key] as $id => $amount) {
			if(isset($_G['setting']['extcredits'][$id])) {
				$result[$key][] = $_G['setting']['extcredits'][$id]['title'].' '.$amount.' '.$_G['setting']['extcredits'][$id]['unit'];
			}
		}
	}

} elseif($operation == 'mods') {

	$modactioncode = lang('forum/modaction');

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td23"','class="td24"','class="td24"','class="td23"'), array(
		cplang('operator'),
		cplang('usergroup'),
		cplang('ip'),
		cplang('time'),
		cplang('forum'),
		cplang('thread'),
		cplang('action'),
		cplang('reason'),
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = dstripslashes($log[2]);
		$log[3] = $usergroup[$log[3]];
		$log[4] = $_G['group']['allowviewip'] ? $log[4] : '-';
		$log[6] = "<a href=\"./forum.php?mod=forumdisplay&fid=$log[5]\" target=\"_blank\">$log[6]</a>";
		$log[8] = "<a href=\"./forum.php?mod=viewthread&tid=$log[7]\" target=\"_blank\" title=\"$log[8]\">".cutstr($log[8], 15)."</a>";
		$log[9] = $modactioncode[trim($log[9])];
		showtablerow('', array('class="bold"'), array(
			"<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">".($log[2] != $_G['member']['username'] ? "<b>$log[2]</b>" : $log[2]),
			$log[3],
			$log[4],
			$log[1],
			$log[6],
			$log[8],
			$log[9],
			$log[10],
		));
	}

} elseif($operation == 'ban') {

	showtablerow('class="header"', array('class="td23"', 'class="td23"', 'class="td23"', 'class="td23"', 'class="td23"', 'class="td23"', 'class="td24"', 'class="td23"'), array(
		cplang('operator'),
		cplang('usergroup'),
		cplang('ip'),
		cplang('time'),
		cplang('username'),
		cplang('operation'),
		cplang('logs_banned_group'),
		cplang('validity'),
		cplang('reason'),
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">$log[2]";
		$log[3] = $usergroup[$log[3]];
		$log[4] = $_G['group']['allowviewip'] ? $log[4] : '-';
		$log[5] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[5])."\" target=\"_blank\">$log[5]</a>";
		$log[8] = trim($log[8]) ? dgmdate($log[8], 'y-n-j') : '';
		showtablerow('', array('class="bold"'), array(
			$log[2],
			$log[3],
			$log[4],
			$log[1],
			$log[5],
			(in_array($log[6], array(4, 5)) && !in_array($log[7], array(4, 5)) ? '<i>'.$lang['logs_banned_unban'].'</i>' : '<b>'.$lang['logs_banned_ban'].'</b>'),
			"{$usergroup[$log[6]]} / {$usergroup[$log[7]]}",
			$log[8],
			$log[9]
		));
	}

} elseif($operation == 'cp') {

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td24"','class="td24"', ''), array(
		cplang('operator'),
		cplang('usergroup'),
		cplang('ip'),
		cplang('time'),
		cplang('action'),
		cplang('other')
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = dstripslashes($log[2]);
		$log[2] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">".($log[2] != $_G['member']['username'] ? "<b>$log[2]</b>" : $log[2])."</a>";
		$log[3] = $usergroup[$log[3]];
		$log[4] = $_G['group']['allowviewip'] ? $log[4] : '-';
		$log[5] = rtrim($log[5]);
		$log[6] = cutstr($log[6], 200);
 		showtablerow('', array('class="bold"'), array($log[2], $log[3], $log[4], $log[1], $log[5], $log[6]));
	}

} elseif($operation == 'error') {

	showtablerow('class="header"', array('class="td23"', 'class="td24"', 'class="td24"'), array(
		cplang('time'),
		cplang('message'),
	));
	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}

		showtablerow('', array('class="bold"'), array(
			substr($log[1], 0, -1),
			$log[2],
		));

	}

} elseif($operation == 'invite') {

	if(!submitcheck('invitesubmit')) {
		showtablerow('class="header"', array('width="35"','class="td23"','class="td24"','class="td24"','class="td23"','class="td24"','class="td24"'), array(
			'',
			cplang('logs_invite_buyer'),
			cplang('logs_invite_buydate'),
			cplang('logs_invite_expiration'),
			cplang('logs_invite_code')
		));

		$tpp = $_G['gp_lpp'] ? intval($_G['gp_lpp']) : $_G['tpp'];
		$start_limit = ($page - 1) * $tpp;


		$invitecount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_invite')." WHERE 1");
		$multipage = multi($invitecount, $tpp, $page, ADMINSCRIPT."?action=logs&operation=invite&lpp=$lpp$statusurl", 0, 3);

		$query = DB::query("SELECT i.*, m.username FROM ".DB::table('common_invite')." i, ".DB::table('common_member')." m
				WHERE i.uid=m.uid LIMIT $start_limit,$tpp");
		while($invite = DB::fetch($query)) {
			$username = "<a href=\"home.php?mod=space&uid=$invite[uid]\">$invite[username]</a>";
			$invite['dateline'] = dgmdate($invite['dateline'], 'Y-n-j H:i');
			$invite['expiration'] = dgmdate($invite['endtime'], 'Y-n-j H:i');

			showtablerow('', array('', 'class="bold"'), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$invite['id'].'" />',
				$username,
				$invite['dateline'],
				$invite['expiration'],
				$invite['code']
			));
		}

	} else {

		if($deletelist = dimplode($delete)) {
			DB::query("DELETE FROM ".DB::table('common_invite')." WHERE id IN ($deletelist)");
		}

		header("Location: $_G[siteurl]".ADMINSCRIPT."?action=logs&operation=invite");
	}

} elseif($operation == 'magic') {

	loadcache('magics');

	$lpp = empty($_G['gp_lpp']) ? 50 : $_G['gp_lpp'];
	$start_limit = ($page - 1) * $lpp;

	$mpurl = ADMINSCRIPT."?action=logs&operation=magic&lpp=$lpp";

	if(in_array($_G['gp_opt'], array('1', '2', '3', '4', '5'))) {
		$optadd = "AND ma.action='{$_G['gp_opt']}'";
		$mpurl .= '&opt='.$_G['gp_opt'];
	} else {
		$optadd = '';
	}

	if(!empty($_G['gp_magicid'])) {
		$magicidadd = "AND ma.magicid='".intval($_G['gp_magicid'])."'";
	} else {
		$magicidadd = '';
	}

	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_magiclog')." ma WHERE 1 $magicidadd $optadd");

	$multipage = multi($num, $lpp, $page, $mpurl, 0, 3);

	$check1 = $check2 = array();
	$check1[$magicid] = 'selected="selected"';
	$check2[$opt] = 'selected="selected"';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=magic&opt='.$_G['gp_opt'].'&lpp='.$lpp.'&magicid=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['magics_type'].'</option><option value="">'.$lang['magics_type_all'].'</option>';
	foreach($_G['cache']['magics'] as $id => $magic) {
		$filters .= '<option value="'.$id.'" '.$check1[$id].'>'.$magic['name'].'</option>';
	}
	$filters .= '</select>';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=magic&magicid='.$magicid.'&lpp='.$lpp.'&opt=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['action'].'</option><option value="">'.$lang['all'].'</option>';
	foreach(array('1', '2', '3', '4', '5') as $o) {
		$filters .= '<option value="'.$o.'" '.$check2[$o].'>'.$lang['logs_magic_operation_'.$o].'</option>';
	}
	$filters .= '</select>';

	showtablerow('class="header"', array('class="td23"', 'class="td23"', 'class="td24"', 'class="td23"', 'class="td23"', 'class="td24"'), array(
		cplang('username'),
		cplang('name'),
		cplang('time'),
		cplang('num'),
		cplang('price'),
		cplang('action')
	));

	$query = DB::query("SELECT ma.*, m.username FROM ".DB::table('common_magiclog')." ma
		LEFT JOIN ".DB::table('common_member')." m USING (uid)
		WHERE 1 $magicidadd $optadd ORDER BY dateline DESC LIMIT $start_limit, $lpp");

	while($log = DB::fetch($query)) {
		$log['name'] = $_G['cache']['magics'][$log['magicid']]['name'];
		$log['dateline'] = dgmdate($log['dateline'], 'Y-n-j H:i');
		$log['action'] = $lang['logs_magic_operation_'.$log['action']];
		showtablerow('', array('class="bold"'), array(
			"<a href=\"home.php?mod=space&username=".rawurlencode($log['username'])."\" target=\"_blank\">$log[username]",
			$log['name'],
			$log['dateline'],
			$log['amount'],
			$log['price'],
			$log['action']
		));
	}

} elseif($operation == 'medal') {

	loadcache('medals');

	$lpp = empty($_G['gp_lpp']) ? 50 : $_G['gp_lpp'];
	$start_limit = ($page - 1) * $lpp;

	$mpurl = ADMINSCRIPT."?action=logs&operation=medal&lpp=$lpp";

	if(in_array($_G['gp_opt'], array('0', '1', '2', '3'))) {
		$optadd = "AND me.type='{$_G['gp_opt']}'";
		$mpurl .= '&opt='.$_G['gp_opt'];
	} else {
		$optadd = '';
	}

	if(!empty($_G['gp_medalid'])) {
		$medalidadd = "AND me.medalid='".intval($_G['gp_medalid'])."'";
	} else {
		$medalidadd = '';
	}

	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_medallog')." me WHERE 1 $medalidadd $optadd");

	$multipage = multi($num, $lpp, $page, $mpurl, 0, 3);

	$check1 = $check2 = array();
	$check1[$_G['gp_medalid']] = 'selected="selected"';
	$check2[$_G['gp_opt']] = 'selected="selected"';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=medal&opt='.$_G['gp_opt'].'&lpp='.$lpp.'&medalid=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['medals'].'</option><option value="">'.$lang['all'].'</option>';
	foreach($_G['cache']['medals'] as $id => $medal) {
		$filters .= '<option value="'.$id.'" '.$check1[$id].'>'.$medal['name'].'</option>';
	}
	$filters .= '</select>';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=medal&medalid='.$_G['gp_medalid'].'&lpp='.$lpp.'&opt=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['action'].'</option><option value="">'.$lang['all'].'</option>';
	foreach(array('0', '1', '2', '3') as $o) {
		$filters .= '<option value="'.$o.'" '.$check2[$o].'>'.$lang['logs_medal_operation_'.$o].'</option>';
	}
	$filters .= '</select>';

	showtablerow('class="header"', array('class="td23"', 'class="td24"', 'class="td23"', 'class="td23"'), array(
		cplang('username'),
		cplang('logs_medal_name'),
		cplang('type'),
		cplang('time'),
		cplang('logs_medal_expiration')
	));

	$query = DB::query("SELECT me.*, m.username FROM ".DB::table('forum_medallog')." me
		LEFT JOIN ".DB::table('common_member')." m USING (uid)
		WHERE 1 $medalidadd $optadd ORDER BY dateline DESC LIMIT $start_limit, $lpp");

	while($log = DB::fetch($query)) {
		$log['name'] = $_G['cache']['medals'][$log['medalid']]['name'];
		$log['dateline'] = dgmdate($log['dateline'], 'Y-n-j H:i');
		$log['expiration'] = empty($log['expiration']) ? cplang('logs_noexpire') : dgmdate($log['expiration'], 'Y-n-j H:i');
		showtablerow('', array('class="td23"', 'class="td24"', 'class="td23"', 'class="td24"'), array(
			"<a href=\"home.php?mod=space&username=".rawurlencode($log['username'])."\" target=\"_blank\">$log[username]",
			$log['name'],
			$lang['logs_medal_operation_'.$log['type']],
			$log['dateline'],
			$log['expiration']
		));
	}

} elseif($operation == 'payment') {

	showtablerow('class="header"', array('width="30%"','class="td23"','class="td23"','class="td24"','class="td23"','class="td24"','class="td24"'), array(
		cplang('subject'),
		cplang('logs_payment_amount'),
		cplang('logs_payment_seller'),
		cplang('logs_payment_buyer'),
		cplang('logs_payment_dateline'),
		cplang('logs_payment_buydateline'),
	));

	$tpp = $_G['gp_lpp'] ? intval($_G['gp_lpp']) : $_G['tpp'];
	$start_limit = ($page - 1) * $tpp;

	$threadcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE operation='BTC'");
	$multipage = multi($threadcount, $tpp, $page, ADMINSCRIPT."?action=logs&operation=payment&lpp=$lpp", 0, 3);
	$paythreadlist = array();

	$query = DB::query("SELECT l.*, m.username, t.subject, t.dateline AS postdateline, t.author, t.authorid AS tauthorid
			FROM ".DB::table('common_credit_log')." l
			LEFT JOIN ".DB::table('common_member')." m ON m.uid=l.uid
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=l.relatedid
			WHERE l.operation='BTC'
			ORDER BY l.dateline DESC LIMIT $start_limit,$tpp");
	while($paythread = DB::fetch($query)) {
		$paythread['seller'] = $paythread['tauthorid'] ? "<a href=\"home.php?mod=space&uid=$paythread[tauthorid]\">$paythread[author]</a>" : cplang('logs_payment_del')."(<a href=\"home.php?mod=space&uid=$paythread[authorid]\">".cplang('logs_payment_view')."</a>)";;
		$paythread['buyer'] = "<a href=\"home.php?mod=space&uid=$paythread[uid]\">$paythread[username]</a>";
		$paythread['subject'] = $paythread['subject'] ? "<a href=\"forum.php?mod=viewthread&tid=$paythread[tid]\">$paythread[subject]</a>" : cplang('logs_payment_del');
		$paythread['dateline'] = dgmdate($paythread['dateline'], 'Y-n-j H:i');
		$paythread['postdateline'] = $paythread['postdateline'] ? dgmdate($paythread['postdateline'], 'Y-n-j H:i') : cplang('logs_payment_del');
		foreach($_G['setting']['extcredits'] as $id => $credits) {
			if($paythread['extcredits'.$id]) {
				$paythread['amount'] = $credits['title'].':'.abs($paythread['extcredits'.$id]);
			}
		}
		$paythreadlist[] = $paythread;
	}

	foreach($paythreadlist as $paythread) {
		showtablerow('', array('', 'class="bold"'), array(
			$paythread['subject'],
			$paythread['amount'],
			$paythread['seller'],
			$paythread['buyer'],
			$paythread['postdateline'],
			$paythread['dateline']
		));
	}
}

function get_log_files($logdir = '', $action = 'action') {
	$dir = opendir($logdir);
	$files = array();
	while($entry = readdir($dir)) {
		$files[] = $entry;
	}
	closedir($dir);

	if($files) {
		sort($files);
		$logfile = $action;
		$logfiles = array();
		$ym = '';
		foreach($files as $file) {
			if(strpos($file, $logfile) !== FALSE) {
				if(substr($file, 0, 6) != $ym) {
					$ym = substr($file, 0, 6);
				}
				$logfiles[$ym][] = $file;
			}
		}
		if($logfiles) {
			$lfs = array();
			foreach($logfiles as $ym => $lf) {
				$lastlogfile = $lf[0];
				unset($lf[0]);
				$lf[] = $lastlogfile;
				$lfs = array_merge($lfs, $lf);
			}
			return array_slice($lfs, -2, 2);
		}
		return array();
	}
	return array();
}
if($_G['gp_keyword']) {
	$filters = '';
}
showsubmit($operation == 'invite' ? 'invitesubmit' : '', 'submit', 'del', $filters, $multipage.(empty($_G['gp_keyword']) ? cplang('logs_lpp').':<select onchange="if(this.options[this.selectedIndex].value != \'\') {window.location=\''.ADMINSCRIPT.'?action=logs&operation='.$operation.'&lpp=\'+this.options[this.selectedIndex].value }"><option value="20" '.$checklpp[20].'> 20 </option><option value="40" '.$checklpp[40].'> 40 </option><option value="80" '.$checklpp[80].'> 80 </option></select>' : ''). '&nbsp;<input type="text" class="txt" name="keyword" value="'.$_G['gp_keyword'].'" /><input type="submit" class="btn" value="'.$lang['search'].'"  />');
showtablefooter();
showformfooter();

?>