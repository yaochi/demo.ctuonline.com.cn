<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_post.php 11542 2010-06-08 00:31:43Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getattach($pid, $posttime = 0) {
	global $_G;

	require_once libfile('function/attachment');
	$attachs = $imgattachs = array();
	$sqladd1 = $posttime > 0 ? "AND a.dateline>'$posttime'" : '';
	$sqladd2 = $pid > 0 ? "OR a.pid='$pid'" : '';
	$query = DB::query("SELECT a.*, af.description
		FROM ".DB::table('forum_attachment')." a
		LEFT JOIN ".DB::table('forum_attachmentfield')." af ON a.aid=af.aid
		WHERE (a.uid='$_G[uid]' AND a.tid='0' $sqladd1) $sqladd2 ORDER BY dateline");
	if(!empty($_G['fid']) && $_G['forum']['attachextensions']) {
		$allowext = str_replace(' ', '', $_G['forum']['attachextensions']);
		$allowext = explode(',', $allowext);
	} else {
		$allowext = '';
	}
	while($attach = DB::fetch($query)) {
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);
		if($allowext && !in_array($attach['ext'], $allowext)) {
			continue;
		}
		$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
		$attach['attachsize'] = sizecount($attach['filesize']);
		$attach['dateline'] = dgmdate($attach['dateline']);
		$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
		if($attach['isimage'] < 1) {
			if($attach['isimage']) {
				$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			}
			if($attach['pid']) {
				$attachs['used'][] = $attach;
			} else {
				$attachs['unused'][] = $attach;
			}
		} else {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'/forum';
			$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			if($attach['pid']) {
				$imgattachs['used'][] = $attach;
			} else {
				$imgattachs['unused'][] = $attach;
			}
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}

function parseattachmedia($attach) {
	$attachurl = 'attach://'.$attach['aid'].'.'.$attach['ext'];
	switch(strtolower($attach['ext'])) {
		case 'mp3':
		case 'wma':
		case 'ra':
		case 'ram':
		case 'wav':
		case 'mid':
			return '[audio]'.$attachurl.'[/audio]';
		case 'wmv':
		case 'rm':
		case 'rmvb':
		case 'avi':
		case 'asf':
		case 'mpg':
		case 'mpeg':
		case 'mov':
		case 'flv':
		case 'swf':
			return '[media='.$attach['ext'].',400,300]'.$attachurl.'[/media]';
		default:
			return;
	}
}

function ftpupload($aids, $uid = 0) {
	global $_G;
	$uid = $uid ? $uid : $_G['uid'];

	if(!$aids || !$_G['setting']['ftp']['on']) {
		return;
	}
	$query = DB::query("SELECT aid, thumb, attachment, filename, filesize FROM ".DB::table('forum_attachment')." WHERE aid IN (".dimplode($aids).") AND uid='$_G[uid]' AND remote='0'");
	$aids = array();
	while($attach = DB::fetch($query)) {
		$attach['ext'] = fileext($attach['filename']);
		if(((!$_G['setting']['ftp']['allowedexts'] && !$_G['setting']['ftp']['disallowedexts']) || ($_G['setting']['ftp']['allowedexts'] && in_array($attach['ext'], $_G['setting']['ftp']['allowedexts'])) || ($_G['setting']['ftp']['disallowedexts'] && !in_array($attach['ext'], $_G['setting']['ftp']['disallowedexts']))) && (!$_G['setting']['ftp']['minsize'] || $attach['filesize'] >= $_G['setting']['ftp']['minsize'] * 1024)) {
			if(ftpcmd('upload', 'forum/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'forum/'.$attach['attachment'].'.thumb.jpg'))) {
				dunlink($attach);
				$aids[] = $attach['aid'];
			}
		}
	}

	if($aids) {
		DB::update('forum_attachment', array('remote' => 1), "aid IN (".dimplode($aids).")");
	}
}

function updateattach($postattachcredits, $tid, $pid, $attachnew, $attachdel, $attachupdate = array(), $uid = 0) {
	global $_G;
	$uid = $uid ? $uid : $_G['uid'];
	$uidadd = $_G['forum']['ismoderator'] ? '' : " AND uid='$uid'";
	$attachnum = $_G['group']['allowpostattach'];
	if($attachnew) {
		$newaids = array_keys($attachnew);
		$newattach = $newattachfile = array();
		$query = DB::query("SELECT aid, tid, attachment FROM ".DB::table('forum_attachment')." WHERE aid IN (".dimplode($newaids).")$uidadd");
		while($attach = DB::fetch($query)) {
			if($_G['group']['maxattachnum']) {
				if($attachnum <= 0) {
					unset($attachnew[$attach['aid']]);
					continue;
				} else {
					$attachnum--;
				}
			}
			if(!$attach['tid']) {
				$newattach[] = $attach['aid'];
				$newattachfile[$attach['aid']] = $attach['attachment'];
			}
		}
		if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
			require_once libfile('class/image');
			$image = new image;
		}
		foreach($attachnew as $aid => $attach) {
			$update = array(
				'readperm' => $_G['group']['allowsetattachperm'] ? $attach['readperm'] : 0,
				'price' => $_G['group']['maxprice'] ? (intval($attach['price']) <= $_G['group']['maxprice'] ? intval($attach['price']) : $_G['group']['maxprice']) : 0,
				'tid' => $tid,
				'pid' => $pid,
				'uid' => $uid
			);
			if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
				$image->Watermark($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid]);
			}
			DB::update('forum_attachment', $update, "aid='$aid'$uidadd");
			DB::query("REPLACE INTO ".DB::table('forum_attachmentfield')." (aid, tid, pid, uid, description) VALUES ('$aid', '$tid', '$pid', '$uid', '".cutstr(dhtmlspecialchars($attach['description']), 100)."')");
		}
		if($newattach) {
			if($uid == $_G['uid']) {
				updatecreditbyaction('postattach', $uid, array(), '', count($newattach));
			}
			ftpupload($newaids, $uid);
		}
	}

	$query = DB::query("SELECT aid, attachment, thumb FROM ".DB::table('forum_attachment')." WHERE uid='$uid'");
	$delaids = array();
	while($attach = DB::fetch($query)) {
		$aids[] = $attach['aid'];
		if($attachdel && in_array($attach['aid'], $attachdel)) {
			$delaids[] = $attach['aid'];
			dunlink($attach);
		}
		if($attachupdate && array_key_exists($attach['aid'], $attachupdate) && $attachupdate[$attach['aid']]) {
			dunlink($attach);
		}
	}

	if($attachupdate) {
		$uaids = dimplode($attachupdate);
		$query = DB::query("SELECT aid, width, filename, filetype, filesize, attachment, isimage, thumb, remote FROM ".DB::table('forum_attachment')." WHERE aid IN ($uaids)$uidadd");
		DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid IN ($uaids)$uidadd");
		$attachupdate = array_flip($attachupdate);
		while($attach = DB::fetch($query)) {
			$update = $attach;
			$update['dateline'] = TIMESTAMP;
			unset($update['aid']);
			DB::update('forum_attachment', $update, "aid='".$attachupdate[$attach['aid']]."'$uidadd");
		}
	}

	if($delaids) {
		DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid IN (".dimplode($delaids).")", 'UNBUFFERED');
		DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE aid IN (".dimplode($delaids).")", 'UNBUFFERED');
	}

	$attachcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_attachment')." WHERE tid='$tid'".($pid > 0 ? " AND pid='$pid'" : ''));
	$attachment = $attachcount ? (DB::result_first("SELECT count(*) FROM ".DB::table('forum_attachment')." WHERE tid='$tid'".($pid > 0 ? " AND pid='$pid'" : '')." AND isimage != 0") ? 2 : 1) : 0;

	DB::query("UPDATE ".DB::table('forum_thread')." SET attachment='$attachment' WHERE tid='$tid'", 'UNBUFFERED');
	$posttable = getposttablebytid($tid);
	DB::query("UPDATE ".DB::table($posttable)." SET attachment='$attachment' WHERE pid='$pid'", 'UNBUFFERED');
	$_G['forum_attachexist'] = $attachment;
}

