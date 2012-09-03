<?php

/* Function: 专区直播
 * Com.:
 * Author: wuhan
 * Date: 2010-7-27
 */
function getUserIdByName($anames = array ()) {
	global $_G, $space;
	$uids = array ();

	$names = empty ($anames) ? array () : explode(',', preg_replace("/(\s+)/s", ',', $anames));

	if ($names) {
		$query = DB :: query("SELECT uid FROM " . DB :: table('common_member') . " WHERE username IN (" . dimplode($names) . ")");
		while ($value = DB :: fetch($query)) {
			$uids[] = $value['uid'];
		}
	}
	if (empty ($uids)) {
		return '';
	} else {
		return implode(',', $uids);
	}
}
function openFileAPI_Newlive($url) {
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);

	return curl_exec($ch);
}
function simpPost4Data($url,$post_data) {
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	return curl_exec($ch);
}
function getUserIds($ids = array ()) {
	global $_G, $space;
	$uids = array ();

	$uids = empty ($ids) ? array () : array_unique(explode(',', preg_replace("/(\s+)/s", ',', $ids)));

	if (empty ($uids)) {
		return '';
	} else {
		return implode(',', $uids);
	}
}

function getUserNameById($auids = '') {
	if (empty ($auids)) {
		return '';
	}

	$uids = explode(',', $auids);

	$names = array ();
	$query = DB :: query("SELECT username FROM " . DB :: table('common_member') . " WHERE uid IN (" . implode(',', $uids) . ")");
	while ($value = DB :: fetch($query)) {
		$names[] = $value['username'];
	}
	return implode(' ', $names);
}

function getUserRealNameById($auids = '') {
	if (empty ($auids)) {
		return '';
	}
	$uids = explode(',', $auids);

	$names = array ();
	$query = DB :: query("SELECT realname FROM " . DB :: table('common_member_profile') . " WHERE uid IN (" . implode(',', $uids) . ")");
	while ($value = DB :: fetch($query)) {
		$names[] = $value['realname'];
	}
	return implode(' ', $names);
}

