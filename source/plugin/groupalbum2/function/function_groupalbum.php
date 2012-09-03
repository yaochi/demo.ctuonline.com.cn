<?php


/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-7-16
 */
function lang_plugin($var) {
	global $_G;

	$vars = explode(':', $var);
	$isplugin = count($vars) == 2;
	if (!$isplugin) {
		return '!' . $var . '!';
	} else {
		!isset ($_G['lang']['plugin_p'][$vars[0]]) && $_G['lang']['plugin_p'][$vars[0]] = array ();
		$langvar = & $_G['lang']['plugin_p'][$vars[0]];
		$var = & $vars[1];
	}
	if (!isset ($langvar[$var])) {
		$scriptlang = array ();
		@ include DISCUZ_ROOT . './data/plugindata/' . $vars[0] . '.lang.php';
		$_G['lang']['plugin_p'][$vars[0]] = $scriptlang[$vars[0]];
	}
	if (isset ($langvar[$var])) {
		return $langvar[$var];
	} else {
		return '!' . $var . '!';
	}
}

function getalbums_group($uid) {
	global $_G;

	$albums = array ();

	$wheresql = "fid = '$_G[fid]'";
	if(!checkperm_group('managealbum')){
		$wheresql .= " AND uid='$uid'";
	}

	$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " WHERE $wheresql ORDER BY albumid DESC");
	while ($value = DB :: fetch($query)) {
		$albums[$value['albumid']] = $value;
	}
	return $albums;
}

function deletealbums_group($albumids) {
	global $_G;

	$sizes = $dels = $newids = $counts = array ();
	$allowmanage = checkperm_group('managealbum');

	$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " WHERE albumid IN (" . dimplode($albumids) . ")");
	while ($value = DB :: fetch($query)) {
		if ($allowmanage || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newids[] = $value['albumid'];
		}
		$counts[$value['uid']]['albums'] -= 1;
	}
	if (empty ($dels))
		return array ();

	$pics = $picids = array ();
	$query = DB :: query("SELECT * FROM " . DB :: table('group_pic') . " WHERE albumid IN (" . dimplode($newids) . ")");
	while ($value = DB :: fetch($query)) {
		$pics[] = $value;
		$picids[] = $value['picid'];
		$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
		if ($value['uid'] != $_G['uid']) {
			$counts[$value['uid']]['coef'] -= 1;
		}
	}

	DB :: query("DELETE FROM " . DB :: table('group_pic') . " WHERE albumid IN (" . dimplode($newids) . ")");
	DB :: query("DELETE FROM " . DB :: table('group_album') . " WHERE albumid IN (" . dimplode($newids) . ")");
	DB :: query("DELETE FROM " . DB :: table('home_feed') . " WHERE id IN (" . dimplode($newids) . ") AND idtype='galbumid'");
	if ($picids)
		DB :: query("DELETE FROM " . DB :: table('home_clickuser') . " WHERE id IN (" . dimplode($picids) . ") AND idtype='gpicid'");
	foreach($picids as $rid){
		hook_delete_resource($rid, "pic");
	}
	if ($counts) {
		foreach ($counts as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			batchupdatecredit('uploadimage', $uid, array (
				'albums' => $setarr['albums'],
				'attachsize' => - $attachsize
			), $setarr['coef']);
		}
	}
	if ($sizes) {
		foreach ($sizes as $uid => $value) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, array (
				'attachsize' => - $attachsize
			), false);
		}
	}

	if ($pics) {
		deletepicfiles_group($pics);
	}

	return $dels;
}

