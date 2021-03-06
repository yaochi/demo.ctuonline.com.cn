<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_manage.php 10066 2010-05-06 09:30:28Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!checkperm('allowmyop')) {
	showmessage('no_privilege');
}

$uchUrl = getsiteurl().'userapp.php?mod=manage';

if(submitcheck('ordersubmit')) {
	if(empty($_POST['order'])) $_POST['order'] = array();
	$displayorder = count($_POST['order']);

	foreach($_POST['order'] as $key => $appid) {
		$appid = intval($appid);
		if($_G['my_userapp'][$appid]['menuorder'] != $displayorder) {
			DB::update('home_userapp', array('menuorder'=>$displayorder), array('uid'=>$space['uid'], 'appid'=>$appid));
		}
		$displayorder--;
	}

	$_POST['menunum'] = abs(intval($_POST['menunum']));
	if($_POST['menunum']) {
		DB::update('common_member_field_home', array('menunum'=>$_POST['menunum']), array('uid'=>$_G['uid']));
	}

	showmessage('do_success', 'userapp.php?mod=manage&ac=menu');
}


$my_prefix = 'http://uchome.manyou.com';
if(empty($_GET['my_suffix'])) {
	$appId = intval($_GET['appid']);
	if ($appId) {
		$mode = $_GET['mode'];
		if($mode == 'about') {
			$my_suffix = '/userapp/about?appId='.$appId;
		} else {
			$my_suffix = '/userapp/privacy?appId='.$appId;
		}
	} else {
		$my_suffix = '/userapp/list';
	}
} else {
	$my_suffix = $_GET['my_suffix'];
}
$my_extra = isset($_GET['my_extra']) ? $_GET['my_extra'] : '';

$delimiter = strrpos($my_suffix, '?') ? '&' : '?';
$myUrl = $my_prefix.urldecode($my_suffix.$delimiter.'my_extra='.$my_extra);



$my_userapp = $my_default_userapp = array();
if($my_suffix == '/userapp/list') {
	$_GET['op'] = 'menu';
	$max_order = 0;
	if(is_array($_G['cache']['userapp'])) {
		foreach($_G['cache']['userapp'] as $value) {
			if(isset($_G['my_userapp'][$value['appid']])) {
				$my_default_userapp[$value['appid']] = $value;
				unset($_G['my_userapp'][$value['appid']]);
			}
		}
	}
	if(is_array($_G['my_userapp'])) {
		foreach($_G['my_userapp'] as $value) {
			$my_userapp[$value['appid']] = $value;
			if($value['displayorder']>$max_order) $max_order = $value['displayorder'];
		}
	}
}

$timestamp = $_G['timestamp'];
$hash = $_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$_G['setting']['my_sitekey'].'|'.$timestamp;
$hash = md5($hash);
$delimiter = strrpos($myUrl, '?') ? '&' : '?';

$url = $myUrl.$delimiter.'s_id='.$_G['setting']['my_siteid'].'&uch_id='.$_G['uid'].'&uch_url='.urlencode($uchUrl).'&my_suffix='.urlencode($my_suffix).'&timestamp='.$timestamp.'&my_sign='.$hash;

$actives = array('view'=> ' class="active"');
$menunum[$_G['member']['menunum']] = ' selected ';

include_once template("userapp/userapp_manage");

?>