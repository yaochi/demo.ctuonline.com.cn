<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_ajax.php 10313 2010-05-10 08:13:57Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if($_G['gp_action'] == 'attachlist') {

	require_once libfile('function/home_attachment');
	
	$attachlist = getattachs($_G['gp_id'], $_G['gp_idtype'], intval($_G['gp_posttime']));
	
	$attachlist = $attachlist['attachs'];

	include template('common/header_ajax');
	include template('home/ajax_attachlist');
	include template('common/footer_ajax');
	dexit();
}

showmessage($_G['setting']['reglinkname']);

?>