function live_post($POST, $olds = array ()) {
	global $_G, $space;
	include_once libfile('function/home');

	$isself = 1;
	if (!empty ($olds['uid']) && $olds['uid'] != $_G['uid']) {
		$isself = 0;
		$__G = $_G;
		$_G['uid'] = $olds['uid'];
		$_G['username'] = addslashes($olds['username']);
	}

	/**
	 * 定义需要传给直播平台的字段
	 */
	$fid;
	$creatorid;
	$creatorname;
	$type2;
	$title;
	$starttime;
	$endtime;
	$presideruid;
	$presidername;
	$lectureruid;
	$lecturername;

	require_once libfile("function/category");
	$allowrequired = common_category_is_required($_G['fid'], 'grouplive');
	if (empty ($POST['typeid']) && $allowrequired) {
		showmessage('select_a_type');
	}

	$POST['subject'] = getstr(trim($POST['subject']), 80, 1, 1, 1);
	if (strlen($POST['subject']) < 1)
		$POST['subject'] = dgmdate($_G['timestamp'], 'Y-m-d');
	$POST['friend'] = intval($POST['friend']);
	$POST['target_ids'] = '';
	$POST['friend'] == 0;
	if ($POST['friend'] == 2) {
		$uids = array ();
		$names = empty ($_POST['target_names']) ? array () : explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
		if ($names) {
			$query = DB :: query("SELECT uid FROM " . DB :: table('common_member') . " WHERE username IN (" . dimplode($names) . ")");
			while ($value = DB :: fetch($query)) {
				$uids[] = $value['uid'];
			}
		}
		if (empty ($uids)) {
			$POST['friend'] = 3;
		} else {
			$POST['target_ids'] = implode(',', $uids);
		}
	}
	elseif ($POST['friend'] == 4) {
		$POST['password'] = trim($POST['password']);
		if ($POST['password'] == '')
			$POST['friend'] = 0;
	}
	if ($POST['friend'] !== 2) {
		$POST['target_ids'] = '';
	}
	if ($POST['friend'] !== 4) {
		$POST['password'] == '';
	}

	$livearr = array (
		'subject' => $POST['subject'],
		'type' => intval($POST['type']),
		'starttime' => @ strtotime($POST['starttime']),
		'endtime' => @ strtotime($POST['endtime']),
		'firstman_ids' => getUserIds($POST['firstman_names_uids']),
		'secondman_ids' => getUserIds($POST['secondman_names_uids']),
		'guest_ids' => getUserIds($POST['guest_names_uids']),
		'url' => $POST['url'], 'lastpost' => $_G['timestamp'],
		'fid' => $_G['fid'], 'typeid' => intval($POST['typeid']));

	if (checkperm_group('managelive')) {
		$livearr['hot'] = intval($POST['hot']);
	}

		   //新平台直播操作
	if ($livearr['type'] == 3) {
		if ($olds['liveid']) {   //此处有值表示编辑
			$liveid = $olds['liveid'];
			$title = $livearr['subject'];
			$starttime = $livearr['starttime'];
			$endtime = $livearr['endtime'];
			$lectureruid = $POST['secondman_names_uids'];
			$lecturername = $POST['secondman_names'];
			if($olds['newliveid']){    //如果有新平台的直播id,表示可以正常修改
				$newliveid = $olds['newliveid'];
				$presideruid = $POST['presidernames_uids'];
				$presidername = $POST['presidernames'];
				$md5code = md5($starttime . $endtime . $presideruid. $lectureruid . "esnliveupdate".$newliveid);
				$flag = false;
//				$FILE_SEARCH_PAGE = $_G[config][expert][liveurl]."/info/baseinfo.do?action=update&title=" . $title . "&starttime=" . $starttime . "&endtime=" . $endtime . "&presideruid=" . $presideruid .
//				"&presidername=" . $presidername . "&lectureruid=" . $lectureruid . "&lecturername=" . $lecturername . "&id=".$newliveid."&md5code=" . $md5code;
//				$str = openFileAPI_Newlive($FILE_SEARCH_PAGE);

				$requrl=$_G[config][expert][liveurl]."/info/baseinfo.do";
				$reqdata="action=update&title=" . $title . "&starttime=" . $starttime . "&endtime=" . $endtime . "&presideruid=" . $presideruid .
				"&presidername=" . $presidername . "&lectureruid=" . $lectureruid . "&lecturername=" . $lecturername . "&id=".$newliveid."&md5code=" . $md5code;
				$str = simpPost4Data($requrl,$reqdata);

				$obj = json_decode($str, true);
				if ($obj && $obj[status] == 1) {
					$flag = true;
				}
				if($flag){
					DB :: update('group_live', $livearr, array (
					'liveid' => $liveid
					));
				}else{
					showmessage("对不起,由于网络原因导致新直播平台修改失败,请返回重试!!");
				}
			}
			$fuids = array ();

			$livearr['uid'] = $olds['uid'];
			$livearr['username'] = $olds['username'];
		} else {
			$livearr['uid'] = $_G['uid'];
			$livearr['username'] = $_G['username'];
			$livearr['dateline'] = empty ($POST['dateline']) ? $_G['timestamp'] : $POST['dateline'];


			$fid = $livearr['fid'];
			$title = $livearr['subject'];
			$creatorid = $livearr['uid'];
			$creatorname = user_get_user_name($_G['uid']);
			$type2 = $POST['newlivetype'];
			$starttime = $livearr['starttime'];
			$endtime = $livearr['endtime'];
			$lectureruid = $POST['secondman_names_uids'];
			$lecturername = $POST['secondman_names'];
			if ($type2 == 1) {
				$presideruid = $POST['presidernames_uids'];
				$presidername = $POST['presidernames'];
				$livearr['presideruid'] = $presideruid;
				$livearr['presidername'] = $presidername;
				$md5code = md5($fid . $creatorid . $type2 . $starttime . $endtime . $presideruid . $lectureruid . "esnliveadd");
			} else {
				$md5code = md5($fid . $creatorid . $type2 . $starttime . $endtime . $lectureruid . "esnliveadd");
			}
			$newliveid = 0;

//			$FILE_SEARCH_PAGE = $_G[config][expert][liveurl]."/info/baseinfo.do?action=add&fid=" . $fid . "&creatorid=" . $creatorid .
//			"&creatorname=" . $creatorname . "&type=" . $type2 . "&title=" . $title . "&starttime=" . $starttime . "&endtime=" . $endtime . "&presideruid=" . $presideruid .
//			"&presidername=" . $presidername . "&lectureruid=" . $lectureruid . "&lecturername=" . $lecturername . "&md5code=" . $md5code;
//			$str = openFileAPI_Newlive($FILE_SEARCH_PAGE);

			$requrl=$_G[config][expert][liveurl]."/info/baseinfo.do";
			$reqdata="action=add&fid=" . $fid . "&creatorid=" . $creatorid .
			"&creatorname=" . $creatorname . "&type=" . $type2 . "&title=" . $title . "&starttime=" . $starttime . "&endtime=" . $endtime . "&presideruid=" . $presideruid .
			"&presidername=" . $presidername . "&lectureruid=" . $lectureruid . "&lecturername=" . $lecturername . "&md5code=" . $md5code;
			$str = simpPost4Data($requrl,$reqdata);

			$obj = json_decode($str, true);
			if ($obj && $obj[status] == 1) {
				$newliveid = $obj[message];
			}
			if ($newliveid > 0) {
				$livearr['newliveid'] = $newliveid;
				$livearr['newlivetype'] = $type2;
				$liveid = DB :: insert('group_live', $livearr, 1);
				hook_create_resource($liveid, "live", $_G['fid']);
			}else{
				showmessage("对不起,由于网络原因导致新直播平台创建失败,请返回重试!!");
			}
		}

	} else {
		if ($olds['liveid']) {
			$liveid = $olds['liveid'];
			DB :: update('group_live', $livearr, array (
				'liveid' => $liveid
			));

			$fuids = array ();

			$livearr['uid'] = $olds['uid'];
			$livearr['username'] = $olds['username'];
		} else {
			$livearr['uid'] = $_G['uid'];
			$livearr['username'] = $_G['username'];
			$livearr['dateline'] = empty ($POST['dateline']) ? $_G['timestamp'] : $POST['dateline'];
			$liveid = DB :: insert('group_live', $livearr, 1);
			hook_create_resource($liveid, "live", $_G['fid']);
		}
	}
	$livearr['liveid'] = $liveid;

	$fieldarr = array ();

	if ($POST['makefeed'] && empty ($olds['liveid'])) {
		feed_publish_live($livearr['liveid']);
	}

	if ($POST['makenotify'] && empty ($olds['liveid'])) {
		$useduid = array (
			$livearr['uid']
		);
		$url = join_plugin_action2("livecp", array (
			'liveid' => $livearr['liveid'],
			'op' => 'join'
		));
		if ($livearr['firstman_ids']) {
			$firstman_ids = explode(',', $livearr['firstman_ids']);
			foreach ($firstman_ids as $uid) {
				if (!in_array($uid, $useduid)) {
					$useduid[] = $uid;
					notification_add($uid, 'glive', 'live_member_invite_firstman', array (
						'actor' => "<a href=\"home.php?mod=space&uid=$livearr[uid]\" target=\"_blank\">$livearr[username]</a>",
						'subject' => "<a href=\"$url\" target=\"_blank\">$livearr[subject]</a>",
						'starttime' => dgmdate($livearr['starttime'],
						'Y年m月d日 H:i'
					), 'endtime' => dgmdate($livearr['endtime'], 'Y年m月d日 H:i')), 1);
				}
			}
		}
		if ($livearr['secondman_ids']) {
			$secondman_ids = explode(',', $livearr['secondman_ids']);
			foreach ($secondman_ids as $uid) {
				if (!in_array($uid, $useduid)) {
					$useduid[] = $uid;
					notification_add($uid, 'glive', 'live_member_invite_secondman', array (
						'actor' => "<a href=\"home.php?mod=space&uid=$livearr[uid]\" target=\"_blank\">$livearr[username]</a>",
						'subject' => "<a href=\"$url\" target=\"_blank\">$livearr[subject]</a>",
						'starttime' => dgmdate($livearr['starttime'],
						'Y年m月d日 H:i'
					), 'endtime' => dgmdate($livearr['endtime'], 'Y年m月d日 H:i')), 1);
				}
			}
		}
		if ($livearr['guest_ids']) {
			$guest_ids = explode(',', $livearr['guest_ids']);
			foreach ($guest_ids as $uid) {
				if (!in_array($uid, $useduid)) {
					$useduid[] = $uid;
					notification_add($uid, 'glive', 'live_member_invite_guest', array (
						'actor' => "<a href=\"home.php?mod=space&uid=$livearr[uid]\" target=\"_blank\">$livearr[username]</a>",
						'subject' => "<a href=\"$url\" target=\"_blank\">$livearr[subject]</a>",
						'starttime' => dgmdate($livearr['starttime'],
						'Y年m月d日 H:i'
					), 'endtime' => dgmdate($livearr['endtime'], 'Y年m月d日 H:i')), 1);
				}
			}
		}
	}

	if (empty ($olds['liveid'])) {
		//创建积分
		require_once libfile('function/credit');
		credit_create_credit_log($livearr['uid'], 'createbroadcast', $livearr['liveid']);
	}

	if (!empty ($__G))
		$_G = $__G;

	return $livearr;
}

