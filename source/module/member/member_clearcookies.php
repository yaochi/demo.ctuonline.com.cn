<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_clearcookies.php 10361 2010-05-11 01:00:55Z zhaoxiongfei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if(is_array($_COOKIE) && (empty($_G['uid']) || ($_G['uid'] && $formhash == formhash()))) {
	foreach($_G['cookie'] as $key => $val) {
		dsetcookie($key, '', -86400 * 365, 0);
	}
	foreach($_COOKIE as $key => $val) {
		setcookie($key, '', -86400 * 365, $_G['config']['cookie']['cookiepath'], '');
	}
}

showmessage('login_clearcookie', 'forum.php', array(), $_G['inajax'] ? array('msgtype' => 3, 'showmsg' => true) : array());

?>