function checkflood() {
	global $_G;
	if(!$_G['group']['disablepostctrl'] && $_G['uid']) {
		$floodmsg = $_G['setting']['floodctrl'] && (TIMESTAMP - $_G['setting']['floodctrl'] <= getuserprofile('lastpost')) ? 'post_flood_ctrl' : '';

		if(empty($floodmsg) && $_G['group']['maxpostsperhour']) {
			$userposts = getcountofposts(DB::table('forum_post'), "authorid='$_G[uid]' AND dateline>$_G[timestamp]-3600");
			$floodmsg = $userposts && ($userposts >= $_G['group']['maxpostsperhour']) ? 'thread_maxpostsperhour_invalid' : '';
		}

		if(empty($floodmsg)) {
			return FALSE;
		} elseif(CURMODULE != 'wap') {
			showmessage($floodmsg, '', array('maxpostsperhour' => $_G['group']['maxpostsperhour'], 'floodctrl' => $_G['setting']['floodctrl']));
		} else {
			wapmsg($floodmsg);
		}
	}
	return FALSE;
}

function checkpost($subject, $message, $special = 0) {
	global $_G;
	if(strlen($subject) > 255) {
		return 'post_subject_toolong';
	}
	if(!$_G['group']['disablepostctrl'] && !$special) {
		if($_G['setting']['maxpostsize'] && strlen($message) > $_G['setting']['maxpostsize']) {
			return 'post_message_toolong';
		} elseif($_G['setting']['minpostsize'] && strlen(preg_replace("/\[quote\].+?\[\/quote\]/is", '', $message)) < $_G['setting']['minpostsize']) {
			return 'post_message_tooshort';
		}
	}
	return FALSE;
}

