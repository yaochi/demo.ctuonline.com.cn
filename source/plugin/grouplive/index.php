<?php
/* Function: 直播查询
 * Com.:
 * Author: wuhan
 * Date: 2010-7-23
 */

if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

if (empty ($_G['fid'])) {
	showmessage('group_rediret_now', 'group.php');
}

require_once libfile('function/home');

$minhot = $_G['setting']['feedhotmin'] < 1 ? 3 : $_G['setting']['feedhotmin'];
$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1)
	$page = 1;
$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);

if ($id) {
	$query = DB :: query("SELECT * FROM " . DB :: table('group_live') . " WHERE liveid='$id' AND fid='$_G[fid]'");
	$live = DB :: fetch($query);
	if (empty ($live)) {
		showmessage('view_to_info_did_not_exist');
	}
	
	$live['starttime'] = dgmdate($live['starttime']);
	$live['endtime'] = dgmdate($live['endtime']);
	
	if ($_G['uid'] != $live['uid'] && $_G['cookie']['view_liveid'] != $live['liveid']) {
		DB :: query("UPDATE " . DB :: table('group_live') . " SET viewnum=viewnum+1 WHERE liveid='$live[liveid]'");
		dsetcookie('view_liveid', $nwkt['liveid']);
	}

	include template("grouplive:live_view");
	dexit();
	
} else {
	$perpage = 20;
	$perpage = mob_perpage($perpage);
	$start = ($page -1) * $perpage;
	ckstart($start, $perpage);

	$summarylen = 300;

	$list = array ();
	$userlist = array ();
	$count = $pricount = 0;

	$gets = array (
		'orderby' => $_GET['orderby'],
		'orderseq' => $_GET['orderseq'],
		'typeid' => $_GET['typeid'],
	);
	$theurl = join_plugin_action2('index', $gets);
	
	$multi = '';

	$wheresql = "l.fid='$_G[fid]'";
	$f_index = '';
	$orderby = empty($_GET['orderby'])|| !in_array($_GET['orderby'], array('subject', 'st', 'starttime','playnum'))? 'starttime':$_GET['orderby'];
	$orderseq = $_GET['orderseq']? 'ASC' : 'DESC';
	
	$ordersql = "l.displayorder DESC, l.$orderby $orderseq, l.dateline DESC";
	
	$need_count = true;

	if ($need_count) {
		if($_GET['typeid']){
			$wheresql .= " AND l.typeid='$_GET[typeid]'";
		}
		
		if ($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wheresql .= " AND l.subject LIKE '%$searchkey%'";
		}

		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('group_live') . " l WHERE $wheresql"), 0);
		if ($count) {
			if($orderby == 'st'){
				$query = DB:: query("SELECT l.* FROM ((SELECT a.*, '2' as st FROM ".DB::table('group_live')." a WHERE a.starttime > '$_G[timestamp]') UNION (SELECT b.*, '1' as st FROM ".DB::table('group_live')." b WHERE b.starttime <= '$_G[timestamp]' AND b.endtime >= '$_G[timestamp]') UNION (SELECT c.*, '0' as st FROM ".DB::table('group_live')." c WHERE c.endtime < '$_G[timestamp]')) l WHERE $wheresql ORDER BY $ordersql LIMIT $start,$perpage");
			}else{
				$query = DB :: query("SELECT l.* FROM " . DB :: table('group_live') . " l $f_index WHERE $wheresql ORDER BY $ordersql LIMIT $start,$perpage");
			}
			
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
    $allowedittype = common_category_is_enable($_G['fid'], $pluginid);
    $allowprefix = common_category_is_prefix($_G['fid'], $pluginid);
    
    $categorys = array();
    if($allowedittype){
        $categorys = common_category_get_category($_G['fid'], $pluginid);
    }
}
?>