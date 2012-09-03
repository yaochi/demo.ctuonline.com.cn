<?php


/* Function: 你我课堂 方法
 * Com.:
 * Author: wuhan
 * Date: 2010-7-20
 */

function nwkt_bbcode($message) {
	$message = preg_replace("/\[flash\=?(media|real)*\](.+?)\[\/flash\]/ie", "blog_flash('\\2', '\\1')", $message);
	return $message;
}

function getUserIdByName($anames = array()) {
	global $_G, $space;
	$uids = array ();
	
	$names = empty($anames)?array():explode(',', preg_replace("/(\s+)/s", ',', $anames));
	
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

function getUserIds($ids = array()){
	global $_G, $space;
	$uids = array ();
	
	$uids = empty($ids)?array(): array_unique(explode(',', preg_replace("/(\s+)/s", ',', $ids)));
	
	if (empty ($uids)) {
		return '';
	} else {
		return implode(',', $uids);
	}
}

function getUserNameById($auids = ''){
	if(empty($auids)){
		return '';
	}
	
	$uids = explode(',', $auids);
	
	$names = array();
	$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN (".implode(',', $uids).")");
	while ($value = DB::fetch($query)) {
		$names[] = $value['username'];
	}
	return implode(' ', $names);
}

function getRealNameById($auids = '', $mark = ' '){
	if(empty($auids)){
		return '';
	}
	$uids = explode(',', $auids);
	
	$names = array();
	$query = DB::query("SELECT realname FROM ".DB::table('common_member_profile')." WHERE uid IN (".implode(',', $uids).")");
	while ($value = DB::fetch($query)) {
		$names[] = $value['realname'];
	}
	return implode($mark, $names);
}

function nwkt_post($POST, $olds = array ()) {
	global $_G, $space;

	$isself = 1;
	if (!empty ($olds['uid']) && $olds['uid'] != $_G['uid']) {
		$isself = 0;
		$__G = $_G;
		$_G['uid'] = $olds['uid'];
		$_G['username'] = addslashes($olds['username']);
	}

	$POST['subject'] = getstr(trim($POST['subject']), 80, 1, 1, 1);
	if (strlen($POST['subject']) < 1)
		$POST['subject'] = dgmdate($_G['timestamp'], 'Y-m-d');
	//	$POST['friend'] = intval($POST['friend']);

	//	$POST['target_ids'] = '';
	//	$POST['friend'] == 0;
	//	if($POST['friend'] == 2) {
	//		$uids = array();
	//		$names = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
	//		if($names) {
	//			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN (".dimplode($names).")");
	//			while ($value = DB::fetch($query)) {
	//				$uids[] = $value['uid'];
	//			}
	//		}
	//		if(empty($uids)) {
	//			$POST['friend'] = 3;
	//		} else {
	//			$POST['target_ids'] = implode(',', $uids);
	//		}
	//	} elseif($POST['friend'] == 4) {
	//		$POST['password'] = trim($POST['password']);
	//		if($POST['password'] == '') $POST['friend'] = 0;
	//	}
	//	if($POST['friend'] !== 2) {
	//		$POST['target_ids'] = '';
	//	}
	//	if($POST['friend'] !== 4) {
	//		$POST['password'] == '';
	//	}
	
	if ($_G['mobile']) {
		$POST['message'] = getstr($POST['message'], 0, 1, 0, 1, 1);
	} else {
		$POST['message'] = checkhtmlnwkt($POST['message']);
		$POST['message'] = getstr($POST['message'], 0, 1, 0, 1, 0, 1);
		$POST['message'] = preg_replace(array (
			"/\<div\>\<\/div\>/i",
			"/\<a\s+href\=\"([^\>]+?)\"\>/i"
		), array (
			'',
			'<a href="\\1" target="_blank">'
		), $POST['message']);
	}
	$message = $POST['message'];

	$classid = intval($POST['classid']);

	$ckmessage = preg_replace("/(\<div\>|\<\/div\>|\s|\&nbsp\;|\<br\>|\<p\>|\<\/p\>)+/is", '', $message);

	if (empty ($ckmessage)) {
		return false;
	}

	$message = addslashes($message);

	$nwktarr = array (
		'subject' => $POST['subject'],
		'classid' => $classid,
		'message' => $message,
		'type' => intval($POST['type']),
		'maxnum' => intval($POST['maxnum']),
		'starttime' => @ strtotime($POST['starttime']),
		'endtime' => @ strtotime($POST['endtime']),
		'firstman_ids' => getUserIds($POST['firstman_names_uids']),
		'secondman_ids' => getUserIds($POST['secondman_names_uids']),
		'guest_ids' => getUserIds($POST['guest_names_uids']),
		'aid' => $POST['aid'],
	);

	if (checkperm('managenwkt')) {
		$nwktarr['hot'] = intval($POST['hot']);
	}

	if ($olds['nwktid']) {

		$nwktid = $olds['nwktid'];
		DB :: update('home_nwkt', $nwktarr, array (
			'nwktid' => $nwktid
		));

		$fuids = array ();

		$nwktarr['uid'] = $olds['uid'];
		$nwktarr['username'] = $olds['username'];
	} else {

		$nwktarr['uid'] = $_G['uid'];
		$nwktarr['username'] = $_G['username'];
		$nwktarr['dateline'] = empty ($POST['dateline']) ? $_G['timestamp'] : $POST['dateline'];
		$nwktid = DB :: insert('home_nwkt', $nwktarr, 1);
	}

	$nwktarr['nwktid'] = $nwktid;
	
	$fieldarr = array ();

	if($POST['makefeed'] && empty($olds['nwktid']) && empty($nwktarr['type'])) {
		feed_publish_nwkt($nwktarr['nwktid']);
	}
	
	if($POST['makenotify'] && empty($olds['nwktid'])){
		$useduid = array($nwktarr['uid']);
		if($nwktarr['firstman_ids']) {
			$firstman_ids = explode(',', $nwktarr['firstman_ids']);
			foreach($firstman_ids as $uid){
				if(!in_array($uid, $useduid)){
					$useduid[] = $uid;
					notification_add($uid, 'nwkt', 'nwkt_member_invite_firstman', array('actor' => "<a href=\"home.php?mod=space&uid=$nwktarr[uid]\" target=\"_blank\">$nwktarr[username]</a>" , 'subject' => "<a href=\"home.php?mod=space&uid=$nwktarr[uid]&do=nwkt&id=$nwktarr[nwktid]\" target=\"_blank\">$nwktarr[subject]</a>", 'starttime' => dgmdate($nwktarr['starttime'], 'Y年m月d日 H:i'), 'endtime' => dgmdate($nwktarr['endtime'], 'Y年m月d日 H:i')), 1);
				
				}
			}
		}
		if($nwktarr['secondman_ids']) {
			$secondman_ids = explode(',', $nwktarr['secondman_ids']);
			foreach($secondman_ids as $uid){
				if(!in_array($uid, $useduid)){
					$useduid[] = $uid;
					notification_add($uid, 'nwkt', 'nwkt_member_invite_secondman', array('actor' => "<a href=\"home.php?mod=space&uid=$nwktarr[uid]\" target=\"_blank\">$nwktarr[username]</a>" , 'subject' => "<a href=\"home.php?mod=space&uid=$nwktarr[uid]&do=nwkt&id=$nwktarr[nwktid]\" target=\"_blank\">$nwktarr[subject]</a>", 'starttime' => dgmdate($nwktarr['starttime'], 'Y年m月d日 H:i'), 'endtime' => dgmdate($nwktarr['endtime'], 'Y年m月d日 H:i')), 1);
				}
			}
		}
		if($nwktarr['guest_ids']) {
			$guest_ids = explode(',', $nwktarr['guest_ids']);
			foreach($guest_ids as $uid){
				if(!in_array($uid, $useduid)){
					$useduid[] = $uid;
					notification_add($uid, 'nwkt', 'nwkt_member_invite_guest', array('actor' => "<a href=\"home.php?mod=space&uid=$nwktarr[uid]\" target=\"_blank\">$nwktarr[username]</a>" , 'subject' => "<a href=\"home.php?mod=space&uid=$nwktarr[uid]&do=nwkt&id=$nwktarr[nwktid]\" target=\"_blank\">$nwktarr[subject]</a>", 'starttime' => dgmdate($nwktarr['starttime'], 'Y年m月d日 H:i'), 'endtime' => dgmdate($nwktarr['endtime'], 'Y年m月d日 H:i')), 1);
				}
			}
		}
	}
	
	if(empty($olds['nwktid'])){
		//创建积分
		require_once libfile('function/credit');
		credit_create_credit_log($nwktarr['uid'], "createyouiclassroom", $nwktarr['nwktid']);
		hook_create_resource($nwktarr['nwktid'], "nwkt");//fumz,2010-9-21 13:02:34
	}

	if (!empty ($__G))
		$_G = $__G;

	return $nwktarr;
}

function deletenwkts($nwktids, $deleteattach = true) {
	global $_G;

	$nwkts = $newnwktids = $counts = $aids = array ();
	$allowmanage = true;
	$query = DB :: query("SELECT * FROM " . DB :: table('home_nwkt') . " WHERE nwktid IN (" . dimplode($nwktids) . ")");
	while ($value = DB :: fetch($query)) {
		if ($allowmanage || $value['uid'] == $_G['uid']) {
			$nwkts[] = $value;
			$newnwktids[] = $value['nwktid'];

			if ($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['nwktid'] -= 1;

			if ($value['aid']) {
				$aids[] = $value['aid'];
			}
		}
	}
	if (empty ($nwkts))
		return array ();

	//added by fumz,2010-9-30 12:02:44
	//begin
	hook_delete_resources($newnwktids,'nwkt');
	//end
	DB :: query("DELETE FROM " . DB :: table('home_nwkt') . " WHERE nwktid IN (" . dimplode($newnwktids) . ")");
	//删除附件
	if ($deleteattach) {
		$query = DB :: query("SELECT aid FROM " . DB :: table('home_attachment') . " WHERE id IN (" . dimplode($newnwktids) . ") AND idtype = 'nwktid'");
		while ($value = DB :: fetch($query)) {
			$aids[] = $value['aid'];
		}
		require_once libfile('function/home_attachment');
		if (!empty ($aids)) {
			$query = DB :: query("SELECT attachment, thumb, remote, aid FROM " . DB :: table('home_attachment') . " WHERE aid IN (" . dimplode($aids) . ")");
			while ($attach = DB :: fetch($query)) {
				dunlink_home($attach);
			}
			DB :: query("DELETE FROM " . DB :: table('home_attachment') . " WHERE aid IN (" . dimplode($aids) . ")");
		}
	}
	DB :: query("DELETE FROM " . DB :: table('home_comment') . " WHERE id IN (" . dimplode($newnwktids) . ") AND idtype='nwktid'");
	DB :: query("DELETE FROM " . DB :: table('home_feed') . " WHERE id IN (" . dimplode($newnwktids) . ") AND idtype='nwktid'");

	//发送通知
	foreach($nwkts as $nwkt){
		if($nwkts['uid'] != $_G['uid']){
			notification_add($nwkt['uid'], 'nwkt', 'nwkt_delete', array('actor' => "<a href=\"home.php?mod=space&uid=$nwkt[uid]\" target=\"_blank\">$nwkt[username]</a>", 'subject' => $nwkt['subject']), 1);
		}
	}

	if ($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('publishnwkt', $uid, array (
				'nwkts' => $setarr['nwkts']
			), $setarr['coef']);
		}
	}
	return $nwkts;
}

function getnwktclassarr() {
	global $_G;

	$classarr = array ();
	$query = DB :: query("SELECT classid, classname FROM " . DB :: table('home_nwkt_class'));
	while ($value = DB :: fetch($query)) {
		$classarr[$value['classid']] = $value;
	}
	return $classarr;
}

function checkhtmlnwkt($html) {
	$html = dstripslashes($html);
	if (!checkperm('allowhtml')) {

		preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

		$searchs[] = '<';
		$replaces[] = '&lt;';
		$searchs[] = '>';
		$replaces[] = '&gt;';

		if ($ms[1]) {
			$allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|object|param|embed';
			$ms[1] = array_unique($ms[1]);
			foreach ($ms[1] as $value) {
				$searchs[] = "&lt;" . $value . "&gt;";

				$value = str_replace('&', '_uch_tmp_str_', $value);
				$value = dhtmlspecialchars($value);
				$value = str_replace('_uch_tmp_str_', '&', $value);

				$value = str_replace(array (
					'\\',
					'/*'
				), array (
					'.',
					'/.'
				), $value);
				$value = preg_replace(array (
					"/(javascript|script|eval|behaviour|expression|style|class)/i",
					"/(\s+|&quot;|')on/i"
				), array (
					'.',
					' .'
				), $value);
				if (!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
					$value = '';
				}
				$replaces[] = empty ($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
			}
		}
		$html = str_replace($searchs, $replaces, $html);
	}
	$html = addslashes($html);

	return $html;
}

function ckType($touid, $type, $firstman_ids, $secondman_ids, $guest_ids){
	global $_G, $space;

	if(empty($_G['uid'])) return $type?false:true;
	if($touid == $_G['uid']) return true;
	
	$var = false;
	switch ($type) {
		//公开
		case 0:
			$var = true;
			break;
		//封闭
		case 1:
			if($firstman_ids) {
				$firstman_ids = explode(',', $firstman_ids);
				if(in_array($_G['uid'], $firstman_ids)) $var = true;
			}
			if($secondman_ids) {
				$secondman_ids = explode(',', $secondman_ids);
				if(in_array($_G['uid'], $secondman_ids)) $var = true;
			}
			if($guest_ids) {
				$guest_ids = explode(',', $guest_ids);
				if(in_array($_G['uid'], $guest_ids)) $var = true;
			}
			break;
	}
	return $var;
}

function feed_publish_nwkt($id){
	global $_G;
	
	include_once libfile('function/feed');
	include_once libfile('function/home_attachment');
	
	$query = DB :: query("SELECT * FROM " . DB :: table('home_nwkt') . " WHERE nwktid='$id'");
	if($nwkt = DB::fetch($query)){
		$feed['icon'] = 'nwkt';
		$feed['id'] = $nwkt['nwktid'];
		$feed['idtype'] = 'nwktid';
		$feed['uid'] = $nwkt['uid'];
		$feed['username'] = $nwkt['username'];
		$feed['dateline'] = $nwkt['dateline'];
		$feed['hot'] = $nwkt['hot'];
		$url = "home.php?mod=space&uid=$nwkt[uid]&do=nwkt&id=$nwkt[nwktid]";
		if($nwkt['aid']){
			$query = DB :: query("SELECT * FROM " . DB :: table('home_attachment') . " WHERE aid='$nwkt[aid]'");
			if($value = DB::fetch($query)) {
				$feed['image_1'] = getAttachUrl($value['attachment'], $value['thumb'], $value['remote']);
				$feed['image_1_link'] = $url;
			}
		}
		$feed['title_template'] = 'feed_nwkt_title';
		$feed['body_template'] = 'feed_nwkt_body';
		$message = preg_replace("/&[a-z]+\;/i", '', $nwkt['message']);
		$feed['body_data'] = array(
			'subject' => "<a href=\"$url\">$nwkt[subject]</a>",
			'summary' => getstr($message, 150, 1, 1, 0, 0, -1)
		);
		$feed = mkfeed($feed);
		
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array($feed['image_1']), array($feed['image_1_link']), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username']);
	}
}

function deletedata($ids,$contenttype){
	if($ids){
		foreach($ids as $id){
			//删除附件
				$query = DB :: query("SELECT aid FROM " . DB :: table('home_attachment') . " WHERE id=$id AND idtype = 'nwktid'");
				while ($value = DB :: fetch($query)) {
					$aids[] = $value['aid'];
				}
				require_once libfile('function/home_attachment');
				if (!empty ($aids)) {
					$query = DB :: query("SELECT attachment, thumb, remote, aid FROM " . DB :: table('home_attachment') . " WHERE aid IN (" . dimplode($aids) . ")");
					while ($attach = DB :: fetch($query)) {
						dunlink_home($attach);
					}
					DB :: query("DELETE FROM " . DB :: table('home_attachment') . " WHERE aid IN (" . dimplode($aids) . ")");
				}
			DB :: query("DELETE FROM " . DB :: table('home_comment') . " WHERE id=$id AND idtype='nwktid'");
			DB :: query("DELETE FROM " . DB :: table('home_feed') . " WHERE id=$id AND idtype='nwktid'");
			$condition = "nwktid = ".$id;
			$table='home_nwkt';
            $res = DB::delete($table, $condition);
			hook_delete_resource($id,'nwkt');
		}
	}	
	return $res;
}


?>
