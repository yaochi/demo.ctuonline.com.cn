<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_group.php 11092 2010-05-21 09:42:01Z zhaoxiongfei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/home');
require_once libfile('function/post');

if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}
if(!$_G['setting']['search']['group']['status']) {
	showmessage('search_group_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 16)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$srchmod = 5;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$sdb = DB::object();

$srchtype = empty($_G['gp_srchtype']) ? '' : trim($_G['gp_srchtype']);
$checkarray = array('posts' => '', 'trade' => '', 'threadsort' => '');

$searchid = isset($_G['gp_searchid']) ? intval($_G['gp_searchid']) : 0;

$srchtxt = $_G['gp_srchtxt'];
$srchfid = intval($_G['gp_srchfid']);

$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/group');

} else {

	$orderby = in_array($_G['gp_orderby'], array('dateline', 'replies', 'views')) ? $_G['gp_orderby'] : 'lastpost';
	$ascdesc = isset($_G['gp_ascdesc']) && $_G['gp_ascdesc'] == 'asc' ? 'asc' : 'desc';

	if(!empty($searchid)) {

		require_once libfile('function/group');

		$page = max(1, intval($_G['gp_page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = $sdb->fetch_first("SELECT searchstring, keywords, num, ids FROM ".DB::table('common_searchindex')." WHERE searchid='$searchid' AND srchmod='$srchmod'");
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = htmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$searchstring = explode('|', $index['searchstring']);
		$srchfid = $searchstring[2];
		if($searchstring[2]) {
			$threadlist = array();
			require_once libfile('function/misc');
			$query = $sdb->query("SELECT t.*, f.name AS forumname FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_forum')." f ON t.fid=f.fid WHERE t.tid IN ($index[ids]) AND t.displayorder>='0' ORDER BY $orderby $ascdesc LIMIT $start_limit, $_G[tpp]");
			while($thread = $sdb->fetch_array($query)) {
				$thread['subject'] = bat_highlight($thread['subject'], $keyword);
				$threadlist[$thread['tid']] = procthread($thread);
			}
			if($threadlist) {
				$tids = implode(',', array_keys($threadlist));
				$query = $sdb->query("SELECT tid, message FROM ".DB::table('forum_post')." WHERE tid IN ($tids) AND first='1'");
				while($post = $sdb->fetch_array($query)) {
					$threadlist[$post['tid']]['message'] = bat_highlight(messagecutstr($post['message'], 200), $keyword);
				}
			}
		} else {
			$grouplist = array();
			$query = $sdb->query("SELECT f.*,ff.group_type, ff.description, ff.membernum, ff.icon,ff.jointype FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid WHERE f.fid IN ($index[ids]) AND f.status='3' AND `type`='sub' LIMIT $start_limit, $_G[tpp]");
			while($group = $sdb->fetch_array($query)) {
				$group['icon'] = get_groupimg($group['icon'], 'icon');
				$group['name'] = bat_highlight($group['name'], $keyword);
				$group['description'] = bat_highlight($group['description'], $keyword);
				if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
					$group['type_icn_id'] = 'brzone_s';
				} elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
					$group['type_icn_id'] = 'bizone_s';
				}
				$grouplist[] = $group;
			}
		}
		//print_r($grouplist);
		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=group&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=group&'.$_SERVER['QUERY_STRING'];

		include template('search/group');

	} else {

		$srchuname = isset($_G['gp_srchuname']) ? trim($_G['gp_srchuname']) : '';

		$searchstring = 'group|title|'.$srchfid.'|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		$query = $sdb->query("SELECT searchid, dateline,
			('".$_G['setting']['search']['group']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<'".$_G['setting']['search']['group']['searchctrl']."') AS flood,
			(searchstring='$searchstring' AND expiration>'$_G[timestamp]') AS indexvalid
			FROM ".DB::table('common_searchindex')."
			WHERE srchmod='$srchmod' AND ('".$_G['setting']['search']['group']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<".$_G['setting']['search']['group']['searchctrl'].") OR (searchstring='$searchstring' AND expiration>'$_G[timestamp]')
			ORDER BY flood");

		while($index = $sdb->fetch_array($query)) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=group', array('searchctrl' => $_G['setting']['search']['group']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname) {
				showmessage('search_invalid', 'search.php?mod=group');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['group']['maxspm']) {
				if(($sdb->result_first("SELECT COUNT(*) FROM ".DB::table('common_searchindex')." WHERE srchmod='$srchmod' AND dateline>'$_G[timestamp]'-60")) >= $_G['setting']['search']['group']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=group', array('maxspm' => $_G['setting']['search']['group']['maxspm']));
				}
			}

			$num = $ids = 0;
			$_G['setting']['search']['group']['maxsearchresults'] = $_G['setting']['search']['group']['maxsearchresults'] ? intval($_G['setting']['search']['group']['maxsearchresults']) : 500;

                        $srchtxtsql = addcslashes($srchtxt, '%_');
                        $search = explode(" ", $srchtxtsql);
                        $search = $search[0];
			if($srchfid) {
				$query = $sdb->query("SELECT tid FROM ".DB::table('forum_thread')." WHERE fid='$srchfid' AND isgroup='1' AND subject LIKE '%$search%' ORDER BY tid DESC LIMIT ".$_G['setting']['search']['group']['maxsearchresults']);
				while($thread = $sdb->fetch_array($query)) {
					$ids .= ','.$thread['tid'];
					$num++;
				}
				DB::free_result($query);
			} else {
				$query = $sdb->query("SELECT fid FROM ".DB::table('forum_forum')." WHERE `type`='sub' AND status='3' AND name LIKE '%$search%' LIMIT ".$_G['setting']['search']['group']['maxsearchresults']);
				while($group = $sdb->fetch_array($query)) {
					$ids .= ','.$group['fid'];
					$num++;
				}
			}

			$keywords = str_replace('%', '+', $srchtxt);
			$expiration = TIMESTAMP + $cachelife_text;

			DB::query("INSERT INTO ".DB::table('common_searchindex')." (srchmod, keywords, searchstring, useip, uid, dateline, expiration, num, ids)
					VALUES ('$srchmod', '$keywords', '$searchstring', '$_G[clientip]', '$_G[uid]', '$_G[timestamp]', '$expiration', '$num', '$ids')");
			$searchid = DB::insert_id();

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=group&searchid=$searchid&searchsubmit=yes");

	}

}

?>