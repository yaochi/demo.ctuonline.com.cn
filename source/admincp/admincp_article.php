<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_article.php 10946 2010-05-18 08:26:59Z xupeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('trash')) ? $operation : 'list';

$category = array();
$query = DB::query('SELECT * FROM '.DB::table('portal_category'));
while($value = DB::fetch($query)) {
	$category[$value[catid]] = $value;
}
foreach ($category as $key=>$value) {
	$upid = $value['upid'];
	if($upid) {
		$category[$upid]['children'][] = $key;
		$category[$key]['level'] = 1;
		while($category[$upid]) {
			$category[$key]['level'] += 1;
			$upid = $category[$upid]['upid'];
		}
	} else {
		$category[$key]['level'] = 0;
	}
}

if($operation == 'trash') {

	if(submitcheck('batchsubmit')) {
		if(empty($_POST['ids'])) {
			cpmsg('article_choose_at_least_one_article', 'action=article&operation=trash', 'error');
		}

		if($_POST['optype'] == 'recover') {

			$inserts = $ids = $catids = array();
			$query = DB::query('SELECT * FROM '.DB::table('portal_article_trash')." WHERE aid IN (".dimplode($_POST['ids']).")");
			while($value=DB::fetch($query)) {
				$ids[] = intval($value['aid']);
				$article = unserialize($value['content']);
				$catids[] = intval($article['catid']);
				$article = daddslashes($article);
				$inserts[] = "('$article[aid]', '$article[uid]', '$article[username]', '$article[title]', '$article[url]', '$article[pic]', '$article[prename]', '$article[preurl]', '$article[id]', '$article[idtype]', '$article[contents]', '$article[dateline]', '$article[catid]')";
			}

			if($inserts) {
				DB::query('REPLACE INTO '.DB::table('portal_article_title')."(aid, uid, username, title, url, pic, prename, preurl, id, idtype, contents, dateline, catid) VALUES ".implode(',',$inserts));
				DB::query('DELETE FROM '.DB::table('portal_article_trash')." WHERE aid IN (".dimplode($ids).")");
			}

			$catids = array_unique($catids);
			if($catids) {
				foreach($catids as $catid) {
					$cnt = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$catid'");
					DB::update('portal_category', array('articles'=>$cnt), array('catid'=>$catid));
				}
			}
			cpmsg('article_trash_recover_succeed', 'action=article&operation=trash', 'succeed');

		} elseif($_POST['optype'] == 'delete') {

			require_once libfile('function/delete');
			deletetrasharticle($_POST['ids']);
			cpmsg('article_trash_delete_succeed', 'action=article&operation=trash', 'succeed');
		} else {
			cpmsg('article_choose_at_least_one_operation', 'action=article&operation=trash', 'error');
		}

	} else {
		shownav('portal', 'article');
		showsubmenu('article',  array(
			array('list', 'article', 0),
			array('article_trash', 'article&operation=trash', 1),
			array('article_add', 'portal.php?mod=portalcp&ac=index', 0, 1, 1)
		));

		$perpage = 10;

		$start = ($page-1)*$perpage;

		$mpurl .= '&perpage='.$perpage;
		$perpages = array($perpage => ' selected');

		$mpurl = ADMINSCRIPT.'?mod=portal&action=article&operation='.$operation;

		showformheader('article&operation=trash');
		showtableheader('article_trash_list');
		showsubtitle(array('', 'article_title', 'article_category', 'article_username', 'article_dateline'));

		$multipage = '';
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_trash')." WHERE 1"), 0);
		if($count) {
			$query = DB::query("SELECT * FROM ".DB::table('portal_article_trash')." WHERE 1 LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$value = unserialize($value['content']);
				showtablerow('', array('class="td25"', 'class=""', 'class="td28"'), array(
						"<input type=\"checkbox\" class=\"checkbox\" name=\"ids[]\" value=\"$value[aid]\">",
						$value[title],
						$category[$value['catid']]['catname'],
						"<a href=\"home.php?mod=space&do=profile&uid=$value[uid]\" target=\"_blank\">$value[username]</a>",
						dgmdate($value[dateline])
					));
			}
			$multipage = multi($count, $perpage, $page, $mpurl);
		}

		$batchradio = '<input type="radio" name="optype" value="recover" id="op_recover" class="radio" /><label for="op_recover">'.cplang('article_trash_recover').'</label>&nbsp;&nbsp;';
		$batchradio .= '<input type="radio" name="optype" value="delete" id="op_delete" class="radio" /><label for="op_delete">'.cplang('article_trash_delete').'</label>&nbsp;&nbsp;';
		showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'.$batchradio.'<input type="submit" class="btn" name="batchsubmit" value="'.cplang('submit').'" />', $multipage);
		showtablefooter();
		showformfooter();
	}
} else {

	if(submitcheck('articlesubmit')) {

		$articles = $catids = array();
		$aids = !empty($_G['gp_ids']) && is_array($_G['gp_ids']) ? $_G['gp_ids'] : array();
		if($aids) {
			$query = DB::query('SELECT aid, catid FROM '.DB::table('portal_article_title')." WHERE aid IN (".dimplode($aids).')');
			while($value=DB::fetch($query)) {
				$articles[$value['aid']] = $value;
				$catids[] = intval($value['catid']);
			}
		}
		if(empty($articles)) {
			cpmsg('article_choose_at_least_one_article', 'action=article', 'error');
		}
		$aids = array_keys($articles);

		if($_POST['optype'] == 'trash') {
			require_once libfile('function/delete');
			deletearticle($aids, true);

			cpmsg('article_trash_succeed', 'action=article', 'succeed');

		} elseif($_POST['optype'] == 'move') {

			$tocatid = intval($_POST['tocatid']);
			$catids[] = $tocatid;
			$catids = array_merge($catids);
			DB::update('portal_article_title', array('catid'=>$tocatid), 'aid IN ('.dimplode($aids).')');
			foreach($catids as $catid) {
				$catid = intval($catid);
				$cnt = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$catid'");
				DB::update('portal_category', array('articles'=>intval($cnt)), array('catid'=>$catid));
			}
			cpmsg('article_move_succeed', 'action=article', 'succeed');

		} else {
			cpmsg('article_choose_at_least_one_operation', 'action=article', 'error');
		}

	} else {

		shownav('portal', 'article');
		showsubmenu('article',  array(
			array('list', 'article', $operation == 'list'),
			array('article_trash', 'article&operation=trash', $operation == 'trash'),
			array('article_add', 'portal.php?mod=portalcp&ac=index', false, 1, 1)
		));

		$mpurl = ADMINSCRIPT.'?action=article';

		$intkeys = array('aid', 'catid', 'uid');
		$strkeys = array();
		$randkeys = array();
		$likekeys = array('title', 'username');
		$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
		$wherearr = $results['wherearr'];
		$mpurl .= '&'.implode('&', $results['urls']);
		$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

		$orders = getorders(array('dateline'), 'aid');
		$ordersql = $orders['sql'];
		if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
		$orderby = array($_GET['orderby']=>' selected');
		$ordersc = array($_GET['ordersc']=>' selected');

		$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
		if(!in_array($perpage, array(10,20,50,100))) $perpage = 10;

		$categoryselect = showcategoryselect('catid');
		$searchlang = array();
		$keys = array('search', 'likesupport', 'resultsort', 'defaultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100',
		'article_dateline', 'article_id', 'article_title', 'article_uid', 'article_username', 'article_category');
		foreach ($keys as $key) {
			$searchlang[$key] = cplang($key);
		}
		$adminscript = ADMINSCRIPT;
		echo <<<SEARCH
		<form method="get" autocomplete="off" action="$adminscript">
			<div style="margin-top:8px;">
				<table cellspacing="3" cellpadding="3">
					<tr>
						<th>$searchlang[article_id]</th><td><input type="text" name="aid" value="$_GET[aid]"></td>
						<th>$searchlang[article_title]*</th><td><input type="text" name="title" value="$_GET[title]">*$searchlang[likesupport]</td>
					</tr>
					<tr>
						<th>$searchlang[article_uid]</th><td><input type="text" name="uid" value="$_GET[uid]"></td>
						<th>$searchlang[article_username]*</th><td><input type="text" name="username" value="$_GET[username]"></td>
					</tr>
					<tr>
						<th>$searchlang[article_category]</th><td>$categoryselect</td>
						<th>&nbsp;</td>
					</tr>
					<tr>
						<th>$searchlang[resultsort]</th>
						<td colspan="3">
							<select name="orderby">
							<option value="">$searchlang[defaultsort]</option>
							<option value="dateline"$orderby[dateline]>$searchlang[article_dateline]</option>
							</select>
							<select name="ordersc">
							<option value="desc"$ordersc[desc]>$searchlang[orderdesc]</option>
							<option value="asc"$ordersc[asc]>$searchlang[orderasc]</option>
							</select>
							<select name="perpage">
							<option value="10"$perpages[10]>$searchlang[perpage_10]</option>
							<option value="20"$perpages[20]>$searchlang[perpage_20]</option>
							<option value="50"$perpages[50]>$searchlang[perpage_50]</option>
							<option value="100"$perpages[100]>$searchlang[perpage_100]</option>
							</select>
							<input type="hidden" name="action" value="article">
							<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">
						</td>
					</tr>
				</table>
			</div>
		</form>
SEARCH;

		$start = ($page-1)*$perpage;

		$mpurl .= '&perpage='.$perpage;
		$perpages = array($perpage => ' selected');

		$mpurl = ADMINSCRIPT.'?mod=portal&action=article&operation='.$operation;

		showformheader('article&operation=list');
		showtableheader('article_list');
		showsubtitle(array('', 'article_title', 'article_category', 'article_username', 'article_dateline'));

		$multipage = '';
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_title')." WHERE $wheresql"), 0);
		if($count) {
			$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				showtablerow('', array('class="td25"', 'class=""', 'class="td28"'), array(
						"<input type=\"checkbox\" class=\"checkbox\" name=\"ids[]\" value=\"$value[aid]\">",
						"<a href=\"portal.php?mod=view&aid=$value[aid]\" target=\"_blank\">$value[title]</a>",
						$category[$value['catid']]['catname'],
						"<a href=\"home.php?mod=space&do=profile&uid=$value[uid]\" target=\"_blank\">$value[username]</a>",
						dgmdate($value[dateline])
					));
			}
			$multipage = multi($count, $perpage, $page, $mpurl);
		}

		$optypehtml = ''
			.'<input type="radio" name="optype" id="optype_trash" value="trash" class="radio" /><label for="optype_trash">'.cplang('article_optrash').'</label>&nbsp;&nbsp;'
			.'<input type="radio" name="optype" id="optype_move" value="move" class="radio" /><label for="optype_move">'.cplang('article_opmove').'</label> '
			.showcategoryselect('tocatid', false)
			.'&nbsp;&nbsp;';
		showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'.$optypehtml.'<input type="submit" class="btn" name="articlesubmit" value="'.cplang('submit').'" />', $multipage);
		showtablefooter();
		showformfooter();
	}
}

