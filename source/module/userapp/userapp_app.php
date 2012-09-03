<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_app.php 11292 2010-05-28 03:03:00Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($appid == '1036584') {
} else {
	require_once libfile('function/spacecp');
	ckrealname('userapp');

	ckvideophoto('userapp');

	if(!checkperm('allowmyop')) {
		showmessage('no_privilege');
	}
}

$app = array();
$query = DB::query("SELECT * FROM ".DB::table('common_myapp')." WHERE appid='$appid' LIMIT 1");
if($app = DB::fetch($query)) {
	if($app['flag']<0) {
		showmessage('no_privilege_myapp');
	}
}

$canvasTitle = '';
$isFullscreen = 0;
$displayUserPanel = 0;
if ($app['canvastitle']) {
	$canvasTitle =$app['canvastitle'];
}
if ($app['fullscreen']) {
	$isFullscreen = $app['fullscreen'];
}
if ($app['displayuserpanel']) {
	$displayUserPanel = $app['displayuserpanel'];
}

$my_appId = $appid;
$my_suffix = base64_decode(urldecode($_GET['my_suffix']));

$my_prefix = getsiteurl();

updatecreditbyaction('useapp', 0, array(), $appid);

if (!$my_suffix) {
	header('Location: userapp.php?mod=app&id='.$my_appId.'&my_suffix='.urlencode(base64_encode('/')));
	exit;
}

if (preg_match('/^\//', $my_suffix)) {
	$url = 'http://apps.manyou.com/'.$my_appId.$my_suffix;
} else {
	if ($my_suffix) {
		$url = 'http://apps.manyou.com/'.$my_appId.'/'.$my_suffix;
	} else {
		$url = 'http://apps.manyou.com/'.$my_appId;
	}
}
if (strpos($my_suffix, '?')) {
	$url = $url.'&my_uchId='.$_G['uid'].'&my_sId='.$_G['setting']['my_siteid'];
} else {
	$url = $url.'?my_uchId='.$_G['uid'].'&my_sId='.$_G['setting']['my_siteid'];
}
$url .= '&my_prefix='.urlencode($my_prefix).'&my_suffix='.urlencode($my_suffix);
$current_url = getsiteurl().'userapp.php';
if ($_SERVER['QUERY_STRING']) {
	$current_url = $current_url.'?'.$_SERVER['QUERY_STRING'];
}
$extra = $_GET['my_extra'];
$timestamp = $_G['timestamp'];
$url .= '&my_current='.urlencode($current_url);
$url .= '&my_extra='.urlencode($extra);
$url .= '&my_ts='.$timestamp;
$url .= '&my_appVersion='.$app['version'];
$url .= '&my_fullscreen='.$isFullscreen;
$hash = $_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$appid.'|'.$current_url.'|'.$extra.'|'.$timestamp.'|'.$_G['setting']['my_sitekey'];
$hash = md5($hash);
$url .= '&my_sig='.$hash;
$my_suffix = urlencode($my_suffix);

$canvasTitle = '';
$isFullscreen = 0;
$displayUserPanel = 0;
if ($app['canvastitle']) {
	$canvasTitle =$app['canvastitle'];
}
if ($app['fullscreen']) {
	$isFullscreen = $app['fullscreen'];
}
if ($app['displayuserpanel']) {
	$displayUserPanel = $app['displayuserpanel'];
}
include_once template("userapp/userapp_app");
?>