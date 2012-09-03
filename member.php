<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member.php 10578 2010-05-12 08:54:40Z liulanbo $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'member');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('activate', 'clearcookies', 'emailverify', 'getpasswd',
					'groupexpiry', 'logging', 'lostpasswd',
					'register', 'regverify', 'switchstatus','viewdirection');


$mod = !in_array($discuz->var['mod'], $modarray) ? 'logging' : $discuz->var['mod'];

define('CURMODULE', $mod);

$modcachelist = array('register' => array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional', 'ipctrl'));

$cachelist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}

$discuz->cachelist = $cachelist;
$discuz->init();

runhooks();

require DISCUZ_ROOT.'./source/module/member/member_'.$mod.'.php';

function getinvite() {
	global $_G;

	$result = array();
	$cookies = empty($_G['cookie']['invite_auth'])?array():explode(',', $_G['cookie']['invite_auth']);
	$cookiecount = count($cookies);

	if($cookiecount == 2) {
		$id = intval($cookies[0]);
		$code = $cookies[1];

		$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE id='$id'");
		if($invite = DB::fetch($query)) {
			if($invite['code'] == $code && empty($invite['fuid']) && (empty($invite['endtime']) || $_G['timestamp'] < $invite['endtime'])) {
				$result['uid'] = $invite['uid'];
				$result['id'] = $invite['id'];
				$result['appid'] = $invite['appid'];
			}
		}
	} elseif($cookiecount == 3) {
		$uid = intval($cookies[0]);
		$code = $cookies[1];
		$appid = intval($cookies[2]);

		$invite_code = space_key($uid, $appid);
		if($code == $invite_code) {
			$result['uid'] = $uid;
			$result['appid'] = $appid;
		}
	}

	if($result['uid']) {
		$member = getuserbyuid($result['uid']);
		$result['username'] = $member['username'];
	} else {
		dsetcookie('invite_auth', '', -86400 * 365);
	}

	return $result;
}


?>