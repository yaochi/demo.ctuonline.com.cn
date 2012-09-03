<?php
/* Function: 专区相册demo，显示相册列表, 单个相册和相册图片
 * 1	对于表态,评论 idtype = gpicid
 * Com.:
 * Author: wuhan
 * Date: 2010-7-12
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

require_once libfile('function/home');

if (empty ($_G['fid'])) {
	showmessage('group_rediret_now', 'group.php');
}

$minhot = $_G['setting']['feedhotmin'] < 1 ? 3 : $_G['setting']['feedhotmin'];
$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);
$picid = empty ($_GET['picid']) ? 0 : intval($_GET['picid']);

$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1)
	$page = 1;

if ($id) {

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	if ($id > 0) {
		$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " WHERE albumid='$id' AND fid='$_G[fid]' LIMIT 1");
		$album = DB :: fetch($query);
		if (empty ($album)) {
			showmessage('to_view_the_photo_does_not_exist');
		}

		ckfriend_album_group($album);

		$wheresql = "albumid='$id'";
		$count = $album['picnum'];

		if (empty ($count) && $_G['uid'] != $album['uid'])	{
			DB :: query("DELETE FROM " . DB :: table('group_album') . " WHERE albumid='$id'");
			showmessage('to_view_the_photo_does_not_exist', join_plugin_action2('index'));
		}

	} else {
		$wheresql = "albumid='0'";
		$count = getcount('group_pic', array (
			'albumid' => 0,
			'uid' => $_G['uid']
		));

		$album = array (
			'uid' => $_G['uid'],
			'albumid' => -1,
			'albumname' => lang('space', 'default_albumname'),
			'picnum' => $count
		);
	}

		//require_once libfile('function/org'); 		
		 //路径按照实际情况修改 
		 // 根据用户的id获取该用户组织信息 
		// $org_id = getUserGroupByuserId($album['uid']); 
		 //取得省公司的名称
		// $provinceArray=getOrgById($org_id); 
		// $orgname = mb_convert_encoding($provinceArray[0]['name'], "UTF-8", "GBK");
		
		// $album['userorg'] = $orgname;	
		 
	$albumlist = array ();
	$maxalbum = $nowalbum = $key = 0;
	$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " WHERE fid='$_G[fid]' ORDER BY updatetime DESC LIMIT 0, 100");
	while ($value = DB :: fetch($query)) {
		if ($value['friend'] != 4 && ckfriend_group($value['uid'], $value['friend'], $value['target_ids'])) {
			$value['pic'] = pic_cover_get_group($value['pic'], $value['picflag']);
		}
		elseif ($value['picnum']) {
			$value['pic'] = STATICURL . 'image/common/nopublish.gif';
		} else {
			$value['pic'] = '';
		}
		$albumlist[$key][$value['albumid']] = $value;
		$key = count($albumlist[$key]) == 5 ? ++ $key : $key;
	}
	$maxalbum = count($albumlist);

	$list = array ();
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('group_pic') . " WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$value['pic'] = pic_get($value['filepath'], 'plugin_groupalbum2', $value['thumb'], $value['remote']);
			$list[] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, join_plugin_action2('index',array('id'=>$id))."#comment");

	$actives = array (
		'me' => ' class="a"'
	);

	$_G['home_css'] = 'album';
	
	$diymode = intval($_G['cookie']['groupalbum2_diymode']);
	
	include template("groupalbum2:album_view");
	
	dexit();
}
elseif ($picid) {

	$query = DB :: query("SELECT * FROM " . DB :: table('group_pic') . " WHERE picid='$picid' LIMIT 1");
	if (!$pic = DB :: fetch($query)) {
		showmessage('view_images_do_not_exist');
	}

	$picid = $pic['picid'];
	$theurl = join_plugin_action2('index',array('picid'=>$picid));

	$album = array ();
	if ($pic['albumid']) {
		$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " WHERE albumid='$pic[albumid]'");
		if (!$album = DB :: fetch($query)) {
			DB :: update('group_pic', array (
				'albumid' => 0
			), array (
				'albumid' => $pic['albumid']
			));
		}
	}

	if ($album) {
		ckfriend_album_group($album);
		$wheresql = "albumid='$pic[albumid]'";
	} else {
		$album['picnum'] = getcount('group_pic', array (
			'uid' => $pic['uid'],
			'albumid' => 0
		));
		$album['albumid'] = $pic['albumid'] = '-1';
		$wheresql = "uid='$_G[uid]' AND albumid='0'";
	}

	$piclist = $list = $keys = array ();
	$keycount = 0;
	$query = DB :: query("SELECT * FROM " . DB :: table('group_pic') . " WHERE $wheresql ORDER BY dateline DESC");
	while ($value = DB :: fetch($query)) {
		$keys[$value['picid']] = $keycount;
		$list[$keycount] = $value;
		$keycount++;
	}

	$upid = $nextid = 0;
	$nowkey = $keys[$picid];
	$endkey = $keycount -1;
	if ($endkey > 4) {
		$newkeys = array (
			$nowkey -2,
			$nowkey -1,
			$nowkey,
			$nowkey +1,
			$nowkey +2
		);
		if ($newkeys[1] < 0) {
			$newkeys[0] = $endkey -1;
			$newkeys[1] = $endkey;
		}
		elseif ($newkeys[0] < 0) {
			$newkeys[0] = $endkey;
		}
		if ($newkeys[3] > $endkey) {
			$newkeys[3] = 0;
			$newkeys[4] = 1;
		}
		elseif ($newkeys[4] > $endkey) {
			$newkeys[4] = 0;
		}
		$upid = $list[$newkeys[1]]['picid'];
		$nextid = $list[$newkeys[3]]['picid'];

		foreach ($newkeys as $nkey) {
			$piclist[] = $list[$nkey];
		}
	} else {
		$newkeys = array (
			$nowkey -1,
			$nowkey,
			$nowkey +1
		);
		if ($newkeys[0] < 0) {
			$newkeys[0] = $endkey;
		}
		if ($newkeys[2] > $endkey) {
			$newkeys[2] = 0;
		}
		$upid = $list[$newkeys[0]]['picid'];
		$nextid = $list[$newkeys[2]]['picid'];

		$piclist = $list;
	}
	foreach ($piclist as $key => $value) {
		$value['pic'] = pic_get($value['filepath'], 'plugin_groupalbum2', $value['thumb'], $value['remote']);
		$piclist[$key] = $value;
	}

	$pic['pic'] = pic_get($pic['filepath'], 'plugin_groupalbum2', $pic['thumb'], $pic['remote'], 0);
	$pic['size'] = formatsize($pic['size']);

	$exifs = array ();
	$allowexif = function_exists('exif_read_data');
	if (isset ($_GET['exif']) && $allowexif) {
		require_once libfile('function/exif');
		$exifs = getexif($pic['pic']);
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$cid = empty ($_GET['cid']) ? 0 : intval($_GET['cid']);
	$csql = $cid ? "cid='$cid' AND" : '';
	$siteurl = getsiteurl();
	$list = array ();
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$pic[picid]' AND idtype='gpicid'"), 0);
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$pic[picid]' AND idtype='gpicid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);

	if (empty ($album['albumname']))
		$album['albumname'] = lang('space', 'default_albumname');

	$pic_url = $pic['pic'];
	if (!preg_match("/^http\:\/\/.+?/i", $pic['pic'])) {
		$pic_url = getsiteurl() . $pic['pic'];
	}
	$pic_url2 = rawurlencode($pic['pic']);

	$hash = md5($pic['uid'] . "\t" . $pic['dateline']);
	$id = $pic['picid'];
	$idtype = 'gpicid';

	$maxclicknum = 0;
	loadcache('click');
	$clicks = empty ($_G['cache']['click']['blogid']) ? array () : $_G['cache']['click']['blogid'];
	$_G['cache']['click']['gpicid'] = $clicks;
	
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $pic["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if ($value['clicknum'] > $maxclicknum)
			$maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$clickuserlist = array ();
	$query = DB :: query("SELECT * FROM " . DB :: table('home_clickuser') . "
			WHERE id='$id' AND idtype='$idtype'
			ORDER BY dateline DESC
			LIMIT 0,20");
	while ($value = DB :: fetch($query)) {
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		$clickuserlist[] = $value;
	}

	$actives = array (
		'me' => ' class="a"'
	);

	if ($album['picnum']) {
		if ($_GET['goto'] == 'down') {
			$sequence = empty ($_G['cookie']['pic_sequence']) ? $album['picnum'] : intval($_G['cookie']['pic_sequence']);
			$sequence++;
			if ($sequence > $album['picnum']) {
				$sequence = 1;
			}
		}
		elseif ($_GET['goto'] == 'up') {
			$sequence = empty ($_G['cookie']['pic_sequence']) ? $album['picnum'] : intval($_G['cookie']['pic_sequence']);
			$sequence--;
			if ($sequence < 1) {
				$sequence = $album['picnum'];
			}
		} else {
			$sequence = 1;
		}
		dsetcookie('pic_sequence', $sequence);
	}
 	 
		require_once libfile('function/org'); 		
		 //路径按照实际情况修改 
		 // 根据用户的id获取该用户组织信息 
		// $org_id = getUserGroupByuserId($album['uid']); 
		 //取得省公司的名称
		// $provinceArray=getOrgById($org_id); 
		// $orgname = mb_convert_encoding($provinceArray[0]['name'], "UTF-8", "GBK");
		
		// $album['userorg'] = $orgname;	
	
	//feed_view_pic($picid);
	
	//查看经验值
	require_once libfile('function/group');
	group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'photo_view', $picid);
	
	//查看积分
	require_once libfile('function/credit');
	credit_create_credit_log($_G['uid'], 'viewpicture', $picid);

	$diymode = intval($_G['cookie']['groupalbum2_diymode']);

	include template("groupalbum2:album_pic");
	dexit();
} else {
//	loadcache('albumcategory');
//	$category = $_G['cache']['albumcategory'];

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$_GET['friend'] = intval($_GET['friend']);

	$default = array ();
	$f_index = '';
	$list = array ();
	$pricount = 0;
	$picmode = 0;

	$gets = array (
	);
	$theurl = join_plugin_action2('index');
	$actives = array (
		$_GET['view'] => ' class="a"'
	);

	$need_count = true;

	if ($_GET['from'] == 'space')
		$diymode = 1;

	$wheresql = "fid='$_G[fid]'";

	if ($need_count) {

		if ($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wheresql .= " AND albumname LIKE '%$searchkey%'";
		}


		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('group_album') . " WHERE $wheresql"), 0);

		if ($count) {
			$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " $f_index WHERE $wheresql ORDER BY updatetime DESC LIMIT $start,$perpage");
			while ($value = DB :: fetch($query)) {
				if ($value['friend'] != 4 && ckfriend_group($value['uid'], $value['friend'], $value['target_ids'])) {
					$value['pic'] = pic_cover_get_group($value['pic'], $value['picflag']);
				}
				elseif ($value['picnum']) {
					$value['pic'] = STATICURL . 'image/common/nopublish.gif';
				} else {
					$value['pic'] = '';
				}
				$list[] = $value;
			}
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);
}
?>
