<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newthread.php 11483 2010-06-04 02:36:22Z liulanbo $
 */



if(!defined('IN_DISCUZ')) {
	exit('Access Denied'); 
}
define('APPTYPEID', 2);
define('CURSCRIPT', 'forum');

//require_once libfile('class/core');
//require_once libfile('class/forum');//引入这连个文件存在错误

$discuz = & discuz_core::instance();
$modarray = array('ajax','announcement','attachment','forumdisplay',
'group','image','index','medal','misc','modcp','notice','post','redirect',
'relatekw','relatethread','rss','search','topicadmin','trade','viewthread','activity'
);
$modcachelist = array(
'index'		=> array('announcements', 'onlinelist', 'forumlinks', 'advs_index',
'heats', 'historyposts', 'onlinerecord', 'blockclass', 'userstats'),
'forumdisplay'	=> array('smilies', 'announcements_forum', 'globalstick', 'forums',
'icons', 'onlinelist', 'forumstick', 'blockclass',
'threadtable_info', 'threadtableids'),
'viewthread'	=> array('smilies', 'smileytypes', 'forums', 'usergroups', 'ranks',
'stamps', 'bbcodes', 'smilies',
'custominfo', 'groupicon', 'stamps', 'threadtableids', 'threadtable_info'),
'post'		=> array('bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes',
'icons', 'domainwhitelist'),
'space'		=> array('fields_required', 'fields_optional', 'custominfo'),
'group'		=> array('grouptype'),
);
$mod = !in_array($discuz->var['mod'], $modarray) ? 'index' : $discuz->var['mod'];
define('CURMODULE', $mod != 'redirect' ? $mod : 'viewthread');
$cachelist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}
$discuz->cachelist = $cachelist;
$discuz->init();
loadforum();
set_rssauth();
runhooks();



define('NOROBOT', TRUE);

cknewuser();

require_once libfile('class/credit');
require_once libfile('function/post');


$pid = intval(getgpc('pid'));
$sortid = intval(getgpc('sortid'));
$typeid = intval(getgpc('typeid'));
$special = intval(getgpc('special'));//发帖类型

$_G['gp_from'] = !empty($_G['gp_from']) && in_array($_G['gp_from'], array('home', 'portal')) ? $_G['gp_from'] : '';

$postinfo = array('subject' => '');
$thread = array('readperm' => '', 'pricedisplay' => '', 'hiddenreplies' => '');

$_G['forum_dtype'] = $_G['forum_checkoption'] = $_G['forum_optionlist'] = $tagarray = $_G['forum_typetemplate'] = array();
if($sortid) {
	require_once libfile('function/threadsort');
	threadsort_checkoption($sortid);
}

if($_G['forum']['status'] == 3) {
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid'], 'post');
	if($status == -1) {
		showmessage('forum_not_group', 'index.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', "forum.php?mod=group&fid=$_G[fid]");
	} elseif($status == 3) {
		showmessage('forum_group_moderated', "forum.php?mod=group&fid=$_G[fid]");
	} elseif($status == 4) {
		showmessage('forum_group_not_groupmember', "forum.php?mod=forumdisplay&fid=$_G[fid]", array(), array('showmsg' => 1));
	}
}

if(empty($_G['gp_action'])) {
	showmessage('undefined_action', NULL);
} elseif($_G['gp_action'] == 'threadsorts') {
	require_once libfile('function/threadsort');
	loadcache(array('threadsort_option_'.$_G['gp_sortid'], 'threadsort_template_'.$_G['gp_sortid']));
	threadsort_optiondata($_G['gp_pid'], $_G['gp_sortid'], $_G['cache']['threadsort_option_'.$_G['gp_sortid']], $_G['cache']['threadsort_template_'.$_G['gp_sortid']]);
	$template = intval($_G['gp_operate']) ? 'forum/search_sortoption' : 'forum/post_sortoption';
	include template($template);
	exit;
}  elseif(($_G['forum']['simple'] & 1) || $_G['forum']['redirect']) {
	showmessage('forum_disablepost');
}

require_once libfile('function/discuzcode');

$space = array();
space_merge($space, 'field_home');

if($_G['gp_action'] == 'reply') {
	$addfeedcheck = !empty($space['privacy']['feed']['newreply']) ? 'checked="checked"': '';
} else {
	$addfeedcheck = !empty($space['privacy']['feed']['newthread']) ? 'checked="checked"': '';
}


$navigation = $navtitle = $homedo = '';

if(!empty($_G['gp_cedit'])) {
	unset($_G['inajax'], $_G['gp_infloat'], $_G['gp_ajaxtarget'], $_G['gp_handlekey']);
}

