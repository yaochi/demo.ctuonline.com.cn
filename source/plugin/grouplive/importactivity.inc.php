<?php
/* Function: 导入专区直播到活动
 * Com.:
 * Author: wuhan
 * Date: 2010-8-16
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once (dirname(__FILE__)."/function/function_live.php");
require_once libfile('function/home');

$_G['ismember'] = DB::result_first("SELECT 1 FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");


if($_GET['op'] == 'copy') {
	if(submitcheck('copysubmit')) {
		$liveids = $_POST['liveids'];
		if(empty($liveids)){
			showmessage('select_a_item', dreferer());
		}
		
		if(copy_live($liveids)){
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

	$summarylen = 300;

	$list = array ();
	$userlist = array ();
	$count = $pricount = 0;

	$theurl = join_plugin_action2('index');
	$multi = '';

	$wheresql = "fid='$fup'";
	$f_index = '';
	$orderby = empty($_GET['orderby'])|| !in_array($_GET['orderby'], array('subject', 'st', 'starttime','playnum'))? 'starttime':$_GET['orderby'];
	$orderseq = $_GET['orderseq']? 'ASC' : 'DESC';
	
	$ordersql = "$orderby $orderseq, dateline DESC";
	
	$need_count = true;

	if ($need_count) {
		if($_GET['typeid']){
			$wheresql .= " AND typeid='$_GET[typeid]'";
		}
		
		if ($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wheresql .= " AND subject LIKE '%$searchkey%'";
		}

		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('group_live') . " WHERE $wheresql"), 0);
		if ($count) {
			$query = DB :: query("SELECT *, (starttime + endtime - 2 * $_G[timestamp]) as st FROM " . DB :: table('group_live') . " $f_index WHERE $wheresql ORDER BY $ordersql LIMIT $start,$perpage");
			
			include_once libfile('function/cache');
			
			while ($value = DB :: fetch($query)) {
				$value['dateline'] = dgmdate($value['dateline']);
				
				if(dgmdate($value['starttime'], 'Y-m-d') == dgmdate($value['endtime'], 'Y-m-d')){
					$value['time'] = dgmdate($value['starttime'])." - " .dgmdate($value['endtime'], 'H:i');
				}
				else{
					$value['time'] = dgmdate($value['starttime'])." - " .dgmdate($value['endtime']);
				}
				
				if($_G['timestamp'] < $value['starttime']){
					$value['status'] = '未开始';
				}
				elseif($_G['timestamp'] > $value['endtime']){
					$value['status'] = '已结束';
				}
				else{
					$value['status'] = '正在进行';
				}
				
				
				$value['highlight'] = parsehighlight($value['highlight']);
				
				$list[] = $value;
			}
		}
	}
	
	$multi = multi($count, $perpage, $page, $theurl);
	
	//分类
	require_once libfile("function/category");
    $pluginid = $_GET["plugin_name"];
    $allowedittype = common_category_is_enable($fup, $pluginid);
    $categorys = array();
    if($allowedittype){
        $categorys = common_category_get_category($fup, $pluginid);
    }
}
?>
