<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_space.php 11772 2010-06-12 09:59:58Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$uid = empty($_GET['uid']) ? 0 : intval($_GET['uid']);

if($_GET['username']) {
	$member = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_GET[username]' LIMIT 1");
	$uid = $member['uid'];
} elseif ($_GET['domain']) {
	$member = DB::fetch_first("SELECT uid FROM ".DB::table('common_member_field_home')." WHERE domain='$_GET[domain]' LIMIT 1");
	$uid = $member['uid'];
}

$dos = array('index', 'doing', 'blog', 'album', 'friend', 'wall',
	'notice', 'share', 'home', 'pm', 'videophoto', 'top', 'favorite',
	'thread', 'trade', 'poll', 'activity', 'debate', 'reward', 'group', 'profile','nwkt','doc','follow','tag','atme','mycomment','myfeed','me','mecomment','plugin');

$do = (!empty($_GET['do']) && in_array($_GET['do'], $dos))?$_GET['do']:'index';

if(empty($uid)) $uid = $_G['uid'];

if($uid) {
	if($uid==-1){
		showmessage('由于用户采用匿名发布，您无法查看其个人信息！','home.php');
	}else{
		$space = getspace($uid);
		$space['regname'] = $space['username'];
		if(empty($space)) {
			showmessage('space_does_not_exist');
		}
			// 插入用户真实姓名 by lujianqing 2010-08-30
			$space['username'] = user_get_user_name($uid);
	}
}




if(empty($space)) {
	if(in_array($do, array('doing', 'blog', 'album', 'share', 'home', 'thread', 'trade', 'poll', 'activity', 'debate', 'reward', 'group'))) {
		$_GET['view'] = 'all';
		$space['uid'] = 0;
	} else {
		showmessage('login_before_enter_home', 'member.php?mod=logging&action=login');
	}
} else {

	if($space['status'] == -1 && $_G['adminid'] != 1) {
		showmessage('space_has_been_locked');
	}

	if(in_array($space['groupid'], array(4, 5, 6)) && $_G['adminid'] != 1) {
		$_GET['do'] = $do = 'profile';
	}

	if(!ckprivacy($do, 'view')) {
		include template('home/space_privacy');
		exit();
	}

	if(!$space['self']) $_GET['view'] = 'me';

	get_my_userapp();

	get_my_app();
}

$diymode = 0;

$seccodecheck = $_G['setting']['seccodestatus'] & 4;
$secqaacheck = $_G['setting']['secqaa']['status'] & 2;


//---判断是否是访问官方博客用户的主页-------------

//add by songsp 2010-7-23 15:33:15
if( checkIsOfficial($_GET['uid']) ){
	//if(in_array($do, array('home', 'doing', 'blog', 'album', 'thread', 'share', 'friend', 'wall', 'profile'))){
		$isOfficial = 1;
		if('space'==$_GET['from']){
		$do = "index";
		//$_GET['from']="space";
		//$_GET['view']="me";
		}
	//}


}

//--end--------------------------------------------


require_once libfile('space/'.$do, 'include');

?>