if($_G['gp_action'] == 'edit' || $_G['gp_action'] == 'reply') {

	if($thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$_G[tid]'".($_G['forum_auditstatuson'] ? '' : " AND displayorder>='0'"))) {

		$navtitle = $thread['subject'].' - ';
		if($thread['readperm'] && $thread['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $thread['authorid'] != $_G['uid']) {
			showmessage('thread_nopermission', NULL, array('readperm' => $thread['readperm']), array('login' => 1));
		}

		$_G['fid'] = $thread['fid'];
		$special = $thread['special'];

	} else {
		showmessage('thread_nonexistence');
	}

	if($_G['gp_action'] == 'reply' && ($thread['closed'] == 1) && !$_G['forum']['ismoderator']) {
		showmessage('post_thread_closed');
	}

}

if($_G['forum']['status'] == 3) {
	$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_G['gp_extra']) ? '&action=list&'.preg_replace("/^(&)*/", '', $_G['gp_extra']) : '').'#groupnav';
	$navigation = ' &rsaquo; <a href="group.php">'.$_G['setting']['navs'][3]['navname'].'</a> '.get_groupnav($_G['forum']);
} else {
	if($_G['gp_from'] != 'portal') {
		$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_G['gp_extra']) ? '&'.preg_replace("/^(&)*/", '', $_G['gp_extra']) : '');
		$navigation = '&rsaquo; <a href="'.$returnurl.'">'.$_G['forum']['name'].'</a> '.$navigation;
	}
	$navtitle = $navtitle.strip_tags($_G['forum']['name']).' - ';

	if($_G['forum']['type'] == 'sub') {
		$fup = DB::fetch_first("SELECT name, fid FROM ".DB::table('forum_forum')." WHERE fid='".$_G['forum']['fup']."'");
		$navigation = '&rsaquo; <a href="forum.php?mod=forumdisplay&fid='.$fup['fid'].'">'.$fup['name'].'</a> '.$navigation;
		$navtitle = $navtitle.strip_tags($fup['name']).' - ';
	}
	$navigation = ' &rsaquo; <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> '.$navigation;
}

periodscheck('postbanperiods');

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	showmessage('forum_passwd', "forum.php?mod=forumdisplay&fid=$_G[fid]");
}

if(empty($_G['forum']['allowview'])) {
	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		showmessagenoperm('viewperm', $_G['fid']);
	}
} elseif($_G['forum']['allowview'] == -1) {
	showmessage('forum_access_view_disallow');
}

formulaperm($_G['forum']['formulaperm']);

if(!$_G['adminid'] && $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 3600)) {
	if(TIMESTAMP - (DB::result_first("SELECT regdate FROM ".DB::table('common_member')." WHERE uid='$_G[uid]'")) < $_G['setting']['newbiespan'] * 3600) {
		showmessage('post_newbie_span', '', array('newbiespan' => $_G['setting']['newbiespan']));
	}
}

$special = $special > 0 && $special < 7 || $special == 127 ? intval($special) : 0;

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
$_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
$_G['group']['attachextensions'] = $_G['forum']['attachextensions'] ? $_G['forum']['attachextensions'] : $_G['group']['attachextensions'];
if($_G['group']['attachextensions']) {
	$imgexts = explode(',', str_replace(' ', '', $_G['group']['attachextensions']));
	$imgexts = array_intersect(array('jpg','jpeg','gif','png','bmp'), $imgexts);
	$imgexts = implode(', ', $imgexts);
} else {
	$imgexts = 'jpg, jpeg, gif, png, bmp';
}
$allowuploadnum = TRUE;
if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
	if($_G['group']['maxattachnum']) {
		$allowuploadnum = $_G['group']['maxattachnum'] - DB::result_first("SELECT count(*) FROM ".DB::table('forum_attachment')." WHERE uid='$_G[uid]' AND pid>'0' AND dateline>'$_G[timestamp]'-86400");
		$allowuploadnum = $allowuploadnum < 0 ? 0 : $allowuploadnum;
	}
	if($_G['group']['maxsizeperday']) {
		$allowuploadsize = $_G['group']['maxsizeperday'] - intval(DB::result_first("SELECT SUM(filesize) FROM ".DB::table('forum_attachment')." WHERE uid='$_G[uid]' AND dateline>'$_G[timestamp]'-86400"));
		$allowuploadsize = $allowuploadsize < 0 ? 0 : $allowuploadsize;
		$allowuploadsize = $allowuploadsize / 1048576 >= 1 ? round(($allowuploadsize / 1048576), 1).'MB' : round(($allowuploadsize / 1024)).'KB';
	}
}
$allowpostimg = $_G['group']['allowpostimage'] && $imgexts;
$enctype = ($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) ? 'enctype="multipart/form-data"' : '';
$maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1).'MB' : round(($_G['group']['maxattachsize'] / 1024)).'KB';

