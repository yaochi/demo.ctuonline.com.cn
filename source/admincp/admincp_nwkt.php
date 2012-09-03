<?php
/* Function: 你我课堂管理
 * Com.:
 * Author: wuhan
 * Date: 2010-7-23
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$detail = $_G['gp_detail'];
$uid = $_G['gp_uid'];
$nwktid = $_G['gp_nwktid'];
$users = $_G['gp_users'];
$keywords = $_G['gp_keywords'];
$lengthlimit = $_G['gp_lengthlimit'];
$viewnum1 = $_G['gp_viewnum1'];
$viewnum2 = $_G['gp_viewnum2'];
$replynum1 = $_G['gp_replynum1'];
$replynum2 = $_G['gp_replynum2'];
$hot1 = $_G['gp_hot1'];
$hot2 = $_G['gp_hot2'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$nwktids = $_G['gp_nwktids'];
$ppp = $_G['ppp'];

if(!submitcheck('nwktsubmit')) {

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

	shownav('topic', 'nav_nwkt');
	showsubmenusteps('nav_nwkt', array(
		array('nwkt_search', !$searchsubmit),
		array('nav_nwkt', $searchsubmit)
	));
	showtips('nwkt_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/forum_calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('nwktforum').page.value=number;
	$('nwktforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("nwkt", '', 'nwktforum');
	showhiddenfields(array('page' => $page));
	showtableheader();
	showsetting('nwkt_search_detail', 'detail', $detail, 'radio');
	showsetting('nwkt_search_uid', 'uid', $uid, 'text');
	showsetting('nwkt_search_nwktid', 'nwktid', $nwktid, 'text');
	showsetting('nwkt_search_user', 'users', $users, 'text');
	showsetting('nwkt_search_keyword', 'keywords', $keywords, 'text');
	showsetting('nwkt_search_lengthlimit', 'lengthlimit', $lengthlimit, 'text');
	showsetting('nwkt_search_view', array('viewnum1', 'viewnum2'), array('', ''), 'range');
	showsetting('nwkt_search_reply', array('replynum1', 'replynum2'), array('', ''), 'range');
	showsetting('nwkt_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$nwktids = authcode($nwktids, 'DECODE');
	$nwktidsadd = $nwktids ? explode(',', $nwktids) : $_G['gp_delete'];

	include_once libfile('function/nwkt');
	$deletecount = count(deletenwkts($nwktidsadd));
	$cpmsg = cplang('nwkt_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?=$cpmsg?>');parent.$('nwktforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit')) {

	$nwktids = $nwktcount = '0';
	$sql = $error = '';
	$keywords = trim($keywords);
	$users = trim($users);

	if($users != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_nwkt')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
		while($member = DB::fetch($query)) {
			$uids .= ",$member[uid]";
		}
		$sql .= " AND  uid IN ($uids)";
	}

	if($keywords != '') {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $keywords));

		for($i = 0; $i < count($keywords); $i++) {
			if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
				$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
				$sqlkeywords .= " $or subject REGEXP '".$keywords[$i]."' OR message REGEXP '".$keywords[$i]."'";
			} else {
				$sqlkeywords .= " $or subject LIKE '%".$keywords[$i]."%' OR message LIKE '%".$keywords[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if($lengthlimit != '') {
		$lengthlimit = intval($lengthlimit);
		$sql .= " AND LENGTH(message) < $lengthlimit";
	}

	if($starttime != '0') {
		$starttime = strtotime($starttime);
		$sql .= " AND dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '0') {
			$endtime = strtotime($endtime);
			$sql .= " AND dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	if($nwktid != '') {
		$nwktids = '-1';
		$query = DB::query("SELECT nwktid FROM ".DB::table('home_nwkt')." WHERE nwktid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $nwktid))."')");
		while($bidarr = DB::fetch($query)) {
			$nwktids .= ",$bidarr[nwktid]";
		}
		$sql .= " AND  nwktid IN ($nwktids)";
	}

	if($uid != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_nwkt')." WHERE uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $uid))."')");
		while($uidarr = DB::fetch($query)) {
			$uids .= ",$uidarr[uid]";
		}
		$sql .= " AND uid IN ($uids)";
	}

	$sql .= $viewnum1 ? " AND viewnum >= '$viewnum1'" : '';
	$sql .= $viewnum2 ? " AND viewnum <= '$viewnum2'" : '';
	$sql .= $replynum1 ? " AND replynum >= '$replynum1'" : '';
	$sql .= $replynum2 ? " AND replynum <= '$replynum2'" : '';
	$sql .= $hot1 ? " AND hot >= '$hot1'" : '';
	$sql .= $hot2 ? " AND hot <= '$hot2'" : '';

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'nwkt_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$pagetmp = $page;
			do{
				$query = DB::query("SELECT hot, replynum, viewnum, nwktid, uid, username, dateline, message, subject FROM ".DB::table('home_nwkt').
						" WHERE 1 $sql ORDER BY dateline DESC LIMIT ".(($pagetmp - 1) * $ppp).",{$ppp}");
				$pagetmp--;
			} while(!DB::num_rows($query) && $pagetmp);
			$nwkts = '';
			while($nwkt = DB::fetch($query)) {
				$nwkt['dateline'] = dgmdate($nwkt['dateline']);
				$nwkt['subject'] = cutstr($nwkt['subject'], 30);
				$nwkts .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$nwkt[nwktid]\" />",
					$nwkt['nwktid'],
					"<a href=\"home.php?mod=space&uid=$nwkt[uid]\" target=\"_blank\">$nwkt[username]</a>",
					"<a href=\"home.php?mod=space&uid=$nwkt[uid]&do=nwkt&id=$nwkt[nwktid]\" target=\"_blank\">$nwkt[subject]</a>",
					$nwkt['viewnum'],
					$nwkt['replynum'],
					$nwkt['dateline']
				), TRUE);
			}
			$nwktcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_nwkt')." WHERE 1 $sql");
			$multi = multi($nwktcount, $ppp, $page, ADMINSCRIPT."?action=nwkt");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=nwkt&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=nwkt&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$nwktcount = 0;
			$query = DB::query("SELECT nwktid FROM ".DB::table('home_nwkt')." WHERE 1 $sql");
			while($nwkt = DB::fetch($query)) {
				$nwktids .= ','.$nwkt['nwktid'];
				$nwktcount++;
			}
			$multi = '';
		}

		if(!$nwktcount) {
			$error = 'nwkt_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('nwkt&frame=no', 'target="nwktframe"');
	showhiddenfields(array('nwktids' => authcode($nwktids, 'ENCODE')));
	showtableheader(cplang('nwkt_result').' '.$nwktcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'nwktid', 'author', 'subject', 'view', 'reply', 'time'));
			echo $nwkts;
		}
	}

	showsubmit('nwktsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="nwktframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>
