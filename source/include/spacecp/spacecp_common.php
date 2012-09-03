<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_common.php 9160 2010-04-27 07:04:36Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':trim($_GET['op']);

if($op == 'report') {

	$_GET['idtype'] = trim($_GET['idtype']);
	$_GET['id'] = intval($_GET['id']);
	$uidarr = $report = array();

	if(!in_array($_GET['idtype'], array('picid', 'blogid', 'albumid', 'tagid', 'tid', 'sid', 'uid', 'pid', 'eventid', 'comment', 'post')) || empty($_GET['id'])) {
		showmessage('report_error');
	}
	$query = DB::query("SELECT * FROM ".DB::table('common_report')." WHERE id='$_GET[id]' AND idtype='$_GET[idtype]'");
	if($report = DB::fetch($query)) {
		$uidarr = unserialize($report['uids']);
		if($uidarr[$space['uid']]) {
			showmessage('repeat_report');
		}
	}

	if(submitcheck('reportsubmit')) {
		$reason = getstr($_POST['reason'], 150, 1, 1);

		$reason = "<li><strong><a href=\"home.php?mod=space&uid=$space[uid]\" target=\"_blank\">$_G[username]</a>:</strong> ".$reason.' ('.dgmdate($_G['timestamp'], 'n-j H:i').')</li>';

		if($report) {
			$uidarr[$space['uid']] = $space['username'];
			$uids = addslashes(serialize($uidarr));
			$reason = addslashes($report['reason']).$reason;
			DB::query("UPDATE ".DB::table('home_report')." SET num=num+1, reason='$reason', dateline='$_G[timestamp]', uids='$uids' WHERE rid='$report[rid]'");
		} else {
			$uidarr[$space['uid']] = $space['username'];

			$setarr = array(
				'id' => $_GET['id'],
				'idtype' => $_GET['idtype'],
				'num' => 1,
				'new' => 1,
				'reason' => $reason,
				'uids' => addslashes(serialize($uidarr)),
				'dateline' => $_G['timestamp']
			);
			DB::insert('common_report', $setarr);
		}
		showmessage('report_success');
	}

	if(isset($report['num']) && $report['num'] < 1) {
		showmessage('the_normal_information');
	}

	$reason = explode("\r\n", trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $_G['setting']['reportreason'])));
	if(is_array($reason) && count($reason) == 1 && empty($reason[0])) {
		$reason = array();
	}

} elseif($op == 'ignore') {

	$type = empty($_GET['type'])?'':preg_replace("/[^0-9a-zA-Z\_\-\.]/", '', $_GET['type']);
	if(submitcheck('ignoresubmit')) {
		$authorid = empty($_POST['authorid']) ? 0 : intval($_POST['authorid']);
		if($type) {
			$type_uid = $type.'|'.$authorid;
			if(empty($space['privacy']['filter_note']) || !is_array($space['privacy']['filter_note'])) {
				$space['privacy']['filter_note'] = array();
			}
			$space['privacy']['filter_note'][$type_uid] = $type_uid;
			privacy_update();
		}
		showmessage('do_success', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
	}
	$formid = random(8);

} elseif($op == 'getuserapp') {
	getuserapp();
	if(empty($_GET['subop'])) {
		$my_userapp = array();
		foreach($_G['my_userapp'] as $value) {
			if($value['allowsidenav'] && !isset($_G['cache']['userapp'][$value['appid']])) {
				$my_userapp[] = $value;
			}
		}
	} else {
		$my_userapp = $_G['my_menu'];
	}
} elseif($op == 'closefeedbox') {

	dsetcookie('closefeedbox', 1);

}

include template('home/spacecp_common');

?>