$postcredits = $_G['forum']['postcredits'] ? $_G['forum']['postcredits'] : $_G['setting']['creditspolicy']['post'];
$replycredits = $_G['forum']['replycredits'] ? $_G['forum']['replycredits'] : $_G['setting']['creditspolicy']['reply'];
$digestcredits = $_G['forum']['digestcredits'] ? $_G['forum']['digestcredits'] : $_G['setting']['creditspolicy']['digest'];
$postattachcredits = $_G['forum']['postattachcredits'] ? $_G['forum']['postattachcredits'] : $_G['setting']['creditspolicy']['postattach'];

$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

$extra = (!empty($_G['gp_extra']) ? rawurlencode($_G['gp_extra']) : '').(!empty($_G['gp_from']) ? '&from='.rawurlencode($_G['gp_from']) : '');
$notifycheck = empty($emailnotify) ? '' : 'checked="checked"';
$stickcheck = empty($sticktopic) ? '' : 'checked="checked"';
$digestcheck = empty($addtodigest) ? '' : 'checked="checked"';

$subject = isset($_G['gp_subject']) ? dhtmlspecialchars(censor(trim($_G['gp_subject']))) : '';
$subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
$message = isset($_G['gp_message']) ? censor($_G['gp_message']) : '';
$polloptions = isset($polloptions) ? censor(trim($polloptions)) : '';
$readperm = isset($_G['gp_readperm']) ? intval($_G['gp_readperm']) : 0;
$price = isset($_G['gp_price']) ? intval($_G['gp_price']) : 0;
$_G['setting']['tagstatus'] = $_G['setting']['tagstatus'] && $_G['forum']['allowtag'] ? ($_G['setting']['tagstatus'] == 2 ? 2 : $_G['forum']['allowtag']) : 0;

if(empty($bbcodeoff) && !$_G['group']['allowhidecode'] && !empty($message) && preg_match("/\[hide=?\d*\].+?\[\/hide\]/is", preg_replace("/(\[code\](.+?)\[\/code\])/is", ' ', $message))) {
	showmessage('post_hide_nopermission');
}

if(periodscheck('postmodperiods', 0)) {
	$modnewthreads = $modnewreplies = 1;
} else {
	$censormod = censormod($subject."\t".$message);
	$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $_G['forum']['modnewposts'] || $censormod ? 1 : 0;
	$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $_G['forum']['modnewposts'] == 2 || $censormod ? 1 : 0;
}

if($_G['group']['allowposturl'] < 3 && $message) {
	$urllist = get_url_list($message);
	if(is_array($urllist[1])) foreach($urllist[1] as $key => $val) {
		if(!$val = trim($val)) continue;
		if(!iswhitelist($val)) {
			if($_G['group']['allowposturl'] == 0) {
				showmessage('post_url_nopermission');
			} elseif($_G['group']['allowposturl'] == 1) {
				$modnewthreads = $modnewreplies = 1;
				break;
			} elseif($_G['group']['allowposturl'] == 2) {
				$message = str_replace('[url]'.$urllist[0][$key].'[/url]', $urllist[0][$key], $message);
				$message = preg_replace("@\[url={$urllist[0][$key]}\](.*?)\[/url\]@i", '\\1', $message);
			}
		}
	}
}

$urloffcheck = $usesigcheck = $smileyoffcheck = $codeoffcheck = $htmloncheck = $emailcheck = '';

$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);

$_G['group']['allowpostpoll'] = $_G['group']['allowpost'] && $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
$_G['group']['allowposttrade'] = $_G['group']['allowpost'] && $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
$_G['group']['allowpostreward'] = $_G['group']['allowpost'] && $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4) && isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]);
$_G['group']['allowpostactivity'] = $_G['group']['allowpost'] && $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
$_G['group']['allowpostdebate'] = $_G['group']['allowpost'] && $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'] ? 'checked="checked"' : '';
$ordertypecheck = !empty($thread['tid']) && getstatus($thread['status'], 4) ? 'checked="checked"' : '';
$specialextra = !empty($_G['gp_specialextra']) ? $_G['gp_specialextra'] : '';

if($specialextra && $_G['group']['allowpost'] && $_G['setting']['threadplugins'] &&
	(!array_key_exists($specialextra, $_G['setting']['threadplugins']) ||
	!@in_array($specialextra, is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : unserialize($_G['forum']['threadplugin'])) ||
	!@in_array($specialextra, $_G['group']['allowthreadplugin']))) {
	$specialextra = '';
}

$_G['group']['allowanonymous'] = $_G['forum']['allowanonymous'] || $_G['group']['allowanonymous'] ? 1 : 0;