function deletepics_group($picids) {
	global $_G;

	$sizes = $pics = $newids = $counts = array ();
	$allowmanage = checkperm_group('managealbum');

	$albumids = array ();
	$query = DB :: query("SELECT * FROM " . DB :: table('group_pic') . " WHERE picid IN (" . dimplode($picids) . ")");
	while ($value = DB :: fetch($query)) {
		if ($allowmanage || $value['uid'] == $_G['uid']) {
			$pics[] = $value;
			$newids[] = $value['picid'];

			if ($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			$albumids[$value['albumid']] = $value['albumid'];
		}
	}
	if (empty ($pics))
		return array ();

	DB :: query("DELETE FROM " . DB :: table('group_pic') . " WHERE picid IN (" . dimplode($newids) . ")");
	DB :: query("DELETE FROM " . DB :: table('home_comment') . " WHERE id IN (" . dimplode($newids) . ") AND idtype='gpicid'");
	DB :: query("DELETE FROM " . DB :: table('home_feed') . " WHERE id IN (" . dimplode($newids) . ") AND idtype='gpicid'");
	DB :: query("DELETE FROM " . DB :: table('home_clickuser') . " WHERE id IN (" . dimplode($newids) . ") AND idtype='gpicid'");

//	if ($counts) {
//		foreach ($counts as $uid => $setarr) {
//			$attachsize = intval($sizes[$uid]);
//			batchupdatecredit('uploadimage', $uid, array (
//				'attachsize' => - $attachsize
//			), $setarr['coef']);
//			unset ($sizes[$uid]);
//		}
//	}
	if ($sizes) {
		foreach ($sizes as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, array (
				'attachsize' => - $attachsize
			), false);
		}
	}

	require_once libfile('function/spacecp');
	foreach ($albumids as $albumid) {
		if ($albumid) {
			album_update_pic_group($albumid);
		}
	}

	deletepicfiles_group($pics);

	foreach ($pics as $pic) {
		if ($pic['uid'] != $_G['uid']) {
			notification_add($pic['uid'], 'gpic', 'gpic_delete', array (
				'actor' => "<a href=\"home.php?mod=space&uid=$pic[uid]\" target=\"_blank\">$pic[username]</a>",
				'pic' => $pic['title'],
				'group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">" . $_G['forum']['name'] . "</a>"
			), 1);
		}
	}

	//删除图片 经验值
	require_once libfile('function/group');
	foreach ($pics as $pic) {
		 hook_delete_resource($pic[picid], "pic");
		group_add_empirical_by_setting($pic['uid'], $_G['fid'], 'photo_delete', $pic[picid]);
	}

	//删除图片积分
	require_once libfile('function/credit');
	foreach ($pics as $pic) {
		credit_create_credit_log($pic['uid'], 'deletepicture', $pic['picid']);
	}

	return $pics;
}

function deletepicfiles_group($pics) {
	global $_G;
	$remotes = array ();
	include_once libfile('function/home');
	foreach ($pics as $pic) {
		pic_delete($pic['filepath'], 'plugin_groupalbum2', $pic['thumb'], $pic['remote']);
	}
}

function album_update_pic_group($albumid, $picid = 0) {
	global $_G;

	$setarr = array ();
	if ($picid) {
		$wheresql = "AND picid='$picid'";
	} else {
		$wheresql = "ORDER BY picid DESC LIMIT 1";
		$piccount = getcount('group_pic', array (
			'albumid' => $albumid
		));
		if (empty ($piccount)) {
			DB :: query("DELETE FROM " . DB :: table('group_album') . " WHERE albumid='$albumid'");
			return false;
		} else {
			$setarr['picnum'] = $piccount;
		}
	}
	$query = DB :: query("SELECT * FROM " . DB :: table('group_pic') . " WHERE albumid='$albumid' $wheresql");
	if (!$pic = DB :: fetch($query)) {
		return false;
	}

	$basedir = !getglobal('setting/attachdir') ? (DISCUZ_ROOT . './data/attachment/') : getglobal('setting/attachdir');
	$picdir = 'cover/' . substr(md5($albumid), 0, 2) . '/';
	dmkdir($basedir . './plugin_groupalbum2/' . $picdir);
	if ($pic['remote']) {
		$picsource = pic_get($pic['filepath'], 'plugin_groupalbum2', $pic['thumb'], $pic['remote'], 0);
	} else {
		$picsource = $basedir . './plugin_groupalbum2/' . $pic['filepath'];
	}
	require_once libfile('class/image');
	$image = new image();
	if ($image->Thumb($picsource, 'plugin_groupalbum2/' . $picdir . $albumid . '.jpg', 120, 120, 2)) {
		$setarr['pic'] = $picdir . $albumid . '.jpg';
		$setarr['picflag'] = 1;
		if (getglobal('setting/ftp/on')) {
			if (ftpcmd('upload', 'plugin_groupalbum2/' . $picdir . $albumid . '.jpg')) {
				$setarr['picflag'] = 2;
			}
		}
	} else {
		$setarr['pic'] = $pic['thumb'] ? $pic['filepath'] . '.thumb.jpg' : $pic['filepath'];
		$setarr['picflag'] = $pic['remote'] ? 2 : 1;
	}
	DB :: update('group_album', $setarr, array (
		'albumid' => $albumid
	));
}

