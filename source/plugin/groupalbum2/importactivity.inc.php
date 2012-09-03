<?php
/* Function: 导入专区相册到活动
 * Com.:
 * Author: wuhan
 * Date: 2010-8-16
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once (dirname(__FILE__)."/function/function_groupalbum.php");
require_once libfile('function/home');

$_G['ismember'] = DB::result_first("SELECT 1 FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");

if($_GET['op'] == 'copy') {
	if(submitcheck('copysubmit')) {
		$albumids = $_POST['albumids'];
		if(empty($albumids)){
			showmessage('select_a_item', dreferer());
		}
		
		if(copy_album($albumids)){
			$_GET['plugin_op'] = 'groupmenu';
			showmessage('do_success', join_plugin_action2('index'));
		}
		else{
			showmessage('import_to_activity_failed', dreferer());
		}
	}
}
else{
    $fup = DB::result_first("SELECT fup FROM " . DB::table("forum_forum") . " WHERE fid = '$_G[fid]'");
	if(empty($fup)){
		showmessage('group_rediret_now', "forum.php?mod=activity&fid=$_G[fid]");
	}
	
	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)
		$page = 1;
	
	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$_GET['friend'] = intval($_GET['friend']);

	$default = array ();
	$f_index = '';
	$list = array ();
	$pricount = 0;
	$picmode = 0;

	$gets = array (
	);
	
	$plugin_op = $_GET['plugin_op'];
	$_GET['plugin_op'] = 'groupmenu';
	$theurl = join_plugin_action2('index');
	$_GET['plugin_op'] = $plugin_op;
	
	$actives = array (
		$_GET['view'] => ' class="a"'
	);

	$need_count = true;

	if ($_GET['from'] == 'space')
		$diymode = 1;

	$wheresql = "fid='$fup'";

	if ($need_count) {

		if ($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wheresql .= " AND albumname LIKE '%$searchkey%'";
		}

		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('group_album') . " WHERE $wheresql"), 0);

		if ($count) {
			$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " $f_index WHERE $wheresql ORDER BY updatetime DESC LIMIT $start,$perpage");
			while ($value = DB :: fetch($query)) {
				if ($value['friend'] != 4 && ckfriend_group($value['uid'], $value['friend'], $value['target_ids'])) {
					$value['pic'] = pic_cover_get_group($value['pic'], $value['picflag']);
				}
				elseif ($value['picnum']) {
					$value['pic'] = STATICURL . 'image/common/nopublish.gif';
				} else {
					$value['pic'] = '';
				}
				$value['realname'] = user_get_user_name($value['uid']);
				$list[] = $value;
			}
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);
}
 
?>
