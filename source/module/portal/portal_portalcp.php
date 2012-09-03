<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_portalcp.php 11336 2010-05-31 02:03:51Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$ac = in_array($_GET['ac'], array('comment', 'article', 'block', 'portalblock', 'topic', 'diy', 'upload', 'category'))?$_GET['ac']:'index';
if(empty($ac)) {
	showmessage('portalcp_operation_invalid');
}

if(empty($_G['uid'])) {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		dsetcookie('_refer', rawurlencode('portal.php?mod=portalcp&ac='.$ac));
	}
	showmessage('to_login', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
}

require_once libfile('function/portalcp');
require_once libfile('portalcp/'.$ac, 'include');
?>