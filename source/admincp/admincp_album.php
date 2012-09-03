<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_album.php 7513 2010-04-08 02:04:15Z lifangming $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$detail = $_G['gp_detail'];
$albumname = $_G['gp_albumname'];
$albumid = $_G['gp_albumid'];
$uid = $_G['gp_uid'];
$users = $_G['gp_users'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$albumids = $_G['gp_albumids'];
$ppp = $_G['ppp'];

if(!submitcheck('albumsubmit')) {

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

	shownav('topic', 'nav_album');
	showsubmenusteps('nav_album', array(
		array('album_search', !$searchsubmit),
		array('nav_album', $searchsubmit)
	));
	showtips('album_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/forum_calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('albumforum').page.value=number;
	$('albumforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("album", '', 'albumforum');
	showhiddenfields(array('page' => $page));
	showtableheader();
	showsetting('album_search_detail', 'detail', $detail, 'radio');
	showsetting('album_search_albumname', 'albumname', $albumname, 'text');
	showsetting('album_search_albumid', 'albumid', $albumid, 'text');
	showsetting('album_search_uid', 'uid', $uid, 'text');
	showsetting('album_search_user', 'users', $users, 'text');
	showsetting('album_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$albumids = authcode($albumids, 'DECODE');
	$albumidsadd = $albumids ? explode(',', $albumids) : $_G['gp_delete'];
	include_once libfile('function/delete');
	$deletecount = count(deletealbums($albumidsadd));
	$cpmsg = cplang('album_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?=$cpmsg?>');parent.$('albumforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit')) {

	$albumids = $albumcount = '0';
	$sql = $error = '';
	$users = trim($users);

	if($users != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_album')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
		while($member = DB::fetch($query)) {
			$uids .= ",$member[uid]";
		}
		$sql .= " AND a.uid IN ($uids)";
	}

	if($albumname !='') {
		$query =DB::query("SELECT albumname FROM ".DB::table('home_album')." WHERE albumname='$albumname'");
		$arr = DB::fetch($query);
		$albumname = $arr['albumname'];
		$sql .= " AND a.albumname='$albumname'";
	}

	if($starttime != '0') {
		$starttime = strtotime($starttime);
		$sql .= " AND a.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '0') {
			$endtime = strtotime($endtime);
			$sql .= " AND a.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	if($albumid != '') {
		$albumids = '-1';
		$query = DB::query("SELECT albumid FROM ".DB::table('home_album')." WHERE albumid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $albumid))."')");
		while($arr = DB::fetch($query)) {
			$albumids .= ",$arr[albumid]";
		}
		$sql .= " AND a.albumid IN ($albumids)";
	}

	if($uid != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_album')." WHERE uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $uid))."')");
		while($uidarr = DB::fetch($query)) {
			$uids .= ",$uidarr[uid]";
		}
		$sql .= " AND a.uid IN ($uids)";
	}

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'album_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$pagetmp = $page;
			do{
				$query = DB::query("SELECT * FROM ".DB::table('home_album')." a WHERE 1 $sql ORDER BY a.updatetime DESC LIMIT ".(($pagetmp - 1) * $ppp).",{$ppp}");
				$pagetmp--;
			} while(!DB::num_rows($query) && $pagetmp);
			$albums = '';

			include_once libfile('function/home');
			while($album = DB::fetch($query)) {
				if($album['friend'] != 4 && ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
					$album['pic'] = pic_cover_get($album['pic'], $album['picflag']);
				} else {
					$album['pic'] = STATICURL.'image/common/nopublish.gif';
				}
				$album['updatetime'] = dgmdate($album['updatetime']);
				$albums .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$album[albumid]\" />",
					"<img src='$album[pic]' />",
					"<a href=\"home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]\" target=\"_blank\">$album[albumname]</a>",
					"<a href=\"home.php?mod=space&uid=$album[uid]\" target=\"_blank\">".$album['username']."</a>",
					$album['updatetime']
				), TRUE);
			}
			$albumcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_album')." a WHERE 1 $sql");
			$multi = multi($albumcount, $ppp, $page, ADMINSCRIPT."?action=album");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=album&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=album&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$albumcount = 0;
			$query = DB::query("SELECT a.albumid FROM ".DB::table('home_album')." a WHERE 1 $sql");
			while($album = DB::fetch($query)) {
				$albumids .= ','.$album['albumid'];
				$albumcount++;
			}
			$multi = '';
		}

		if(!$albumcount) {
			$error = 'album_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('album&frame=no', 'target="albumframe"');
	showhiddenfields(array('albumids' => authcode($albumids, 'ENCODE')));
	showtableheader(cplang('album_result').' '.$albumcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'albumpic', 'albumname', 'author', 'updatetime'));
			echo $albums;
		}
	}

	showsubmit('albumsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="albumframe" style="display:none"></iframe>';
	showtagfooter('div');

}
?>