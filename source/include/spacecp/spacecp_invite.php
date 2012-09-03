<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_invite.php 10453 2010-05-11 08:47:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

space_merge($space, 'count');



$siteurl = getsiteurl();

$maxcount = 50;

$config = $_G['setting']['inviteconfig'];
$creditname = $config['inviterewardcredit'];
//$allowinvite = ($_G['setting']['regstatus'] > 1 && $creditname && $_G['group']['allowinvite']) ? 1 : 0;
$allowinvite = 1;
$unit = $_G['setting']['extcredits'][$creditname]['unit'];
$credittitle = $_G['setting']['extcredits'][$creditname]['title'];
$creditnum = $_G['group']['inviteprice'];
$creditname = 'extcredits'.$creditname;

$inviteurl = $invite_code = '';
$appid = empty($_GET['app']) ? 0 : intval($_GET['app']);
$fid=empty($_GET['fid']) ? 0 : intval($_GET['fid']);
$sitename=empty($_POST['sitename']) ? $_G['setting']['sitename'] : $_POST['sitename'];
if($fid!='0'){
	$query=DB::query("SELECT * FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
	$groupinfo=DB::fetch($query);
	$baseurl = 'forum.php?mod=group&action=manage&op=group&fid='.$fid;
	$groupicon=get_groupimg($groupinfo['icon'], 'icon');
	$avatar='<img src="'.getsiteurl().$groupicon.'" complete="complete" width="124" height="124"/>';
}else{
	$baseurl = 'home.php?mod=spacecp&ac=invite';
	$avatar=avatar($space['uid'], 'middle');
}
$mailvar = array(
	'avatar' => $avatar,
	'uid' => $space['uid'],
	'username' => $space['username'],
	'sitename' =>$sitename,
	'siteurl' => $siteurl
);

$appinfo = array();
if($appid) {
	$query = DB::query("SELECT * FROM ".DB::table('common_myapp')." WHERE appid='$appid'");
	$appinfo = DB::fetch($query);
	if($appinfo) {
		$inviteapp = "&amp;app=$appid";
		$mailvar['appid'] = $appid;
		$mailvar['appname'] = $appinfo['appname'];
	} else {
		$appid = 0;
	}
}

if(!$creditnum) {
	$inviteurl = getinviteurl(0, 0, $appid);
}

if(submitcheck('emailinvite')) {

	if(!$allowinvite) {
		showmessage('close_invite', $baseurl);
	}

	//if(!$_G['group']['allowmailinvite']) {
	//	showmessage('mail_invite_not_allow', $baseurl);
	//}
	$keytime = intval($_POST['keytime']);

	$_POST['email'] = str_replace("\n", ',', $_POST['email']);
	$newmails = array();
	$mails = explode(",", $_POST['email']);
	foreach ($mails as $value) {
		$value = trim($value);
		if(isemail($value)) {
			$newmails[] = $value;
		}
	}
	$newmails = array_unique($newmails);
	$invitenum = count($newmails);

	if($invitenum < 1) {
		showmessage('mail_can_not_be_empty', $baseurl);
	}

	$msetarr = array();
	if($creditnum) {
		$allcredit = $invitenum * $creditnum;
		if($space[$creditname] < $allcredit) {
			showmessage('mail_credit_inadequate', $baseurl);
		}

		foreach($newmails as $value) {
			$code = strtolower(random(6));
			$setarr = array(
				'uid' => $_G['uid'],
				'code' => $code,
				'email' => daddslashes($value),
				'type' => 1,
				'appid' => $appid,
				'dateline' => $_G['timestamp'],
				'endtime' => ($_G['group']['maxinviteday']?($_G['timestamp']+$_G['group']['maxinviteday']*24*3600):0)
			);
			$id = DB::insert('common_invite', $setarr, 1);

			$mailvar['inviteurl'] = getinviteurl($id, $code, $appid);
			
			createmail($value, $mailvar);
		}

		updatemembercount($_G['uid'], array($creditname => "-$allcredit"));

	} else {
		if($fid!='0'){
			$mailvar['inviteurl'] = getinviteurls($id, $code, $appid,$fid,$keytime);
			foreach($newmails as $value) {
				createmails($value, $mailvar,$fid);
			}
		}else{
			$mailvar['inviteurl'] = $inviteurl;
			foreach($newmails as $value) {
				createmail($value, $mailvar);
			}
		}
	}
	//print_r($mailvar);
	//exit;
	showmessage('send_result_succeed',$baseurl);
}

if($_GET['op'] == 'resend') {

	$id = $_GET['id'] ? intval($_GET['id']) : 0;

	if(submitcheck('resendsubmit')) {

		if(empty($id)) {
			showmessage('send_result_resend_error', $baseurl);
		}

		$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE id='$id' AND uid='$_G[uid]'");
		if($value = DB::fetch($query)) {
			if($creditnum) {
				$inviteurl = getinviteurl($value['id'], $value['code'], $value['appid']);
			}
			$mailvar['inviteurl'] = $inviteurl;

			createmail($value['email'], $mailvar);
			showmessage('send_result_succeed', dreferer(), array('id' => $id), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 2));

		} else {
			showmessage('send_result_resend_error', $baseurl, array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 2));
		}
	}

} elseif($_GET['op'] == 'delete') {

	$id = $_GET['id'] ? intval($_GET['id']) : 0;
	if(empty($id)) {
		showmessage('there_is_no_record_of_invitation_specified', $baseurl);
	}
	$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE id='$id' AND uid='$_G[uid]'");
	if($value = DB::fetch($query)) {
		if(submitcheck('deletesubmit')) {
			DB::query("DELETE FROM ".DB::table('common_invite')." WHERE id='$id'");
			showmessage('do_success', dreferer(), array('id' => $id), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 2));
		}
	} else {
		showmessage('there_is_no_record_of_invitation_specified', $baseurl, array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 2));
	}

} else {

	$list = $flist = $dels = array();
	$count = 0;

	$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE uid='$_G[uid]' ORDER BY id DESC");
	while ($value = DB::fetch($query)) {

		if($value['fuid']) {
			$flist[] = $value;
		} else {

			if($_G['timestamp'] > $value['endtime']) {
				$dels[] = $value['id'];
				continue;
			}

			if($creditnum) {
				$inviteurl = getinviteurl($value['id'], $value['code'], $value['appid']);
			}

			if($value['type']) {
				$maillist[] = array(
					'email' => $value['email'],
					'url' => $inviteurl,
					'id' => $value['id']
				);
			} else {
				$list[] = $inviteurl;
				$count++;
			}
		}
	}

	if($dels) {
		DB::query("DELETE FROM ".DB::table('common_invite')." WHERE id IN (".dimplode($dels).")");
	}

	if($creditnum) {
		$list_str = empty($list)?'':implode("\n", $list);
		if(submitcheck('invitesubmit')) {

			if(!$allowinvite) {
				showmessage('close_invite', $baseurl);
			}

			$invitenum = intval($_POST['invitenum']);
			if($invitenum < 1) $invitenum = 1;

			if($_G['group']['maxinvitenum']) {
				$daytime = $_G['timestamp'] - 24*3600;
				$invitecount = getcount('common_invite', "uid='$_G[uid]' AND dateline>'$daytime'");
				if($invitecount + $invitenum > $_G['group']['maxinvitenum']) {
					showmessage('max_invitenum_error', NULL, array('maxnum'=>$_G['group']['maxinvitenum']));
				}
			}

			$allcredit = $invitenum * $creditnum;
			if($space[$creditname] < $allcredit) {
				showmessage('mail_credit_inadequate', $baseurl);
			}

			$codes = array();
			for ($i=0; $i<$invitenum; $i++) {
				$code = strtolower(random(6));
				$codes[] = "('$_G[uid]', '$code', '$_G[timestamp]', '".($_G['group']['maxinviteday']?($_G['timestamp']+$_G['group']['maxinviteday']*24*3600):0)."')";
			}

			if($codes) {
				DB::query("INSERT INTO ".DB::table('common_invite')." (uid, code, dateline, endtime) VALUES ".implode(',', $codes));
				require_once libfile('class/credit');
				$creditobj = new credit();
				$creditobj->updatemembercount(array($creditname=>0-$allcredit), $_G['uid']);
			}
			showmessage('do_success', 'home.php?mod=spacecp&ac=invite&quickforward=1');
		}
	}
	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$uri = substr($uri, 0, strrpos($uri, '/')+1);

	$actives = array('invite'=>' class="active"');
}