function ckfriend_group($touid, $friend, $target_ids = '') {
	global $_G, $space;

	if (empty ($_G['uid']))
		return $friend ? false : true;
	if ($touid == $_G['uid'])
		return true;

	$var = 'group_ckfriend_' . md5($touid . '_' . $friend . '_' . $target_ids);
	if (isset ($_G[$var]))
		return $_G[$var];

	$_G[$var] = false;
	switch ($friend) {
		case 0 :
			$_G[$var] = true;
			break;
		case 4 :
			$_G[$var] = true;
			break;
		case 5 :
			$_G[$var] = $_G['ismember'] ? 1 : 2;
		default :
			break;
	}
	return $_G[$var];
}

function ckfriend_album_group($album) {
	global $_G, $space;

	if (!ckfriend_group($album['uid'], $album['friend'], $album['target_ids'])) {
		if (empty ($_G['uid'])) {
			showmessage('to_login', 'member.php?mod=logging&action=login', array (), array (
				'showmsg' => true,
				'login' => 1
			));
		}

		require_once libfile('function/home');
		$space = getspace($album['uid']);

		require_once libfile('function/friend');
		$isfriend = friend_check($album['uid']);
		space_merge($space, 'count');
		space_merge($space, 'profile');

		include template('home/space_privacy');
		exit ();
	}elseif(ckfriend_group($album['uid'], $album['friend'], $album['target_ids'])===2){
		showmessage('您还没有加入专区','forum.php?mod=group&fid='.$_G['fid']);
	}
	elseif ($album['uid'] != $_G['uid'] && $album['friend'] == 4) {
		$cookiename = "view_pwd_galbum_$album[albumid]";
		$cookievalue = empty ($_G['cookie'][$cookiename]) ? '' : $_G['cookie'][$cookiename];
		if ($cookievalue != md5(md5($album['password']))) {
			$ginvalue = $album;
			include template('home/misc_inputpwd');
			exit ();
		}
	}
}

function album_creat_by_id_group($albumid) {
	global $_G, $space;
	preg_match("/^new\:(.+)$/i", $albumid, $matchs);
	if (!empty ($matchs[1])) {
		$albumname = dhtmlspecialchars(trim($matchs[1]));
		if (empty ($albumname))
			$albumname = dgmdate($_G['timestamp'], 'Ymd');
		$albumid = album_creat_group(array (
			'albumname' => $albumname
		));
	} else {
		$albumid = intval($albumid);
		if ($albumid) {
			$wheresql = "albumid='$albumid' AND fid='$_G[fid]'";
			if(!checkperm_group('managealbum')){
				$wheresql .= " AND uid='$_G[uid]'";
			}
			$query = DB :: query("SELECT albumname,friend FROM " . DB :: table('group_album') . " WHERE $wheresql");
			if ($value = DB :: fetch($query)) {
				$albumname = addslashes($value['albumname']);
				$albumfriend = $value['friend'];
			} else {
				$albumname = dgmdate($_G['timestamp'], 'Ymd');
				$albumid = album_creat_group(array (
					'albumname' => $albumname
				));
			}
		}
	}

	return $albumid;
}

