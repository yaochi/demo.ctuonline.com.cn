<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_pic.php 11761 2010-06-12 08:49:36Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = $_G['gp_detail'];
$albumid = $_G['gp_albumid'];
$users = $_G['gp_users'];
$picid = $_G['gp_picid'];
$postip = $_G['gp_postip'];
$hot1 = $_G['gp_hot1'];
$hot2 = $_G['gp_hot2'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$picids = $_G['gp_picids'];
$ppp = $_G['ppp'];

cpheader();

if(!submitcheck('picsubmit')) {

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

	shownav('topic', 'nav_pic');
	showsubmenusteps('nav_pic', array(
		array('pic_search', !$searchsubmit),
		array('nav_pic', $searchsubmit)
	));
	showtips('pic_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/forum_calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('picforum').page.value=number;
	$('picforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("pic", '', 'picforum');
	showhiddenfields(array('page' => $page));
	showtableheader();
	showsetting('pic_search_detail', 'detail', $detail, 'radio');
	showsetting('pic_search_albumid', 'albumid', $albumid, 'text');
	showsetting('pic_search_user', 'users', $users, 'text');
	showsetting('pic_search_picid', 'picid', $picid, 'text');
	showsetting('pic_search_ip', 'postip', $postip, 'text');
	showsetting('pic_search_hot', array('hot1', 'hot2'), array('', ''), 'range');
	showsetting('pic_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$picids = authcode($picids, 'DECODE');
	$picidsadd = $picids ? explode(',', $picids) : $_G['gp_delete'];
	include_once libfile('function/delete');
	$deletecount = count(deletepics($picidsadd));
	$cpmsg = cplang('pic_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?=$cpmsg?>');parent.$('picforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit')) {

	$picids = $piccount = '0';
	$sql = $error = '';
	$users = trim($users);

	if($albumid !='') {
		$albumids = '-1';
		$query = DB::query("SELECT albumid FROM ".DB::table('home_album')." WHERE albumid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $albumid))."')");
		while($arr = DB::fetch($query)) {
			$albumids .=",$arr[albumid]";
		}
		$sql .=" AND p.albumid IN ($albumids)";
	}

	if($users != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_album')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
		while($arr = DB::fetch($query)) {
			$uids .= ",$arr[uid]";
		}
		$sql .= " AND p.uid IN ($uids)";
	}

	if($picid !='') {
		$picids = '-1';
		$query = DB::query("SELECT picid FROM ".DB::table('home_pic')." WHERE picid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $picid))."')");
		while($arr = DB::fetch($query)) {
			$picids .=",$arr[picid]";
		}
		$sql .=" AND p.picid IN ($picids)";
	}

	if($postip != '') {
		$sql .= " AND p.postip LIKE '".str_replace('*', '%', $postip)."'";
	}

	if($starttime != '0') {
		$starttime = strtotime($starttime);
		$sql .= " AND p.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '0') {
			$endtime = strtotime($endtime);
			$sql .= " AND p.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	$sql .= $hot1 ? " AND p.hot >= '$hot1'" : '';
	$sql .= $hot2 ? " AND p.hot <= '$hot2'" : '';

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'pic_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$pagetmp = $page;
			do{
				$query = DB::query("SELECT a.*, p.* FROM ".DB::table('home_pic')." p LEFT JOIN ".DB::table('home_album')." a USING(albumid) WHERE 1 $sql ORDER BY p.dateline DESC LIMIT ".(($pagetmp - 1) * $ppp).",{$ppp}");
				$pagetmp--;
			} while(!DB::num_rows($query) && $pagetmp);
			$pics = '';

			include_once libfile('function/home');
			while($pic = DB::fetch($query)) {
				$pic['dateline'] = dgmdate($pic['dateline']);
				$pic['pic'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
				$pic['albumname'] = empty($pic['albumname']) && empty($pic['albumid']) ? $lang['album_default'] : $pic['albumname'];
				$pic['albumid'] = empty($pic['albumid']) ? -1 : $pic['albumid'];
				$pics .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$pic[picid]\" />",
					"<a href='home.php?mod=space&uid=$pic[uid]&do=album&picid=$pic[picid]'  target='_blank'><img src='$pic[pic]'/></a>",
					$pic['size'],
					"<a href='home.php?mod=space&uid=$pic[uid]&do=album&id=$pic[albumid]'  target='_blank'>$pic[albumname]</a>",
					"<a href=\"home.php?mod=space&uid=$pic[uid]\" target=\"_blank\">".$pic['username']."</a>",
					$pic['dateline']
				), TRUE);
			}
			$piccount = DB::result_first("SELECT count(*) FROM ".DB::table('home_pic')." p WHERE 1 $sql");
			$multi = multi($piccount, $ppp, $page, ADMINSCRIPT."?action=pic");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=pic&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=pic&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$piccount = 0;
			$query = DB::query("SELECT p.picid FROM ".DB::table('home_pic')." p WHERE 1 $sql");
			while($pic = DB::fetch($query)) {
				$picids .= ','.$pic['picid'];
				$piccount++;
			}
			$multi = '';
		}

		if(!$piccount) {
			$error = 'pic_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('pic&frame=no', 'target="picframe"');
	showhiddenfields(array('picids' => authcode($picids, 'ENCODE')));
	showtableheader(cplang('pic_result').' '.$piccount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'albumpic', 'pic_size', 'albumname', 'author', 'time'));
			echo $pics;
		}
	}

	showsubmit('picsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="picframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>