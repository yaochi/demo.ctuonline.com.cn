<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_prune.php 11546 2010-06-08 02:20:35Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$searchsubmit = $_G['gp_searchsubmit'];

require_once libfile('function/misc');
loadcache('forums');

if(!submitcheck('prunesubmit')) {

	require_once libfile('function/forumlist');

	if($_G['adminid'] == 1 || $_G['adminid'] == 2) {
		$forumselect = '<select name="forums"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

		if($_G['gp_forums']) {
			$forumselect = preg_replace("/(\<option value=\"$_G[gp_forums]\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}
	} else {
		$forumselect = $comma = '';
		$query = DB::query("SELECT f.name FROM ".DB::table('forum_moderator')." m, ".DB::table('forum_forum')." f WHERE m.uid='$_G[uid]' AND m.fid=f.fid");
		while($forum = DB::fetch($query)) {
			$forumselect .= $comma.$forum['name'];
			$comma = ', ';
		}
		$forumselect = $forumselect ? $forumselect : $lang['none'];
	}

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_G['gp_starttime']) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $_G['gp_starttime'];
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_G['gp_endtime']) ? dgmdate(TIMESTAMP, 'Y-n-j') : $_G['gp_endtime'];

	shownav('topic', 'nav_prune');
	showsubmenusteps('nav_prune', array(
		array('prune_search', !$searchsubmit),
		array('nav_prune', $searchsubmit)
	));
	showtips('prune_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/forum_calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('pruneforum').page.value=number;
	$('pruneforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("prune", '', 'pruneforum');
	showhiddenfields(array('page' => $page));
	showtableheader();
	showsetting('prune_search_detail', 'detail', $_G['gp_detail'], 'radio');
	showsetting('prune_search_forum', '', '', $forumselect);
	showsetting('prune_search_time', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
	showsetting('prune_search_user', 'users', $_G['gp_users'], 'text');
	showsetting('prune_search_ip', 'useip', $_G['gp_useip'], 'text');
	showsetting('prune_search_keyword', 'keywords', $_G['gp_keywords'], 'text');
	showsetting('prune_search_lengthlimit', 'lengthlimit', $_G['gp_lengthlimit'], 'text');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {

	$tidsdelete = $pidsdelete = '0';
	$pids = authcode($_G['gp_pids'], 'DECODE');
	$pidsadd = $pids ? 'pid IN ('.$pids.')' : 'pid IN ('.dimplode($_G['gp_pidarray']).')';

	$postarray = getfieldsofposts('fid, tid, pid, first, authorid', "$pidsadd");
	foreach($postarray as $post) {
		$prune['forums'][] = $post['fid'];
		$prune['thread'][$post['tid']]++;

		$pidsdelete .= ",$post[pid]";
		$tidsdelete .= $post['first'] ? ",$post[tid]" : '';
		if($post['first']) {
			my_thread_log('delete', array('tid' => $post['tid']));
		} else {
			my_post_log('delete', array('pid' => $post['pid']));
		}
	}

	if($pidsdelete) {
		require_once libfile('function/post');

		if(!$_G['gp_donotupdatemember']) {
			$postsarray = $tuidarray = $ruidarray = array();
			$postarray1 = getfieldsofposts('fid, pid, first, authorid', "pid IN ($pidsdelete)");
			$postarray2 = getfieldsofposts('fid, pid, first, authorid', "tid IN ($tidsdelete)");
			while((list($tmpkey, $post) = each($postarray1)) || (list($tmpkey, $post) = each($postarray2))) {
				$forumpostsarray[$post['fid']][$post['pid']] = $post;
			}
			foreach($forumpostsarray as $fid => $postsarray) {
				$tuidarray = $ruidarray = array();
				foreach($postsarray as $post) {
					if($post['first']) {
						$tuidarray[] = $post['authorid'];
					} else {
						$ruidarray[] = $post['authorid'];
					}
				}
				if($tuidarray) {
					updatepostcredits('-', $tuidarray, 'post', $fid);
				}
				if($ruidarray) {
					updatepostcredits('-', $ruidarray, 'reply', $fid);
				}
			}
		}

		require_once libfile('function/delete');
		$deletedposts = deletepost("pid IN ($pidsdelete)");
		$deletedposts += deletepost("tid IN ($tidsdelete)");
		$deletedthreads = deletethread("tid IN ($tidsdelete)");

		if(count($prune['thread']) < 50) {
			foreach($prune['thread'] as $tid => $decrease) {
				updatethreadcount($tid);
			}
		} else {
			$repliesarray = array();
			foreach($prune['thread'] as $tid => $decrease) {
				$repliesarray[$decrease][] = $tid;
			}
			foreach($repliesarray as $decrease => $tidarray) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies-$decrease WHERE tid IN (".implode(',', $tidarray).")");
			}
		}

		if($_G['setting']['globalstick']) {
			updatecache('globalstick');
		}

		foreach(array_unique($prune['forums']) as $fid) {
			updateforumcount($fid);
		}

	}

	$deletedthreads = intval($deletedthreads);
	$deletedposts = intval($deletedposts);
	updatemodworks('DLP', $deletedposts);
	$cpmsg = cplang('prune_succeed', array('deletedthreads' => $deletedthreads, 'deletedposts' => $deletedposts));

?>
<script type="text/JavaScript">alert('<?=$cpmsg?>');parent.$('pruneforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit')) {

	$pids = $postcount = '0';
	$sql = $error = '';

	$_G['gp_keywords'] = trim($_G['gp_keywords']);
	$_G['gp_users'] = trim($_G['gp_users']);
	if(($_G['gp_starttime'] == '0' && $_G['gp_endtime'] == '0') || ($_G['gp_keywords'] == '' && $_G['gp_useip'] == '' && $_G['gp_users'] == '')) {
		$error = 'prune_condition_invalid';
	}

	if($_G['adminid'] == 1 || $_G['adminid'] == 2) {
		if($_G['gp_forums']) {
			$sql .= " AND p.fid='{$_G['gp_forums']}'";
		}
	} else {
		$forums = '0';
		$query = DB::query("SELECT fid FROM ".DB::table('forum_moderator')." WHERE uid='$_G[uid]'");
		while($forum = DB::fetch($query)) {
			$forums .= ','.$forum['fid'];
		}
		$sql .= " AND p.fid IN ($forums)";
	}

	if($_G['gp_users'] != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $_G['gp_users']))."')");
		while($member = DB::fetch($query)) {
			$uids .= ",$member[uid]";
		}
		$sql .= " AND p.authorid IN ($uids)";
	}
	if($_G['gp_useip'] != '') {
		$sql .= " AND p.useip LIKE '".str_replace('*', '%', $useip)."'";
	}
	if($_G['gp_keywords'] != '') {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $_G['gp_keywords']));
		for($i = 0; $i < count($keywords); $i++) {
			if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
				$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
				$sqlkeywords .= " $or p.subject REGEXP '".$keywords[$i]."' OR p.message REGEXP '".$keywords[$i]."'";
			} else {
				$sqlkeywords .= " $or p.subject LIKE '%".$keywords[$i]."%' OR p.message LIKE '%".$keywords[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if($_G['gp_lengthlimit'] != '') {
		$lengthlimit = intval($_G['gp_lengthlimit']);
		$sql .= " AND LENGTH(p.message) < $lengthlimit";
	}

	if(!empty($_G['gp_starttime'])) {
		$starttime = strtotime($_G['gp_starttime']);
		$sql .= " AND p.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $_G['gp_endtime'] != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if(!empty($_G['gp_endtime'])) {
			$endtime = strtotime($_G['gp_endtime']);
			$sql .= " AND p.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}
	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'prune_mod_range_illegal';
	}

	if(!$error) {
		if($_G['gp_detail']) {
			$pagetmp = $page;
			do{
				$postarray = getallwithposts(array(
					'select' => 'p.fid, p.tid, p.pid, p.author, p.authorid, p.dateline, t.subject, p.message',
					'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t USING(tid)",
					'where' => "t.digest>=0 $sql",
					'limit' => ($pagetmp - 1) * $_G['setting']['postperpage'].", {$_G['setting']['postperpage']}"));
				$pagetmp--;
			} while(!count($postarray) && $pagetmp);
			$posts = '';
			foreach($postarray as $post) {
				$post['dateline'] = dgmdate($post['dateline']);
				$post['subject'] = cutstr($post['subject'], 30);
				$post['message'] = dhtmlspecialchars(cutstr($post['message'], 50));
				$posts .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"pidarray[]\" value=\"$post[pid]\" checked />",
					"<a href=\"forum.php?mod=redirect&goto=findpost&pid=$post[pid]&ptid=$post[tid]\" target=\"_blank\">$post[subject]</a>",
					$post['message'],
					"<a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\" target=\"_blank\">{$_G['cache'][forums][$post[fid]][name]}</a>",
					"<a href=\"home.php?mod=space&uid=$post[authorid]\" target=\"_blank\">$post[author]</a>",
					$post['dateline']
				), TRUE);
			}
			$postcount = getcountofposts(
				DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t USING(tid)",
				"t.digest>=0 $sql"
			);
			$multi = multi($postcount, $_G['setting']['postperpage'], $page, ADMINSCRIPT."?action=prune");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=prune&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=prune&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$postcount = 0;
			$postarray = getallwithposts(array(
				'select' => 'pid',
				'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t USING(tid)",
				'where' => "t.digest>=0 $sql",
			));
			foreach($postarray as $post) {
				$pids .= ','.$post['pid'];
				$postcount++;
			}
			$multi = '';
		}

		if(!$postcount) {
			$error = 'prune_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('prune&frame=no', 'target="pruneframe"');
	showhiddenfields(array('pids' => authcode($pids, 'ENCODE')));
	showtableheader(cplang('prune_result').' '.$postcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		cpmsg($error);
	} else {
		if($_G['gp_detail']) {
			showsubtitle(array('', 'subject', 'message', 'forum', 'author', 'time'));
			echo $posts;
		}
	}

	showsubmit('prunesubmit', 'submit', $_G['gp_detail'] ? '<input type="checkbox" name="chkall" id="chkall" class="checkbox" checked onclick="checkAll(\'prefix\', this.form, \'pidarray\')" /><label for="chkall">'.cplang('del').'</label>' : '',
		'<input class="checkbox" type="checkbox" name="donotupdatemember" id="donotupdatemember" value="1" checked="checked" /><label for="donotupdatemember"> '.cplang('prune_no_update_member').'</label>', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="pruneframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>