<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blogcategory.php 11641 2010-06-10 05:01:56Z chenchunshao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation == 'delete' ? 'delete' : 'list';

$category = array();
$query = DB::query('SELECT * FROM '.DB::table('home_blog_category'));
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

		shownav('portal', 'blogcategory');
		showsubmenu('blogcategory',  array(
					array('list', 'blogcategory', 1)
				));

		showformheader('blogcategory');
		showtableheader();
		showsetting('system_category_stat', 'settingnew[blogcategorystat]', $_G['setting']['blogcategorystat'], 'radio', '', 1);
		showsetting('system_category_required', 'settingnew[blogcategoryrequired]', $_G['setting']['blogcategoryrequired'], 'radio', '');
		echo '<tr><td colspan="2">';
		showtableheader();
		showsubtitle(array('', 'blogcategory_name', 'blogcategory_num', 'operation'));
		foreach ($category as $key=>$value) {
			if($value['level'] == 0) {
				echo showcategoryrow($key, 0, '');
			}
		}
		echo '<tr><td>&nbsp;</td><td><div><a class="addtr" onclick="addcategory(this, 0, 0)" href="###">'.cplang('blogcategory_addcategory').'</a></div></td><td>&nbsp;</td></tr>';
		showtablefooter();
		echo '</td></tr>';

		showtableheader('', 'notop');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

		$langs = array();
		$keys = array('blogcategory_addcategory', 'blogcategory_addsubcategory', 'blogcategory_addthirdcategory');
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
		 cell.colSpan = 2;
		 var name = 'newname[' + catid + '][]';
		 if(level == 2) {
		 	cell.innerHTML = '<div class="childboard"><input type="text" class="txt" value="$lang[blogcategory_addthirdcategory]" name="' + name + '"/></div>';
		 } else if(level == 1) {
		 	cell.innerHTML = '<div class="board"><input type="text" class="txt" value="$lang[blogcategory_addsubcategory]" name="' + name + '"/></div>';
		 } else {
		 	cell.innerHTML = '<div class="parentboard"><input type="text" class="txt" value="$lang[blogcategory_addcategory]" name="' + name + '"/></div>';
		 }
	}
</script>
SCRIPT;

	} else {

		if($_POST['name']) {
			foreach($_POST['name'] as $key=>$value) {
				if($category[$key] && $category[$key]['catname'] != $value) {
					DB::query('UPDATE '.DB::table('home_blog_category')." SET catname = '$value' WHERE catid = '$key'");
				}
			}
		}
		if($_POST['newname']) {
			foreach ($_POST['newname'] as $upid=>$names) {
				foreach ($names as $name) {
					DB::insert('home_blog_category', array('upid' => $upid, 'catname' => $name));
				}
			}
		}
		$settings = array();
		foreach($_POST['settingnew'] as $key => $value) {
			$value = intval($value);
			$settings[] = "('$key', '$value')";
		}
		if($settings) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ".implode(',', $settings));
			updatecache('setting');
		}

		include_once libfile('function/cache');
		updatecache('blogcategory');

		cpmsg('blogcategory_update_succeed', 'action=blogcategory', 'succeed');
	}

} elseif($operation == 'delete') {

	if(!$_GET['catid'] || !$category[$_GET['catid']]) {
		cpmsg('blogcategory_catgory_not_found', '', 'error');
	}
	if(!submitcheck('deletesubmit')) {
		$blog_count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('home_blog')." WHERE catid = '$_GET[catid]'");
		if(!$blog_count && empty($category[$_GET[catid]]['children'])) {
			DB::query('DELETE FROM '.DB::table('home_blog_category')." WHERE catid = '$_GET[catid]'");
			cpmsg('blogcategory_delete_succeed', 'action=blogcategory', 'succeed');
		}

		shownav('portal', 'blogcategory');
		showsubmenu('blogcategory',  array(
					array('list', 'blogcategory', 0),
					array('delete', 'blogcategory&operation=delete&catid='.$_GET['catid'], 1)
				));

		showformheader('blogcategory&operation=delete&catid='.$_GET['catid']);
		showtableheader();
		if($category[$_GET[catid]]['children']) {
			showsetting('blogcategory_subcategory_moveto', '', '',
				'<input type="radio" name="subcat_op" value="trash" id="subcat_op_trash" checked="checked" />'.
				'<label for="subcat_op_trash" />'.cplang('blogcategory_subcategory_moveto_trash').'</label>'.
				'<input type="radio" name="subcat_op" value="parent" id="subcat_op_parent" checked="checked" />'.
				'<label for="subcat_op_parent" />'.cplang('blogcategory_subcategory_moveto_parent').'</label>'
			);
		}
		showsetting('blogcategory_blog_moveto', '', '', showmovetocategoryselect('tocatid', $category[$_GET['catid']]['upid']));
		showsubmit('deletesubmit');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['tocatid'] == $_GET['catid']) {
			cpmsg('blogcategory_move_category_failed', 'action=blogcategory', 'error');
		}
		$delids = array($_GET['catid']);
		if($category[$_GET['catid']]['children']) {
			if($_POST['subcat_op'] == 'parent') {
				$upid = intval($category[$_GET['catid']]['upid']);
				DB::query('UPDATE '.DB::table('home_blog_category')." SET upid = '$upid' WHERE catid IN (".dimplode($category[$_GET['catid']]['children']).')');
			} else {
				$delids = array_merge($delids, $category[$_GET['catid']]['children']);
				foreach ($category[$_GET['catid']]['children'] as $id) {
					$value = $category[$id];
					if($value['children']) {
						$delids = array_merge($delids, $value['children']);
					}
				}
				if(!$category[$_POST['tocatid']] || in_array($_POST['tocatid'], $delids)) {
					cpmsg('blogcategory_move_category_failed', 'action=blogcategory', 'error');
				}
			}
		}
		if($delids) {
			DB::query('DELETE FROM '.DB::table('home_blog_category')." WHERE catid IN (".dimplode($delids).")");
			DB::query('UPDATE '.DB::table('home_blog')." SET catid = '$_POST[tocatid]' WHERE catid IN (".dimplode($delids).")");
			$num = DB::result_first('SELECT COUNT(*) FROM '.DB::table('home_blog')." WHERE catid = '$_POST[tocatid]'");
			DB::update('home_blog_category', array('num'=>$num), array('catid'=>$_POST['tocatid']));
		}

		include_once libfile('function/cache');
		updatecache('blogcategory');

		cpmsg('blogcategory_delete_succeed', 'action=blogcategory', 'succeed');
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
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td>&nbsp;</td><td><div class="board">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.htmlspecialchars($value['catname']).'" class="txt" />'.
		'<a class="addchildboard" onclick="addcategory(this, 2, '.$value['catid'].')" href="###">'.cplang('blogcategory_addthirdcategory').'</a></div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i==$L-1);
		}
	} else {
		$return = '<tr class="hover"><td>&nbsp;</td><td><div class="parentboard">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.htmlspecialchars($value['catname']).'" class="txt" />'.
		'</div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '<tr><td>&nbsp;</td><td colspan="2"><div class="lastboard"><a class="addtr" onclick="addcategory(this, 1, '.$value['catid'].')" href="###">'.cplang('blogcategory_addsubcategory').'</a></div>';
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