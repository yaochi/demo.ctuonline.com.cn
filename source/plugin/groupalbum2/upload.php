<?php
/* Function: 上传图片
 * Com.:
 * Author: wuhan
 * Date: 2010-7-13
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/space');
require_once libfile('function/spacecp');

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);

if($_GET['op'] == 'recount') {
	$newsize = db::result(db::query("SELECT SUM(size) FROM ".db::table('group_pic')." WHERE uid='$_G[uid]'"), 0);
	db::update('common_member_count', array('attachsize'=>$newsize), array('uid'=>$_G['uid']));
	showmessage('do_success', join_plugin_action2('upload'));
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
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['dateline'] = $setarr['updatetime'] = $_G['timestamp'];
		$setarr['friend'] = $_POST['friend'];
		$setarr['password'] = $_POST['password'];
		$setarr['target_ids'] = $_POST['target_ids'];
		$setarr['fid'] = $_G['fid'];

		$albumid = DB::insert('group_album', $setarr, 1);

		 hook_create_resource($albumid, "ablum",$_G['fid']);
//		不对个人相册记数
//		if(empty($space['albumnum'])) {
//			$space['albums'] = getcount('group_album', array('uid'=>$space['uid']));
//			$albumnumsql = "albums=".$space['albums'];
//		} else {
//			$albumnumsql = 'albums=albums+1';
//		}
//		DB::query("UPDATE ".DB::table('common_member_count')." SET {$albumnumsql} WHERE uid='$_G[uid]'");

	} else {
		$albumid = intval($_POST['albumid']);
	}

	if($_G['mobile']) {
		showmessage('do_success', join_plugin_action2('upload'));
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

	if(!checkperm_group('allowupload')) {
		if($_G['mobile']) {
			showmessage(lang('spacecp', 'not_allow_upload'));
		} else {
			echo "<script>";
			echo "alert(\"".lang('spacecp', 'not_allow_upload')."\")";
			echo "</script>";
			exit();
		}
	}

	$uploadfiles = pic_save_group($_FILES['attach'], $_POST['albumid'], $_POST['pic_title']);
	if($uploadfiles && is_array($uploadfiles)) {
		$albumid = $uploadfiles['albumid'];
		$picid = $uploadfiles['picid'];
		$uploadStat = 1;
		if($albumid > 0) {
			album_update_pic_group($albumid);
		}
	} else {
		$uploadStat = $uploadfiles;
	}

	if($picid) {
		if(ckprivacy('upload', 'feed')) {
			feed_publish_pic($picid);
		}
		// hook_create_resource($picid, "pic");
		//上传经验值
		require_once libfile('function/group');
		group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'photo_upload', $picid);
		
		//上传积分
		require_once libfile('function/credit');
		credit_create_credit_log($_G['uid'], 'uploadpicture', $picid);
	}

	if($_G['mobile']) {
		if($picid) {
			showmessage('do_success', join_plugin_action2('albumcp', array('picid'=>$picid)));
		} else {
			showmessage($uploadStat, join_plugin_action2('upload'));
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

} elseif(submitcheck('viewAlbumid')) {

	if($_POST['opalbumid'] > 0) {
		album_update_pic_group($_POST['opalbumid']);
	}

	if(ckprivacy('upload', 'feed')) {
		feed_publish_album($_POST['opalbumid']);
	}
	
	$url = join_plugin_action2('index', array('quickforward' => '1', 'id' => (empty($_POST['opalbumid'])?-1:$_POST['opalbumid'])));

	showmessage('upload_images_completed', $url);

}
if($_G['gp_op'] == 'select'){
	//$myalbums = getalbums($_G['uid']);
	$albums = getalbums_group($_G['uid']);
	$tagarr=DB::fetch_first("select * from ".DB::TABLE('common_user_tag')." where uid=$_G[uid]");
	$tagsid=unserialize($tagarr['tags']);
	if($tagsid){
		$tagquery=DB::query("select * from ".DB::TABLE("home_tag")." where id in (".$tagsid[albumid].")");
		while($tagvalue=DB::fetch($tagquery)){
			$taglist[]=$tagvalue;
		}
	}
	$actives = array($_GET['op'] => ' class="a"');

	if(submitcheck('selectsubmit')){
		$albumid = empty($_POST['ablumid'])?0:intval($_POST['ablumid']);
		if($_POST['picids']){
			$picids = $_POST['picids'];
			
			$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " WHERE albumid='$albumid' AND fid='$_G[fid]' LIMIT 1");
			$album = DB :: fetch($query);
			if (empty ($album)) {
				showmessage('to_view_the_photo_album_does_not_exist', join_plugin_action2('index'));
			}
			
			require_once libfile('class/upload');
			$upload = new discuz_upload();
			$targetdir = $upload->get_target_dir('plugin_groupalbum2');
			
			$piccount = 0;
			$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE uid='$_G[uid]' AND picid IN (".dimplode($picids).")");
			while ($value = DB::fetch($query)){
				//复制文件
				if(strpos($value['filepath'],'attachment/album')){
					$value['filepath']=substr($value['filepath'],22);
					$filearr=explode('.',$value['filepath']);
				}
				$source = getglobal('setting/attachdir').'./album/'.$value['filepath'];
				$filepath = $targetdir.$upload->get_target_filename('plugin_groupalbum2',0,'').$upload->get_target_extension($upload->fileext($value['filename']));
				$target = getglobal('setting/attachdir').'./plugin_groupalbum2/'.$filepath;
				copy($source, $target);
				if($value['thumb']){
					if($filearr){
						$source=getglobal('setting/attachdir').'./album/'.$filearr[0];
					}
					echo($source);
					$source = $source.'.thumb.jpg';
					$target = $target.'.thumb.jpg';
					copy($source, $target);
				}
				$setarr = array (
					'albumid' => $albumid,
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
				$setarr['picid'] = DB :: insert('group_pic', $setarr, 1);
				$piccount++;
			}
			if($piccount){
				album_update_pic_group($albumid);
			}
		}
		showmessage('do_success', join_plugin_action2('index', array('id'=>$albumid)));
	}	
}else{
	if(!checkperm_group('allowupload')) {
		showmessage('no_privilege');
	}
	ckrealname('album');

	ckvideophoto('album');

	cknewuser();

	$config = urlencode(join_plugin_action2('swfupload').'&op=config'.($_GET['op'] == 'cam'? '&cam=1' : ''));

	global $_G;

	

	$albums = getalbums_group($_G['uid']);
//	$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE uid='$_G[uid]' AND fid='$_G[fid]' ORDER BY albumid DESC");
//	while ($value = DB::fetch($query)) {
//		$albums[$value['albumid']] = $value;
//	}

	$actives = ($_GET['op'] == 'flash' || $_GET['op'] == 'cam' || $_GET['op'] == 'select')?array($_GET['op']=>' class="a"'):array('js'=>' class="a"');

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

//	loadcache('albumcategory');
//	$category = $_G['cache']['albumcategory'];
//
//	$categoryselect = '';
//	if($category) {
//		$categoryselect = "<select id=\"catid\" name=\"catid\" width=\"120\"><option value=\"0\">------</option>";
//		foreach ($category as $value) {
//			if($value['level'] == 0) {
//				$selected = $_GET['catid'] == $value['catid']?' selected':'';
//				$categoryselect .= "<option value=\"$value[catid]\"{$selected}>$value[catname]</option>";
//				if(!$value['children']) {
//					continue;
//				}
//				foreach ($value['children'] as $catid) {
//					$selected = $_GET['catid'] == $catid?' selected':'';
//					$categoryselect .= "<option value=\"{$category[$catid][catid]}\"{$selected}>-- {$category[$catid][catname]}</option>";
//					if($category[$catid]['children']) {
//						foreach ($category[$catid]['children'] as $catid2) {
//							$selected = $_GET['catid'] == $catid2?' selected':'';
//							$categoryselect .= "<option value=\"{$category[$catid2][catid]}\"{$selected}>---- {$category[$catid2][catname]}</option>";
//						}
//					}
//				}
//			}
//		}
//		$categoryselect .= "</select>";
//	}
}
include template("groupalbum2:upload");

dexit();
?>