function deletelives($liveids) {
	global $_G;

	$lives = $newliveids = $counts = $aids = array ();
	//TODO 权限
	//	$allowmanage = checkperm('managelive');
	$allowmanage = checkperm_group('managelive');

	$query = DB :: query("SELECT * FROM " . DB :: table('group_live') . " WHERE liveid IN (" . dimplode($liveids) . ")");
	while ($value = DB :: fetch($query)) {
		if ($allowmanage || $value['uid'] == $_G['uid']) {
			$lives[] = $value;
			$newliveids[] = $value['liveid'];

			if ($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['liveid'] -= 1;

			if ($value['aid']) {
				$aids[] = $value['aid'];
			}
			if($value['type'] == 3){
				$newliveids2[] = $value['newliveid'];
			}
		}
	}
	if (empty ($lives))
		return array ();

	foreach ($newliveids2 as $newliveid) {
				//先删除新直播平台
				$md5code = md5($newliveid."esnlivedel");
			$FILE_SEARCH_PAGE = $_G[config][expert][liveurl]."/info/baseinfo.do?action=del&id=".$newliveid."&md5code=".$md5code;
			$str = openFileAPI_Newlive($FILE_SEARCH_PAGE);
			$obj = json_decode($str, true);
	}

	DB :: query("DELETE FROM " . DB :: table('group_live') . " WHERE liveid IN (" . dimplode($newliveids) . ")");
	foreach ($newliveids as $rid) {
		hook_delete_resource($rid, "live");
	}
	//动态修改
	DB :: query("DELETE FROM " . DB :: table('home_feed') . " WHERE id IN (" . dimplode($newliveids) . ") AND idtype='liveid'");

	//发送通知
	$forumname = $_G['forum']['name'];
	foreach ($lives as $live) {
		if ($live['uid'] != $_G['uid']) {
			notification_add($live['uid'], 'glive', 'live_delete', array (
				'actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>",
				'subject' => $live['subject'],
				'group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">$forumname</a>"
			), 1);
		}
	}

	if ($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('publishlive', $uid, array (
				'lives' => $setarr['lives']
			), $setarr['coef']);
		}
	}
	return $lives;
}