function checkbbcodes($message, $bbcodeoff) {
	return !$bbcodeoff && (!strpos($message, '[/') && !strpos($message, '[hr]')) ? -1 : $bbcodeoff;
}

function checksmilies($message, $smileyoff) {
	global $_G;

	if($smileyoff) {
		return 1;
	} else {
		if(!empty($_G['cache']['smileycodes']) && is_array($_G['cache']['smileycodes'])) {
			$message = dstripslashes($message);
			foreach($_G['cache']['smileycodes'] as $id => $code) {
				if(strpos($message, $code) !== FALSE) {
					return 0;
				}
			}
		}
		return -1;
	}
}

function updatepostcredits($operator, $uidarray, $action, $fid = 0) {
	global $_G;
	$val = $operator == '+' ? 1 : -1;
	$extsql = array();
	if($action == 'reply') {
		$extsql = array('posts' => $val);
	} elseif($action == 'post') {
		$extsql = array('threads' => $val, 'posts' => $val);
	}
	$uidarray = (array)$uidarray;

	foreach($uidarray as $uid) {
		if($uid == $_G['uid']) {
			updatecreditbyaction($action, $uid, $extsql, '', $val, 1, $fid);
		} else {
			batchupdatecredit($action, $uid, $extsql, $val, $fid);
		}
	}
	if($operator == '+' && ($action == 'reply' || $action == 'post')) {
		$uids = implode(',', $uidarray);
		DB::query("UPDATE ".DB::table('common_member_status')." SET lastpost='".TIMESTAMP."' WHERE uid IN ('$uids')", 'UNBUFFERED');
	}
}

function updateattachcredits($operator, $uidarray, $creditsarray) {
	global $_G;
	$creditsadd1 = '';
	if(is_array($creditsarray)) {
		foreach($creditsarray as $id => $addcredits) {
			$creditsadd1[] = "extcredits$id=extcredits$id$operator$addcredits*\$attachs";
		}
	}
	if(is_array($creditsadd1)) {
		$creditsadd1 = implode(', ', $creditsadd1);
		foreach($uidarray as $uid => $attachs) {
			eval("\$creditsadd2 = \"$creditsadd1\";");
			DB::query("UPDATE ".DB::table('common_member_count')." SET $creditsadd2 WHERE uid = $uid", 'UNBUFFERED');
		}
	}
}

