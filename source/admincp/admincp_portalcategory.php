<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_portalcategory.php 11435 2010-06-02 06:46:04Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('delete', 'move', 'perm')) ? $operation : 'list';

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

if($operation == 'list') {

	if(!submitcheck('editsubmit')) {

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
					array('list', 'portalcategory', 1)
				));

		showformheader('portalcategory');
		showtableheader();
		showsubtitle(array('', 'portalcategory_name', 'portalcategory_articles', 'operation'));
		foreach ($category as $key=>$value) {
			if($value['level'] == 0) {
				echo showcategoryrow($key, 0, '');
			}
		}
		echo '<tr><td>&nbsp;</td><td><div><a class="addtr" onclick="addcategory(this, 0, 0)" href="###">'.cplang('portalcategory_addcategory').'</a></div></td><td colspan="2">&nbsp;</td></tr>';
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

		$langs = array();
		$keys = array('portalcategory_addcategory', 'portalcategory_addsubcategory', 'portalcategory_addthirdcategory');
		foreach ($keys as $key) {
			$langs[$key] = cplang($key);
		}
		echo <<<SCRIPT
<script type="text/Javascript">
	function addcategory(obj, level, catid) {
		var table = obj.parentNode.parentNode.parentNode.parentNode;
		 if(level == 2) {
			var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex + 1);
		 } else {
			var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex);
		 }
		 cell = row.insertCell(0);
		 cell.innerHTML = "&nbsp;";
		 cell = row.insertCell(1);
		 cell.colSpan = 3;
		 var name = 'newname[' + catid + '][]';
		 if(level == 2) {
		 	cell.innerHTML = '<div class="childboard"><input type="text" class="txt" value="$lang[portalcategory_addthirdcategory]" name="' + name + '"/></div>';
		 } else if(level == 1) {
		 	cell.innerHTML = '<div class="board"><input type="text" class="txt" value="$lang[portalcategory_addsubcategory]" name="' + name + '"/></div>';
		 } else {
		 	cell.innerHTML = '<div class="parentboard"><input type="text" class="txt" value="$lang[portalcategory_addcategory]" name="' + name + '"/></div>';
		 }
	}
