<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_album.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$id = empty($_GET['id'])?0:intval($_GET['id']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$tagid=$_GET['tagid'];

if($id) {

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	if($id > 0) {
		$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$id' AND uid='$space[uid]' LIMIT 1");
		$album = DB::fetch($query);
		if(empty($album)) {
			showmessage('to_view_the_photo_does_not_exist');
		}

		ckfriend_album($album);

		$wheresql = "albumid='$id'";
		$count = $album['picnum'];

		/*if(empty($count) && !$space['self']) {
			DB::query("DELETE FROM ".DB::table('home_album')." WHERE albumid='$id'");
			showmessage('to_view_the_photo_does_not_exist', "home.php?mod=space&uid=$album[uid]&do=album&view=me");
		}*/

		if($album['catid']) {
			$album['catname'] = db::result(db::query("SELECT catname FROM ".db::table('home_album_category')." WHERE catid='$album[catid]'"), 0);
		}

	} else {
		$wheresql = "albumid='0' AND uid='$space[uid]'";
		$count = getcount('home_pic', array('albumid'=>0, 'uid'=>$space['uid']));

		$album = array(
			'uid' => $space['uid'],
			'albumid' => -1,
			'albumname' => lang('space', 'default_albumname'),
			'picnum' => $count
		);
	}

	$albumlist = array();
	$maxalbum = $nowalbum = $key = 0;
	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE uid='$space[uid]' ORDER BY updatetime DESC LIMIT 0, 100");
	while ($value = DB::fetch($query)) {
		if($value['friend'] != 4 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
			$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
		} elseif ($value['picnum']) {
			$value['pic'] = STATICURL.'image/common/nopublish.gif';
		} else {
			$value['pic'] = '';
		}
		$albumlist[$key][$value['albumid']] = $value;
		$key = count($albumlist[$key]) == 5 ? ++$key : $key;
	}
	$maxalbum = count($albumlist);

	$list = array();
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
			$list[] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=space&uid=$album[uid]&do=$do&id=$id#comment");

	$actives = array('me' =>' class="a"');

	$_G['home_css'] = 'album';

	$diymode = intval($_G['cookie']['home_diymode']);
	include_once template("diy:home/space_album_view");

} elseif ($picid) {

	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid='$picid' AND uid='$space[uid]' LIMIT 1");
	if(!$pic = DB::fetch($query)) {
		showmessage('view_images_do_not_exist');
	}

	$picid = $pic['picid'];
	$theurl = "home.php?mod=space&uid=$pic[uid]&do=$do&picid=$picid";

	$album = array();
	if($pic['albumid']) {
		$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$pic[albumid]'");
		if(!$album = DB::fetch($query)) {
			DB::update('home_pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));
		}
	}

	/*if($album) {
		ckfriend_album($album);
		$wheresql = "albumid='$pic[albumid]'";
	} else {
		$album['picnum'] = getcount('home_pic', array('uid'=>$pic['uid'], 'albumid'=>0));
		$album['albumid'] = $pic['albumid'] = '-1';
		$wheresql = "uid='$space[uid]' AND albumid='0'";
	}*/
	
	if($tagid) {
		$wheresql = "uid='$space[uid]' and pt.tags like '%,".$tagid.",%'";
	} else {
		$album['picnum'] = getcount('home_pic', array('uid'=>$pic['uid'], 'albumid'=>0));
		$album['albumid'] = $pic['albumid'] = '-1';
		$wheresql = "uid='$space[uid]'";
	}
	
	if($space[uid]!=$_G[uid]){
		$wheresql=$wheresql." and anonymity =0 ";
	}
	
	$piclist = $list = $keys = array();
	$keycount = 0;
	$query = DB::query("select hp.*,pt.tags FROM ".DB::table('home_pic')." as hp left join ".DB::TABLE("pic_tag")." as pt on hp.picid=pt.picid where $wheresql order by hp.dateline desc");
	while ($value = DB::fetch($query)) {
		$keys[$value['picid']] = $keycount;
		$list[$keycount] = $value;
		$keycount++;
	}

	$upid = $nextid = 0;
	$nowkey = $keys[$picid];
	$endkey = $keycount - 1;
	if($endkey>4) {
		$newkeys = array($nowkey-2, $nowkey-1, $nowkey, $nowkey+1, $nowkey+2);
		if($newkeys[1] < 0) {
			$newkeys[0] = $endkey-1;
			$newkeys[1] = $endkey;
		} elseif($newkeys[0] < 0) {
			$newkeys[0] = $endkey;
		}
		if($newkeys[3] > $endkey) {
			$newkeys[3] = 0;
			$newkeys[4] = 1;
		} elseif($newkeys[4] > $endkey) {
			$newkeys[4] = 0;
		}
		$upid = $list[$newkeys[1]]['picid'];
		$nextid = $list[$newkeys[3]]['picid'];

		foreach ($newkeys as $nkey) {
			$piclist[] = $list[$nkey];
		}
	} else {
		$newkeys = array($nowkey-1, $nowkey, $nowkey+1);
		if($newkeys[0] < 0) {
			$newkeys[0] = $endkey;
		}
		if($newkeys[2] > $endkey) {
			$newkeys[2] = 0;
		}
		$upid = $list[$newkeys[0]]['picid'];
		$nextid = $list[$newkeys[2]]['picid'];

		$piclist = $list;
	}
	foreach ($piclist as $key => $value) {
		if(strpos($value['filepath'],'attachment/album')){
			$filepath=explode('.',$value['filepath']);
			$value['pic']=$filepath[0].'.thumb.'.$value[type];
		}else{
			$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
		}
		$piclist[$key] = $value;
	}

	if(strpos($pic['filepath'],'attachment/album')){
		$pic['pic']=$pic['filepath'];
	}else{
		$pic['pic'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote'], 0);
	}
	$pic['size'] = formatsize($pic['size']);

	$exifs = array();
	$allowexif = function_exists('exif_read_data');
	if(isset($_GET['exif']) && $allowexif) {
		require_once libfile('function/exif');
		$exifs = getexif($pic['pic']);
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$csql = $cid?"cid='$cid' AND":'';
	$siteurl = getsiteurl();
	$list = array();
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_comment')." WHERE $csql id='$pic[picid]' AND idtype='picid'"),0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE $csql id='$pic[picid]' AND idtype='picid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if($value[anonymity]>0){
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value[anonymity]);
				$value[repeats]=$repeatsinfo;
			}elseif($value[anonymity]=='-1'){
				$value[authorid]=-1;
				$value[author]="匿名";
			}
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);

	if(empty($album['albumname'])) $album['albumname'] = lang('space', 'default_albumname');

	$pic_url = $pic['pic'];
	if(!preg_match("/^http\:\/\/.+?/i", $pic['pic'])) {
		$pic_url = getsiteurl().$pic['pic'];
	}
	$pic_url2 = rawurlencode($pic['pic']);

	$hash = md5($pic['uid']."\t".$pic['dateline']);
	$id = $pic['picid'];
	$idtype = 'picid';

	$maxclicknum = 0;
	loadcache('click');
	$clicks = empty($_G['cache']['click']['picid'])?array():$_G['cache']['click']['picid'];
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $pic["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}
	
	$clickuserlist = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,20");
	while ($value = DB::fetch($query)) {
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		if($value['anonymity']>0){
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($value['anonymity']);
			$value[repeats]=$repeatsinfo;
		}
		$clickuserlist[] = $value;
	}
	$actives = array('me' =>' class="a"');

	if($album['picnum']) {
		if($_GET['goto']=='down') {
			$sequence = empty($_G['cookie']['pic_sequence'])?$album['picnum']:intval($_G['cookie']['pic_sequence']);
			$sequence++;
			if($sequence>$album['picnum']) {
				$sequence = 1;
			}
		} elseif($_GET['goto']=='up') {
			$sequence = empty($_G['cookie']['pic_sequence'])?$album['picnum']:intval($_G['cookie']['pic_sequence']);
			$sequence--;
			if($sequence<1) {
				$sequence = $album['picnum'];
			}
		} else {
			$sequence = 1;
		}
		dsetcookie('pic_sequence', $sequence);
	}
	$diymode=1;
	if(!$diymode){
		$diymode = intval($_G['cookie']['home_diymode']);
	}else{
		$_G['cookie']['home_diymode']=1;
	}

	include_once template("diy:home/space_album_pic");


} else {
	$op=$_GET['op'];
	if($op=='pic'){
		if($_GET['from'] == 'space') $diymode = 1;

		$perpage = 20;
		$perpage = mob_perpage($perpage);
	
		$start = ($page-1)*$perpage;
	
		ckstart($start, $perpage);
		
		$wheresql = "uid='$space[uid]'";
		$tagarr=DB::fetch_first("select * from ".DB::TABLE('common_user_tag')." where $wheresql");
		$tagsid=unserialize($tagarr['tags']);
		if($tagsid[albumid]){
			$tagquery=DB::query("select * from ".DB::TABLE("home_tag")." where id in (".$tagsid[albumid].")");
			while($tagvalue=DB::fetch($tagquery)){
				$taglist[]=$tagvalue;
			}
		}
		
		if($tagid){
			$wheresql =$wheresql ." and pt.tags like '%,".$tagid.",%'";
		}
		
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_pic')." as hp left join ".DB::TABLE("pic_tag")." as pt on hp.picid=pt.picid WHERE $wheresql"),0);
		if($count){
			$query=DB::query("select hp.*,pt.tags FROM ".DB::table('home_pic')." as hp left join ".DB::TABLE("pic_tag")." as pt on hp.picid=pt.picid where $wheresql order by hp.dateline desc");
			while($value=DB::fetch($query)){
				if(strpos($value['filepath'],'attachment/album')){
					$filepath=explode('.',$value['filepath']);
					$value['pic']=$filepath[0].'.thumb.'.$value[type];
				}else{
					$value['pic']=pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
				}
				
				$list[]=$value;
			}
			
			
		}
		
	}else{
		loadcache('albumcategory');
		$category = $_G['cache']['albumcategory'];
	
		$perpage = 20;
		$perpage = mob_perpage($perpage);
	
		$start = ($page-1)*$perpage;
	
		ckstart($start, $perpage);
	
		$_GET['friend'] = intval($_GET['friend']);
	
		$default = array();
		$f_index = '';
		$list = array();
		$pricount = 0;
		$picmode = 0;
	
		if(empty($_GET['view'])) {
			$_GET['view'] = 'me';
		}
	
		$gets = array(
			'mod' => 'space',
			'uid' => $space['uid'],
			'do' => 'album',
			'view' => $_GET['view'],
			'catid' => $_GET['catid'],
			'order' => $_GET['order'],
			'fuid' => $_GET['fuid'],
			'searchkey' => $_GET['searchkey'],
			'from' => $_GET['from']
		);
		$theurl = 'home.php?'.url_implode($gets);
		$actives = array($_GET['view'] =>' class="a"');
	
		$need_count = true;
	
		if($_GET['view'] == 'all') {
	
			$wheresql = '1';
	
			//if($_GET['order'] == 'hot') {
				$orderactives = array('hot' => ' class="a"');
				$picmode = 1;
				$need_count = false;
	
				$ordersql = 'p.dateline';
				$wheresql = "p.hot>='$minhot'";
	
				$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_pic')." p WHERE $wheresql"),0);
				if($count) {
					$query = DB::query("SELECT p.*, a.albumname, a.friend, a.target_ids FROM ".DB::table('home_pic')." p
						LEFT JOIN ".DB::table('home_album')." a ON a.albumid=p.albumid
						WHERE $wheresql
						ORDER BY $ordersql DESC
						LIMIT $start,$perpage");
					while ($value = DB::fetch($query)) {
						if($value['friend'] != 4 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
							$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
							$list[] = $value;
						} else {
							$pricount++;
						}
					}
				}
			/*} else {
				$orderactives = array('dateline' => ' class="a"');
			}*/
	
		}else {
	
			if($_GET['from'] == 'space') $diymode = 1;
	
			$wheresql = "uid='$space[uid]'";
		}
	
		if($need_count) {
			$perpage = 18;
			$perpage = mob_perpage($perpage);
		
			$start = ($page-1)*$perpage;
		
			ckstart($start, $perpage);
			
			$wheresql = "uid='$space[uid]'";
			$tagarr=DB::fetch_first("select * from ".DB::TABLE('common_user_tag')." where $wheresql");
			$tagsid=unserialize($tagarr['tags']);
			if($tagsid[albumid]){
				$tagquery=DB::query("select * from ".DB::TABLE("home_tag")." where id in (".$tagsid[albumid].")");
				while($tagvalue=DB::fetch($tagquery)){
					$taglist[]=$tagvalue;
				}
			}
			
			if($tagid){
				$wheresql =$wheresql ." and pt.tags like '%,".$tagid.",%'";
			}
			
			$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_pic')." as hp left join ".DB::TABLE("pic_tag")." as pt on hp.picid=pt.picid WHERE $wheresql"),0);
			if($count){
				$query=DB::query("select hp.*,pt.tags FROM ".DB::table('home_pic')." as hp left join ".DB::TABLE("pic_tag")." as pt on hp.picid=pt.picid where $wheresql order by hp.dateline desc limit $start, $perpage");
				while($value=DB::fetch($query)){
					if(strpos($value['filepath'],'attachment/album')){
						$filepath=explode('.',$value['filepath']);
						$value['pic']=$filepath[0].'.thumb.'.$value[type];
					}else{
						$value['pic']=pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
					}
					
					$list[]=$value;
				}
				
				
			}
			
		}
	
		$multi = multi($count, $perpage, $page, $theurl);
	
		dsetcookie('home_diymode', $diymode);
	}

	include_once template("diy:home/space_album_list");
}

function ckfriend_album($album) {
	global $_G, $space;

	if(!ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
		if(empty($_G['uid'])) {
			showmessage('to_login', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
		}
		require_once libfile('function/friend');
		$isfriend = friend_check($album['uid']);
		space_merge($space, 'count');
		space_merge($space, 'profile');
		include template('home/space_privacy');
		exit();
	} elseif(!$space['self'] && $album['friend'] == 4) {
		$cookiename = "view_pwd_album_$album[albumid]";
		$cookievalue = empty($_G['cookie'][$cookiename])?'':$_G['cookie'][$cookiename];
		if($cookievalue != md5(md5($album['password']))) {
			$invalue = $album;
			include template('home/misc_inputpwd');
			exit();
		}
	}
}

?>