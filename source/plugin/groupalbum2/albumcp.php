<?php
/* Function: 专区相册demo，修改,删除图片或相册
 * Com.:
 * Author: wuhan
 * Date: 2010-7-12
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/spacecp');

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

if($_GET['op'] == 'edit') {

	if($albumid < 1) {
		showmessage('photos_do_not_support_the_default_settings', join_plugin_action2('albumcp', array('op'=>'eidtpic','quickforward'=>'1')));
	}

	$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid='$albumid'");
	if(!$album = DB::fetch($query)) {
		showmessage('no_privilege');
	}

	if($album['uid'] != $_G['uid'] && !checkperm_group('managealbum')) {
		showmessage('no_privilege');
	}

	if(submitcheck('editsubmit')) {
		$_POST['albumname'] = getstr($_POST['albumname'], 50, 1, 1, 1);
		if(empty($_POST['albumname'])) {
			showmessage('album_name_errors');
		}

		$_POST['friend'] = intval($_POST['friend']);
		$_POST['target_ids'] = '';
		if($_POST['friend'] == 2) {
			$uids = array();
			$names = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
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
			$_POST['password'] == '';
		}

//		$_POST['catid'] = intval($_POST['catid']);
//		if($_POST['catid'] != $album['catid']) {
//			if($album['catid']) {
//				db::query("UPDATE ".db::table('home_album_category')." SET num=num-1 WHERE catid='$album[catid]' AND num>0");
//			}
//			if($_POST['catid']) {
//				db::query("UPDATE ".db::table('home_album_category')." SET num=num+1 WHERE catid='$_POST[catid]'");
//			}
//		}

		DB::update('group_album', array('albumname'=>$_POST['albumname'], 'friend'=>$_POST['friend'], 'password'=>$_POST['password'], 'target_ids'=>$_POST['target_ids']), array('albumid'=>$albumid));
		showmessage('spacecp_edit_ok', join_plugin_action2('albumcp', array('op'=> 'edit', 'albumid'=>$albumid)));
	}

	$album['target_names'] = '';

	$friendarr = array($album['friend'] => ' selected');

	$passwordstyle = $selectgroupstyle = 'display:none';
	if($album['friend'] == 4) {
		$passwordstyle = '';
	} elseif($album['friend'] == 2) {
		$selectgroupstyle = '';
		if($album['target_ids']) {
			$names = array();
			$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN ($album[target_ids])");
			while ($value = DB::fetch($query)) {
				$names[] = $value['username'];
			}
			$album['target_names'] = implode(' ', $names);
		}
	}

	require_once libfile('function/friend');


	$groups = friend_group_list();

//	if($_G['setting']['albumcategorystat']) {
//		loadcache('albumcategory');
//		$category = $_G['cache']['albumcategory'];
//
//		$categoryselect = '';
//		if($category) {
//			$categoryselect = "<select id=\"catid\" name=\"catid\" width=\"120\"><option value=\"0\">------</option>";
//			foreach ($category as $value) {
//				if($value['level'] == 0) {
//					$selected = $album['catid'] == $value['catid']?' selected':'';
//					$categoryselect .= "<option value=\"$value[catid]\"{$selected}>$value[catname]</option>";
//					if(!$value['children']) {
//						continue;
//					}
//					foreach ($value['children'] as $catid) {
//						$selected = $album['catid'] == $catid?' selected':'';
//						$categoryselect .= "<option value=\"{$category[$catid][catid]}\"{$selected}>-- {$category[$catid][catname]}</option>";
//						if($category[$catid]['children']) {
//							foreach ($category[$catid]['children'] as $catid2) {
//								$selected = $album['catid'] == $catid2?' selected':'';
//								$categoryselect .= "<option value=\"{$category[$catid2][catid]}\"{$selected}>---- {$category[$catid2][catname]}</option>";
//							}
//						}
//					}
//				}
//			}
//			$categoryselect .= "</select>";
//		}
//	}

} elseif($_GET['op'] == 'delete') {

	$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid='$albumid'");
	if(!$album = DB::fetch($query)) {
		showmessage('no_privilege');
	}
	if($album['uid'] != $_G['uid'] && !checkperm_group('managealbum')) {
		showmessage('no_privilege');
	}

	$albums = getalbums_group($album['uid']);
	if(empty($albums[$albumid])) {
		showmessage('no_privilege');
	}

	if(submitcheck('deletesubmit')) {
		$_POST['moveto'] = intval($_POST['moveto']);
		if($_POST['moveto'] < 0) {
			require_once libfile('function/delete');
			deletealbums_group(array($albumid));
			hook_delete_resource($albumid, "ablum");
		} else {
			if($_POST['moveto'] > 0 && $_POST['moveto'] != $albumid && !empty($albums[$_POST['moveto']])) {
				DB::update('group_pic', array('albumid'=>$_POST['moveto']), array('albumid'=>$albumid));
				album_update_pic_group($_POST['moveto']);
			} else {
				DB::update('group_pic', array('albumid'=>0), array('albumid'=>$albumid));
			}
			DB::query("DELETE FROM ".DB::table('group_album')." WHERE albumid='$albumid'");
			hook_delete_resource($albumid, "ablum");
		}
		showmessage('do_success', join_plugin_action2('index', array('picid'=>$picid)));
	}
} elseif($_GET['op'] == 'editpic') {

	$managealbum = checkperm_group('managealbum');

	require_once libfile('class/bbcode');

	if($albumid > 0) {
		$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid='$albumid'");
		if(!$album = DB::fetch($query)) {
			showmessage('to_view_the_photo_does_not_exist', join_plugin_action2('index'));
		}

		if($album['uid'] != $_G['uid'] && !$managealbum) {
			showmessage('no_privilege');
		}
	} else {
		$album['uid'] = $_G['uid'];
	}
	if(submitcheck('editpicsubmit')) {
		if($_GET['subop'] == 'delete') {
			$updates = $deleteids = array();
			foreach ($_POST['title'] as $picid => $value) {
				if(empty($_POST['ids'][$picid])) {
					$title = getstr($value, 150, 1, 1, 1);

					$wherearr = array('picid'=>$picid);
					if(!$managealbum) $wherearr['uid'] = $_G['uid'];

					DB::update('group_pic', array('title'=>$title), $wherearr);
				} else {
					$deleteids[$picid] = $picid;
				}
			}
			if($deleteids) {

				deletepics_group($deleteids);

				if($albumid > 0) album_update_pic_group($albumid);

				$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid='$albumid' LIMIT 1");
				$album = DB::fetch($query);
				if(empty($album)) {
					showmessage('do_success', join_plugin_action2('index'));
				}
			}

		} elseif($_GET['subop'] == 'update') {

			foreach ($_POST['title'] as $picid => $value) {
				$title = getstr($value, 150, 1, 1, 1);

				$wherearr = array('picid'=>$picid);
				if(!$managealbum) $wherearr['uid']  = $_G['uid'];

				DB::update('group_pic', array('title'=>$title), $wherearr);
			}

		} elseif($_GET['subop'] == 'move') {
			foreach ($_POST['title'] as $picid => $value) {
				$title = getstr($value, 150, 1, 1, 1);

				$wherearr = array('picid'=>$picid);
				if(!$managealbum) $wherearr['uid'] = $_G['uid'];
				DB::update('group_pic', array('title'=>$title), $wherearr);
			}
			if($_POST['ids']) {
				$plussql = $managealbum?'':"AND uid='$_G[uid]'";
				$_POST['newalbumid'] = intval($_POST['newalbumid']);
				if($_POST['newalbumid']) {
					$query = DB::query("SELECT albumid FROM ".DB::table('group_album')." WHERE albumid='$_POST[newalbumid]' $plussql");
					if(!$album = DB::fetch($query)) {
						$_POST['newalbumid'] = 0;
					}
				}
				DB::query("UPDATE ".DB::table('group_pic')." SET albumid='$_POST[newalbumid]' WHERE picid IN (".dimplode($_POST['ids']).") $plussql");
				$updatecount = DB::affected_rows();
				if($updatecount) {
					if($albumid>0) {
						DB::query("UPDATE ".DB::table('group_album')." SET picnum=picnum-$updatecount WHERE albumid='$albumid' $plussql");
						album_update_pic_group($albumid);
					}
					if($_POST['newalbumid']) {
						DB::query("UPDATE ".DB::table('group_album')." SET picnum=picnum+$updatecount WHERE albumid='$_POST[newalbumid]' $plussql");
						album_update_pic_group($_POST['newalbumid']);
					}
					//发送移动相册通知
					$albumcategory = array();
					$query = DB::query("SELECT albumid, albumname FROM ".DB::table('group_album')." WHERE albumid IN ('$_POST[newalbumid]', '$albumid') $plussql");
					while($album = DB::fetch($query)){
						$albumcategory[$album['albumid']] = $album;
					}
					$query = DB::query("SELECT * FROM ".DB::table('group_pic')." WHERE picid IN (".dimplode($_POST['ids']).") $plussql");
					while($pic = DB::fetch($query)){
						if($pic['uid'] != $_G['uid']){
							notification_add($pic['uid'], 'gpic', 'gpic_move', array('actor' => "<a href=\"home.php?mod=space&uid=$pic[uid]\" target=\"_blank\">$pic[username]</a>", 'pic' => $pic['title'], 'group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">".$_G['forum']['name']."</a>", 'album1' => $albumcategory[$albumid]['albumname'], 'album2' => $albumcategory[$_POST['newalbumid']]['albumname']), 1);
						}
					}
				}
			}
		}

		$url = join_plugin_action2('albumcp', array('op'=>'editpic', 'albumid'=>$albumid, 'page'=> "$_POST[page]"));
		showmessage('do_success', $url);
	}

	$perpage = 10;
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$picsql = $picid?"picid='$picid' AND ":'';

	if($albumid > 0) {
		$wheresql = "albumid='$albumid'";
		$count = $picid?1:$album['picnum'];
	} else {
		$wheresql = "albumid='0' AND uid='$_G[uid]'";
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('group_pic')." WHERE $picsql $wheresql"), 0);
	}

	$list = array();
	if($count) {
		if($page > 1 && $start >=$count) {
			$page--;
			$start = ($page-1)*$perpage;
		}
		$bbcode = & bbcode::instance();
		$query = DB::query("SELECT * FROM ".DB::table('group_pic')." WHERE $picsql $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if($picid) {
				$value['checked'] = ' checked';
			}
			$value['title'] = $bbcode->html2bbcode($value['title']);
			$value['pic'] = pic_get($value['filepath'], 'plugin_groupalbum2', $value['thumb'], $value['remote']);
			$value['bigpic'] = pic_get($value['filepath'], 'plugin_groupalbum2', $value['thumb'], $value['remote'], 0);
			$list[] = $value;
		}
	}
	$albumid=$_GET[albumid];
	$url="forum.php?mod=group&action=plugin&fid=".$_G [fid]."&plugin_name=groupalbum2&plugin_op=groupmenu&op=editpic&albumid=".$albumid."&groupalbum2_action=albumcp";
	$multi = multi($count, $perpage, $page,$url);

	$albumlist = getalbums_group($album['uid']);

} elseif($_GET['op'] == 'setpic') {

	album_update_pic_group($albumid, $picid);
	showmessage('do_success', dreferer(), array('picid' => $picid), array('showmsg' => true, 'closetime' => 1));

} elseif($_GET['op'] == 'edittitle') {

	$picid = empty($_GET['picid'])?0:intval($_GET['picid']);
	$uidsql = checkperm_group('managealbum')?'':"AND uid='$_G[uid]'";
	$query = DB::query("SELECT * FROM ".DB::table('group_pic')." WHERE picid='$picid' $uidsql");
	$pic = DB::fetch($query);

} elseif($_GET['op'] == 'edithot') {
	if(!checkperm_group('managealbum')) {
		showmessage('no_privilege');
	}

	$query = DB::query("SELECT * FROM ".DB::table('group_pic')." WHERE picid='$picid'");
	if(!$pic = DB::fetch($query)) {
		showmessage('no_privilege');
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('group_pic', array('hot'=>$_POST['hot']), array('picid'=>$picid));
		if($_POST['hot'] > 0) {
			require_once libfile('function/feed');
			feed_publish($picid, 'gpicid');
		} else {
			DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$picid, 'idtype'=>'gpicid'));
		}

		showmessage('do_success', dreferer().'&quickforward=1');
	}

}elseif($_GET['op'] == 'mypic'){
	$tagid=$_GET['tagid'];
//	$perpage = 20;
//	$perpage = mob_perpage($perpage);
//
//	$start = ($page -1) * $perpage;
//
//	ckstart($start, $perpage);
	
	$wheresql = "uid='$_G[uid]'";
	if($tagid){
		$wheresql =$wheresql ." and pt.tags like '%,".$tagid.",%'";
	}
		
	
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_pic')." as hp left join ".DB::TABLE("pic_tag")." as pt on hp.picid=pt.picid WHERE $wheresql"),0);
	$list = array ();
	if ($count) {
		$query=DB::query("select hp.*,pt.tags FROM ".DB::table('home_pic')." as hp left join ".DB::TABLE("pic_tag")." as pt on hp.picid=pt.picid where $wheresql order by hp.dateline desc");
		while ($value = DB :: fetch($query)) {
			if(strpos($value['filepath'],'attachment/album')){
					$filepath=explode('.',$value['filepath']);
					$value['pic']=$filepath[0].'.thumb.'.$value[type];
				}else{
					$value['pic']=pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
				}
				
			$list[] = $value;
		}
	}
}

include template("groupalbum2:albumcp");

dexit();
?>
