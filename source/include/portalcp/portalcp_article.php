<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 10793 2010-05-17 01:52:12Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('addpage', 'edit', 'delpage', 'delete', 'related', 'batch', 'push')) ? $_GET['op'] : 'add';
$aid = intval($_G['gp_aid']);
$catid = intval($_G['gp_catid']);
$seccodecheck = $_G['setting']['seccodestatus'] & 4;
$secqaacheck = $_G['setting']['secqaa']['status'] & 2;

$article = $article_content = array();
if($aid) {
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$aid'");
	$article = DB::fetch($query);
	if(!$article) {
		showmessage('article_not_exist');
	}
}

if(submitcheck("articlesubmit", 0, $seccodecheck, $secqaacheck)) {

	if($aid) {
		check_articleperm($article['catid'],$aid);
	} else {
		check_articleperm($catid);
	}

	$_POST['title'] = getstr(trim($_POST['title']), 80, 1, 1, 1);
	if(strlen($_POST['title']) < 1) {
		showmessage('title_not_too_little');
	}

	if(empty($_POST['summary'])) $_POST['summary'] = preg_replace("/(\s|###NextPage###)+/", ' ', $_POST['content']);
	$summary = portalcp_get_summary($_POST['summary']);
	$prename = getstr(dhtmlspecialchars($_POST['prename']), 255, 1, 1, 1);

	$_G['gp_author'] = dhtmlspecialchars($_G['gp_author']);
	$_G['gp_from'] = dhtmlspecialchars($_G['gp_from']);
	$_G['gp_fromurl'] = dhtmlspecialchars($_G['gp_fromurl']);
	$_G['gp_shorttitle'] = getstr(trim(dhtmlspecialchars($_G['gp_shorttitle'])), 80, 1, 1, 1);
	$setarr = array(
		'title' => $_POST['title'],
		'shorttitle' => $_G['gp_shorttitle'],
		'author' => $_G['gp_author'],
		'from' => $_G['gp_from'],
		'fromurl' => $_G['gp_fromurl'],
		'url' => $_POST['url'],
		'summary' => $summary,
		'prename' => $prename,
		'preurl' => $_POST['preurl'],
		'catid' => intval($_POST['catid'])
	);

	if(empty($setarr['catid'])) {
		showmessage('article_choose_system_category');
	}

	if($_FILES['pic']) {
		if(($files = pic_upload($_FILES['pic'], 'portal', 300, 300, 2))) {
			$setarr['pic'] = $files['pic'];
			$setarr['thumb'] = $files['thumb'];
			$setarr['remote'] = $files['remote'];
		}
		if($setarr['pic'] && $article['pic']) {
			pic_delete($article['pic'], 'portal', $article['thumb'], $article['remote']);
		}
	}

	if(empty($article)) {
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['dateline'] = $_G['timestamp'];
		$setarr['id'] = intval($_POST['id']);
		if($setarr['id']) $setarr['idtype'] = $_POST['idtype']=='blogid'?'blogid':'tid';
		$aid = DB::insert('portal_article_title', $setarr, 1);

		DB::query('UPDATE '.DB::table('portal_category')." SET articles=articles+1 WHERE catid = '$setarr[catid]'");
	} else {
		DB::update('portal_article_title', $setarr, array('aid' => $aid));
	}

	$cid = intval($_POST['cid']);
	if($cid) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_content')." WHERE cid='$cid' AND aid='$aid'");
		$article_content = DB::fetch($query);
	}

	$content = getstr($_POST['content'], 0, 1, 1, 1, 0, 1);
	$contents = explode('###NextPage###', $content);
	$content_count = count($contents);

	$pageorder = intval($_POST['pageorder']);

	if($pageorder>0) {
		$startorder = $pageorder - 1;
		$pageorder = DB::result(DB::query("SELECT pageorder FROM ".DB::table('portal_article_content')." WHERE aid='$aid' ORDER BY pageorder LIMIT $startorder, 1"), 0);

		if($article_content && $article_content['pageorder'] == $pageorder) {
			$content_count = $content_count - 1;
		}
		if($content_count > 0) {
			DB::query('UPDATE '.DB::table('portal_article_content')." SET pageorder = pageorder+$content_count WHERE aid='$aid' AND pageorder>='$pageorder'");
		}
	} else {
		$pageorder = DB::result(DB::query("SELECT MAX(pageorder) FROM ".DB::table('portal_article_content')." WHERE aid='$aid'"), 0);
		$pageorder = $pageorder + 1;
	}

	if($article_content) {
		$setarr = array(
			'content' => trim($contents[0]),
			'pageorder' => $pageorder,
			'dateline' => $_G['timestamp']
		);
		DB::update('portal_article_content', $setarr, array('cid'=>$cid));
		unset($contents[0]);
	}

	if($contents) {
		$inserts = array();
		foreach ($contents as $key => $value) {
			$value = trim($value);
			$inserts[] = "('$aid', '$value', '".($pageorder+$key)."', '$_G[timestamp]')";
		}
		DB::query("INSERT INTO ".DB::table('portal_article_content')."
			(aid, content, pageorder, dateline)
			VALUES ".implode(',', $inserts));

		DB::query('UPDATE '.DB::table('portal_article_title')." SET contents = contents+".count($inserts)." WHERE aid='$aid'");
	}

	$newaids = array();
	$_POST['attach_ids'] = explode(',', $_POST['attach_ids']);
	foreach ($_POST['attach_ids'] as $newaid) {
		$newaid = intval($newaid);
		if($newaid) $newaids[$newaid] = $newaid;
	}
	if($newaids) {
		DB::update('portal_attachment', array('aid'=>$aid), "attachid IN (".dimplode($newaids).") AND aid='0'");
	}

	if($_POST['raids']) {
		DB::query("DELETE FROM ".DB::table('portal_article_related')." WHERE aid='$aid' OR raid='$aid'");

		$replaces = array();
		$query = DB::query("SELECT aid FROM ".DB::table('portal_article_title')." WHERE aid IN (".dimplode($_POST['raids']).")");
		while ($value = DB::fetch($query)) {
			if($value['aid'] != $aid) {
				$replaces[] = "('$aid', '$value[aid]')";
				$replaces[] = "('$value[aid]', '$aid')";
			}
		}
		if($replaces) {
			DB::query("REPLACE INTO ".DB::table('portal_article_related')." (aid,raid) VALUES ".implode(',', $replaces));
		}
	}

	if($_G['gp_from_idtype'] && $_G['gp_from_id']) {

		$id = intval($_G['gp_from_id']);
		$notify = array();
		switch ($_G['gp_from_idtype']) {
			case 'blogid':
				$blog = DB::fetch_first("SELECT * FROM ".DB::table('home_blog')." WHERE blogid='$id'");
				if(!empty($blog)) {
					$notify = array(
						'url' => "home.php?mod=space&do=blog&id=$id",
						'subject' => $blog['subject']
					);
					$touid = $blog['uid'];
				}
				break;
			case 'tid':
				$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$id'");
				if(!empty($thread)) {
					$notify = array(
						'url' => "forum.php?mod=viewthread&tid=$id",
						'subject' => $thread['subject']
					);
					$touid = $thread['authorid'];
				}
				break;
		}
		if(!empty($notify)) {
			$notify['newurl'] = 'portal.php?mod=view&aid='.$aid;
			notification_add($touid, 'pusearticle', 'puse_article', $notify, 1);
		}
	}


	if($_POST['addpage']) {
		$url = 'portal.php?mod=portalcp&ac=article&op=addpage&aid='.$aid;
	} else {
		$url = $_POST['url']?"portal.php?mod=list&catid=$_POST[catid]":'portal.php?mod=view&aid='.$aid;
	}
	showmessage('do_success', $url);

}