function getlivetypearr() {
	global $_G;

	$classarr = array ();
	$query = DB :: query("SELECT * FROM " . DB :: table('forum_threadclass') . " WHERE fid='$_G[fid]'");
	while ($value = DB :: fetch($query)) {
		$classarr[$value['typeid']] = $value;
	}
	return $classarr;
}

function copy_live($liveids) {
	global $_G;

	if (empty ($liveids)) {
		return false;
	}

	$query = DB :: query("select * from " . DB :: table('group_live') . " where liveid in (" . dimplode($liveids) . ")");
	while ($value = DB :: fetch($query)) {
		$livearr = array (
			'fid' => $_G['fid'],
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'type' => $value['type'],
			'subject' => $value['subject'],
			'starttime' => $value['starttime'],
			'endtime' => $value['endtime'],
			'url' => $value['url'],
			'firstman_ids' => $value['firstman_ids'],
			'secondman_ids' => $value['secondman_ids'],
			'guest_ids' => $value['guest_ids'],
			'dateline' => $_G['timestamp'],
			'lastpost' => $_G['timestamp'],

			'friend' => $value['friend'],
			'password' => $value['password'],
			'target_ids' => $value['target_ids'],

		);

		$livearr['liveid'] = DB :: insert("group_live", $livearr, 1);
		hook_create_resource($livearr['liveid'], "live", $_G['fid']);

	}

	return true;
}