if($_G['gp_action'] == 'newthread' && $_G['forum']['allowspecialonly'] && !$special) {
	if($_G['group']['allowpostpoll']) {
		$special = 1;
	} elseif($_G['group']['allowposttrade']) {
		$special = 2;
	} elseif($_G['group']['allowpostreward']) {
		$special = 3;
	} elseif($_G['group']['allowpostactivity']) {
		$special = 4;
	} elseif($_G['group']['allowpostdebate']) {
		$special = 5;
	} elseif($_G['group']['allowpost'] && $_G['setting']['threadplugins'] && $_G['group']['allowthreadplugin'] && ($_G['forum']['threadplugin'] = unserialize($_G['forum']['threadplugin']))) {
		$threadpluginary = array_intersect($_G['group']['allowthreadplugin'], $_G['forum']['threadplugin']);
		$specialextra = $threadpluginary[0] ? $threadpluginary[0] : '';
	}

	if(!$special && !$specialextra) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}
}

if(!empty($_G['gp_from']) && $_G['gp_from'] == 'home') {
	switch($special) {
		case 0:$homedo = 'thread';break;
		case 1:$homedo = 'poll';break;
		case 2:$homedo = 'trade';break;
		case 3:$homedo = 'reward';break;
		case 4:$homedo = 'activity';break;
		case 5:$homedo = 'debate';break;
	}
	if($homedo) {
		$navigation = '';
	}
}

$editorid = 'e';
$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 2, 0, STR_PAD_LEFT);
$editormode = $_G['setting']['editoroptions']{0};
$allowswitcheditor = $_G['setting']['editoroptions']{1};
$editor = array(
	'editormode' => $editormode,
	'allowswitcheditor' => $allowswitcheditor,
	'allowhtml' => $_G['group']['allowhtml'],
	'allowhtml' => $_G['forum']['allowhtml'],
	'allowsmilies' => $_G['forum']['allowsmilies'],
	'allowbbcode' => $_G['forum']['allowbbcode'],
	'allowimgcode' => $_G['forum']['allowimgcode'],
	'allowcustombbcode' => $_G['forum']['allowbbcode'] && $_G['group']['allowcusbbcode'],
	'allowresize' => 1,
	'textarea' => 'message',
);
if($specialextra) {
	$special = 127;
}

if($_G['gp_action'] == 'newthread') {
	$policykey = 'post';
} elseif($_G['gp_action'] == 'reply') {
	$policykey = 'reply';
} else {
	$policykey = '';
}
if($policykey) {
	$postcredits = $_G['forum'][$policykey.'credits'] ? $_G['forum'][$policykey.'credits'] : $_G['setting']['creditspolicy'][$policykey];
}

