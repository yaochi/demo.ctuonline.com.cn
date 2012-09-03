<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_album.php 10452 2010-05-11 08:47:28Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

if($_GET['op'] == 'edit') {

	if($albumid < 1) {
		showmessage('photos_do_not_support_the_default_settings', "home.php?mod=spacecp&ac=album&uid=$_G[uid]&op=editpic&quickforward=1");
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
	if(!$album = DB::fetch($query)) {
		showmessage('no_privilege');
	}

	if($album['uid'] != $_G['uid'] && !checkperm('managealbum')) {
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
			$prenames = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
			$realnames = empty($_POST['target_realnames'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_realnames']));
			foreach($prenames as $key=>$value){
				$realvalue=user_get_user_name_by_username($value);
				if(in_array($realvalue,$realnames)){
					$names[]=$value;
				}
			}
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

		$_POST['catid'] = intval($_POST['catid']);
		if($_POST['catid'] != $album['catid']) {
			if($album['catid']) {
				db::query("UPDATE ".db::table('home_album_category')." SET num=num-1 WHERE catid='$album[catid]' AND num>0");
			}
			if($_POST['catid']) {
				db::query("UPDATE ".db::table('home_album_category')." SET num=num+1 WHERE catid='$_POST[catid]'");
			}
		}

		DB::update('home_album', array('albumname'=>$_POST['albumname'], 'catid'=>$_POST['catid'], 'friend'=>$_POST['friend'], 'password'=>$_POST['password'], 'target_ids'=>$_POST['target_ids']), array('albumid'=>$albumid));
		showmessage('spacecp_edit_ok', "home.php?mod=spacecp&ac=album&op=edit&albumid=$albumid");
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
				$realnames[]=user_get_user_name_by_username($value['username']);
			}
			$album['target_names'] = implode(' ', $names);
			$album['target_realnames'] = implode(' ', $realnames);
		}
	}

	require_once libfile('function/friend');
	$groups = friend_group_list();

	if($_G['setting']['albumcategorystat']) {
		loadcache('albumcategory');
		$category = $_G['cache']['albumcategory'];

		$categoryselect = '';
		if($category) {
			$categoryselect = "<select id=\"catid\" name=\"catid\" width=\"120\"><option value=\"0\">------</option>";
			foreach ($category as $value) {
				if($value['level'] == 0) {
					$selected = $album['catid'] == $value['catid']?' selected':'';
					$categoryselect .= "<option value=\"$value[catid]\"{$selected}>$value[catname]</option>";
					if(!$value['children']) {
						continue;
					}
					foreach ($value['children'] as $catid) {
						$selected = $album['catid'] == $catid?' selected':'';
						$categoryselect .= "<option value=\"{$category[$catid][catid]}\"{$selected}>-- {$category[$catid][catname]}</option>";
						if($category[$catid]['children']) {
							foreach ($category[$catid]['children'] as $catid2) {
								$selected = $album['catid'] == $catid2?' selected':'';
								$categoryselect .= "<option value=\"{$category[$catid2][catid]}\"{$selected}>---- {$category[$catid2][catname]}</option>";
							}
						}
					}
				}
			}
			$categoryselect .= "</select>";
		}
	}

} elseif($_GET['op'] == 'delete') {

	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
	if(!$album = DB::fetch($query)) {
		showmessage('no_privilege');
	}
	if($album['uid'] != $_G['uid'] && !checkperm('managealbum')) {
		showmessage('no_privilege');
	}

	$albums = getalbums($album['uid']);
	if(empty($albums[$albumid])) {
		showmessage('no_privilege');
	}

	if(submitcheck('deletesubmit')) {
		$_POST['moveto'] = intval($_POST['moveto']);
		if($_POST['moveto'] < 0) {
			require_once libfile('function/delete');
			deletealbums(array($albumid));
		} else {
			if($_POST['moveto'] > 0 && $_POST['moveto'] != $albumid && !empty($albums[$_POST['moveto']])) {
				DB::update('home_pic', array('albumid'=>$_POST['moveto']), array('albumid'=>$albumid));
				album_update_pic($_POST['moveto']);
			} else {
				DB::update('home_pic', array('albumid'=>0), array('albumid'=>$albumid));
			}
			DB::query("DELETE FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
		}
		showmessage('do_success', "home.php?mod=space&do=album&uid=$_G[gp_uid]&view=me");
	}
} elseif($_GET['op'] == 'editpic') {

	$managealbum = checkperm('managealbum');

	require_once libfile('class/bbcode');

	if($albumid > 0) {
		$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
		if(!$album = DB::fetch($query)) {
			showmessage('to_view_the_photo_does_not_exist', "home.php?mod=space&uid=$album[uid]&do=album&view=me");
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

					DB::update('home_pic', array('title'=>$title), $wherearr);
				} else {
					$deleteids[$picid] = $picid;
				}
			}
			if($deleteids) {
				require_once libfile('function/delete');
				deletepics($deleteids);

				if($albumid > 0) album_update_pic($albumid);
				
				$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid' LIMIT 1");
				$album = DB::fetch($query);
				if(empty($album)) {
					$url = "home.php?mod=space&do=album&view=me";
					showmessage('do_success', $url);
				}
			}

		} elseif($_GET['subop'] == 'update') {

			foreach ($_POST['title'] as $picid => $value) {
				$title = getstr($value, 150, 1, 1, 1);

				$wherearr = array('picid'=>$picid);
				if(!$managealbum) $wherearr['uid']  = $_G['uid'];

				DB::update('home_pic', array('title'=>$title), $wherearr);
			}

		} elseif($_GET['subop'] == 'move') {
			foreach ($_POST['title'] as $picid => $value) {
				$title = getstr($value, 150, 1, 1, 1);

				$wherearr = array('picid'=>$picid);
				if(!$managealbum) $wherearr['uid'] = $_G['uid'];
				DB::update('home_pic', array('title'=>$title), $wherearr);
			}
			if($_POST['ids']) {
				$plussql = $managealbum?'':"AND uid='$_G[uid]'";
				$_POST['newalbumid'] = intval($_POST['newalbumid']);
				if($_POST['newalbumid']) {
					$query = DB::query("SELECT albumid FROM ".DB::table('home_album')." WHERE albumid='$_POST[newalbumid]' $plussql");
					if(!$album = DB::fetch($query)) {
						$_POST['newalbumid'] = 0;
					}
				}
				DB::query("UPDATE ".DB::table('home_pic')." SET albumid='$_POST[newalbumid]' WHERE picid IN (".dimplode($_POST['ids']).") $plussql");
				$updatecount = DB::affected_rows();
				if($updatecount) {
					if($albumid>0) {
						DB::query("UPDATE ".DB::table('home_album')." SET picnum=picnum-$updatecount WHERE albumid='$albumid' $plussql");
						album_update_pic($albumid);
					}
					if($_POST['newalbumid']) {
						DB::query("UPDATE ".DB::table('home_album')." SET picnum=picnum+$updatecount WHERE albumid='$_POST[newalbumid]' $plussql");
						album_update_pic($_POST['newalbumid']);
					}
				}
			}

		}
		
		if($picid){
			$url="home.php?mod=space&uid=$_G[uid]&do=album&picid=$picid&tagid=";
		}else{
			$url = "home.php?mod=spacecp&ac=album&op=editpic&albumid=$albumid&page=$_POST[page]";
		}
		
		
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
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_pic')." WHERE $picsql $wheresql"), 0);
	}

	$list = array();
	if($count) {
		if($page > 1 && $start >=$count) {
			$page--;
			$start = ($page-1)*$perpage;
		}
		$bbcode = & bbcode::instance();
		$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE $picsql $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if($picid) {
				$value['checked'] = ' checked';
			}
			$value['title'] = $bbcode->html2bbcode($value['title']);
			$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
			$value['bigpic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=album&op=editpic&albumid=$albumid");

	$albumlist = getalbums($album['uid']);

} elseif($_GET['op'] == 'setpic') {

	album_update_pic($albumid, $picid);
	showmessage('do_success', dreferer(), array('picid' => $picid), array('showmsg' => true, 'closetime' => 1));

} elseif($_GET['op'] == 'edittitle') {

	$picid = empty($_GET['picid'])?0:intval($_GET['picid']);
	$uidsql = checkperm('managealbum')?'':"AND uid='$_G[uid]'";
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid='$picid' $uidsql");
	$pic = DB::fetch($query);

} elseif($_GET['op'] == 'edithot') {
	if(!checkperm('managealbum')) {
		showmessage('no_privilege');
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid='$picid'");
	if(!$pic = DB::fetch($query)) {
		showmessage('no_privilege');
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('home_pic', array('hot'=>$_POST['hot']), array('picid'=>$picid));
		if($_POST['hot'] > 0) {
			require_once libfile('function/feed');
			feed_publish($picid, 'picid');
		} else {
			DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$picid, 'idtype'=>'picid'));
		}

		showmessage('do_success', dreferer().'&quickforward=1');
	}

}elseif($_GET['op'] == 'upload'){
	if(submitcheck('addsubmit')){
		$message=$_POST['msginput'];
		if($_POST['anonymity']){
			$anonymity=$_POST['anonymity'];
		}else{
			$anonymity=$_G[member][repeatsstatus];
		}
		if($anonymity>0){
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($anonymity);
			$fid=$repeatsinfo['fid'];
		}
		
		if(empty($message)||$message=='图片说明（可选）'){
			$message='推荐图片';
		}
		if($_POST['atinput']){
			$atarray=explode(',',$_POST['atinput']);
			for($i=0;$i<count($atarray);$i++){
				$message='@'.$atarray[$i].' '.$message;
			}
		}
		if($_POST['taginput']){
			$tagarray=explode(',',$_POST['taginput']);
			for($i=0;$i<count($tagarray);$i++){		
				if($i=0){
					$message=$message.' #'.$tagarray[$i].'#';
				}else{
					$message=$message.'#'.$tagarray[$i].'#';
				}			
			}
		}
		$atjson=$_POST['atjson'];
		$picpath=explode(',',$_POST['picpath']);
		$pictitle=explode(',',$_POST['pictitle']);
		$picname=explode(',',$_POST['picname']);
		$pictype=explode(',',$_POST['pictype']);
		$picsize=explode(',',$_POST['picsize']);
		
		for($i=0;$i<count($picpath);$i++){
			if($pictitle[$i]){
			}else{
				$pictitle[$i]=$picname[$i];
			}
	
			$setarr = array(
				'albumid' => 0,
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'filename' =>urldecode($picname[$i]) ,
				'postip' => $_G['clientip'],
				'title' => urldecode($pictitle[$i]),
				'type' => urldecode($pictype[$i]),
				'size' => urldecode($picsize[$i]),
				'filepath' =>urldecode($picpath[$i]).urldecode($picname[$i]),
				'anonymity'=>$anonymity,
				'thumb' => 1
			);
			$feedpicid=DB::insert('home_pic', $setarr, 1);
			if($i<5){
				$feedarr['image_'.($i+1)]= urldecode($picpath[$i]).urldecode($picname[$i]);
				$feedarr['image_'.($i+1).'_link'] ="home.php?mod=space&uid=".$_G['uid']."&do=album&picid=".$feedpicid ;
			}
			$feedpicids[]=$feedpicid;
			
		}
		//对@解析
		if($atjson){
			$resarr=parseat1($message,$_G['uid'],$atjson);
		}else{
			$resarr=parseat($message,$_G['uid'],1);
		}
		$message=$resarr['message'];
		
		
		$feedarr['icon']='album';
		$feedarr['uid']= $_G['uid'];
		$feedarr['username']=$_G['username'];
		$feedarr['dateline']=$_G['timestamp'];
		$feedarr['body_template']=count($picpath).'张图片';
		$feedarr['body_general']=$message;
		$feedarr['target_ids']=implode(',',$feedpicids);
		$feedarr['id']=0;
		$feedarr['idtype']='albumid';
		$feedarr['sharetofids']=",".implode(',',$resarr['atfids']).",";
		$feedarr['anonymity']=$anonymity;
		$feedarr['fid']=$fid;
		
		$newfeedid=DB::insert('home_feed',$feedarr,1);
		if($resarr['atfids']){
			for($i=0;$i<count($resarr['atfids']);$i++){
				group_add_empirical_by_setting($_G['uid'],$resarr['atfids'][$i], 'at_group', $resarr['atfids'][$i]);
			}
		}
		if($resarr[atuids]){
			foreach(array_keys($resarr[atuids]) as $uidkey){
				notification_add($resarr[atuids][$uidkey],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 在其<a href="home.php?view=atme">发表的图片</a>中提到了您，赶快去看看吧', array(), 0);
			}
		}
		
		for($i=0;$i<count($feedpicids);$i++){
			DB::query("update ".DB::TABLE("home_pic")." set feedid=".$newfeedid." where picid=".$feedpicids[$i]);
		}
		
		$tagidarr=parsetag($title,$message,'albumid',$newfeedid);
		if($tagidarr){
			for($j=0;$j<count($feedpicids);$j++){
				DB::insert('pic_tag', array('picid'=>$feedpicids[$j],
				'tags'=>','.implode(',',array_keys($tagidarr)).',')
				);
			}
		}
		if($resarr[atuids]){
			atrecord($resarr[atuids],$newfeedid);
		}
		
		if($anonymity=='0'){
			DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$_G[uid]);
		}
		showmessage('do_success', dreferer());
		
	
	}
	
	
	
	
	
	
}

include_once template("home/spacecp_album");

?>