if ($op == 'delpage') {

	if(!$aid) {
		showmessage('article_edit_nopermission');
	}
	check_articleperm($article['catid'],$aid);


	$pageorder = intval($_GET['pageorder']);
	$aid = intval($_GET['aid']);
	$cid = intval($_GET['cid']);

	if($aid && $cid) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_content')." WHERE aid='$aid'"), 0);
		if($count > 1) {
			DB::query('DELETE FROM '.DB::table('portal_article_content')." WHERE cid='$cid' AND aid='$aid'");
			DB::query('UPDATE '.DB::table('portal_article_title')." SET contents = contents-1 WHERE aid='$aid'");
		} else {
			showmessage('article_delete_invalid_lastpage');
		}
	}
	showmessage('do_success', "portal.php?mod=portalcp&ac=article&op=edit&quickforward=1&aid=$aid");

} elseif($op == 'delete') {

	if(!$aid) {
		showmessage('article_edit_nopermission');
	}
	check_articleperm($article['catid'],$aid);

	if(submitcheck('deletesubmit')) {
		include_once libfile('function/delete');
		$article = deletearticle(array(intval($_POST['aid'])), intval($_POST['optype']));
		showmessage('article_delete_success', "portal.php?mod=list&catid={$article[0][catid]}");
	}

} elseif($op == 'related') {

	$raid = intval($_GET['raid']);
	$ra = array();
	if($raid) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$raid'");
		$ra = DB::fetch($query);
	}

} elseif($op == 'batch') {

	check_articleperm($catid);

	$aids = $_POST['aids'];
	$optype = $_POST['optype'];
	if(empty($optype) || $optype == 'push') showmessage('article_action_invalid');
	if(empty($aids)) showmessage('article_not_choose');

	if (submitcheck('batchsubmit')) {
		if ($optype == 'trash' || $optype == 'delete') {
				require_once libfile('function/delete');
				$istrash = $optype == 'trash' ? 1 : 0;
				$article = deletearticle($_POST['aids'], $istrash);
				showmessage('article_delete_success', "portal.php?mod=portalcp&ac=category&catid={$article[0][catid]}");
		}
	}

} elseif($op == 'push') {

	$articles = $aids = array();
	$_GET['aid'] = !empty($_GET['aid']) ? $_GET['aid'] : '';
	if($_GET['aid']) {
		$aids = explode(',', $_GET['aid']);
		$aids = array_map('intval', $aids);
		$query = DB::query('SELECT * FROM '.DB::table('portal_article_title')." WHERE aid IN (".dimplode($aids).')');
		$aids = array();
		while($value=DB::fetch($query)) {
			check_articleperm($value['catid'],$value['aid']);
			$aids[] = $value['aid'];
			$articles[] = $value;
		}
	}
	if(empty($articles)) {
		showmessage('article_invalid');
	}

	if(submitcheck('pushsubmit')) {

		$_POST['bid'] = intval($_POST['bid']);
		$block = DB::fetch_first('SELECT bid FROM '.DB::table('common_block')." WHERE bid = '$_POST[bid]'");
		if(empty($block)) {
			showmessage('article_push_category_invalid');
		}

		if (!checkperm('allowdiy')) {
			$check = DB::fetch_first('SELECT bid FROM '.DB::table('common_block_permission')." WHERE bid = '$_POST[bid]' AND uid='$_G[uid]' AND allowdata='1'");
			if(!$check) {
				showmessage('article_push_not_allowed');
			}
		}

		DB::query('UPDATE '.DB::table('portal_article_title')." SET bid='$_POST[bid]' WHERE aid IN (".dimplode($aids).')');
		$tourl = !empty($_POST['referer']) ? $_POST['referer'] : 'portal.php?mod=portalcp';
		showmessage('article_push_succeed', $tourl, array(), array('msgtype'=>2, 'showdialog'=>1, 'closetime'=>2));
	}

	include_once libfile('function/block');
	$wherearr = array();
	if(!empty($_GET['searchkey'])) {
		$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
		$wherearr[] = " b.name LIKE '%$_GET[searchkey]%'";
	}
	if($_G['from'] == 'push') {
		$wherearr[] = " b.blockclass='portal_article'";
	}
	$wheresql = empty($wherearr) ? '' : 'WHERE '.implode(' AND ', $wherearr);

	$page = !empty($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
	$perpage = 10;
	$start = ($page-1) * $perpage;

	$blocks = array();
	if($_G['allowdiy']) {
		$count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_block')." b $wheresql");
		$sql = 'SELECT b.* FROM '.DB::table('common_block')." b INNER JOIN ".DB::table('common_template_block')." tb ON bp.bid=tb.bid $wheresql LIMIT $start, $perpage";
	} else {
		$count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_block_permission')." WHERE uid='$_G[uid]' AND allowdata='1'");
		$sql = 'SELECT b.*, tb.targettplname FROM '.DB::table('common_block_permission')." bp LEFT JOIN ".DB::table('common_block')." b ON bp.bid = b.bid INNER JOIN ".DB::table('common_template_block')." tb ON bp.bid=tb.bid WHERE bp.uid='$_G[uid]' AND bp.allowdata='1' LIMIT $start, $perpage";
	}
	if($count) {
		$query = DB::query($sql);
		while($value=DB::fetch($query)) {
			$tplinfo = block_getdiyurl($value['targettplname']);
			$value['url'] = $tplinfo['url'];
			$blocks[] = $value;
		}
	}

} else {

	$language =  lang('portal/template');
	if($aid) {
		$catid = intval($article['catid']);
	}
	check_articleperm($catid, $aid);

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = $page-1;

	$pageselect = '';

	require_once libfile('function/portalcp');
	$category = getcategory();
	$cate = $category[$catid];

	if($article) {

		if($op == 'addpage') {
			$article_content = array();
		} else {
			$query = DB::query("SELECT * FROM ".DB::table('portal_article_content')." WHERE aid='$aid' ORDER BY pageorder LIMIT $start,1");
			$article_content = DB::fetch($query);
		}

		$article['attach_image'] = $article['attach_file'] = '';
		$query = DB::query("SELECT * FROM ".DB::table('portal_attachment')." WHERE aid='$aid' ORDER BY attachid");
		while ($value = DB::fetch($query)) {
			if($value['isimage']) {
				$article['attach_image'] .= get_uploadcontent($value);
			} else {
				$article['attach_file'] .= get_uploadcontent($value);
			}
		}

		if($article['contents'] > 0) {
			$pageselect = '<select name="pageorder">';
			$pageselect .= "<option value=\"0\">".lang('core','end')."</option>";
			for($i=1; $i<=$article['contents']; $i++) {
				$selected = ($op!='addpage' && $page == $i)?' selected':'';
				$pageselect .= "<option value=\"$i\"$selected>$i</option>";
			}
			$pageselect .= '</select>';
		}

		$multi = multi($article['contents'], 1, $page, "portal.php?mod=portalcp&ac=article&aid=$aid");

		if($article['pic']) {
			$article['pic'] = pic_get($article['pic'], 'portal', $article['thumb'], $article['remote'], 1);
		}

		$article['related'] = array();
		if($page < 2 && $op != 'addpage') {
			$query = DB::query("SELECT a.aid,a.title
				FROM ".DB::table('portal_article_related')." r
				LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=r.raid
				WHERE r.aid='$aid'");
			while ($value = DB::fetch($query)) {
				$article['related'][] = $value;
			}
		}
	}

	$_GET['from_id'] = empty ($_GET['from_id'])?0:intval($_GET['from_id']);
	if($_GET['from_idtype'] != 'blogid') $_GET['from_idtype'] = 'tid';

	$idtypes = array($_GET['from_idtype'] => ' selected');
	if($_GET['from_idtype'] && $_GET['from_id']) {

		$havepush = db::result(db::query("SELECT COUNT(*) FROM ".db::table('portal_article_title')." WHERE id='$_GET[from_id]' AND idtype='$_GET[from_idtype]'"), 0);
		if($havepush) {
			showmessage('article_push_invalid_repeat', '', array(), array('return'=>true));
		}

		switch ($_GET['from_idtype']) {
		case 'blogid':
			$query = DB::query("SELECT b.*, bf.message FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$_GET[from_id]'");
			if($blog = DB::fetch($query)) {
				if($blog['friend']) {
					showmessage('article_push_invalid_private');
				}
				$article['title'] = getstr($blog['subject'], 0);
				$article['summary'] = portalcp_get_summary($blog['message']);
				$article_content['content'] = dhtmlspecialchars($blog['message']);
			}
			break;
		default:
			$posttable = getposttablebytid($_GET['from_id']);
			$query = DB::query("SELECT t.*, p.* FROM ".DB::table('forum_thread')." t
				LEFT JOIN ".DB::table($posttable)." p ON p.tid=t.tid AND p.first='1'
				WHERE t.tid='$_GET[from_id]'");
			if($thread = DB::fetch($query)) {
				$article['title'] = $thread['subject'];

				$language = lang('forum/misc');
				$thread['message'] = preg_replace($language['post_edit_regexp'], '', $thread['message']);

				require_once libfile('function/discuzcode');
				$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff'], $thread['htmlon']);

				$article['summary'] = portalcp_get_summary($thread['message']);
				$article_content['content'] = dhtmlspecialchars($thread['message']);

				$query = DB::query("SELECT aid FROM ".DB::table('forum_attachment')." WHERE pid='$thread[pid]'");
				while($attach = DB::fetch($query)) {
					$attachcode = '[attach]'.$attach['aid'].'[/attach]';
					if(!strexists($article_content['content'], $attachcode)) {
						$article_content['content'] .= '<br /><br />'.$attachcode;
					}
				}
			}
			break;
		}
	}

}

include_once template("portal/portalcp_article");

function portalcp_get_summary($message) {
	$message = preg_replace(array("/\[attach\].*?\[\/attach\]/", "/\&[a-z]+\;/i"), '', $message);
	$message = preg_replace("/\[.*?\]/", '', $message);
	$message = getstr(strip_tags($message), 200);
	return $message;
}

function check_articleperm($catid,$aid=0) {
	global $_G;

	if(empty($catid) && empty($aid)) showmessage('article_category_empty');

	if($_G['group']['allowmanagearticle'] || (empty($aid) && $_G['group']['allowpostarticle'])) {
		return true;
	}

	$permission = getallowcategory($_G['uid']);
	if(isset($permission[$catid])) {
		if($permission[$catid]['allowmanage'] || (empty($aid) && $permission[$catid]['allowpublish'])) {
			return true;
		}
	}
	showmessage('article_edit_nopermission');
}

?>