function pic_save_group($FILE, $albumid, $title, $iswatermark = true) {
	global $_G, $space;

	$albumfriend = 0;
	$albumid = album_creat_by_id_group($albumid);


	$allowpictype = array (
		'jpg',
		'jpeg',
		'gif',
		'png'
	);

	require_once libfile('class/upload');
	$upload = new discuz_upload();
	$upload->init($FILE, 'plugin_groupalbum2');

	if ($upload->error()) {
		return lang('spacecp', 'lack_of_access_to_upload_file_size');
	}

	if (!$upload->attach['isimage']) {
		return lang('spacecp', 'only_allows_upload_file_types');
	}

	if (empty ($space)) {
		$_G['member'] = $space = getspace($_G['uid']);
		$_G['username'] = addslashes($space['username']);
	}
	$_G['member'] = $space;

	loadcache('usergroup_' . $space['groupid']);
	$_G['group'] = $_G['cache']['usergroup_' . $space['groupid']];

	//TODO 权限修改
	if (!checkperm_group('allowupload')) {
		return lang('spacecp', 'not_allow_upload');
	}

	if (!ckrealname('album', 1)) {
		return lang('spacecp', 'not_allow_upload');
	}

		if (!ckvideophoto('album', array (), 1)) {
		return lang('spacecp', 'not_allow_upload');
	}

	if (!cknewuser(1)) {
		return lang('spacecp', 'not_allow_upload');
	}
	//---------------------------------------------

	$maxspacesize = checkperm('maxspacesize');
	if ($maxspacesize) {
		space_merge($space, 'count');
		space_merge($space, 'field_home');
                /* 注释
                * $maxspacesize 在$_G['group']['maxspacesize']
                * common_usergroup_field中，初始均为0
                * Add by lujianqing
		if ($space['attachsize'] + $upload->attach['size'] > $maxspacesize + $space['addsize']) {
			return lang('spacecp', 'inadequate_capacity_space');
		}
                 *
                 */
	}



	/*$showtip = true;
	if ($albumid) {
		$albumid = album_creat_by_id_group($albumid);
	} else {
		$albumid = 0;
		$showtip = false;
	}*/

	$upload->save();
	if ($upload->error()) {
		return lang('spacecp', 'mobile_picture_temporary_failure');
	}

	$new_name = $upload->attach['target'];

	require_once libfile('class/image');
	$image = new image();
	$result = $image->Thumb($new_name, '', 140, 140, 1);
	$thumb = empty ($result) ? 0 : 1;

	if ($_G['setting']['maxthumbwidth'] && $_G['setting']['maxthumbheight']) {
		if ($_G['setting']['maxthumbwidth'] < 300)
			$_G['setting']['maxthumbwidth'] = 300;
		if ($_G['setting']['maxthumbheight'] < 300)
			$_G['setting']['maxthumbheight'] = 300;
		$image->Thumb($new_name, '', $_G['setting']['maxthumbwidth'], $_G['setting']['maxthumbheight'], 1, 1);
	}

	if ($iswatermark) {
		$image->Watermark($new_name);
	}
	$pic_remote = 0;
	$album_picflag = 1;

	if (getglobal('setting/ftp/on')) {
		$ftpresult_thumb = 0;
		$ftpresult = ftpcmd('upload', 'plugin_groupalbum2/' . $upload->attach['attachment']);
		if ($ftpresult) {
			if ($thumb) {
				ftpcmd('upload', 'plugin_groupalbum2/' . $upload->attach['attachment'] . '.thumb.jpg');
			}
			$pic_remote = 1;
			$album_picflag = 2;
		} else {
			if (getglobal('setting/ftp/mirror')) {
				@ unlink($upload->attach['target']);
				@ unlink($upload->attach['target'] . '.thumb.jpg');
				return lang('spacecp', 'ftp_upload_file_size');
			}
		}
	}

	$title = getstr($title, 200, 1, 1, 1);

	$setarr = array (
		'albumid' => $albumid,
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'filename' => addslashes($upload->attach['name']),
		'postip' => $_G['clientip'],
		'title' => $title,
		'type' => addslashes($upload->attach['ext']),
		'size' => $upload->attach['size'],
		'filepath' => $upload->attach['attachment'],
		'thumb' => $thumb,
		'remote' => $pic_remote
	);
	$setarr['picid'] = DB :: insert('group_pic', $setarr, 1);
	hook_create_resource($setarr['picid'], "pic",$_G['fid']);
	DB :: query("UPDATE " . DB :: table('common_member_count') . " SET attachsize=attachsize+{$upload->attach['size']} WHERE uid='$_G[uid]'");

	include_once libfile('function/stat');
	updatestat_group('pic');

	return $setarr;
}

