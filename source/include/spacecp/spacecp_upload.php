<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_upload.php 10707 2010-05-14 02:36:45Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);

if($_GET['op'] == 'recount') {
	$newsize = db::result(db::query("SELECT SUM(size) FROM ".db::table('home_pic')." WHERE uid='$_G[uid]'"), 0);
	db::update('common_member_count', array('attachsize'=>$newsize), array('uid'=>$_G['uid']));
	showmessage('do_success', 'home.php?mod=spacecp&ac=upload');
}

if(submitcheck('albumsubmit')) {
	if($_POST['albumop'] == 'creatalbum') {
		$_POST['albumname'] = empty($_POST['albumname'])?'':getstr($_POST['albumname'], 50, 1, 1);
		if(empty($_POST['albumname'])) $_POST['albumname'] = gmdate('Ymd');

		$_POST['friend'] = intval($_POST['friend']);

		$_POST['target_ids'] = '';
		if($_POST['friend'] == 2) {
			$uids = array();
			$names = empty($_POST['target_names'])?array():explode(' ', str_replace(array(lang('spacecp', 'tab_space'), "\r\n", "\n", "\r"), ' ', $_POST['target_names']));
			if($names) {
				$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN (".dimplode($names).")");
				while ($value = DB::fetch($query)) {
					$uids[] = $value['uid'];
				}
			}
			if(empty($uids)) {
				$_POST['friend'] = 3;
			} else {
				$_POST['target_ids'] = implode(',', $uids);
			}
		} elseif($_POST['friend'] == 4) {
			$_POST['password'] = trim($_POST['password']);
			if($_POST['password'] == '') $_POST['friend'] = 0;
		}
		if($_POST['friend'] !== 2) {
			$_POST['target_ids'] = '';
		}
		if($_POST['friend'] !== 4) {
			$_POST['password'] = '';
		}

		$setarr = array();
		$setarr['albumname'] = $_POST['albumname'];
		$setarr['catid'] = intval($_POST['catid']);
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['dateline'] = $setarr['updatetime'] = $_G['timestamp'];
		$setarr['friend'] = $_POST['friend'];
		$setarr['password'] = $_POST['password'];
		$setarr['target_ids'] = $_POST['target_ids'];

		$albumid = DB::insert('home_album', $setarr, 1);
		hook_create_resource($albumid, "home_album");//fumz创建相册,2010-9-25 17:54:22

		if($setarr['catid']) {
			db::query("UPDATE ".db::table('home_album_category')." SET num=num+1 WHERE catid='$setarr[catid]'");
		}

		if(empty($space['albumnum'])) {
			$space['albums'] = getcount('home_album', array('uid'=>$space['uid']));
			$albumnumsql = "albums=".$space['albums'];
		} else {
			$albumnumsql = 'albums=albums+1';
		}
		DB::query("UPDATE ".DB::table('common_member_count')." SET {$albumnumsql} WHERE uid='$_G[uid]'");

	} else {
		$albumid = intval($_POST['albumid']);
	}

	if($_G['mobile']) {
		showmessage('do_success', 'home.php?mod=spacecp&ac=upload');
	} else {
		echo "<script>";
		echo "parent.no_insert = 1;";
		echo "parent.albumid = $albumid;";
		echo "parent.start_upload();";
		echo "</script>";
	}
	exit();

} elseif(submitcheck('uploadsubmit')) {

	$albumid = $picid = 0;

	if(!checkperm('allowupload')) {
		if($_G['mobile']) {
			showmessage(lang('spacecp', 'not_allow_upload'));
		} else {
			echo "<script>";
			echo "alert(\"".lang('spacecp', 'not_allow_upload')."\")";
			echo "</script>";
			exit();
		}
	}

	$uploadfiles = pic_save($_FILES['attach'], $_POST['albumid'], $_POST['pic_title']);
	if($uploadfiles && is_array($uploadfiles)) {
		$albumid = $uploadfiles['albumid'];
		$picid = $uploadfiles['picid'];
		$uploadStat = 1;
		if($albumid > 0) {
			album_update_pic($albumid);
		}
		
	} else {
		$uploadStat = $uploadfiles;
	}

	//上传积分
	require_once libfile('function/credit');
	credit_create_credit_log($_G['uid'], 'uploadimage', $picid);
	//hook_create_resource($picid, "home_pic");//fumz,2010-9-25 18:00:16

	if($_G['mobile']) {
		if($picid) {
			if(ckprivacy('upload', 'feed')) {
				require_once libfile('function/feed');
				//feed_publish($albumid, 'albumid');
			}
			showmessage('do_success', "home.php?mod=space&do=album&picid=$picid");
		} else {
			showmessage($uploadStat, 'home.php?mod=spacecp&ac=upload');
		}
	} else {
		echo "<script>";
		echo "parent.albumid = $albumid;";
		echo "parent.uploadStat = '$uploadStat';";
		echo "parent.picid = $picid;";
		echo "parent.upload();";
		echo "</script>";
	}
	exit();

}elseif(submitcheck('addsubmit')){
	for($i=0;$i<=20;$i++)
	{
		$uploadfiles.$i = pic_save($_FILES['attach'.$i], $_POST['albumid'], $_POST['pic_title'.$i]);
		if($uploadfiles.$i && is_array($uploadfiles.$i)) {
			$albumid = $uploadfiles.$i['albumid'];
			$picid = $uploadfiles.$i['picid'];
			$uploadStat = 1;
			if($albumid > 0) {
				album_update_pic($albumid);
			}
			
		} else {
			$uploadStat = $uploadfiles.$i;
		}
	}
	exit();
} elseif(submitcheck('viewAlbumid')) {

	if($_POST['opalbumid'] > 0) {
		album_update_pic($_POST['opalbumid']);
	}

	if(ckprivacy('upload', 'feed')) {
		require_once libfile('function/feed');
		//feed_publish($_POST['opalbumid'], 'albumid');
	}

	$url = "home.php?mod=space&uid=$_G[uid]&do=album&quickforward=1&id=".(empty($_POST['opalbumid'])?-1:$_POST['opalbumid']);

	showmessage('upload_images_completed', $url);

} else {

	if(!checkperm('allowupload')) {
		showmessage('no_privilege');
	}
	ckrealname('album');

	ckvideophoto('album');

	cknewuser();

	$config = urlencode($_G['siteroot'].'home.php?mod=misc&ac=swfupload&op=config'.($_GET['op'] == 'cam'? '&cam=1' : ''));

	$albums = getalbums($_G['uid']);

	$actives = ($_GET['op'] == 'flash' || $_GET['op'] == 'cam')?array($_GET['op']=>' class="a"'):array('js'=>' class="a"');

	$maxspacesize = checkperm('maxspacesize');
	if(!empty($maxspacesize)) {

		space_merge($space, 'count');
		space_merge($space, 'field_home');

		$maxspacesize = $maxspacesize + $space['addsize'];
		$haveattachsize = formatsize($maxspacesize - $space['attachsize']);
	} else {
		$haveattachsize = 0;
	}

	require_once libfile('function/friend');
	$groups = friend_group_list();

	loadcache('albumcategory');
	$category = $_G['cache']['albumcategory'];

	$categoryselect = '';
	if($category) {
		$categoryselect = "<select id=\"catid\" name=\"catid\" width=\"120\"><option value=\"0\">------</option>";
		foreach ($category as $value) {
			if($value['level'] == 0) {
				$selected = $_GET['catid'] == $value['catid']?' selected':'';
				$categoryselect .= "<option value=\"$value[catid]\"{$selected}>$value[catname]</option>";
				if(!$value['children']) {
					continue;
				}
				foreach ($value['children'] as $catid) {
					$selected = $_GET['catid'] == $catid?' selected':'';
					$categoryselect .= "<option value=\"{$category[$catid][catid]}\"{$selected}>-- {$category[$catid][catname]}</option>";
					if($category[$catid]['children']) {
						foreach ($category[$catid]['children'] as $catid2) {
							$selected = $_GET['catid'] == $catid2?' selected':'';
							$categoryselect .= "<option value=\"{$category[$catid2][catid]}\"{$selected}>---- {$category[$catid2][catname]}</option>";
						}
					}
				}
			}
		}
		$categoryselect .= "</select>";
	}

}

include_once template("home/spacecp_upload");

?>