function showcategoryrow($key, $type = '', $last = '') {
	global $category, $lang;

	$forum = $forums[$key];
	$showedforums[] = $key;

	if($last == '') {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = '';
		if(!$forum['status']  || $forum['password'] || $forum['redirect']) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : $lang['forums_admin_hidden'];
			$boardattr .= !$forum['password'] ? '' : ' '.$lang['forums_admin_password'];
			$boardattr .= !$forum['redirect'] ? '' : ' '.$lang['forums_admin_url'];
			$boardattr .= '</div>';
		}

		$return .= '<input type="text" name="name['.$forum['fid'].']" value="'.htmlspecialchars($forum['name']).'" class="txt" />'.
			($type == '' ? '<a href="###" onclick="addrowdirect = 1;addrow(this, 2, '.$forum['fid'].')" class="addchildboard">'.$lang['forums_admin_add_sub'].'</a>' : '').
			'</div>'.$boardattr.
			'</td><td>'.showforum_moderators($forum).'</td>
			<td><a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'" title="'.$lang['forums_edit_comment'].'" class="act">'.$lang['edit'].'</a>'.
			($type != 'group' ? '<a href="'.ADMINSCRIPT.'?action=forums&operation=copy&source='.$forum['fid'].'" title="'.$lang['forums_copy_comment'].'" class="act">'.$lang['forums_copy'].'</a>' : '').
			'<a href="'.ADMINSCRIPT.'?action=forums&operation=delete&fid='.$forum['fid'].'" title="'.$lang['forums_delete_comment'].'" class="act">'.$lang['delete'].'</a></td></tr>';
	} else {
		if($last == 'lastboard') {
			$return = '<tr><td></td><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.$lang['forums_admin_add_forum'].'</a></div></td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['forums_admin_add_category'].'</a></div></td></tr>';
		}
	}

	return $return;
}

function update_categoryarticles($catid, $num) {
	global $category;
	$num = intval($num);
	$ids = array();
	while($category[$catid]) {
		$ids[] = $catid;
		$catid = $category[$catid]['upid'];
	}
	if($ids) {
		DB::query('UPDATE '.DB::table('portal_category')." SET articles = articles + ($num) WHERE catid IN (".dimplode($ids).")");
	}
}

function showcategoryselect($name, $shownolimit=true){
	global $category;
	$select = "<select name=\"$name\">";
	if($shownolimit) {
		$select .= '<option value="">'.cplang('nolimit').'</option>';
	}
	foreach ($category as $value) {
		if($value['level'] == 0) {
			$select .= "<option value=\"$value[catid]\">$value[catname]</option>";
			if(!$value['children']) {
				continue;
			}
			foreach ($value['children'] as $catid) {
				$select .= "<option value=\"{$category[$catid][catid]}\">-- {$category[$catid][catname]}</option>";
				if($category[$catid]['children']) {
					foreach ($category[$catid]['children'] as $catid2) {
						$select .= "<option value=\"{$category[$catid2][catid]}\">---- {$category[$catid2][catname]}</option>";
					}
				}
			}
		}
	}
	$select .= "</select>";
	return $select;
}

?>