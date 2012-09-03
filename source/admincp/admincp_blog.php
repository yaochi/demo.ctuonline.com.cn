<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blog.php 6944 2010-03-27 03:52:50Z chenchunshao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$detail = $_G['gp_detail'];
$uid = $_G['gp_uid'];
$blogid = $_G['gp_blogid'];
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
$blogids = $_G['gp_blogids'];
$ppp = $_G['ppp'];

if(!submitcheck('blogsubmit')) {

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

	shownav('topic', 'nav_blog');
	showsubmenusteps('nav_blog', array(
		array('blog_search', !$searchsubmit),
		array('nav_blog', $searchsubmit)
	));
	showtips('blog_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/forum_calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('blogforum').page.value=number;
	$('blogforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("blog", '', 'blogforum');
	showhiddenfields(array('page' => $page));
	showtableheader();
	showsetting('blog_search_detail', 'detail', $detail, 'radio');
	showsetting('blog_search_uid', 'uid', $uid, 'text');
	showsetting('blog_search_blogid', 'blogid', $blogid, 'text');
	showsetting('blog_search_user', 'users', $users, 'text');
	showsetting('blog_search_keyword', 'keywords', $keywords, 'text');
	showsetting('blog_search_lengthlimit', 'lengthlimit', $lengthlimit, 'text');
	showsetting('blog_search_view', array('viewnum1', 'viewnum2'), array('', ''), 'range');
	showsetting('blog_search_reply', array('replynum1', 'replynum2'), array('', ''), 'range');
	showsetting('blog_search_hot', array('hot1', 'hot2'), array('', ''), 'range');
	showsetting('blog_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {

	$blogids = authcode($blogids, 'DECODE');
	$blogidsadd = $blogids ? explode(',', $blogids) : $_G['gp_delete'];
	include_once libfile('function/delete');
	$deletecount = count(deleteblogs($blogidsadd));
	$cpmsg = cplang('blog_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?=$cpmsg?>');parent.$('blogforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit')) {

	$blogids = $blogcount = '0';
	$sql = $error = '';
	$keywords = trim($keywords);
	$users = trim($users);

	if($users != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_blog')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
		while($member = DB::fetch($query)) {
			$uids .= ",$member[uid]";
		}
		$sql .= " AND  b.uid IN ($uids)";
	}

	if($keywords != '') {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $keywords));

		for($i = 0; $i < count($keywords); $i++) {
			if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
				$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
				$sqlkeywords .= " $or b.subject REGEXP '".$keywords[$i]."' OR bf.message REGEXP '".$keywords[$i]."'";
			} else {
				$sqlkeywords .= " $or b.subject LIKE '%".$keywords[$i]."%' OR bf.message LIKE '%".$keywords[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if($lengthlimit != '') {
		$lengthlimit = intval($lengthlimit);
		$sql .= " AND LENGTH(bf.message) < $lengthlimit";
	}

	if($starttime != '0') {
		$starttime = strtotime($starttime);
		$sql .= " AND b.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '0') {
			$endtime = strtotime($endtime);
			$sql .= " AND b.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	if($blogid != '') {
		$blogids = '-1';
		$query = DB::query("SELECT blogid FROM ".DB::table('home_blog')." WHERE blogid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $blogid))."')");
		while($bidarr = DB::fetch($query)) {
			$blogids .= ",$bidarr[blogid]";
		}
		$sql .= " AND  b.blogid IN ($blogids)";
	}

	if($uid != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_blog')." WHERE uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $uid))."')");
		while($uidarr = DB::fetch($query)) {
			$uids .= ",$uidarr[uid]";
		}
		$sql .= " AND b.uid IN ($uids)";
	}

	$sql .= $viewnum1 ? " AND b.viewnum >= '$viewnum1'" : '';
	$sql .= $viewnum2 ? " AND b.viewnum <= '$viewnum2'" : '';
	$sql .= $replynum1 ? " AND b.replynum >= '$replynum1'" : '';
	$sql .= $replynum2 ? " AND b.replynum <= '$replynum2'" : '';
	$sql .= $hot1 ? " AND b.hot >= '$hot1'" : '';
	$sql .= $hot2 ? " AND b.hot <= '$hot2'" : '';

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'blog_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$pagetmp = $page;
			do{
				$query = DB::query("SELECT b.hot, b.replynum, b.viewnum, b.blogid, b.uid, b.username, b.dateline, bf.message, b.subject FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid) " .
						"WHERE 1 $sql ORDER BY b.dateline DESC LIMIT ".(($pagetmp - 1) * $ppp).",{$ppp}");
				$pagetmp--;
			} while(!DB::num_rows($query) && $pagetmp);
			$blogs = '';
			while($blog = DB::fetch($query)) {
				$blog['dateline'] = dgmdate($blog['dateline']);
				$blog['subject'] = cutstr($blog['subject'], 30);
				$blogs .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$blog[blogid]\" />",
					$blog['blogid'],
					"<a href=\"home.php?mod=space&uid=$blog[uid]\" target=\"_blank\">$blog[username]</a>",
					"<a href=\"home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]\" target=\"_blank\">$blog[subject]</a>",
					$blog['viewnum'],
					$blog['replynum'],
					$blog['hot'],
					$blog['dateline']
				), TRUE);
			}
			$blogcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid) WHERE 1 $sql");
			$multi = multi($blogcount, $ppp, $page, ADMINSCRIPT."?action=blog");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=blog&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=blog&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$blogcount = 0;
			$query = DB::query("SELECT b.blogid FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid) WHERE 1 $sql");
			while($blog = DB::fetch($query)) {
				$blogids .= ','.$blog['blogid'];
				$blogcount++;
			}
			$multi = '';
		}

		if(!$blogcount) {
			$error = 'blog_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('blog&frame=no', 'target="blogframe"');
	showhiddenfields(array('blogids' => authcode($blogids, 'ENCODE')));
	showtableheader(cplang('blog_result').' '.$blogcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'blogid', 'author', 'subject', 'view', 'reply', 'hot', 'time'));
			echo $blogs;
		}
	}

	showsubmit('blogsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="blogframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>