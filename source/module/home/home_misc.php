<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_misc.php 6790 2010-03-25 12:30:53Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$ac = empty($_G['gp_ac']) ? '' : $_G['gp_ac'];
$acs = array('lostpasswd', 'swfupload', 'inputpwd', 'ajax', 'seccode', 'sendmail', 'stat', 'emailcheck','upload','learningupload');
if(empty($ac) || !in_array($ac, $acs)) {
	showmessage('enter_the_space', 'home.php?mod=space');
}

$theurl = 'home.php?mod=misc&ac='.$ac;

echo $_GET[type];

if ($_GET['type']=='*') {
	require_once libfile('misc/uploadattachment', 'include');
}

require_once libfile('misc/'.$ac, 'include');

?>