function feed_publish_live($id) {
	global $_G;

	include_once libfile('function/feed');
	require_once (dirname(dirname(dirname(dirname(__FILE__)))) . "/joinplugin/common.inc.php");

	$query = DB :: query("SELECT * FROM " . DB :: table('group_live') . " WHERE liveid='$id'");
	if ($live = DB :: fetch($query)) {
		$feed['icon'] = 'live';
		$feed['id'] = $live['liveid'];
		$feed['idtype'] = 'gliveid';
		$feed['uid'] = $live['uid'];
		$feed['username'] = $live['username'];
		$feed['dateline'] = $live['dateline'];
		$feed['hot'] = $live['hot'];
		$url = join_plugin_action2("livecp", array (
			'liveid' => $live['liveid'],
			'op' => 'join'
		));
		$feed['title_template'] = 'feed_live_title';
		$feed['body_template'] = 'feed_live_body';
		$feed['body_data'] = array (
			'subject' => "<a href=\"$url\">$live[subject]</a>",

		);
		$feed = mkfeed($feed);

		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array (
			$feed['image_1']
		), array (
			$feed['image_1_link']
		), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username'], $_G['fid']);
	}
}
//直播
function feed_play_live($id) {
	global $_G;

	include_once libfile('function/feed');
	require_once (dirname(dirname(dirname(dirname(__FILE__)))) . "/joinplugin/common.inc.php");

	$query = DB :: query("SELECT * FROM " . DB :: table('group_live') . " WHERE liveid='$id'");
	if ($live = DB :: fetch($query)) {
		$feed['icon'] = 'live';
		$feed['id'] = $live['liveid'];
		$feed['idtype'] = 'gliveid';
		$feed['uid'] = $live['uid'];
		$feed['username'] = $live['username'];
		$feed['dateline'] = $live['dateline'];
		$feed['hot'] = $live['hot'];
		$url = join_plugin_action2("livecp", array (
			'liveid' => $live['liveid'],
			'op' => 'join'
		));
		$feed['title_template'] = 'feed_live_play_title';
		$feed['body_template'] = 'feed_live_play_body';
		$feed['body_data'] = array (
			'subject' => "<a href=\"$url\">$live[subject]</a>",

		);
		$feed = mkfeed($feed);

		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array (
			$feed['image_1']
		), array (
			$feed['image_1_link']
		), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username'], $_G['fid']);
	}
}
//点播
function feed_replay_live($id) {
	global $_G;

	include_once libfile('function/feed');
	require_once (dirname(dirname(dirname(dirname(__FILE__)))) . "/joinplugin/common.inc.php");

	$query = DB :: query("SELECT * FROM " . DB :: table('group_live') . " WHERE liveid='$id'");
	if ($live = DB :: fetch($query)) {
		$feed['icon'] = 'live';
		$feed['id'] = $live['liveid'];
		$feed['idtype'] = 'gliveid';
		$feed['uid'] = $live['uid'];
		$feed['username'] = $live['username'];
		$feed['dateline'] = $live['dateline'];
		$feed['hot'] = $live['hot'];
		//		$url = join_plugin_action2("livecp", array('id' => $live['liveid'], 'op' => 'join'));
		$url = "forum.php?mod=group&action=plugin&fid=" . $live[fid] . "&plugin_name=grouplive&plugin_op=groupmenu&liveid=" . $live[liveid] . "&op=join&grouplive_action=livecp";
		$feed['title_template'] = 'feed_live_replay_title';
		$feed['body_template'] = 'feed_live_replay_body';
		$feed['body_data'] = array (
			'subject' => "<a href=\"$url\">$live[subject]</a>",

		);
		$feed = mkfeed($feed);

		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array (
			$feed['image_1']
		), array (
			$feed['image_1_link']
		), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username'], $_G['fid']);
	}
}

function checkperm_group($permtype) {
	global $_G;

	if (substr($permtype, 0, 6) == 'manage') {
		return $_G['forum']['ismoderator'];
	} else {
		return true;
	}
}

?>