function updatestat_group($type, $primary = 0) {
	global $_G;

	if (empty ($_G['uid']) || empty ($_G['setting']['updatestat']))
		return false;

	$nowdaytime = dgmdate($_G['timestamp'], 'Ymd');
	if ($primary) {
		$setarr = array (
			'uid' => $_G['uid'],
			'daytime' => '$nowdaytime',
			'type' => $type
		);
		if (getcount('common_statuser', $setarr)) {
			return false;
		} else {
			DB :: insert('common_statuser', $setarr);
		}
	}
	if (getcount('common_stat', array (
			'daytime' => $nowdaytime
		))) {
		DB :: query("UPDATE " . DB :: table('common_stat') . " SET `$type`=`$type`+1 WHERE daytime='$nowdaytime'");
	} else {
		DB :: query("DELETE FROM " . DB :: table('common_statuser') . " WHERE daytime != '$nowdaytime'");
		DB :: insert('common_stat', array (
			'daytime' => $nowdaytime,
			$type => '1'
		));
	}
}

function pic_cover_get_group($pic, $picflag) {
	global $_G;

	if ($picflag == 1) {
		$url = $_G['setting']['attachurl'] . 'plugin_groupalbum2/' . $pic;
	}
	elseif ($picflag == 2) {
		$url = $_G['setting']['ftp']['attachurl'] . 'plugin_groupalbum2/' . $pic;
	} else {
		$url = $pic;
	}
	return $url;
}

function album_creat_group($arr) {
	global $_G, $space;

	$albumid = DB :: result(DB :: query("SELECT albumid FROM " . DB :: table('group_album') . " WHERE albumname='$arr[albumname]' AND uid='$_G[uid]' AND fid='$_G[fid]'"));
	if ($albumid) {
		return $albumid;
	} else {
		$arr['uid'] = $_G['uid'];
		$arr['username'] = $_G['username'];
		$arr['fid'] = $_G['fid'];
		$arr['dateline'] = $arr['updatetime'] = $_G['timestamp'];
		$albumid = DB :: insert('group_album', $arr, 1);
		hook_create_resource($albumid, "ablum",$_G['fid']);
		//DB::query("UPDATE ".DB::table('common_member_count')." SET albums = albums + 1 WHERE uid = '$_G[uid]'");

		return $albumid;
	}
}


function copy_album($albumids){
	global $_G;

	if(empty($albumids)){
		return false;
	}

	require_once libfile('class/upload');
	$upload = new discuz_upload();
	$targetdir = $upload->get_target_dir('plugin_groupalbum2');

	//复制相册
	$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid IN (".dimplode($albumids).")");
	while($value = DB :: fetch($query)){
		//复制相册
		$albumarr = array(
			'albumname' => $value['albumname'],
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'updatetime' => $_G['timestamp'],
			'picnum' => $value['picnum'],
			'pic' => $value['pic'],//TODO 相册封面是否需要复制
			'picflag' => $value['picflag'],
			'friend' => $value['friend'],
			'password' => $value['password'],
			'target_ids' => $value['target_ids'],
			'fid' =>$_G['fid'],
		);

		$albumarr['albumid'] = DB::insert("group_album", $albumarr, 1);

	     hook_create_resource($albumarr['albumid'], "ablum",$_G['fid']);
		//复制图片
		$query2 = DB::query("SELECT * FROM ".DB::table('group_pic')." WHERE albumid = '$value[albumid]'");
		while($value = DB::fetch($query2)){
			$source = getglobal('setting/attachdir').'./plugin_groupalbum2/'.$value['filepath'];
			$filepath = $targetdir.$upload->get_target_filename('plugin_groupalbum2',0,'').$upload->get_target_extension($upload->fileext($value['filename']));
			$target = getglobal('setting/attachdir').'./plugin_groupalbum2/'.$filepath;
			copy($source, $target);
			if($value['thumb']){
				$source = $source.'.thumb.jpg';
				$target = $target.'.thumb.jpg';
				copy($source, $target);
			}

			$picarr = array (
				'albumid' => $albumarr['albumid'],
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'filename' => $value['filename'],
				'postip' => $_G['clientip'],
				'title' => $value['title'],
				'type' => $value['type'],
				'size' => $value['size'],
				'filepath' => $filepath,
				'thumb' => $value['thumb'],
				'remote' => $value['remote']
			);
			$picarr['picid'] = DB :: insert('group_pic', $picarr, 1);
			 hook_create_resource($picarr['picid'], "pic",$_G['fid']);
		}
	}

	return true;
}