</script>
SCRIPT;

	} else {

		if($_POST['name']) {
			foreach($_POST['name'] as $key=>$value) {
				if($category[$key] && $category[$key]['catname'] != $value) {
					DB::query('UPDATE '.DB::table('portal_category')." SET catname = '$value' WHERE catid = '$key'");
				}
			}
		}
		if($_POST['newname']) {
			foreach ($_POST['newname'] as $upid=>$names) {
				foreach ($names as $name) {
					DB::insert('portal_category', array('upid' => $upid, 'catname' => $name));
				}
			}
		}

		include_once libfile('function/cache');
		updatecache('portalcategory');

		cpmsg('portalcategory_update_succeed', 'action=portalcategory', 'succeed');
	}

} elseif($operation == 'perm') {

	$catid = intval($_G['gp_catid']);
	if(!submitcheck('permsubmit')) {
		$category = DB::fetch_first('SELECT * FROM '.DB::table('portal_category')." WHERE catid='$catid'");
		shownav('portal', 'portalcategory');
		showsubmenu(cplang('portalcategory_perm_edit').' - '.$category['catname']);
		showformheader("portalcategory&operation=perm&catid=$catid");

		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'username',
		'<input class="checkbox" type="checkbox" name="chkallpublish" onclick="checkAll(\'prefix\', this.form, \'publish\', \'chkallpublish\')" id="chkallpublish" /><label for="chkallpublish"><br />'.cplang('portalcategory_perm_publish').'</label>',
		'<input class="checkbox" type="checkbox" name="chkallpush" onclick="checkAll(\'prefix\', this.form, \'push\', \'chkallpush\')" id="chkallpush" /><label for="chkallpush"><br />'.cplang('portalcategory_perm_push').'</label>',
		'<input class="checkbox" type="checkbox" name="chkallmanage" onclick="checkAll(\'prefix\', this.form, \'manage\', \'chkallmanage\')" id="chkallmanage" /><label for="chkallmanage"><br />'.cplang('portalcategory_perm_manage').'</label>'
		));

		$query = DB::query("SELECT * FROM ".DB::table('common_member')." m ,".DB::table('portal_category_permission')." cp WHERE cp.catid='$catid' AND cp.uid=m.uid");
		while($value = DB::fetch($query)) {
			showtablerow('', array('class="td25"'), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[$value[uid]]\" value=\"$value[uid]\" /><input type=\"hidden\" name=\"perm[$value[uid]]\" value=\"$value[catid]\" />",
				"$value[username]",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowpublish[$value[uid]]\" value=\"1\" ".($value['allowpublish'] ? 'checked' : '').' />',
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowpush[$value[uid]]\" value=\"1\" ".($value['allowpush'] ? 'checked' : '').' />',
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowmanage[$value[uid]]\" value=\"1\" ".($value['allowmanage'] ? 'checked' : '').' />',
			));
		}
		showtablerow('', array('class="td25"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newuser" value="" size="20" />',
			'<input type="checkbox" class="checkbox" name="newpublish" value="1" />',
			'<input type="checkbox" class="checkbox" name="newpush" value="1" />',
			'<input type="checkbox" class="checkbox" name="newmanage" value="1" />',
		));

		showsubmit('permsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
	} else {

		if(!empty($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('portal_category_permission')." WHERE catid='$catid' AND uid IN(".dimplode($_G['gp_delete']).")");
		} else {

		}
		$perms = array();
		if(is_array($_G['gp_perm'])) {
			foreach($_G['gp_perm'] as $uid => $value) {
				if(empty($_G['gp_delete']) || !in_array($uid, $_G['gp_delete'])) {
					$uid = intval($uid);
					$publish = $_G['gp_allowpublish'][$uid] ? 1 : 0;
					$push = $_G['gp_allowpush'][$uid] ? 1 : 0;
					$manage = $_G['gp_allowmanage'][$uid] ? 1 : 0;
					$perms[] = "('$catid', '$uid', '$publish', '$push', '$manage')";
				}
			}
		}
		if(!empty($_G['gp_newuser'])) {
			$uid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_newuser]'");
			if($uid) {
				$publish = $_G['gp_newpublish'] ? 1 : 0;
				$push = $_G['gp_newpush'] ? 1 : 0;
				$manage = $_G['gp_newmanage'] ? 1 : 0;
				$perms[] = "('$catid', '$uid', '$publish', '$push', '$manage')";
			}
		}
		if(!empty($perms)) {
			DB::query("REPLACE INTO ".DB::table('portal_category_permission')." (`catid`, `uid`, `allowpublish`, `allowpush`, `allowmanage`) VALUES ".implode(',', $perms));
		}

		$query = DB::query('SELECT DISTINCT(uid) FROM '.DB::table('portal_category_permission')." LIMIT 200");
		$uids = array();
		while($value=DB::fetch($query)) {
			$uids[] = intval($value['uid']);
		}
		DB::insert('common_setting', array('skey'=>'ext_portalmanager', 'svalue'=>addslashes(serialize($uids))), false, true);
		updatecache('setting');

		cpmsg('portalcategory_perm_update_succeed', "action=portalcategory&operation=perm&catid=$catid", 'succeed');
	}

} elseif($operation == 'delete') {

	if(!$_GET['catid'] || !$category[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}
	if(!submitcheck('deletesubmit')) {
		$article_count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_GET[catid]'");
		if(!$article_count && empty($category[$_GET[catid]]['children'])) {
			DB::query('DELETE FROM '.DB::table('portal_category')." WHERE catid = '$_GET[catid]'");
			cpmsg('portalcategory_delete_succeed', 'action=portalcategory', 'succeed');
		}

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
					array('list', 'portalcategory', 0),
					array('delete', 'portalcategory&operation=delete&catid='.$_GET['catid'], 1)
				));

		showformheader('portalcategory&operation=delete&catid='.$_GET['catid']);
		showtableheader();
		if($category[$_GET[catid]]['children']) {
			showsetting('portalcategory_subcategory_moveto', '', '',
				'<input type="radio" name="subcat_op" value="trash" id="subcat_op_trash" checked="checked" />'.
				'<label for="subcat_op_trash" />'.cplang('portalcategory_subcategory_moveto_trash').'</label>'.
				'<input type="radio" name="subcat_op" value="parent" id="subcat_op_parent" checked="checked" />'.
				'<label for="subcat_op_parent" />'.cplang('portalcategory_subcategory_moveto_parent').'</label>'
			);
		}
		showsetting('portalcategory_article_moveto', '', '', showmovetocategoryselect('tocatid', $category[$_GET['catid']]['upid']));
		showsubmit('deletesubmit', 'portalcategory_delete');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['tocatid'] == $_GET['catid'] || empty($category[$_POST['tocatid']])) {
			cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
		}
		$delids = array($_GET['catid']);
		if($category[$_GET['catid']]['children']) {
			if($_POST['subcat_op'] == 'parent') {
				$upid = intval($category[$_GET['catid']]['upid']);
				DB::query('UPDATE '.DB::table('portal_category')." SET upid = '$upid' WHERE catid IN (".dimplode($category[$_GET['catid']]['children']).')');
			} else {
				$delids = array_merge($delids, $category[$_GET['catid']]['children']);
				foreach ($category[$_GET['catid']]['children'] as $id) {
					$value = $category[$id];
					if($value['children']) {
						$delids = array_merge($delids, $value['children']);
					}
				}
				if(!$category[$_POST['tocatid']] || in_array($_POST['tocatid'], $delids)) {
					cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
				}
			}
		}
		if($delids) {
			DB::query('DELETE FROM '.DB::table('portal_category')." WHERE catid IN (".dimplode($delids).")");
			DB::query('UPDATE '.DB::table('portal_article_title')." SET catid = '$_POST[tocatid]' WHERE catid IN (".dimplode($delids).")");
			$num = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_POST[tocatid]'");
			DB::update('portal_category', array('articles'=>$num), array('catid'=>$_POST['tocatid']));
		}

		include_once libfile('function/cache');
		updatecache('portalcategory');

		cpmsg('portalcategory_delete_succeed', 'action=portalcategory', 'succeed');
	}

} elseif($operation == 'move') {

	if(!$_GET['catid'] || !$category[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}
	if(!submitcheck('movesubmit')) {
		$article_count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_GET[catid]'");
		if(!$article_count) {
			cpmsg('portalcategory_move_empty_error', 'action=portalcategory', 'succeed');
		}

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
					array('list', 'portalcategory', 0),
					array('portalcategory_move', 'portalcategory&operation=move&catid='.$_GET['catid'], 1)
				));

		showformheader('portalcategory&operation=move&catid='.$_GET['catid']);
		showtableheader();
		if($category[$_GET[catid]]['children']) {
			showsetting('portalcategory_subcategory_moveto', '', '',
				'<input type="radio" name="subcat_op" value="trash" id="subcat_op_trash" checked="checked" />'.
				'<label for="subcat_op_trash" />'.cplang('portalcategory_subcategory_moveto_trash').'</label>'.
				'<input type="radio" name="subcat_op" value="parent" id="subcat_op_parent" checked="checked" />'.
				'<label for="subcat_op_parent" />'.cplang('portalcategory_subcategory_moveto_parent').'</label>'
			);
		}
		showsetting('portalcategory_article_moveto', '', '', showmovetocategoryselect('tocatid', $category[$_GET['catid']]['upid']));
		showsubmit('movesubmit', 'portalcategory_move');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['tocatid'] == $_GET['catid'] || empty($category[$_POST['tocatid']])) {
			cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
		}

		DB::query('UPDATE '.DB::table('portal_article_title')." SET catid = '$_POST[tocatid]' WHERE catid ='$_GET[catid]'");
		DB::update('portal_category', array('articles'=>0), array('catid'=>$_GET['catid']));
		$num = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$_POST[tocatid]'");
		DB::update('portal_category', array('articles'=>$num), array('catid'=>$_POST['tocatid']));

		cpmsg('portalcategory_move_succeed', 'action=portalcategory', 'succeed');
	}
}

