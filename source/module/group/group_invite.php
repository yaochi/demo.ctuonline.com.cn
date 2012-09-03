<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_invite.php 11548 2010-06-08 02:30:44Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
	$fid=intval($_GET['fid']);
	$groupkey=$_GET['groupkey'];
	$uid = intval($_GET['u']);
	if($_GET['accept'] == 'no'){
		showmessage('当前专区邀请已经忽略了', 'home.php');
	}
	if(md5($fid.'group')!=$groupkey){
		showmessage('您没有受到该专区的邀请','home.php');
	}
	//BEGIN CHANGE BY QIAOYONGZHI 2011-2-22 13:00
    $invitekey=$_GET['key'];
    $invitekey=urldecode($invitekey);
    $invitekey=base64_decode($invitekey);
    $now=time();
	if($invitekey){
		if($invitekey=='group'.$fid||$now<$invitekey||$fid=='1275'||$fid=='1277'){
		}else{
			showmessage('该邀请链接已过期','home.php?mod=space&do=home');
		}
	}else{
		showmessage('该邀请链接已过期','home.php?mod=space&do=home');
	}
	//END
	
	if($fid){
		$query=DB::query("SELECT * FROM " . DB::table('forum_forum') . " f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE fid='$fid'");
		$groupinf=DB::fetch($query);
		$scode=md5($fid.'esn');
		$groupinf['icon'] = get_groupimg($groupinf['icon'], 'icon');
		if($uid){
			$query=DB::query("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid='$uid' ");
			$userinf=DB::fetch($query);
		}
		$flist = groupuserlist($fid, '', '12', '0', "AND level='4'");	
	}
	/*else{
		showmessage('该邀请链接已过期','home.php?mod=space&do=home');
	}*/
	
include_once template('group/invite');

?>