function feed_publish_album($id, $add = 0) {
	global $_G;

	require_once libfile("function/home");

	$idtype = 'galbumid';

	$setarr = array ();
	$key = 1;
	if ($id > 0) {
		$query = DB :: query("SELECT a.username, a.albumname, a.picnum, a.friend, a.target_ids, a.fid, p.* FROM " . DB :: table('group_pic') . " p
									LEFT JOIN " . DB :: table('group_album') . " a ON a.albumid=p.albumid
									WHERE p.albumid='$id' ORDER BY dateline DESC LIMIT 0,4");
		while ($value = DB :: fetch($query)) {
			if ($value['friend'] <= 2) {
				if (empty ($setarr['icon'])) {
					$setarr['icon'] = 'album';
					$setarr['id'] = $value['albumid'];
					$setarr['idtype'] = $idtype;
					$setarr['uid'] = $value['uid'];
					$setarr['username'] = $value['username'];
					$setarr['dateline'] = $value['dateline'];
					$setarr['target_ids'] = $value['target_ids'];
					$setarr['friend'] = $value['friend'];
					$setarr['title_template'] = 'feed_album_title';
					$setarr['body_template'] = 'feed_album_body';
					$setarr['body_data'] = array (
						'album' => "<a href=\"forum.php?mod=group&action=plugin&fid=$value[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&id=$value[albumid]&groupalbum2_action=index&\">$value[albumname]</a>",
						'picnum' => $value['picnum']
					);
				}
				$setarr['image_' . $key] = pic_get($value['filepath'], 'plugin_groupalbum2', $value['thumb'], $value['remote']);
				$setarr['image_' . $key . '_link'] = "forum.php?mod=group&action=plugin&fid=$value[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&picid=$value[picid]&groupalbum2_action=index&";
				$key++;
			} else {
				break;
			}
		}
	}

	if ($setarr['icon']) {
		$setarr['title_template'] = $setarr['title_template'] ? lang('feed', $setarr['title_template']) : '';
		$setarr['body_template'] = $setarr['body_template'] ? lang('feed', $setarr['body_template']) : '';
		$setarr['body_general'] = $setarr['body_general'] ? lang('feed', $setarr['body_general']) : '';

		$setarr['title_data']['hash_data'] = "{$idtype}{$id}";
		$setarr['title_data'] = serialize($setarr['title_data']);
		$setarr['body_data'] = serialize($setarr['body_data']);
		$setarr = daddslashes($setarr);

		$feedid = 0;
		if (!$add && $setarr['id']) {
			$query = DB :: query("SELECT feedid FROM " . DB :: table('home_feed') . " WHERE id='$id' AND idtype='$idtype'");
			$feedid = DB :: result($query, 0);
		}
		if ($feedid) {
			DB :: update('home_feed', $setarr, array (
				'feedid' => $feedid
			));
		} else {
			DB :: insert('home_feed', $setarr);
		}
	}
}

function feed_publish_pic($id, $add = 0) {
	global $_G;

	require_once libfile("function/home");

	$idtype = 'gpicid';

	$setarr = array ();
	$plussql = $id > 0 ? "p.picid='$id'" : "p.uid='$_G[uid]' ORDER BY dateline DESC LIMIT 1";
	$query = DB :: query("SELECT p.*, a.friend, a.target_ids, a.fid FROM " . DB :: table('group_pic') . " p
					LEFT JOIN " . DB :: table('group_album') . " a ON a.albumid=p.albumid WHERE $plussql");
	if ($value = DB :: fetch($query)) {
		if (empty ($value['friend'])) {
			$setarr['icon'] = 'album';
			$setarr['id'] = $value['picid'];
			$setarr['idtype'] = $idtype;
			$setarr['uid'] = $value['uid'];
			$setarr['username'] = $value['username'];
			$setarr['dateline'] = $value['dateline'];
			$setarr['target_ids'] = $value['target_ids'];
			$setarr['friend'] = $value['friend'];
			$setarr['hot'] = $value['hot'];
			$url = "forum.php?mod=group&action=plugin&fid=$value[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&picid=$value[picid]&groupalbum2_action=index&";
			$setarr['image_1'] = pic_get($value['filepath'], 'plugin_groupalbum2', $value['thumb'], $value['remote']);
			$setarr['image_1_link'] = $url;
			$setarr['title_template'] = 'feed_pic_title';
			$setarr['body_template'] = 'feed_pic_body';
			$setarr['body_data'] = array (
				'title' => $value['title']
			);
		}
	}

	if ($setarr['icon']) {
		$setarr['title_template'] = $setarr['title_template'] ? lang('feed', $setarr['title_template']) : '';
		$setarr['body_template'] = $setarr['body_template'] ? lang('feed', $setarr['body_template']) : '';
		$setarr['body_general'] = $setarr['body_general'] ? lang('feed', $setarr['body_general']) : '';

		$setarr['title_data']['hash_data'] = "{$idtype}{$id}";
		$setarr['title_data'] = serialize($setarr['title_data']);
		$setarr['body_data'] = serialize($setarr['body_data']);
		$setarr = daddslashes($setarr);

		$feedid = 0;
//		if (!$add && $setarr['id']) {
//			$query = DB :: query("SELECT feedid FROM " . DB :: table('home_feed') . " WHERE id='$id' AND idtype='$idtype'");
//			$feedid = DB :: result($query, 0);
//		}
//		if ($feedid) {
//			DB :: update('home_feed', $setarr, array (
//				'feedid' => $feedid
//			));
//		} else {
			DB :: insert('home_feed', $setarr);
//		}
	}

}

function feed_view_pic($id, $add = 0) {
	global $_G;

	require_once libfile("function/home");

	$idtype = 'vgpicid';

	$setarr = array ();
	$plussql = $id > 0 ? "p.picid='$id'" : "p.uid='$_G[uid]' ORDER BY dateline DESC LIMIT 1";
	$query = DB :: query("SELECT p.*, a.friend, a.target_ids, a.fid FROM " . DB :: table('group_pic') . " p
					LEFT JOIN " . DB :: table('group_album') . " a ON a.albumid=p.albumid WHERE $plussql");
	if ($value = DB :: fetch($query)) {
		if (empty ($value['friend'])) {
			$setarr['icon'] = 'album';
			$setarr['id'] = $value['picid'];
			$setarr['idtype'] = $idtype;
			$setarr['uid'] = $_G['uid'];
			$setarr['username'] = $_G['username'];
			$setarr['dateline'] = $_G['timestamp'];
			$setarr['target_ids'] = $value['target_ids'];
			$setarr['friend'] = $value['friend'];
			$setarr['hot'] = $value['hot'];
			$url = "forum.php?mod=group&action=plugin&fid=$value[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&picid=$value[picid]&groupalbum2_action=index&";
			$setarr['image_1'] = pic_get($value['filepath'], 'plugin_groupalbum2', $value['thumb'], $value['remote']);
			$setarr['image_1_link'] = $url;
			$setarr['title_template'] = 'feed_view_pic_title';
			$setarr['body_template'] = 'feed_view_pic_body';
			$setarr['body_data'] = array (
				'title' => $value['title']
			);
		}
	}

	if ($setarr['icon']) {
		$setarr['title_template'] = $setarr['title_template'] ? lang('feed', $setarr['title_template']) : '';
		$setarr['body_template'] = $setarr['body_template'] ? lang('feed', $setarr['body_template']) : '';
		$setarr['body_general'] = $setarr['body_general'] ? lang('feed', $setarr['body_general']) : '';

		$setarr['title_data']['hash_data'] = "{$idtype}{$id}";
		$setarr['title_data'] = serialize($setarr['title_data']);
		$setarr['body_data'] = serialize($setarr['body_data']);
		$setarr = daddslashes($setarr);

		$feedid = 0;
		if (!$add && $setarr['id']) {
			$query = DB :: query("SELECT feedid FROM " . DB :: table('home_feed') . " WHERE id='$id' AND idtype='$idtype'");
			$feedid = DB :: result($query, 0);
		}
		if ($feedid) {
			DB :: update('home_feed', $setarr, array (
				'feedid' => $feedid
			));
		} else {
			DB :: insert('home_feed', $setarr);
		}
	}

}

function checkperm_group($permtype){
	global $_G;

	if(substr($permtype, 0, 6) == 'manage'){
		return $_G['forum']['ismoderator'];
	}else{
		return true;
	}
}
?>
