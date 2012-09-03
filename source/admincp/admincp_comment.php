<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_comment.php 6945 2010-03-27 03:53:28Z chenchunshao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = $_G['gp_detail'];
$idtype = $_G['gp_idtype'];
$id = $_G['gp_id'];
$author = $_G['gp_author'];
$authorid = $_G['gp_authorid'];
$uid = $_G['gp_uid'];
$message = $_G['gp_message'];
$ip = $_G['gp_ip'];
$users = $_G['gp_users'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$cids = $_G['gp_cids'];
$ppp = $_G['ppp'];

cpheader();

if(!submitcheck('commentsubmit')) {

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

	shownav('topic', 'nav_comment');
	showsubmenusteps('nav_comment', array(
		array('comment_search', !$searchsubmit),
		array('nav_comment', $searchsubmit)
	));
	showtips('comment_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/forum_calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('commentforum').page.value=number;
	$('commentforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("comment", '', 'commentforum');
	showhiddenfields(array('page' => $page));
	showtableheader();
	showsetting('comment_search_detail', 'detail', $detail, 'radio');
	showsetting('comment_idtype', array('idtype', array(
		array('',$lang['all']),
		array('uid',$lang['comment_uid']),
		array('blogid',$lang['comment_blogid']),
		array('picid',$lang['comment_picid']),
		array('eventid',$lang['comment_eventid']),
		array('sid',$lang['comment_sid']),
	)), 'comment_idtype', 'select', 'select');
	showsetting('comment_search_id', 'id', $id, 'text');
	showsetting('comment_search_author', 'author', $author, 'text');
	showsetting('comment_search_authorid', 'authorid', $authorid, 'text');
	showsetting('comment_search_uid', 'uid', $uid, 'text');
	showsetting('comment_search_message', 'message', $message, 'text');
	showsetting('comment_search_ip', 'ip', $ip, 'text');
	showsetting('comment_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$cids = authcode($cids, 'DECODE');
	$cidsadd = $cids ? explode(',', $cids) : $_G['gp_delete'];
	include_once libfile('function/delete');
	$deletecount = count(deletecomments($cidsadd));
	$cpmsg = cplang('comment_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?=$cpmsg?>');parent.$('commentforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit')) {

	$comments = $commentcount = '0';
	$sql = $error = '';
	$author = trim($author);

	if($idtype !='') {
		$query = DB::query("SELECT idtype FROM ".DB::table('home_comment')." WHERE idtype='$idtype'");
		$arr = DB::fetch($query);
		$idtype = $arr['idtype'];
		$sql .=" AND c.idtype='$idtype'";
	}

	if($id !='') {
		$ids = '-1';
		$query = DB::query("SELECT id FROM ".DB::table('home_comment')." WHERE id IN ('".str_replace(',', '\',\'', str_replace(' ', '', $id))."')");
		while($arr = DB::fetch($query)) {
			$ids .=",$arr[id]";
		}
		$sql .=" AND c.id IN ($ids)";
	}

	if($author != '') {
		$authorids = '-1';
		$query = DB::query("SELECT authorid FROM ".DB::table('home_comment')." WHERE author IN ('".str_replace(',', '\',\'', str_replace(' ', '', $author))."')");
		while($arr = DB::fetch($query)) {
			$authorids .= ",$arr[authorid]";
		}
		$sql .= " AND c.authorid IN ($authorids)";
	}

	if($authorid != '') {
		$authorids = '-1';
		$query = DB::query("SELECT authorid FROM ".DB::table('home_comment')." WHERE author IN ('".str_replace(',', '\',\'', str_replace(' ', '', $authorid))."')");
		while($arr = DB::fetch($query)) {
			$authorids .= ",$arr[authorid]";
		}
		$sql .= " AND c.authorid IN ($authorids)";
	}

	if($uid !='') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_comment')." WHERE uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $uid))."')");
		while($arr = DB::fetch($query)) {
			$uids .=",$arr[uid]";
		}
		$sql .=" AND c.uid IN ($uids)";
	}

	if($message != '') {
		$sqlmessage = '';
		$or = '';
		$message = explode(',', str_replace(' ', '', $message));

		for($i = 0; $i < count($message); $i++) {
			if(preg_match("/\{(\d+)\}/", $message[$i])) {
				$message[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($message[$i], '/'));
				$message .= " $or c.message REGEXP '".$message[$i]."'";
			} else {
				$sqlmessage .= " $or c.message LIKE '%".$message[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlmessage)";
	}

	if($ip != '') {
		$sql .= " AND c.ip LIKE '".str_replace('*', '%', $ip)."'";
	}

	if($starttime != '0') {
		$starttime = strtotime($starttime);
		$sql .= " AND c.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '0') {
			$endtime = strtotime($endtime);
			$sql .= " AND p.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'comment_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$pagetmp = $page;
			do{
				$query = DB::query("SELECT c.cid, c.uid, c.message, c.author, c.idtype, c.id, c.ip, c.dateline FROM ".DB::table('home_comment')." c  WHERE 1 $sql ORDER BY c.dateline DESC LIMIT ".(($pagetmp - 1) * $ppp).",{$ppp}");
				$pagetmp--;
			} while(!DB::num_rows($query) && $pagetmp);
			$comments = '';

			while($comment = DB::fetch($query)) {
				$comment['dateline'] = dgmdate($comment['dateline']);
				$comments .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$comment[cid]\" />",
					"<a href=\"home.php?mod=space&uid=$comment[uid]&do=blog&id=$comment[id]\" target=\"_blank\">$comment[message]</a>",
					"<a href=\"home.php?mod=space&uid=$comment[uid]\" target=\"_blank\">$comment[author]</a>",
					$comment['ip'],
					$comment['idtype'],
					$comment['dateline']
				), TRUE);
			}
			$commentcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_comment')." c WHERE 1 $sql");
			$multi = multi($commentcount, $ppp, $page, ADMINSCRIPT."?action=comment");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=comment&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=comment&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$commentcount = 0;
			$query = DB::query("SELECT c.cid FROM ".DB::table('home_comment')." c WHERE 1 $sql");
			while($comment = DB::fetch($query)) {
				$cids .= ','.$comment['cid'];
				$commentcount++;
			}
			$multi = '';
		}

		if(!$commentcount) {
			$error = 'comment_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('comment&frame=no', 'target="commentframe"');
	showhiddenfields(array('cids' => authcode($cids, 'ENCODE')));
	showtableheader(cplang('comment_result').' '.$commentcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'message', 'author', 'ip', 'comment_idtype', 'time'));
			echo $comments;
		}
	}

	showsubmit('commentsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="commentframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>