$albumlist = array();
if($_G['uid']) {
	$query = DB::query("SELECT albumid, albumname, picnum FROM ".DB::table('home_album')." WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
	while($value = DB::fetch($query)) {
		if($value['picnum']) {
			$albumlist[] = $value;
		}
	}
}

$posturl = "action=$_G[gp_action]&fid=$_G[fid]".
	(!empty($_G['tid']) ? "&tid=$_G[tid]" : '').
	(!empty($pid) ? "&pid=$pid" : '').
	(!empty($special) ? "&special=$special" : '').
	(!empty($sortid) ? "&sortid=$sortid" : '').
	(!empty($typeid) ? "&typeid=$typeid" : '').
	(!empty($_G['gp_firstpid']) ? "&firstpid=$firstpid" : '').
	(!empty($_G['gp_addtrade']) ? "&addtrade=$addtrade" : '');

if($_G['gp_action'] == 'reply') {
	check_allow_action('allowreply');
} else {
	check_allow_action('allowpost');
}

if($_G['gp_action'] == 'newthread') {
	require_once libfile('post/newthread', 'include');
} 
//elseif($_G['gp_action'] == 'reply') {
//	require_once libfile('post/newreply', 'include');
//} elseif($_G['gp_action'] == 'edit') {
//	require_once libfile('post/editpost', 'include');
//} elseif($_G['gp_action'] == 'newtrade') {
//	require_once libfile('post/newtrade', 'include');
//}

function check_allow_action($action = 'allowpost') {
	global $_G;
	if(isset($_G['forum'][$action]) && $_G['forum'][$action] == -1) {
		showmessage('forum_access_disallow');
	}
}









if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if(($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate'])) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage('postperm_none_nopermission', NULL, array(), array('login' => 1));
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessagenoperm('postperm', $_G['fid']);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage('post_forum_newthread_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
}

checklowerlimit('post');

if(!submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {

	$isfirstpost = 1;
	$tagoffcheck = '';
	$showthreadsorts = !empty($sortid) || $_G['forum']['threadsorts']['required'] && empty($special);

	if($special == 2 && $_G['group']['allowposttrade']) {

		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));

	} elseif($specialextra) {

		$threadpluginclass = null;
		if(isset($_G['setting']['threadplugins'][$specialextra]['module'])) {
			$threadpluginfile = DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
			if(file_exists($threadpluginfile)) {
				@include_once $threadpluginfile;
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread')) {
					$threadplughtml = $threadpluginclass->newthread($_G['fid']);
					$buttontext = $threadpluginclass->buttontext;
					$iconfile = $threadpluginclass->iconfile;
					$iconsflip = array_flip($_G['cache']['icons']);
					$thread['iconid'] = $iconsflip[$iconfile];
				}
			}
		}

		if(!is_object($threadpluginclass)) {
			$specialextra = '';
		}
	}

	if($special == 4) {
		$activity = array('starttimeto' => '', 'starttimefrom' => '', 'place' => '', 'class' => '', 'cost' => '', 'number' => '', 'gender' => '', 'expiration' => '');
		$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach($pid);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	!isset($attachs['unused']) && $attachs['unused'] = array();
	!isset($imgattachs['unused']) && $imgattachs['unused'] = array();

	getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');

} else {
	$subject=getgpc('subject');//added by fumz ，2010-7-26 16:54:45
	
	//exit($subject);
	//exit("hello,fu");
	if($subject == '') {
		showmessage('post_sm_isnull');
	}

	if(!$sortid && !$special && $message == '') {
		showmessage('post_sm_isnull');
	}

	if($post_invalid = checkpost($subject, $message, ($special || $sortid))) {
		showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
	}

	if(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	}

	if($_G['uid']) {
		$attentionon = empty($_G['gp_attention_add']) ? 0 : 1;
	}

	$typeid = isset($typeid) && isset($_G['forum']['threadtypes']['types'][$typeid]) ? $typeid : 0;
	$displayorder = $modnewthreads ? -2 : (($_G['forum']['ismoderator'] && !empty($_G['gp_sticktopic'])) ? 1 : 0);
	$digest = ($_G['forum']['ismoderator'] && !empty($_G['gp_addtodigest'])) ? 1 : 0;
	$readperm = $_G['group']['allowsetreadperm'] ? $readperm : 0;
	$isanonymous = ($_G['group']['allowanonymous'] && $_G['gp_isanonymous']) ? 1 : 0;
	$price = intval($price);
	$price = $_G['group']['maxprice'] && !$special ? ($price <= $_G['group']['maxprice'] ? $price : $_G['group']['maxprice']) : 0;

	if(!$typeid && $_G['forum']['threadtypes']['required'] && !$special) {
		showmessage('post_type_isnull');
	}

	if(!$sortid && $_G['forum']['threadsorts']['required'] && !$special) {
		showmessage('post_sort_isnull');
	}

	if($price > 0 && floor($price * (1 - $_G['setting']['creditstax'])) == 0) {
		showmessage('post_net_price_iszero');
	}

//	if($special == 1) {

//		$polloption = $_G['gp_tpolloption'] == 2 ? explode("\n", $_G['gp_polloptions']) : $_G['gp_polloption'];
//		$pollarray = array();
//		foreach($polloption as $key => $value) {
//			$polloption[$key] = censor($polloption[$key]);
//			if(trim($value) === '') {
//				unset($polloption[$key]);
//			}
//		}
//
//		if(count($polloption) > $_G['setting']['maxpolloptions']) {
//			showmessage('post_poll_option_toomany', '', array('maxpolloptions' => $_G['setting']['maxpolloptions']));
//		} elseif(count($polloption) < 2) {
//			showmessage('post_poll_inputmore');
//		}
//
//		$curpolloption = count($polloption);
//		$pollarray['maxchoices'] = empty($_G['gp_maxchoices']) ? 0 : ($_G['gp_maxchoices'] > $curpolloption ? $curpolloption : $_G['gp_maxchoices']);
//		$pollarray['multiple'] = empty($_G['gp_maxchoices']) || $_G['gp_maxchoices'] == 1 ? 0 : 1;
//		$pollarray['options'] = $polloption;
//		$pollarray['visible'] = empty($_G['gp_visibilitypoll']);
//		$pollarray['overt'] = !empty($_G['gp_overt']);
//
//		if(preg_match("/^\d*$/", trim($_G['gp_expiration']))) {
//			if(empty($_G['gp_expiration'])) {
//				$pollarray['expiration'] = 0;
//			} else {
//				$pollarray['expiration'] = TIMESTAMP + 86400 * $_G['gp_expiration'];
//			}
//		} else {
//			showmessage('poll_maxchoices_expiration_invalid');
//		}

//	} else
	if($special == 3) {

		$rewardprice = intval($_G['gp_rewardprice']);
		/**
		 * undo 积分经验值的问题
		 */
//		if($rewardprice < 1) {
//			showmessage('reward_credits_please');
//		} elseif($rewardprice > 32767) {
//			showmessage('reward_credits_overflow');
//		} elseif($rewardprice < $_G['group']['minrewardprice'] || ($_G['group']['maxrewardprice'] > 0 && $rewardprice > $_G['group']['maxrewardprice'])) {
//			if($_G['group']['maxrewardprice'] > 0) {
//				showmessage('reward_credits_between', '', array('minrewardprice' => $_G['group']['minrewardprice'], 'maxrewardprice' => $_G['group']['maxrewardprice']));
//			} else {
//				showmessage('reward_credits_lower', '', array('minrewardprice' => $_G['group']['minrewardprice']));
//			}
//		} elseif(($realprice = $rewardprice + ceil($rewardprice * $_G['setting']['creditstax'])) > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][2])) {
//			showmessage('reward_credits_shortage');
//		}
		$price = $rewardprice;

	} 
//	elseif($special == 4) {
//
//		$activitytime = intval($_G['gp_activitytime']);
//		if(empty($_G['gp_starttimefrom'][$activitytime])) {
//			showmessage('activity_fromtime_please');
//		} elseif(@strtotime($_G['gp_starttimefrom'][$activitytime]) === -1 || @strtotime($_G['gp_starttimefrom'][$activitytime]) === FALSE) {
//			showmessage('activity_fromtime_error');
//		} elseif($activitytime && ((@strtotime($_G['gp_starttimefrom']) > @strtotime($_G['gp_starttimeto']) || !$_G['gp_starttimeto']))) {
//			showmessage('activity_fromtime_error');
//		} elseif(!trim($_G['gp_activityclass'])) {
//			showmessage('activity_sort_please');
//		} elseif(!trim($_G['gp_activityplace'])) {
//			showmessage('activity_address_please');
//		} elseif(trim($_G['gp_activityexpiration']) && (@strtotime($_G['gp_activityexpiration']) === -1 || @strtotime($_G['gp_activityexpiration']) === FALSE)) {
//			showmessage('activity_totime_error');
//		}
//
//		$activity = array();
//		$activity['class'] = censor(dhtmlspecialchars(trim($_G['gp_activityclass'])));
//		$activity['starttimefrom'] = @strtotime($_G['gp_starttimefrom'][$activitytime]);
//		$activity['starttimeto'] = $activitytime ? @strtotime($_G['gp_starttimeto']) : 0;
//		$activity['place'] = censor(dhtmlspecialchars(trim($_G['gp_activityplace'])));
//		$activity['cost'] = intval($_G['gp_cost']);
//		$activity['gender'] = intval($_G['gp_gender']);
//		$activity['number'] = intval($_G['gp_activitynumber']);
//
//		if($_G['gp_activityexpiration']) {
//			$activity['expiration'] = @strtotime($_G['gp_activityexpiration']);
//		} else {
//			$activity['expiration'] = 0;
//		}
//		if(trim($_G['gp_activitycity'])) {
//			$subject .= '['.dhtmlspecialchars(trim($_G['gp_activitycity'])).']';
//		}
//
//	} elseif($special == 5) {

//		if(empty($_G['gp_affirmpoint']) || empty($_G['gp_negapoint'])) {
//			showmessage('debate_position_nofound');
//		} elseif(!empty($_G['gp_endtime']) && (!($endtime = @strtotime($_G['gp_endtime'])) || $endtime < TIMESTAMP)) {
//			showmessage('debate_endtime_invalid');
//		} elseif(!empty($_G['gp_umpire'])) {
//			if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE username='$_G[gp_umpire]'")) {
//				$_G['gp_umpire'] = dhtmlspecialchars($_G['gp_umpire']);
//				showmessage('debate_umpire_invalid', '', array('umpire' => $umpire));
//			}
//		}
//		$affirmpoint = censor(dhtmlspecialchars($_G['gp_affirmpoint']));
//		$negapoint = censor(dhtmlspecialchars($_G['gp_negapoint']));
//		$stand = censor(intval($_G['gp_stand']));
//
//	} 
	elseif($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit')) {
			$threadpluginclass->newthread_submit($_G['fid']);
		}
		$special = 127;

	}

	$sortid = $special && $_G['forum']['threadsorts']['types'][$sortid] ? 0 : $sortid;
	$typeexpiration = intval($_G['gp_typeexpiration']);

	if($_G['forum']['threadsorts']['expiration'][$typeid] && !$typeexpiration) {
		showmessage('threadtype_expiration_invalid');
	}

	$_G['forum_optiondata'] = array();
	if($_G['forum']['threadsorts']['types'][$sortid] && !$_G['forum']['allowspecialonly']) {
		$_G['forum_optiondata'] = threadsort_validator($_G['gp_typeoption'], $pid);
	}

	$author = !$isanonymous ? $_G['username'] : '';

	$moderated = $digest || $displayorder > 0 ? 1 : 0;

	$thread['status'] = 0;

	$_G['gp_ordertype'] && $thread['status'] = setstatus(4, 1, $thread['status']);

	$_G['gp_hiddenreplies'] && $thread['status'] = setstatus(2, 1, $thread['status']);

	if($_G['group']['allowpostrushreply'] && $_G['gp_rushreply']) {
		$thread['status'] = setstatus(3, 1, $thread['status']);
		$thread['status'] = setstatus(1, 1, $thread['status']);
	}
	$isgroup = $_G['forum']['status'] == 3 ? 1 : 0;
	$posttableid = getposttableid('p');

	DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, posttableid, readperm, price, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, status, isgroup)
		VALUES ('$_G[fid]', '$posttableid', '$readperm', '$price', '$typeid', '$sortid', '$author', '$_G[uid]', '$subject', '$_G[timestamp]', '$_G[timestamp]', '$author', '$displayorder', '$digest', '$special', '0', '$moderated', '$thread[status]', '$isgroup')");
	$tid = DB::insert_id();


	DB::update('common_member_field_home', array('recentnote'=>$subject), array('uid'=>$_G['uid']));

	if($special == 3 && $_G['group']['allowpostreward']) {
		updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][2] => -$realprice), 1, 'RTC', $tid);
	}

	if($moderated) {
		updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
		updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
	}


	if($_G['forum']['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata'])) {
		$filedname = $valuelist = $separator = '';
		foreach($_G['forum_optiondata'] as $optionid => $value) {
			if(($_G['forum_optionlist'][$optionid]['search'] || in_array($_G['forum_optionlist'][$optionid]['type'], array('radio', 'select', 'number'))) && $value) {
				$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
				$valuelist .= $separator."'$value'";
				$separator = ' ,';
			}

			if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
				$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
				$sortaid = intval($_G['gp_typeoption'][$identifier]['aid']);
			}

			DB::query("INSERT INTO ".DB::table('forum_typeoptionvar')." (sortid, tid, fid, optionid, value, expiration)
				VALUES ('$sortid', '$tid', '$_G[fid]', '$optionid', '$value', '".($typeexpiration ? TIMESTAMP + $typeexpiration : 0)."')");
		}

		if($filedname && $valuelist) {
			DB::query("INSERT INTO ".DB::table('forum_optionvalue')."$sortid ($filedname, tid, fid) VALUES ($valuelist, '$tid', '$_G[fid]')");
		}
	}

	$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
	$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
	$parseurloff = !empty($_G['gp_parseurloff']);
	$htmlon = bindec(($_G['setting']['tagstatus'] && !empty($tagoff) ? 1 : 0).($_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0));

	if($_G['setting']['tagstatus'] && $_G['gp_tags'] != '') {
		$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($_G['gp_tags']));
		if(strexists($tags, ',')) {
			$tagarray = array_unique(explode(',', $tags));
		} else {
			$tags = str_replace(array(chr(0xa1).chr(0xa1), chr(0xa1).chr(0x40), chr(0xe3).chr(0x80).chr(0x80)), ' ', $tags);
			$tagarray = array_unique(explode(' ', $tags));
		}
		$tagarraynew = array();
		foreach($tagarray as $k => $tagname) {
			if(preg_match('/^([\x7f-\xff_-]|\w){3,20}$/', $tagname)) {
				$tagarraynew[$k] = trim($tagname);
			}
		}
		$tagarray = $tagarraynew;
		unset($tagarraynew);
	}

	$pinvisible = $modnewthreads ? -2 : 0;
	$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
	$pid = insertpost(array(
		'fid' => $_G['fid'],
		'tid' => $tid,
		'first' => '1',
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'subject' => $subject,
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'useip' => $_G['clientip'],
		'invisible' => $pinvisible,
		'anonymous' => $isanonymous,
		'usesig' => $_G['gp_usesig'],
		'htmlon' => $htmlon,
		'bbcodeoff' => $bbcodeoff,
		'smileyoff' => $smileyoff,
		'parseurloff' => $parseurloff,
		'attachment' => '0',
		'tags' => implode(',', $tagarray),
	));

	if($pid && getstatus($thread['status'], 1)) {
		savepostposition($tid, $pid);
	}


	if($_G['forum']['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata']) && $sortaid) {
		DB::query("UPDATE ".DB::table('forum_attachment')." SET tid='$tid', pid='$pid' WHERE aid='$sortaid'");
	}

	($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_G['gp_attachnew'] || $_G['gp_attachdel'] || $sortid || !empty($_G['gp_activityaid'])) && updateattach($postattachcredits, $tid, $pid, $_G['gp_attachnew'], $_G['gp_attachdel']);

	$param = array('fid' => $_G['fid'], 'tid' => $tid, 'pid' => $pid);
	if($modnewthreads) {
		DB::query("UPDATE ".DB::table('forum_forum')." SET todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
		showmessage('post_newthread_mod_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
	} else {

		$feed = array(
			'icon' => '',
			'title_template' => '',
			'title_data' => array(),
			'body_template' => '',
			'body_data' => array(),
			'title_data'=>array(),
			'images'=>array()
		);

		if(!empty($_G['gp_addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
			if($special == 0) {
				$feed['icon'] = 'thread';
				$feed['title_template'] = 'feed_thread_title';
				$feed['body_template'] = 'feed_thread_message';
				$feed['body_data'] = array(
					'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$tid\">$subject</a>",
					'message' => messagecutstr($message, 150)
				);
				if(!empty($_G['forum_attachexist'])) {
					$firstaid = DB::result_first("SELECT aid FROM ".DB::table('forum_attachment')." WHERE pid='$pid' AND dateline>'0' AND isimage='1' ORDER BY dateline LIMIT 1");
					if($firstaid) {
						$feed['images'] = array(getforumimg($firstaid));
						$feed['image_links'] = array("$_G[siteurl]forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
					}
				}
			} elseif($special > 0) {
				if($special == 1) {
					$pvs = explode("\t", messagecutstr($polloptionpreview, 150));
					$s = '';
					$i = 1;
					foreach($pvs as $pv) {
						$s .= $i.'. '.$pv.'<br />';
					}
					$s .= '&nbsp;&nbsp;&nbsp;...';
					$feed['icon'] = 'poll';
					$feed['title_template'] = 'feed_thread_poll_title';
					$feed['body_template'] = 'feed_thread_poll_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'message' => $s
					);
				} elseif($special == 3) {
					$feed['icon'] = 'reward';
					$feed['title_template'] = 'feed_thread_reward_title';
					$feed['body_template'] = 'feed_thread_reward_message';
					$feed['body_data'] = array(
						'subject'=> "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'rewardprice'=> $rewardprice,
						'extcredits' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['title'],
					);
				} elseif($special == 4) {
					$feed['icon'] = 'activity';
					$feed['title_template'] = 'feed_thread_activity_title';
					$feed['body_template'] = 'feed_thread_activity_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'starttimefrom' => $_G['gp_starttimefrom'][$activitytime],
						'activityplace'=> $activity['place'],
						'message' => messagecutstr($message, 150),
					);
					if($_G['gp_activityaid']) {
						$feed['images'] = array(getforumimg($_G['gp_activityaid']));
						$feed['image_links'] = array("$_G[siteurl]forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
					}
				} elseif($special == 5) {
					$feed['icon'] = 'debate';
					$feed['title_template'] = 'feed_thread_debate_title';
					$feed['body_template'] = 'feed_thread_debate_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'message' => messagecutstr($message, 150),
						'affirmpoint'=> messagecutstr($affirmpoint, 150),
						'negapoint'=> messagecutstr($negapoint, 150)
					);
				}
			}
			$feed['fid']=$_G['fid'];
			$feed['title_data']['hash_data'] = "tid{$tid}";
			$feed['id'] = $tid;
			$feed['idtype'] = 'tid';
			if($feed['icon']) {
				postfeed($feed);
			}
		}

		if($specialextra) {

			$classname = 'threadplugin_'.$specialextra;
			if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit_end')) {
				$threadpluginclass->newthread_submit_end($_G['fid']);
			}

		}
		if($digest) {
			updatepostcredits('+',  $_G['uid'], 'digest', $_G['fid']);
		}
		updatepostcredits('+',  $_G['uid'], 'post', $_G['fid']);
		if($isgroup) {
			DB::query("UPDATE ".DB::table('forum_groupuser')." SET threads=threads+1, lastupdate='".TIMESTAMP."' WHERE uid='$_G[uid]' AND fid='$_G[fid]'");
		}

		$subject = str_replace("\t", ' ', $subject);
		$lastpost = "$tid\t$subject\t$_G[timestamp]\t$author";
		DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
		if($_G['forum']['type'] == 'sub') {
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='".$_G['forum'][fup]."'", 'UNBUFFERED');
		}

		if($_G['forum']['status'] == 3) {
			updateactivity($_G['fid'], 0);
			updategroupcreditlog($_G['fid'], $_G['uid']);
		}
		$statarr = array(0 => 'thread', 1 => 'poll', 2 => 'trade', 3 => 'reward', 4 => 'activity', 5 => 'debate', 127 => 'thread');
		include_once libfile('function/stat');
		updatestat($isgroup ? 'groupthread' : $statarr[$special]);

		showmessage('post_newthread_succeed', "forum.php?mod=viewthread&tid=$tid&extra=$extra", $param);

	}
}

?>