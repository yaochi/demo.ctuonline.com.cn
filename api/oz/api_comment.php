<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-8-10
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

$minhot = $_G['setting']['feedhotmin'] < 1 ? 3 : $_G['setting']['feedhotmin'];
$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1)
	$page = 1;
$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);
$comment_noreply = empty($_GET['disable']) ? 0 : intval($_GET['disable']);

$perpage = 20;
$perpage = mob_perpage($perpage);

$start = ($page-1)*$perpage;

ckstart($start, $perpage);
$fid=DB::result(DB::query("SELECT fid FROM ".DB::table('group_doc')." WHERE docid='$id'"),0);
if(!$fid){
	$fid=DB::result(DB::query("SELECT fid FROM ".DB::table('resourcelist')." WHERE resourceid='$id'"),0);
}
$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
$csql = $cid?"cid='$cid' AND":'';
$siteurl = getsiteurl();
$list = array();
$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='docid'"),0);
if($count) {
	$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='docid' ORDER BY dateline LIMIT $start,$perpage");
	while ($value = DB::fetch($query)) {
		$list[] = $value;
	}
}

$multi = multi($count, $perpage, $page, "ozapi.php?ac=comment&id=$id&type=$_GET[type]&disable=$comment_noreply");

include_once template("api/space_comment");
?>