include template('home/spacecp_invite');

function createmail($mail, $mailvar) {
	global $_G, $space, $appinfo;

	$mailvar['saymsg'] = empty($_POST['saymsg'])?'':getstr($_POST['saymsg'], 500);

	require_once libfile('function/mail');
	$subject = lang('spacecp', $appinfo?'app_invite_subject':'invite_subject', $mailvar);
	$message = lang('spacecp', $appinfo?'app_invite_massage':'invite_massage', $mailvar);
	
	sendmail($mail, $subject, $message);
}

function getinviteurl($inviteid, $invitecode, $appid) {
	global $_G;

	if($inviteid && $invitecode) {
		$inviteurl = getsiteurl()."home.php?mod=invite&amp;id={$inviteid}&amp;c={$invitecode}";
	} else {
		$invite_code = space_key($_G['uid'], $appid);
		$inviteapp = $appid?"&amp;app=$appid":'';
		$inviteurl = getsiteurl()."home.php?mod=invite&amp;u=$_G[uid]&amp;c=$invite_code{$inviteapp}";
	}
	return $inviteurl;
}
function createmails($mail, $mailvar,$fid) {
	global $_G, $space, $appinfo;

	$mailvar['saymsg'] = empty($_POST['saymsg'])?'':getstr($_POST['saymsg'], 500);

	require_once libfile('function/mail');
	$subject = lang('spacecp', 'invite_intoforum', $mailvar);
	$message = lang('spacecp', 'invite_intoforum_massage', $mailvar);
	sendmail($mail, $subject, $message);
}

function getinviteurls($inviteid, $invitecode,$appid,$fid,$keytime) {
	global $_G;
	//update by qiaoyongzhi,2011-2-25,修改邀请链接添加时间水印
	if($keytime!=-1){
		$key=time();
		$key=$key+($keytime*24*60*60);
		$key=base64_encode($key);
		$key=urlencode($key);
		$keyurl="&key={$key}";
	}else{
		$key=base64_encode('group'.$fid);
		$key=urlencode($key);
		$keyurl="&key={$key}";
	}
	$groupkey=md5($fid.'group');
	$inviteurl = getsiteurl()."group.php?mod=invite&u=$_G[uid]&fid=$fid&groupkey=$groupkey".$keyurl;
	return $inviteurl;
}
?>