function showcategoryrow($key, $level = 0, $last = '') {
	global $category, $lang;

	$value = $category[$key];
	$return = '';

	if($level == 2) {
		$class = $last ? 'lastchildboard' : 'childboard';
		$return = '<tr class="hover"><td>&nbsp;</td><td><div class="'.$class.'">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.htmlspecialchars($value['catname']).'" class="txt" />'.
		'</div>'.
		'</td><td>'.$value[articles].'</td><td><a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;&nbsp;<a href="portal.php?mod=portalcp&ac=article&catid='.$value['catid'].'" target="_blank">'.cplang('article_add').'</a>&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_perm').'</a></td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td>&nbsp;</td><td><div class="board">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.htmlspecialchars($value['catname']).'" class="txt" />'.
		'<a class="addchildboard" onclick="addcategory(this, 2, '.$value['catid'].')" href="###">'.cplang('portalcategory_addthirdcategory').'</a></div>'.
		'</td><td>'.$value[articles].'</td><td><a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;&nbsp;<a href="portal.php?mod=portalcp&ac=article&catid='.$value['catid'].'" target="_blank">'.cplang('article_add').'</a>&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_perm').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i==$L-1);
		}
	} else {
		$return = '<tr class="hover"><td>&nbsp;</td><td><div class="parentboard">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.htmlspecialchars($value['catname']).'" class="txt" />'.
		'</div>'.
		'</td><td>'.$value[articles].'</td><td><a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;&nbsp;<a href="portal.php?mod=portalcp&ac=article&catid='.$value['catid'].'" target="_blank">'.cplang('article_add').'</a>&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_perm').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '<tr><td>&nbsp;</td><td colspan="3"><div class="lastboard"><a class="addtr" onclick="addcategory(this, 1, '.$value['catid'].')" href="###">'.cplang('portalcategory_addsubcategory').'</a></td></div>';
	}
	return $return;
}

function showmovetocategoryselect($name, $selid='0'){
	global $category;
	$select = "<select name=\"$name\">";
	foreach ($category as $value) {
		if($value['level'] == 0) {
			$selected = '';
			if($selid == $value['catid']) {
				$selected = ' selected="selected"';
			}
			$select .= "<option value=\"$value[catid]\"$selected>$value[catname]</option>";
			if(!$value['children']) {
				continue;
			}
			foreach ($value['children'] as $catid) {
				$selected = '';
				if($selid == $catid) {
					$selected = ' selected="selected"';
				}
				$select .= "<option value=\"{$category[$catid][catid]}\"$selected>-- {$category[$catid][catname]}</option>";
				if($category[$catid]['children']) {
					foreach ($category[$catid]['children'] as $catid2) {
						$selected = '';
						if($selid == $catid2) {
							$selected = ' selected="selected"';
						}
						$select .= "<option value=\"{$category[$catid2][catid]}\"$selected>---- {$category[$catid2][catname]}</option>";
					}
				}
			}
		}
	}
	$select .= "</select>";
	return $select;
}

?>