function updateforumcount($fid) {

	extract(DB::fetch_first("SELECT COUNT(*) AS threadcount, SUM(t.replies)+COUNT(*) AS replycount
		FROM ".DB::table('forum_thread')." t, ".DB::table('forum_forum')." f
		WHERE f.fid='$fid' AND t.fid=f.fid AND t.displayorder>='0'"));

	$thread = DB::fetch_first("SELECT tid, subject, author, lastpost, lastposter FROM ".DB::table('forum_thread')."
		WHERE fid='$fid' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1");

	$thread['subject'] = addslashes($thread['subject']);
	$thread['lastposter'] = $thread['author'] ? addslashes($thread['lastposter']) : lang('forum/misc', 'anonymous');

	DB::query("UPDATE ".DB::table('forum_forum')." SET posts='$replycount', threads='$threadcount', lastpost='$thread[tid]\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]' WHERE fid='$fid'", 'UNBUFFERED');
}

function updatethreadcount($tid, $updateattach = 0) {
	$posttable = getposttablebytid($tid);
	$replycount = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$tid' AND invisible='0'") - 1;
	$lastpost = DB::fetch_first("SELECT author, anonymous, dateline FROM ".DB::table($posttable)." WHERE tid='$tid' AND invisible='0' ORDER BY dateline DESC LIMIT 1");

	$lastpost['author'] = $lastpost['anonymous'] ? lang('forum/misc', 'anonymous') : addslashes($lastpost['author']);
	$lastpost['dateline'] = !empty($lastpost['dateline']) ? $lastpost['dateline'] : TIMESTAMP;

	if($updateattach) {
		$attach = DB::result_first("SELECT attachment FROM ".DB::table($posttable)." WHERE tid='$tid' AND invisible='0' AND attachment>0 LIMIT 1");
		$attachadd = ', attachment=\''.($attach ? 1 : 0).'\'';
	} else {
		$attachadd = '';
	}
	DB::query("UPDATE ".DB::table('forum_thread')." SET replies='$replycount', lastposter='$lastpost[author]', lastpost='$lastpost[dateline]' $attachadd WHERE tid='$tid'", 'UNBUFFERED');
}

function updatemodlog($tids, $action, $expiration = 0, $iscron = 0, $stamp = 0) {
	global $_G;

	$uid = empty($iscron) ? $_G['uid'] : 0;
	$username = empty($iscron) ? $_G['member']['username'] : 0;
	$expiration = empty($expiration) ? 0 : intval($expiration);

	$data = $comma = '';
	$stampadd = $stampaddvalue = '';
	if($stamp) {
		$stampadd = ', stamp';
		$stampaddvalue = ", '$stamp'";
	}
	foreach(explode(',', str_replace(array('\'', ' '), array('', ''), $tids)) as $tid) {
		if($tid) {
			$data .= "{$comma} ('$tid', '$uid', '$username', '$_G[timestamp]', '$action', '$expiration', '1'$stampaddvalue)";
			$comma = ',';
		}
	}

	!empty($data) && DB::query("INSERT INTO ".DB::table('forum_threadmod')." (tid, uid, username, dateline, action, expiration, status$stampadd) VALUES $data", 'UNBUFFERED');

}

function isopera() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(strpos($useragent, 'opera') !== false) {
		preg_match('/opera(\/| )([0-9\.]+)/', $useragent, $regs);
		return $regs[2];
	}
	return FALSE;
}

function deletethreadcaches($tids) {
	global $_G;
	if(!$_G['setting']['cachethreadon']) {
		return FALSE;
	}
	require_once libfile('function/forumlist');
	if(!empty($tids)) {
		foreach(explode(',', $tids) as $tid) {
			$fileinfo = getcacheinfo($tid);
			@unlink($fileinfo['filename']);
		}
	}
	return TRUE;
}


function disuploadedfile($file) {
	return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
}

//在$feed 中添加  fid字段值（专区id）
function postfeed($feed) {
	global $_G;
	if($feed) {
		require_once libfile('function/feed');
		//feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', $feed['images'], $feed['image_links'], '', '', '', 0, $feed['id'], $feed['idtype']);
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', $feed['images'], $feed['image_links'], '', '', '', 0, $feed['id'], $feed['idtype'], 0, '',$feed['fid'],array(),'',0,0,$feed['anonymity']);
	}
}

function messagecutstr($str, $length) {
	global $_G;
	$language = lang('forum/misc');
	loadcache(array('bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes', 'icons', 'domainwhitelist'));
	$bbcodes = 'b|i|u|p|color|size|font|align|list|indent|float';
	$bbcodesclear = 'url|email|code|free|table|tr|td|img|swf|flash|attach|media|audio|payto'.($_G['cache']['bbcodes_display'] ? '|'.implode('|', array_keys($_G['cache']['bbcodes_display'])) : '');
	$str = cutstr(strip_tags(preg_replace(array(
			"/\[hide=?\d*\](.+?)\[\/hide\]/is",
			"/\[quote](.*?)\[\/quote]/si",
			$language['post_edit_regexp'],
			"/\[($bbcodesclear)=?.*?\].+?\[\/\\1\]/si",
			"/\[($bbcodes)=?.*?\]/i",
			"/\[\/($bbcodes)\]/i",
		), array(
			"[b]$language[post_hidden][/b]",
			'',
			'',
			'',
			'',
			''
		), $str)), $length);
	$str = preg_replace($_G['cache']['smilies']['searcharray'], '', $str);

	if($length<strlen($str)){
		$str=$str."<!--more-->";
	}
	return trim($str);
}

function get_url_list($message) {
	$return = array();

	(strpos($message, '[/img]') || strpos($message, '[/flash]')) && $message = preg_replace("/\[img[^\]]*\].+?\[\/img\]|\[flash[^\]]*\].+?\[\/flash\]/is", '', $message);
	if(preg_match_all("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+\s*)/i", $message, $urllist)) {
		foreach($urllist[0] as $key => $val) {
			$val = trim($val);
			$return[0][$key] = $val;
			if(!preg_match('/^http:\/\//is', $val)) $val = 'http://'.$val;
			$tmp = parse_url($val);
			$return[1][$key] = $tmp['host'];
			if($tmp['port']){
				$return[1][$key] .= ":$tmp[port]";
			}
		}
	}

	return $return;
}

function iswhitelist($host) {
	global $_G;
	static $iswhitelist = array();

	if(isset($iswhitelist[$host])) {
		return $iswhitelist[$host];
	}
	$hostlen = strlen($host);
	$iswhitelist[$host] = false;
	if(is_array($_G['cache']['domainwhitelist'])) foreach($_G['cache']['domainwhitelist'] as $val) {
		$domainlen = strlen($val);
		if($domainlen > $hostlen) {
			continue;
		}
		if(substr($host, -$domainlen) == $val) {
			$iswhitelist[$host] = true;
			break;
		}
	}
	if($iswhitelist[$host] == false) {
		$iswhitelist[$host] = $host == $_SERVER['HTTP_HOST'];
	}
	return $iswhitelist[$host];
}


function savepostposition($tid, $pid, $position = 0, $stick = 0, $expiration = 0) {
	global $_G;
	if(!$position) {
		$pos = DB::result_first("SELECT max(position) FROM ".DB::table('forum_postposition')." WHERE tid='$tid'");
		$pos ++;
	} else {
		$pos = $position;
	}
	$expiration = intval($expiration);
	$stickadd = $stick ? ", stick='1', dateline='$_G[timestamp]', expiration='$expiration'" : '';

	$res = DB::query("REPLACE INTO ".DB::table('forum_postposition')." SET fid='$_G[fid]', tid='$tid', position='$pos', pid='$pid' $stickadd");
	return $res;
}

function deletepostposition($tid, $pid, $stick = 0) {
	$stickadd = $stick ? 'AND stick=\'1\'' : '';
	$pidadd = $pid ? "AND pid='$pid'" : '';
	DB::query("DELETE FROM ".DB::table('forum_postposition')." WHERE tid='$tid' $pidadd $stickadd");
}

function post_forum_inc_threads($fid){
    DB::query("UPDATE ".DB::table("forum_forumfield")." SET threads=threads+1 WHERE fid=".$fid);
    require_once libfile("function/group");
    group_update_forum_hot($fid);
}

function post_forum_dec_threads($fid){
    DB::query("UPDATE ".DB::table("forum_forumfield")." SET threads=threads-1 WHERE fid=".$fid);
    require_once libfile("function/group");
    group_update_forum_hot($fid);
}

function post_forum_inc_replys($fid){
    DB::query("UPDATE ".DB::table("forum_forumfield")." SET replys=replys+1 WHERE fid=".$fid);
    require_once libfile("function/group");
    group_update_forum_hot($fid);
}

function post_forum_dec_replys($fid){
    DB::query("UPDATE ".DB::table("forum_forumfield")." SET replys=replys-1 WHERE fid=".$fid);
    require_once libfile("function/group");
    group_update_forum_hot($fid);
}

?>